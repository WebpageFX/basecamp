<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message\Entity\Observer;

use Sirprize\Basecamp\Message\Entity;

/**
 * Abstract class to observe and print state changes of the observed message
 */
abstract class Abstrakt
{

    abstract public function onLoadSuccess($xmlstring);
    abstract public function onCommentsGetSuccess(Entity $message);

    abstract public function onCommentsGetError(Entity $message);

    protected function _getOnLoadSuccessMessage($xmlstring)
    {
        $msg  = "LOADED '".$xmlstring."'";
        return $msg;
    }

    protected function _getOnCommentsGetSuccessMessage(Entity $message)
    {
        $msg  = "message '".$message->getId()."'";
        $msg .= " comments found";
        return $msg;
    }

    protected function _getOnCommentsGetErrorMessage(Entity $message)
    {
        $msg  = "message '".$message->getId()."'";
        $msg .= " could not find comments";
        return $msg;
    }

}
