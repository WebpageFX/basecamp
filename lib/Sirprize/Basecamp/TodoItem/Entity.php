<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp\TodoItem;

use Sirprize\Basecamp\Id;
use Sirprize\Basecamp\Date;
use Sirprize\Basecamp\Service;
use Sirprize\Basecamp\Response;
use Sirprize\Basecamp\Exception;
use Sirprize\Basecamp\TodoItem\Entity\Observer\Abstrakt;

/**
 * Represent and modify a todo-item
 */
class Entity
{

    const _COMMENTS_COUNT = 'comments-count';
    const _COMPLETED = 'completed';
    const _CONTENT = 'content';
    const _CREATED_AT = 'created-at';
    const _COMPLETED_AT = 'completed-at';
    const _COMPLETER_ID = 'completer-id';
    const _CREATOR_ID = 'creator-id';
    const _DUE_AT = 'due-at';
    const _ID = 'id';
    const _POSITION = 'position';
    const _RESPONSIBLE_PARTY_ID = 'responsible-party-id';
    const _RESPONSIBLE_PARTY_TYPE = 'responsible-party-type';
    const _TODOLIST_ID = 'todo-list-id';
    const _CREATED_ON = 'created-on';

    protected $_service = null;
    protected $_httpClient = null;
    protected $_data = array();
    protected $_loaded = false;
    protected $_response = null;
    protected $_observers = array();
    protected $_responsiblePartyId = null;
    protected $_notify = false;
    protected $_attachments = array();

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
     * @return \Sirprize\Basecamp\TodoItem
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
     * @return \Sirprize\Basecamp\TodoItem
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

    public function setResponsiblePartyId(Id $responsiblePartyId = NULL)
    {
        $this->_responsiblePartyId = $responsiblePartyId;
        return $this;
    }

    public function setDueAt(Date $dueAt)
    {
        $this->_data[self::_DUE_AT] = $dueAt;
        return $this;
    }

    public function setNotify($notify)
    {
        $this->_notify = $notify;
        return $this;
    }

    public function setContent($content)
    {
        $this->_data[self::_CONTENT] = $content;
        return $this;
    }

    public function setTodoListId(Id $todoListId)
    {
        $this->_data[self::_TODOLIST_ID] = $todoListId;
        return $this;
    }

    public function setPosition($position)
    {
        $this->_data[self::_POSITION] = $position;
        return $this;
    }

    public function getCommentsCount()
    {
        return $this->_getVal(self::_COMMENTS_COUNT);
    }

    public function getIsCompleted()
    {
        return $this->_getVal(self::_COMPLETED);
    }

    public function getContent()
    {
        return $this->_getVal(self::_CONTENT);
    }

    public function getCreatedAt()
    {
        return $this->_getVal(self::_CREATED_AT);
    }

    public function getCompletedAt()
    {
        return $this->_getVal(self::_COMPLETED_AT);
    }

