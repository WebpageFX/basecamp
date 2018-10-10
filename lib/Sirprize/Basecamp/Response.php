<?php

/*
 * This file is part of the Basecamp Classic API Wrapper for PHP 5.3+ package
 *
 * (c) Christian Hoegl <chrigu@sirprize.me>
 */

namespace Sirprize\Basecamp;

class Response
{

    protected $_httpResponse = null;
    protected $_data = null;
    protected $_error = null;


    function warning_handler($errno, $errstr) {
        throw new \Exception($errstr);
    }

    public function __construct(\Zend_Http_Response $httpResponse)
    {
        $this->_httpResponse = $httpResponse;

		if(!preg_match('/^\s*$/', $httpResponse->getBody()))
		{
			set_error_handler(array($this,'warning_handler'), E_WARNING);
			try
			{
				$this->_data = simplexml_load_string($httpResponse->getBody());
			}
			catch(\Exception $e)
			{
				if(strpos($httpResponse->getBody(),'Oops, that isn&rsquo;t right.') === false)
				{
					throw new \Exception("Bad XML Basecamp Response:\n".$httpResponse->getBody());
				}
			}
			restore_error_handler();

			if($httpResponse->isError())
			{
				$data = (array)$this->_data;

				if(isset($data['error']))
				{
					$this->_error = $data['error'];
				}
			}
		}
    }

    public function getHttpResponse()
    {
        return $this->_httpResponse;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function isError()
    {
        return (
            $this->_httpResponse->isError() ||
            $this->getCode() !== null ||
            $this->getMessage() !== null
        );
    }

    public function getCode()
    {
        return null;
    }

    public function getMessage()
    {
        return $this->_error;
    }

}
