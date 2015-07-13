Ftp Speculum module
===================

Introduction
------------
Mirrors files between a local dir and a remote FTP location. Using lftp.

Usage
-----
* Edit the local config file. See [src/MirrorerOptions.php](src/MirrorerOptions.php)
  for an explanation of the options.
* Invoke the task via the console. (Perhaps using a cronjob.)
  `php public/index.php ftp-speculum`

ToDo
----
* Use a PSR logger.