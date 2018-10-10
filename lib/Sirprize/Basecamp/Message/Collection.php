<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message;

use Sirprize\Basecamp\Id;
use Sirprize\Basecamp\Service;
use Sirprize\Basecamp\Response;
use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\Message\Entity;
use Sirprize\Basecamp\Message\Collection\Observer\Abstrakt;

/**
 * Encapsulate a set of persisted message objects and the operations performed over them
 */
class Collection extends \SplObjectStorage
{

    const _MESSAGE = 'post';

    protected $_service = null;
    protected $_httpClient = null;
    protected $_started = false;
    protected $_loaded = false;
    protected $_response = null;
    protected $_observers = array();

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
     * @return \Sirprize\Basecamp\Message\Collection
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
     * @return \Sirprize\Basecamp\Message\Collection
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
     * Instantiate a new message entity
     *
     * @return \Sirprize\Basecamp\Message\Entity
     */
    public function getMessageInstance()
    {
        $message = new Entity();
        $message
            ->setHttpClient($this->_getHttpClient())
            ->setService($this->_getService())
        ;

        return $message;
    }

    /**
     * Defined by \SplObjectStorage
     *
     * Add message entity
     *
     * @param \Sirprize\Basecamp\Message\Entity $message
     * @throws \Sirprize\Basecamp\Exception
     * @return \Sirprize\Basecamp\Message\Collection
     */
    public function attach($message, $data = null)
    {
        if(!$message instanceof Entity)
        {
            throw new Exception('expecting an instance of Entity');
        }

        parent::attach($message);
        return $this;
    }

    /**
     * Fetch message for a given project
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return \Sirprize\Basecamp\Message\Collection
     */
    public function startAllByProjectId(Id $projectId, $force = false)
    {
        if($this->_started && !$force)
        {
            return $this;
        }

        $this->_started = true;

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/projects/$projectId/posts/archive.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->request('GET')
            ;
            $this->_response = new Response($response);
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                sleep(15);
                $response = $this->_getHttpClient()->request('GET');
                $this->_response = new Response($response);
            }
            catch(\Exception $exception)
            {
                $this->_onStartError();

                throw new Exception($exception->getMessage());
            }
        }


        if($this->_response->isError())
        {
            // service error
            $this->_onStartError();
            return $this;
        }

        $this->load($this->_response->getData(), $force);
        $this->_onStartSuccess();
        return $this;
    }

    /**
     * Fetch message by id
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return null|Entity
     */
    public function startById(Id $id, $force = false)
    {
        if($this->_started && !$force)
        {
            return $this;
        }

        $this->_started = true;

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/posts/$id.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->request('GET')
            ;
            $this->_response = new Response($response);
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                sleep(15);
                $response = $this->_getHttpClient()->request('GET');
                $this->_response = new Response($response);
            }
            catch(\Exception $exception)
            {
                $this->_onStartError();

                throw new Exception($exception->getMessage());
            }
        }


        if($this->_response->isError())
        {
            // service error
            $this->_onStartError();
            return null;
        }

        $this->load($this->_response->getData(), $force);
        $this->_onStartSuccess();
        $this->rewind();
        return $this->current();
    }


    /**
     * Fetch message by id
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return null|Entity
     */
    public function startBycommentId(Id $id, $force = false)
    {
        if($this->_started && !$force)
        {
            return $this;
        }

        $this->_started = true;

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/comments/$id.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->request('GET')
            ;
            $this->_response = new Response($response);
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                sleep(15);
                $response = $this->_getHttpClient()->request('GET');
                $this->_response = new Response($response);
            }
            catch(\Exception $exception)
            {
                $this->_onStartError();

                throw new Exception($exception->getMessage());
            }
        }

        $data = $this->_response->getData();
        $array_data = (array) $data;
        $id = new Id($array_data['commentable-id']);

        if($this->_response->isError())
        {
            // service error
            $this->_onStartError();
            return null;
        }
        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/posts/$id.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->request('GET')
            ;
            $this->_response = new Response($response);
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                sleep(15);
                $response = $this->_getHttpClient()->request('GET');
                $this->_response = new Response($response);
            }
            catch(\Exception $exception)
            {
                $this->_onStartError();

                throw new Exception($exception->getMessage());
            }
        }


        if($this->_response->isError())
        {
            // service error
            $this->_onStartError();
            return null;
        }

        $this->load($this->_response->getData(), $force);
        $this->_onStartSuccess();
        $this->rewind();
        return $this->current();
    }

    /**
     * Instantiate message objects with api response data
     *
     * @return \Sirprize\Basecamp\Message\Collection
     */
    public function load(\SimpleXMLElement $xml, $force = false)
    {
        if($this->_loaded && !$force)
        {
            throw new Exception('message collection has already been loaded');
        }

        $this->_loaded = true;

        if(isset($xml->id))
        {
            // request for a single entity (not supported on messages)
            $message = $this->getMessageInstance();
            $message->load($xml, $force);
            $this->attach($message);
            return $this;
        }

        $array = (array) $xml;

        if(!isset($array[self::_MESSAGE]))
        {
            // list request - 0 items in response
            return $this;
        }

        if(isset($array[self::_MESSAGE]->id))
        {
            // list request - 1 item in response
            $message = $this->getMessageInstance();
            $message->load($array[self::_MESSAGE], $force);
            $this->attach($message);
            return $this;
        }

        foreach($array[self::_MESSAGE] as $row)
        {
            // list request - 2 or more items in response
            $message = $this->getMessageInstance();
            $message->load($row, $force);
            $this->attach($message);
        }

        return $this;
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

    protected function _onStartSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onStartSuccess($this);
        }
    }

    protected function _onStartError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onStartError($this);
        }
    }

}
