<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\Message;

use Sirprize\Basecamp\Id;
use Sirprize\Basecamp\Date;
use Sirprize\Basecamp\Service;
use Sirprize\Basecamp\Response;
use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\Message\Entity\Observer\Abstrakt;

/**
 * Represent a message
 */
class Entity
{

    const _AUTHOR_ID = 'author-id';
    const _BODY = 'body';
    const _COMMENTED_AT = 'commented-at';
    const _COMMENTS_COUNT = 'comments-count';
    const _ID = 'id';
    const _CREATED_AT = 'posted-on';
    const _PROJECT_ID = 'project-id';
    const _MILESTONE_ID = 'milestone-id';
    const _TITLE = 'title';
    const _ATTACHMENTS_COUNT = 'attachments-count';
    const _PRIVATE = 'private';


    protected $_service = null;
    protected $_httpClient = null;
    protected $_data = array();
    protected $_loaded = false;
    protected $_response = null;
    protected $_observers = array();
    protected $_attachments = array();
    protected $_private = array();

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
     * @return \Sirprize\Basecamp\Message
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
     * @return \Sirprize\Basecamp\Message
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

    public function getCommentsCount()
    {
        return $this->_getVal(self::_COMMENTS_COUNT);
    }

    public function getBody()
    {
        return $this->_getVal(self::_BODY);
    }

    public function setBody($body)
    {
        $this->_data[self::_BODY] = $body;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->_getVal(self::_CREATED_AT);
    }

    public function getCommentedAt()
    {
        return $this->_getVal(self::_COMMENTED_AT);
    }

    public function getAttachmentsCount()
    {
        return $this->_getVal(self::_COMMENTS_COUNT);
    }

    public function getPrivate()
    {
        return $this->_getVal(self::_PRIVATE);
    }

    public function setPrivate($private)
    {
        $this->_private = $private;
        return $this;
    }

    public function getMilestoneId()
    {
        return $this->_getVal(self::_MILESTONE_ID);
    }

    public function getAuthorId()
    {
        return $this->_getVal(self::_AUTHOR_ID);
    }

    public function getTitle()
    {
        return $this->_getVal(self::_TITLE);
    }

    public function setTitle($title)
    {
        $this->_data[self::_TITLE] = $title;
        return $this;
    }

    public function getProjectId()
    {
        return $this->_getVal(self::_PROJECT_ID);
    }


