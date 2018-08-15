<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message\Collection\Observer;

use Sirprize\Basecamp\Message\Collection;

/**
 * Abstract class to observe and print state changes of the observed messages
 */
abstract class Abstrakt
{

    abstract public function onStartSuccess(Collection $collection);
    abstract public function onStartError(Collection $collection);

    protected function _getOnStartSuccessMessage(Collection $collection)
    {
//        return "started message collection. found ".$collection->count()." messages";
    }

    protected function _getOnStartErrorMessage(Collection $collection)
    {
//        return "message collection could not be started";
    }

}
