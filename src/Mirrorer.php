<?php

namespace Bvarent\FtpSpeculum;

use InvalidArgumentException;
use RuntimeException;
use Zend\Uri\Uri;
use Zend\Uri\UriFactory;

/**
 * Mirrors a remote FTP directory with a local directory.
 *
 * @author bvarent <r.arents@bva-auctions.com>
 */
class Mirrorer
{
    
    /**
     * @var MirrorerOptions
     */
    protected $options;

    /**
     * @param MirrorerOptions $options
     */
    public function __construct(MirrorerOptions $options)
    {
        $this->options = $options;
    }
    
    public function __invoke()
    {
        static::verifyLftpBinary();
        
        // Get the commands to perform based on the given options.
        $commands = $this->createCommands();
        $commandSequence = implode("; ", $commands);
        // FIXME Fix escaping in Cygwin.
        if ($this->options->cygwin) {
            $commandSequence = "'" . $commandSequence . "'";
        }
        
        // Invoke the lftp command.
        $lftpCommand = 'lftp -c ' . $this->escapeShellArg($commandSequence) . "\n";
        if ($this->options->options['verbose'] > 2) {
            echo '>>>' . $lftpCommand . "\n";
        }
        $lftpProc = proc_open($lftpCommand, [
            0 => ['pipe', 'r'], // in
            1 => ['pipe', 'w'], // out
            2 => ['pipe', 'w'], // err
            ], $pipes);
        
        // Read the output streams and check for errors.
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        $exit = proc_close($lftpProc);
        
        // FIXME 'mirror' commands exits with 1 (> 0) if there were files to transfer.
        if ($exit > 1) {
            throw new RuntimeException(sprintf('lftp exited with non-zero code: %d. Error output: $s', $exit, $err));
        } elseif ($err) {
            throw new RuntimeException(sprintf('Error output from lftp: %s', $err));
        }
        
        // Print the lftp command's output.
        if ($this->options->options['verbose']) {
            echo $out;
        }
    }
    
    /**
     * Escapes a shell argument. Considers being in a Cygwin environment.
     * @param string $arg
     * @return string
     */
    public function escapeShellArg($arg)
    {
        if ($this->options->cygwin) {
            // FIXME Escape properly under cygwin?
            return $arg;
        } else {
            return escapeshellarg($arg);
        }
    }
    
    /**
     * Verifies that the lftp binary is available in the PATH of this system.
     * @throws RuntimeExeption
     */
    public static function verifyLftpBinary()
    {
        $whichLftp = proc_open('lftp -v', [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $err = stream_get_contents($pipes[2]);
        $exit = proc_close($whichLftp);
        
        if ($exit) {
            throw new RuntimeException(sprintf("The 'lftp' command returned an exit code <> 0: %d", $exit));
        } elseif (!empty($err)) {
            throw new RuntimeException(sprintf("The 'lftp' command threw an error: %s", $err));
        }
    }
    
    /**
     * Creates a sequence of commands to feed to lftp from this' options.
     * @return string[]
     * @throws InvalidArgumentException
     */
    protected function createCommands()
    {
        $commands = [];
        
        // Get the source and target.
        list($sourceDir, $targetPath) = $this->validateAndGetPaths();
        
        // Build the 'set' settings commands.
        $commands += $this->buildSetCommands();
        
        // Build the open connection command.
        $commands[] = 'open ' . $this->options->target_url;
        
        // Build the mirror command.
        $commands[] = $this->buildMirrorCmd($sourceDir, $targetPath);
        
        // End with an exit.
        $commands[] = 'exit';
        
        return $commands;
    }
    
    /**
     * Gets and validates the paths from this' options.
     * @return string[] [sourceDir, targetPath]
     * @throws InvalidArgumentException
     */
    protected function validateAndGetPaths()
    {
        // Get and validate paths.
        $uri = $this->parseFtpUri($this->options->target_url);
        $targetPath = $uri->getPath();
        if (empty($targetPath)) {
            $targetPath = '/';
        }
        if (!$this->options->cygwin) {
            $sourceDir = realpath($this->options->source_dir);
            if (!is_dir($sourceDir)) {
                throw new InvalidArgumentException(sprintf('Invalid source dir: %s', $sourceDir ?: $this->options->source_dir));
            }
        } else {
            // FIXME Verify source dir in Cygwin.
            $sourceDir = $this->options->source_dir;
        }
        
        return [$sourceDir, $targetPath];
    }
    
    /**
     * Builds 'set ...' commands from this' options.
     * @return string[]
     */
    protected function buildSetCommands()
    {
        $commands = [];
        foreach ($this->options->settings as $settingName => $settingValue) {
            if (is_bool($settingValue)) {
                $settingValue = $settingValue ? 'yes' : 'no';
            }
            $commands[] = "set {$settingName} {$settingValue}";
        }
        
        return $commands;
    }
    
    /**
     * Builds a 'mirror ...' command from this' options.
     * @param string $sourceDir 
     * @param string $targetPath
     * @return string
     */
    protected function buildMirrorCmd($sourceDir, $targetPath)
    {
        $mirrorCmd = 'mirror';
        
        // Add the CLI options.
        foreach ($this->options->options as $optionName => $optionValue) {
            if ($optionValue === false) {
                continue;
            }
            $mirrorCmd .= ' ' . ((strlen($optionName) === 1) ? '-' : '--') . $optionName;
            $mirrorCmd .= $optionValue === true ? '' : '=' . $this->escapeShellArg($optionValue);
        }
        
        // Source and target are semantically reversed if --reverse (or -R) is active.
        if (isset($this->options->options['R']) && $this->options->options['R']
                || isset($this->options->options['reverse']) && $this->options->options['reverse']) {
            $mirrorCmd .= ' ' . $this->escapeShellArg($sourceDir) . ' ' . $this->escapeShellArg($targetPath);
        } else {
            $mirrorCmd .= ' ' . $this->escapeShellArg($targetPath) . ' ' . $this->escapeShellArg($sourceDir);
        }
        
        return $mirrorCmd;
    }
    
    /**
     * Parses (and validates) an FTP URI for use in this context.
     * @param string $input E.g. ftp://user:pass@hostname/path
     * @throws InvalidArgumentException
     */
    protected function parseFtpUri($input)
    {
        $uri = UriFactory::factory($input, 'ftp');
        
        // Require ftp scheme.
        if ($uri->getScheme() !== 'ftp') {
            throw new InvalidArgumentException('FTP URI should have an ftp:// scheme.');
        }
        
        // Require a host.
        if (empty($uri->getHost())) {
            throw new InvalidArgumentException('FTP URI should contain a hostname.');
        }
        
        return $uri;
    }
}