    public function setProjectId(Id $ProjectId)
    {
        $this->_data[self::_PROJECT_ID] = $ProjectId;
        return $this;
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getId()
    {
        return $this->_getVal(self::_ID);
    }

    /**
     * Load data returned from an api request
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return \Sirprize\Basecamp\Message
     */
    public function load(\SimpleXMLElement $xml, $force = false)
    {
        if($this->_loaded && !$force)
        {
            throw new Exception('entity has already been loaded');
        }

        $this->_onLoadSuccess($xml->asXML());
//        echo $xml->asXML();die;
        #print_r($xml); exit;
        $this->_loaded = true;
        $array = (array) $xml;

        $id = new Id($array[self::_ID]);

        if(!isset($array[self::_AUTHOR_ID]))
            $array[self::_AUTHOR_ID] = NULL;
        if(!isset($array[self::_PRIVATE]))
            $array[self::_PRIVATE] = NULL;
        if(!isset($array[self::_BODY]))
            $array[self::_BODY] = NULL;
        if(!isset($array[self::_COMMENTS_COUNT]))
            $array[self::_COMMENTS_COUNT] = NULL;
        if(!isset($array[self::_PROJECT_ID]))
            $array[self::_PROJECT_ID] = NULL;
        if(!isset($array[self::_MILESTONE_ID]))
            $array[self::_MILESTONE_ID] = NULL;

        $commented_at = NULL;
        if(isset($array[self::_COMMENTED_AT]) && $array[self::_COMMENTED_AT] !== NULL && ((string) $array[self::_COMMENTED_AT]) != '')
            $commented_at = new \DateTime($array[self::_COMMENTED_AT]);
        $created_at = NULL;
        if(isset($array[self::_CREATED_AT]) && $array[self::_CREATED_AT] !== NULL && ((string) $array[self::_CREATED_AT]) != '')
            $created_at = new \DateTime($array[self::_CREATED_AT]);
        $milestone_id = NULL;
        if(isset($array[self::_MILESTONE_ID]) && $array[self::_MILESTONE_ID] !== NULL && ((string) $array[self::_MILESTONE_ID]) != '' && $array[self::_MILESTONE_ID] != 0)
            $milestone_id = $array[self::_MILESTONE_ID];

        $this->_data = array(
            self::_AUTHOR_ID => $array[self::_AUTHOR_ID],
            self::_BODY => $array[self::_BODY],
            self::_COMMENTED_AT => $commented_at,
            self::_CREATED_AT => $created_at,
            self::_COMMENTS_COUNT => $array[self::_COMMENTS_COUNT],
            self::_TITLE => $array[self::_TITLE],
            self::_ATTACHMENTS_COUNT => $array[self::_ATTACHMENTS_COUNT],
            self::_PRIVATE => $array[self::_PRIVATE],
            self::_ID => $id,
            self::_PROJECT_ID => $array[self::_PROJECT_ID],
            self::_MILESTONE_ID => $milestone_id
        );

        return $this;
    }

    /**
     * Create XML to create a new message
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return string
     */
    public function getXml()
    {
        if($this->getTitle() === null)
        {
            throw new Exception('call setTitle() before '.__METHOD__);
        }

		$xml  = '<request>';
		$xml .= '<post>';
		$xml .= '<title>'.htmlspecialchars($this->getTitle(), ENT_NOQUOTES).'</title>';
		$xml .= '<body>'.htmlspecialchars($this->getBody(), ENT_NOQUOTES).'</body>';
		$xml .= '<private>'.($this->_private ? 1 : 0).'</private>';
		$xml .= '<category-id />';
		$xml .= '</post>';
        $xml .= '</request>';
        return $xml;
    }

    /**
     * Persist this message in storage
     *
     * Note: complete data (id etc) is not automatically loaded upon creation
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function create($comment = false)
    {
        if($this->getProjectId() === null)
        {
            throw new Exception('set project-id before  '.__METHOD__);
        }

        $projectId = $this->getProjectId();

        $xml = $this->getXml();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/projects/$projectId/posts.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
                ->setRawData($xml)
                ->request('POST')
            ;

            if ($comment)
            {

                $attachments_xml = '';
                if (count($this->_attachments) > 0) {

                    foreach ($this->_attachments as $uploadId => $fname) {
                        // Yes there are 2 file tags
                        $attachments_xml .= '<attachments><file><original_filename>'.htmlspecialchars($fname).'</original_filename><file>'.$uploadId.'</file></file></attachments>';
                    }
                }

                $xml2 = '<comment><body>' . htmlspecialchars($comment) . '</body>' . $attachments_xml . '</comment>';
                $response2 = $this->_getHttpClient()
                    ->setUri($response->getHeader('Location') . "/comments.xml")
                    ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                    ->setHeaders('Content-type', 'application/xml')
                    ->setHeaders('Accept', 'application/xml')
                    ->setRawData($xml2)
                    ->request('POST')
                ;
            }
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

        $this->_onCreateSuccess();
        return true;
    }

    /**
     * Add a comment
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function addComment($comment)
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }
        $attachments_xml = '';
        if (count($this->_attachments) > 0) {

            foreach ($this->_attachments as $uploadId => $fname) {
                // Yes there are 2 file tags
                $attachments_xml .= '<attachments><file><original_filename>'.htmlspecialchars($fname).'</original_filename><file>'.$uploadId.'</file></file></attachments>';
            }
        }
        $xml = '<comment><body>' . htmlspecialchars($comment) . '</body>' . $attachments_xml . '</comment>';
        $id = $this->getId();
        try {
                $response = $this->_getHttpClient()
                    ->setUri($this->_getService()->getBaseUri() . "/posts/$id/comments.xml")
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
                $response = $this->_getHttpClient()->request('PUT');
            }
            catch(\Exception $exception)
            {
                $this->_onCommentAddError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onCommentAddError();
            return false;
        }

        $this->_onCommentAddSuccess();
        return true;
    }

    /**
     * Gets recent comments
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function getComments()
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        $id = $this->getId();
        try {
                $response = $this->_getHttpClient()
                    ->setUri($this->_getService()->getBaseUri() . "/posts/$id/comments.xml")
                    ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                    ->setHeaders('Content-type', 'application/xml')
                    ->setHeaders('Accept', 'application/xml')
                    ->request('GET')
                ;
        }
        catch(\Exception $exception)
        {
            try {
                // connection error - try again
                $response = $this->_getHttpClient()->request('GET');
            }
            catch(\Exception $exception)
            {
                $this->_onCommentsGetError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onCommentsGetError();
            return false;
        }

        $this->_onCommentsGetSuccess();
        return $this->_response->getData();
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

    protected function _onLoadSuccess($xmlstring)
    {
        foreach($this->_observers as $observer)
        {
            $observer->onLoadSuccess($xmlstring);
        }
    }

    protected function _onCommentsGetSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentsGetSuccess($this);
        }
    }

    protected function _onCommentsGetError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentsGetError($this);
        }
    }

    protected function _onCommentAddError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentAddError($this);
        }
    }


    protected function _onCreateError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCreateError($this);
        }
    }


    protected function _onCommentAddSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentAddSuccess($this);
        }
    }

    protected function _onCreateSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCreateSuccess($this);
        }
    }
}
