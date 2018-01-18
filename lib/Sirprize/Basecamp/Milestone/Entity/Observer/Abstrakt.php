<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Milestone\Entity\Observer;

use Sirprize\Basecamp\Milestone\Entity;

/**
 * Abstract class to observe and print state changes of the observed milestone
 */
abstract class Abstrakt
{

    abstract public function onCompleteSuccess(Entity $milestone);
    abstract public function onUncompleteSuccess(Entity $milestone);
    abstract public function onCreateSuccess(Entity $milestone);
    abstract public function onLoadSuccess($xmlstring);
    abstract public function onUpdateSuccess(Entity $milestone);
    abstract public function onDeleteSuccess(Entity $milestone);

    abstract public function onCompleteError(Entity $milestone);
    abstract public function onUncompleteError(Entity $milestone);
    abstract public function onCreateError(Entity $milestone);
    abstract public function onUpdateError(Entity $milestone);
    abstract public function onDeleteError(Entity $milestone);

    protected function _getOnCompleteSuccessMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " completed in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnUncompleteSuccessMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " uncompleted in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnCreateSuccessMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " created in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnLoadSuccessMessage($xmlstring)
    {
        $message  = "milestone '".$xmlstring."'";
        return $message;
    }

    protected function _getOnUpdateSuccessMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " updated in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnDeleteSuccessMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " deleted from project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnCompleteErrorMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " could not be completed in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnUncompleteErrorMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " could not be uncompleted in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnCreateErrorMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " could not be created in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnUpdateErrorMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " could not be updated in project '".$milestone->getProjectId()."'";
        return $message;
    }

    protected function _getOnDeleteErrorMessage(Entity $milestone)
    {
        $message  = "milestone '".$milestone->getId()."'";
        $message .= " could not be deleted from project '".$milestone->getProjectId()."'";
        return $message;
    }

}
