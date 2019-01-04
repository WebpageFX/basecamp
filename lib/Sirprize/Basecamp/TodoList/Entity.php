<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\TodoList;

use Sirprize\Basecamp\Id;
use Sirprize\Basecamp\Service;
use Sirprize\Basecamp\Response;
use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\TodoItem\Collection as TodoItemCollection;
use Sirprize\Basecamp\TodoList\Entity\Observer\Abstrakt;

/**
 * Represent and modify a todo-list
 */
class Entity
{

    const _COMPLETED_COUNT = 'completed-count';
    const _DESCRIPTION = 'description';
    const _ID = 'id';
    const _MILESTONE_ID = 'milestone-id';
    const _NAME = 'name';
    const _POSITION = 'position';
    const _PRIVATE = 'private';
    const _PROJECT_ID = 'project-id';
    const _TRACKED = 'tracked';
    const _UNCOMPLETED_COUNT = 'uncompleted-count';
    const _TODO_ITEMS = 'todo-items';
    const _COMPLETE = 'complete';

    protected $_service = null;
    protected $_httpClient = null;
    protected $_data = array();
    protected $_loaded = false;
    protected $_response = null;
    protected $_observers = array();
    protected $_templateId = null;
    protected $_todoItems = null;
    protected $_todoItemsLoaded = false;

    public function setService(Service $service)
    {
        $this->_service = $service;
        return $this;
    }

