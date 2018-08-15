<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */
 
namespace Sirprize\Basecamp\Cli\Message;

use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\Message\Collection as MessageCollection;
use Sirprize\Basecamp\Message\Entity\Observer\Stout;
use Sirprize\Basecamp\Message\Entity\Observer\Log;
use Sirprize\Basecamp\Cli\Message\Entity;

class Collection extends MessageCollection
{

    protected $_log = null;

    public function setLog(\Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    protected function _getLog()
    {
        if($this->_log === null)
        {
            throw new Exception('call setLog() before '.__METHOD__);
        }

        return $this->_log;
    }

    /**
     * Instantiate a new message entity
     *
     * @return \Sirprize\Basecamp\Cli\Message\Entity
     */
    public function getMessageInstance()
    {
        $messageObserverStout = new Stout();

        $messageObserverLog = new Log();
        $messageObserverLog->setLog($this->_getLog());

        $message = new Entity();
        $message
            ->setHttpClient($this->_getHttpClient())
            ->setService($this->_getService())
            ->attachObserver($messageObserverStout)
            ->attachObserver($messageObserverLog)
        ;

        return $message;
    }

}