    public function getCompleterId()
    {
        return $this->_getVal(self::_COMPLETER_ID);
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getCreatorId()
    {
        return $this->_getVal(self::_CREATOR_ID);
    }

    /**
     * @return \Sirprize\Basecamp\Date
     */
    public function getDueAt()
    {
        return $this->_getVal(self::_DUE_AT);
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getId()
    {
        return $this->_getVal(self::_ID);
    }

    public function getPosition()
    {
        return $this->_getVal(self::_POSITION);
    }

    /**
     * @return null|Id
     */
    public function getResponsiblePartyId()
    {
        return $this->_getVal(self::_RESPONSIBLE_PARTY_ID);
    }

    public function getResponsiblePartyType()
    {
        return $this->_getVal(self::_RESPONSIBLE_PARTY_TYPE);
    }

    /**
     * @return \Sirprize\Basecamp\Id
     */
    public function getTodoListId()
    {
        return $this->_getVal(self::_TODOLIST_ID);
    }

    public function getCreatedOn()
    {
        return $this->_getVal(self::_CREATED_ON);
    }

    /**
     * Load data returned from an api request
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return \Sirprize\Basecamp\TodoItem
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
        #$completerId = new Id($array[self::_COMPLETER_ID]);
        $creatorId = new Id($array[self::_CREATOR_ID]);
        $todoListId = new Id($array[self::_TODOLIST_ID]);
        $responsiblePartyId = null;
        $responsiblePartyType = null;

        if(isset($array[self::_RESPONSIBLE_PARTY_ID]))
        {
            $responsiblePartyId = new Id($array[self::_RESPONSIBLE_PARTY_ID]);
        }

        if(isset($array[self::_RESPONSIBLE_PARTY_TYPE]))
        {
            $responsiblePartyType = $array[self::_RESPONSIBLE_PARTY_TYPE];
        }

        $completed = ($array[self::_COMPLETED] == 'true');
        if(isset($array[self::_COMPLETED_AT]))
            $completed_at = $array[self::_COMPLETED_AT];
        else
            $completed_at = NULL;

        if(isset($array[self::_COMPLETER_ID]))
            $completer_id = $array[self::_COMPLETER_ID];
        else
            $completer_id = NULL;

        $dueAt = null;

        if($array[self::_DUE_AT])
        {
            $dueAt = preg_replace('/^(\d{4,4}-\d{2,2}-\d{2,2}).*$/', "$1", $array[self::_DUE_AT]);
            if(!$dueAt) { $dueAt = null; }
        }

        $this->_data = array(
            self::_COMMENTS_COUNT => $array[self::_COMMENTS_COUNT],
            self::_COMPLETED => $completed,
            self::_CONTENT => $array[self::_CONTENT],
            self::_CREATED_AT => $array[self::_CREATED_AT],
            self::_COMPLETED_AT => $completed_at,
            self::_COMPLETER_ID => $completer_id,
            self::_CREATOR_ID => $creatorId,
            self::_DUE_AT => $dueAt,
            self::_ID => $id,
            self::_POSITION => $array[self::_POSITION],
            self::_RESPONSIBLE_PARTY_ID => $responsiblePartyId,
            self::_RESPONSIBLE_PARTY_TYPE => $responsiblePartyType,
            self::_TODOLIST_ID => $todoListId,
            self::_CREATED_ON => $array[self::_CREATED_ON]
        );

        return $this;
    }

    /**
     * Create XML to create a new todoItem
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return string
     */
    public function getXml()
    {
        if($this->getContent() === null)
        {
            throw new Exception('call setContent() before '.__METHOD__);
        }

          $xml  = '<todo-item>';
        $xml .= '<content>'.htmlspecialchars($this->getContent(), ENT_NOQUOTES).'</content>';

        if($this->_responsiblePartyId !== null)
        {
            $xml .= '<responsible-party>'.$this->_responsiblePartyId.'</responsible-party>';
            if($this->_notify) { $xml .= '<notify>true</notify>'; }
        }
        else
            $xml .= '<responsible-party></responsible-party>';

        if($this->getDueAt() !== null)
        {
            $xml .= '<due-at>'.$this->getDueAt().'</due-at>';
        }

		$xml .= '</todo-item>';
        return $xml;
    }

    /**
     * Persist this todo-item in storage
     *
     * Note: complete data (id etc) is not automatically loaded upon creation
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function create($comment)
    {
        if($this->getTodoListId() === null)
        {
            throw new Exception('set todoList-id before  '.__METHOD__);
        }

        $todoListId = $this->getTodoListId();

        $xml = $this->getXml();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_lists/$todoListId/todo_items.xml")
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


    //
    // This is used to upload a file to Basecamp so that we can later
    // attach it to a comment.
    //
    // It's passed a full path to a file, and then returns the basecamp ID
    // for that particular upload
    //
    // Example:
    //
    //    $uploadEntity = new TodoItemEntity();
    //    $uploadEntity->setService($this->basecampService)
    //                 ->setHttpClient(new \Zend_Http_Client());
    //
    //    $fileID = $uploadItem->uploadFile($file);
    //
    public function uploadFile($file)
    {

        // Get the name of the file (minus the directory path)
        $fname = basename($file);

        //
        // Upload the file via a POST
        //
        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/upload")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setFileUpload($file,'name')
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
            return false;
        }


        // The call must have been successful. We should have got back something like this:
        //
        //  SimpleXMLElement Object
        //  (
        //      [id] => 5733c35b1dab6357219c8ae45820aa0c0010
        //  )
        //
        // This will parse the xml into a datastructure we can use
        $data = $this->_response->getData();

        // I got some strange behavior when working with the id, so I'm casting
        // to a string so it's easier to work with.
        return ((string) $data->id);
    }

    // We'll use an array indexed by fileID to define the attachments for this
    // particular TodoItem object. Later when 'create' is called, it will loop
    // through this array to build XML that associates the fileIDs with the new Todo item.
    function setAttachments ($attachments = []) {
        foreach ($attachments as $id => $filename) {
            $this->_attachments[$id] = $filename;
        }
    }


    /**
     * Update this todo-item in storage
     *
     * Note: complete data (id etc) is not automatically loaded upon update
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
                ->setUri($this->_getService()->getBaseUri()."/todo_items/$id.xml")
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
                    ->setUri($this->_getService()->getBaseUri() . "/todo_items/$id/comments.xml")
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
                    ->setUri($this->_getService()->getBaseUri() . "/todo_items/$id/comments.xml")
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

    //gets tagged users from page content
    public function getCommentTagged()
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }
        $id = $this->getId();
        try {
                $response = $this->_getHttpClient()
                    ->setUri($this->_getService()->getBaseUri() . "/todo_items/$id/comments")
                    ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
					->setHeaders('Content-type', NULL)
					->setHeaders('Accept', NULL)
                    ->request('GET')
                ;
                $body = $response->getBody();
                $subscribers = $this->getContents($body,"  <input checked=\"checked\" id=\"notify-","\" name=\"notify[]\" type=\"checkbox\" value=\"");
                return $subscribers;
        }
        catch(\Exception $e)
        {
            \Zend\Debug\Debug::dump($e->getMessage());
            throw new Exception($e->getMessage());
            return false;
        }
    }

    /**
     * adds raw comment with tagged parties; comment must not be empty or all whitespace
     *
     * expects array of party ids to assign
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function addCommentTagged($comment, $party_ids)
    {
        if(!$this->_loaded)
        {  
            throw new Exception('call load() before '.__METHOD__);
        }

        $id = $this->getId();
        try {   
            //always include at least one party; fx projects in this case
            array_push($party_ids,11332601);
            $all_pids = array_merge(array(0),$party_ids);
            $data = array(
                'comment[body]' => $comment,
                'notify[]' => $all_pids,
                'commit' => 'Add this comment',
            );
            $raw = 'utf8=%E2%9C%93&authenticity_token=Wn4eObu0bePwMBiBrcM7w4W5PkmbNUti1tiVVBcU03Y%3D&comment%5Buse_textile%5D=true&basic_uploader=true';
            foreach($data as $dkey => $dval)
            {   
                if($dkey == 'notify[]')
                {   
                    foreach($dval as $nval)
                    {   
                        if($raw != '')
                            $raw .= '&';
                        $raw .= urlencode($dkey).'='.urlencode($nval);
                    }
                }
                else
                {   
                    if($raw != '')
                        $raw .= '&';
                    $raw .= urlencode($dkey).'='.urlencode($dval);
                }
            }
            //                $this->setHttpClient(new \Zend_Http_Client(null,array('timeout'=>30)));
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri() . "/todo_items/$id/comments")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', NULL)
                ->setHeaders('Accept', NULL)
                ->setRawData($raw)
                ->request('POST')
                ;
            $xml_comments = $this->getComments();
            if($xml_comments == false)
                return false;
            $comments = (array) $xml_comments;
            $last_comment = array_pop($comments['comment']);
            $created_at = new \DateTime($last_comment->{'created-at'});
            $now = new \DateTime();
            if($created_at >= $now->modify('-2 minutes'))
            {
                return (int) $last_comment->{'id'};
            }
            else
            {
                return false;
            }

        }
        catch(\Exception $exception)
        {
            throw new Exception($exception->getMessage());
            return false;
        }

    }

    /**
     * Update a comment
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function updateComment($id, $comment)
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
//        $id = $this->getId();
        try {
                $response = $this->_getHttpClient()
                    ->setUri($this->_getService()->getBaseUri() . "/comments/$id.xml")
                    ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                    ->setHeaders('Content-type', 'application/xml')
                    ->setHeaders('Accept', 'application/xml')
                    ->setRawData($xml)
                    ->request('PUT')
                ;
        }
        catch(\Exception $exception)
        {
            throw new Exception($exception->getMessage());
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            return false;
        }

        return true;
    }

    /**
     * Delete this todo-item from storage
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function delete()
    {
        $id = $this->getId();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_items/$id.xml")
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

    /**
     * Complete this todo-item
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function complete()
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        $id = $this->getId();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_items/$id/complete.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
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
                $this->_onCompleteError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onCompleteError();
            return false;
        }

        $this->_onCompleteSuccess();
        return true;
    }

    /**
     * Uncomplete this todo-item
     *
     * @throws \Sirprize\Basecamp\Exception
     * @return boolean
     */
    public function uncomplete()
    {
        if(!$this->_loaded)
        {
            throw new Exception('call load() before '.__METHOD__);
        }

        $id = $this->getId();

        try {
            $response = $this->_getHttpClient()
                ->setUri($this->_getService()->getBaseUri()."/todo_items/$id/uncomplete.xml")
                ->setAuth($this->_getService()->getUsername(), $this->_getService()->getPassword())
                ->setHeaders('Content-type', 'application/xml')
                ->setHeaders('Accept', 'application/xml')
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
                $this->_onCompleteError();

                throw new Exception($exception->getMessage());
            }
        }

        $this->_response = new Response($response);

        if($this->_response->isError())
        {
            // service error
            $this->_onUncompleteError();
            return false;
        }

        $this->_onUncompleteSuccess();
        return true;
    }

    private function getContents($str, $startDelimiter, $endDelimiter) {
        $contents = array();
        $startDelimiterLength = strlen($startDelimiter);
        $endDelimiterLength = strlen($endDelimiter);
        $startFrom = $contentStart = $contentEnd = 0;
        while (false !== ($contentStart = strpos($str, $startDelimiter, $startFrom))) {
            $contentStart += $startDelimiterLength;
            $contentEnd = strpos($str, $endDelimiter, $contentStart);
            if (false === $contentEnd) {
                break;
            }
            $contents[] = substr($str, $contentStart, $contentEnd - $contentStart);
            $startFrom = $contentEnd + $endDelimiterLength;
        }

        return $contents;
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

    protected function _onCompleteSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCompleteSuccess($this);
        }
    }

    protected function _onUncompleteSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onUncompleteSuccess($this);
        }
    }

    protected function _onCreateSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCreateSuccess($this);
        }
    }

    protected function _onLoadSuccess($xmlstring)
    {
        foreach($this->_observers as $observer)
        {
            $observer->onLoadSuccess($xmlstring);
        }
    }

    protected function _onUpdateSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onUpdateSuccess($this);
        }
    }

    protected function _onCommentAddSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentAddSuccess($this);
        }
    }

    protected function _onCommentsGetSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentsGetSuccess($this);
        }
    }

    protected function _onDeleteSuccess()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onDeleteSuccess($this);
        }
    }

    protected function _onCompleteError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCompleteError($this);
        }
    }

    protected function _onUncompleteError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onUncmpleteError($this);
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

    protected function _onCommentAddError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentAddError($this);
        }
    }

    protected function _onCommentsGetError()
    {
        foreach($this->_observers as $observer)
        {
            $observer->onCommentsGetError($this);
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
