<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\TodoItem\Entity\Observer;

use Sirprize\Basecamp\TodoItem\Entity;

/**
 * Abstract class to observe and print state changes of the observed todo-item
 */
abstract class Abstrakt
{

    abstract public function onCompleteSuccess(Entity $todoItem);
    abstract public function onUncompleteSuccess(Entity $todoItem);
    abstract public function onCreateSuccess(Entity $todoItem);
    abstract public function onLoadSuccess($xmlstring);
    abstract public function onUpdateSuccess(Entity $todoItem);
    abstract public function onCommentAddSuccess(Entity $todoItem);
    abstract public function onCommentsGetSuccess(Entity $todoItem);
    abstract public function onDeleteSuccess(Entity $todoItem);

    abstract public function onCompleteError(Entity $todoItem);
    abstract public function onUncompleteError(Entity $todoItem);
    abstract public function onCreateError(Entity $todoItem);
    abstract public function onUpdateError(Entity $todoItem);
    abstract public function onCommentAddError(Entity $todoItem);
    abstract public function onCommentsGetError(Entity $todoItem);
    abstract public function onDeleteError(Entity $todoItem);

    protected function _getOnCompleteSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " completed in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnUncompleteSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " uncompleted in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnCreateSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " created in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnLoadSuccessMessage($xmlstring)
    {
        $message  = "LOADED '".$xmlstring."'";
        return $message;
    }

    protected function _getOnUpdateSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " updated in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnCommentAddSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " comment added in todo-list '".$todoItem->getId()."'";
        return $message;
    }

    protected function _getOnCommentsGetSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " comments found in todo-list '".$todoItem->getId()."'";
        return $message;
    }

    protected function _getOnDeleteSuccessMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " deleted from todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnCompleteErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not be completed in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnUncompleteErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not be uncompleted in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnCreateErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not be created in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnUpdateErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not be updated in todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

    protected function _getOnCommentAddErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not add comment for todo-item '".$todoItem->getId()."'";
        return $message;
    }

    protected function _getOnCommentsGetErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not find comments for todo-item '".$todoItem->getId()."'";
        return $message;
    }

    protected function _getOnDeleteErrorMessage(Entity $todoItem)
    {
        $message  = "todo-item '".$todoItem->getId()."'";
        $message .= " could not be deleted from todo-list '".$todoItem->getTodoListId()."'";
        return $message;
    }

}
