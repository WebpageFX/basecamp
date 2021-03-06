<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message\Entity\Observer;

use Sirprize\Basecamp\Message\Entity;
use Sirprize\Basecamp\Message\Entity\Observer\Abstrakt;

/**
 * Class to observe and print state changes of the observed message
 */
class Stout extends Abstrakt
{

    public function onLoadSuccess($xmlstring)
    {
//        print $this->_getOnLoadSuccessMessage($message)."\n";
    }

    public function onCommentsGetSuccess(Entity $message)
    {
//        print $this->_getOnCommentsGetSuccessMessage($message)."\n";
    }

    public function onCommentsGetError(Entity $message)
    {
//        print $this->_getOnCommentsGetErrorMessage($message)."\n";
    }

    public function onCreateSuccess(Entity $message)
    {
//        print $this->_getOnCreateSuccessMessage($message)."\n";
    }

    public function onCommentAddSuccess(Entity $message)
    {
//        print $this->_getOnCommentAddSuccessMessage($message)."\n";
    }

    public function onCreateError(Entity $message)
    {
//        print $this->_getOnCreateErrorMessage($message)."\n";
    }

    public function onCommentAddError(Entity $message)
    {
//        print $this->_getOnCommentAddErrorMessage($message)."\n";
    }

    public function onUntagSuccess(Entity $message)
    {
//        print $this->_getOnCommentAddSuccessMessage($message)."\n";
    }

    public function onUntagError(Entity $message)
    {
//        print $this->_getOnCreateErrorMessage($message)."\n";
    }

    public function onUpdateSuccess(Entity $message)
    {
//        print $this->_getOnUpdateSuccessMessage($message)."\n";
    }

    public function onUpdateError(Entity $message)
    {
//        print $this->_getOnUpdateErrorMessage($message)."\n";
    }
}
