<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message\Entity\Observer;

use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\Message\Entity;
use Sirprize\Basecamp\Message\Entity\Observer\Abstrakt;

/**
 * Class to observe and log state changes of an observed message
 */
class Log extends Abstrakt
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

    public function onLoadSuccess($xmlstring)
    {
        $this->_getLog()->info($this->_getOnLoadSuccessMessage($xmlstring));
    }

    public function onCommentsGetSuccess(Entity $message)
    {
        $this->_getLog()->info($this->_getOnCommentsGetSuccessMessage($message));
    }

    public function onCommentsGetError(Entity $message)
    {
        $this->_getLog()->err($this->_getOnCommentsGetErrorMessage($message));
    }

    public function onCreateSuccess(Entity $message)
    {
        $this->_getLog()->info($this->_getOnCreateSuccessMessage($message));
    }

    public function onCommentAddSuccess(Entity $message)
    {
        $this->_getLog()->info($this->_getOnCommentAddSuccessMessage($message));
    }

    public function onCreateError(Entity $message)
    {
        $this->_getLog()->err($this->_getOnCreateErrorMessage($message));
    }

    public function onCommentAddError(Entity $message)
    {
        $this->_getLog()->err($this->_getOnCommentAddErrorMessage($message));
    }
}
