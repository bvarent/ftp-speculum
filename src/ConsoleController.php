<?php

namespace Bvarent\FtpSpeculum;

use Zend\Mvc\Controller\AbstractConsoleController;

/**
 * This class controls actions called from the console (CLI).
 *
 * @author bvarent <r.arents@bva-auctions.com>
 */
class ConsoleController extends AbstractConsoleController
{
    
    public function mirrorAction()
    {
        $sm = $this->serviceLocator;
        $mirrorer = $sm->get(Mirrorer::class);
        /* @var $mirrorer Mirrorer */
        $mirrorer();
    }
}