    public function setHttpClient(\Zend_Http_Client $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    /**
     * Get response object
     *
     * @return \Sirprize\Basecamp\Response|null
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Attach observer object
     *
     * @return \Sirprize\Basecamp\TodoList
     */
    public function attachObserver(Abstrakt $observer)
    {
        $exists = false;

        foreach(array_keys($this->_observers) as $key)
        {
            if($observer === $this->_observers[$key])
            {
                $exists = true;
                break;
            }
        }

        if(!$exists)
        {
            $this->_observers[] = $observer;
        }

        return $this;
    }

    /**
     * Detach observer object
     *
     * @return \Sirprize\Basecamp\TodoList
     */
    public function detachObserver(Abstrakt $observer)
    {
        foreach(array_keys($this->_observers) as $key)
        {
            if($observer === $this->_observers[$key])
            {
                unset($this->_observers[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Set todo-list template id to use when later calling create()
     *
     * @param \Sirprize\Basecamp\Id $templateId If this id is set then setName() is optional on create()
     */
    public function setTemplateId(Id $templateId)
    {
        $this->_templateId = $templateId;
        return $this;
    }

    public function getTemplateId()
    {
        return $this->_templateId;
    }

    public function setName($name)
    {
        $this->_data[self::_NAME] = $name;
        return $this;
    }

    public function setProjectId(Id $projectId)
    {
        $this->_data[self::_PROJECT_ID] = $projectId;
        return $this;
    }

    public function setDescription($description)
    {
        $this->_data[self::_DESCRIPTION] = $description;
        return $this;
    }

    public function setMilestoneId(Id $milestoneId)
    {
        $this->_data[self::_MILESTONE_ID] = $milestoneId;
        return $this;
    }

    public function setIsPrivate($private)
    {
        $this->_data[self::_PRIVATE] = $private;
        return $this;
    }

    public function setIsTracked($tracked)
    {
        $this->_data[self::_TRACKED] = $tracked;
        return $this;
    }

    public function setTodoItems(TodoItemCollection $todoItems)
    {
        $this->_todoItems = $todoItems;
        return $this;
    }

    public function getCompletedCount()
    {
        return $this->_getVal(self::_COMPLETED_COUNT);
    }

    public function getDescription()
    {
        return $this->_getVal(self::_DESCRIPTION);
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getId()
    {
        return $this->_getVal(self::_ID);
    }

    /**
     * @return \Sirprize\Basecamp\Id|null (if this list is not assigned to a milestone)
     */
    public function getMilestoneId()
    {
        return $this->_getVal(self::_MILESTONE_ID);
    }

    public function getName()
    {
        return $this->_getVal(self::_NAME);
    }

    public function getPosition()
    {
        return $this->_getVal(self::_POSITION);
    }

    public function getIsPrivate()
    {
        return $this->_getVal(self::_PRIVATE);
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getProjectId()
    {
        return $this->_getVal(self::_PROJECT_ID);
    }

    public function getIsTracked()
    {
        return $this->_getVal(self::_TRACKED);
    }

    public function getUncompletedCount()
    {
        return $this->_getVal(self::_UNCOMPLETED_COUNT);
    }

    /**
     * @return \Sirprize\Basecamp\TodoItems\Collection
     */
    public function getTodoItems()
    {
        if($this->_todoItems === null)
        {
            $this->_todoItems = $this->_getService()->getTodoItemsInstance();
        }

        return $this->_todoItems;
    }

    public function getIsComplete()
    {
        return $this->_getVal(self::_COMPLETE);
    }

    /**
     * Load data returned from an api request
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return \Sirprize\Basecamp\TodoList
     */
    public function load(\SimpleXMLElement $xml, $force = false)
    {
        if($this->_loaded && !$force)
        {
            throw new Exception('entity has already been loaded');
        }

        $this->_loaded = true;
        $array = (array) $xml;

        if(isset($array[self::_TODO_ITEMS]))
        {
            $this->getTodoItems()->load($array[self::_TODO_ITEMS]);
            $this->_todoItemsLoaded = true;
        }

        $id = new Id($array[self::_ID]);

        $projectId = new Id($array[self::_PROJECT_ID]);

        $milestoneId
            = ($array[self::_MILESTONE_ID] != '')
            ? new Id($array[self::_MILESTONE_ID])
            : null
        ;

        $private = ($array[self::_PRIVATE] == 'true');
        $tracked = ($array[self::_TRACKED] == 'true');
        $complete = ($array[self::_COMPLETE] == 'true');

        $this->_data = array(
            self::_COMPLETED_COUNT => $array[self::_COMPLETED_COUNT],
            self::_DESCRIPTION => $array[self::_DESCRIPTION],
            self::_ID => $id,
            self::_MILESTONE_ID => $milestoneId,
            self::_NAME => $array[self::_NAME],
            self::_POSITION => $array[self::_POSITION],
            self::_PRIVATE => $private,
            self::_PROJECT_ID => $projectId,
            self::_TRACKED => $tracked,
            self::_UNCOMPLETED_COUNT => $array[self::_UNCOMPLETED_COUNT],
            #self::_TODO_ITEMS => $todoItems,
            self::_COMPLETE => $complete
        );

        return $this;
    }

    public function startTodoItems($force = false)
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        if($this->_todoItemsLoaded && !$force)
        {
            return $this;
        }

        $this->getTodoItems()->startAllByTodoListId($this->getId(), $force);
        $this->_todoItemsLoaded = true;
        return $this;
    }

    public function findTodoItemByContent($content)
    {
        foreach($this->getTodoItems() as $todoItem)
        {
            if($content == $todoItem->getContent())
            {
                return $todoItem;
            }
        }

        return null;
    }

    /**
     * Create XML to create a new todoList
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return string
     */
    public function getXml()
    {
        if($this->getName() === null && $this->getTemplateId() == null)
        {
            throw new Exception('call setName() before '.__METHOD__);
        }

          $xml  = '<todo-list>';
        $xml .= '<name>'.htmlspecialchars($this->getName(), ENT_NOQUOTES).'</name>';
        $xml .= '<description>'.htmlspecialchars($this->getDescription(), ENT_NOQUOTES).'</description>';
        $xml .= '<private type="boolean">'.(($this->getIsPrivate()) ? 'true' : 'false').'</private>';

        if($this->getMilestoneId() !== null)
        {
            $xml .= '<milestone-id>'.$this->getMilestoneId().'</milestone-id>';
        }

        if($this->getTemplateId() !== null)
        {
            $xml .= '<todo-list-template-id>'.$this->getTemplateId().'</todo-list-template-id>';
        }
        $xml .= '</todo-list>';
        return $xml;
    }

    /**
     * Persist this todoList in storage
     *
     * Note: complete data (id etc) is not automatically loaded upon creation
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function create()
    {
        if($this->getProjectId() === null)
        {
            throw new Exception('set project-id before  '.__METHOD__);
        }

        $projectId = $this->getProjectId();
        $xml = $this->getXml();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/projects/$projectId/todo_lists.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
                ->setRawData($xml)
                ->request('POST')
            ;
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                $response = $this->_getHttpClient()->request('POST');
            }
            catch(\Exception $exception)
            {
                $this->_onCreateError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onCreateError();
            return false;
        }

        $this->_loaded = true;
        $this->_onCreateSuccess();
        return true;
    }

    /**
     * Update this todoList in storage
     *
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function update()
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        $xml = $this->getXml();
        $id = $this->getId();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_lists/$id.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
                ->setRawData($xml)
                ->request('PUT')
            ;
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                $response = $this->_getHttpClient()->request('PUT');
            }
            catch(\Exception $exception)
            {
                $this->_onUpdateError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onUpdateError();
            return false;
        }

        $this->_onUpdateSuccess();
        return true;
    }

    /**
     * Delete this todoList from storage
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_lists/$id.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
                ->request('DELETE')
            ;
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                $response = $this->_getHttpClient()->request('DELETE');
            }
            catch(\Exception $exception)
            {
                $this->_onDeleteError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onDeleteError();
            return false;
        }

        $this->_onDeleteSuccess();
        $this->_data = array();
        $this->_loaded = false;
        return true;
    }


// REORDER

    /**
     * Re-order todoList Items
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function reorder($todo_items)
    {
        $id = $this->getId();

        //
        // Build the XML payload
        //
        $xml = '<todo-items type="array">'."\n";

        foreach ($todo_items as $todoItemId) {
            $xml .= "  <todo-item><id>$todoItemId</id></todo-item>\n";
        }

        $xml .= '</todo-items>';

        //
        // POST /todo_lists/#{todo_list_id}/todo_items/reorder.xml
        //
        // We should get back a '200' response
        //
        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_lists/$id/todo_items/reorder.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
                ->setRawData($xml)
                ->request('POST')
            ;
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                $response = $this->_getHttpClient()->request('POST');
            }
            catch(\Exception $exception)
            {
                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            \Zend\Debug\Debug::dump($response);
            return false;
        }

        $this->_loaded = true;
        return true;
    }


// REORDER

    /**
     * Move List
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function moveList($destination)
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        $id = $this->getId();
        try {
            $raw = 'utf8=%E2%9C%93&authenticity_token=e75hPdlMOh%2FU16dzQ9S5d424u4p8YST08%2FInf9TBydU%3D';
            $raw .= '&move_operation%5Bsource_resource_type%5D=TodoList&move_operation%5Bsource_resource_id%5D='.
                $id->get().
                '&move_operation%5Bdestination_project_id%5D='.
                $destination.
                '&commit=Move+this+to-do+list';
//                \Zend\Debug\Debug::dump($raw);
//            $this->setHttpClient(new \Zend_Http_Client(null,array('timeout'=>30)));
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri() . "/move_operations")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setRawData($raw)
                ->setHeaders(array(
'Cookie: session_token=82879374cc9a535327cb; flashVersion=; wcsid=KjCZtJvnWznOuxpm2u7J40UR3N9Aoavj; hblid=u82b2h7k2jLsdLbc2u7J40UNaRbbjkv3; _okdetect=%7B%22token%22%3A%2215460075144980%22%2C%22proto%22%3A%22https%3A%22%2C%22host%22%3A%22webpagefx.basecamphq.com%22%7D; olfsk=olfsk2896615709396291; _okbk=cd4%3Dtrue%2Cvi5%3D0%2Cvi4%3D1546007514746%2Cvi3%3Dactive%2Cvi2%3Dfalse%2Cvi1%3Dfalse%2Ccd8%3Dchat%2Ccd6%3D0%2Ccd5%3Daway%2Ccd3%3Dfalse%2Ccd2%3D0%2Ccd1%3D0%2C; _ok=3808-668-10-7562; noOlark=true; twisted_token=22bec94daee7789856117b2230bcee3bdcc3; _basecamp_session_v2=BAh7C0kiD3Nlc3Npb25faWQGOgZFRiIlMTg3ODdiNmM2MmM3ODI4ZTk5MmJkZTdlMDc3NzA3MzRJIgx1c2VyX2lkBjsARmkD%2BeusSSIQaWRlbnRpdHlfaWQGOwBGaQO%2F2Y1JIh1tZXNhdXJlX3BhZ2VfcGVyZm9ybWFuY2UGOwBGRkkiEF9jc3JmX3Rva2VuBjsARkkiMWU3NWhQZGxNT2gvVTE2ZHpROVM1ZDQyNHU0cDhZU1QwOC9JbmY5VEJ5ZFU9BjsARkkiCmZsYXNoBjsARklDOiVBY3Rpb25EaXNwYXRjaDo6Rmxhc2g6OkZsYXNoSGFzaHsHOhRzaWdudXBfY29tcGxldGVGOhJyc3ZwX2NvbXBsZXRlRgY6CkB1c2VkbzoIU2V0BjoKQGhhc2h7BzsHVDsIVA%3D%3D--97762fa0834b9b060844575a185f0928950a1430; return_to=https%3A%2F%2Fwebpagefx.basecamphq.com%2Fprojects%2F14369343-r-d-test-2-adrian%2Fposts; _oklv=1546008714671%2CKjCZtJvnWznOuxpm2u7J40UR3N9Aoavj'
                ))
                ->request('POST')
                ;
            if($response->getMessage() == 'OK')
            {
                $this->setProjectId(new Id($destination));
                return true;
            }
            else
            {
//                \Zend\Debug\Debug::dump($response);
                throw new Exception($response->getMessage());
                return false;
            }
        }
        catch(\Exception $exception)
        {
            throw new Exception($exception->getMessage());
            return false;
        }
    }


    protected function _getService()
    {
        if($this->_service === null)
        {
            throw new Exception('call setService() before '.__METHOD__);
        }

        return $this->_service;
    }

    protected function _getHttpClient()
    {
        if($this->_httpClient === null)
        {
            throw new Exception('call setHttpClient() before '.__METHOD__);
        }

        return $this->_httpClient;
    }

    protected function _getVal($name)
    {
        return (isset($this->_data[$name])) ? $this->_data[$name] : null;
    }

    protected function _onCreateSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCreateSuccess($this);
        }
    }

    protected function _onUpdateSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onUpdateSuccess($this);
        }
    }

    protected function _onDeleteSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onDeleteSuccess($this);
        }
    }

    protected function _onCreateError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCreateError($this);
        }
    }

    protected function _onUpdateError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onUpdateError($this);
        }
    }

    protected function _onDeleteError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onDeleteError($this);
        }
    }

}
