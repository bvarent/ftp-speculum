<?php

namespace Bvarent\FtpSpeculum;

use InvalidArgumentException;
use Zend\Stdlib\AbstractOptions;

/**
 * Options for the Mirrorer.
 * 
 * @property string $source_dir E.g. /my/source/dir
 * @property string $target_url E.g. ftp://user:pass@host.tld/path/to/dir
 *  If the target directory ends with a slash (except the root), the source base name is appended to target directory name.
 * @property int $verbose Level 0-3
 * @property boolean $cygwin The command will be run in a cygwin environment.
 * @property array $options CLI options for the lftp mirror command. E.g. ['reverse' => true, 'include-glob' => '*.zip']
 * @property array $settings General settable variables for lftp. E.g. ['net:socket-bind-ipv4' => 'my.ip.add.re'].
 * 
 * @author bvarent <r.arents@bva-auctions.com>
 */
class MirrorerOptions extends AbstractOptions
{
    
    /**
     * The key in the Module config array by which these options can be found in ZF2 config.
     * @var string
     */
    const MODULE_CONFIG_SUBKEY = 'mirrorer';
    
    /**
     * @return array Default values for all options.
     */
    public static function defaults()
    {
        return [
            'source_dir' => null,
            'target_url' => null,
            'cygwin' => false,
            'options' => [
                'verbose' => 0,
            ],
            'settings' => [],
        ];
    }

    protected $sourceDir;
    
    protected function getSourceDir()
    {
        return $this->sourceDir;
    }

    protected function setSourceDir($val)
    {
        if (is_null($val)) {
            throw new InvalidArgumentException("Source dir must be specified.");
        }
        $this->sourceDir = (string)$val;
    }

    protected $targetUrl;
    
    protected function getTargetUrl()
    {
        return $this->targetUrl;
    }

    protected function setTargetUrl($val)
    {
        if (is_null($val)) {
            throw new InvalidArgumentException("Target URL must be specified.");
        }
        $this->targetUrl = (string)$val;
    }

    protected $verbose;
    
    protected function getVerbose()
    {
        return $this->verbose;
    }

    protected function setVerbose($val)
    {
        $this->verbose = max(0, (int)$val);
    }

    protected $cygwin;
    
    protected function getCygwin()
    {
        return $this->cygwin;
    }

    protected function setCygwin($val)
    {
        $this->cygwin = !!$val;
    }

    protected $options;
    
    protected function getOptions()
    {
        return $this->options;
    }

    protected function setOptions(array $val = [])
    {
        $this->options = $val;
    }

    protected $settings;
    
    protected function getSettings()
    {
        return $this->settings;
    }

    protected function setSettings(array $val = [])
    {
        $this->settings = $val;
    }

}
