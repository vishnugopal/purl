<?php
/**
 * purl
 * Minimal hurl.it in PHP.
 *
 * Copyright Vishnu Gopal, 2010
 * This file is released under the new BSD license.
 */

/**
 * Some HTTP method constants
 */
if (!defined('HTTP_METH_GET')) {
	define('HTTP_METH_HEAD', 'HEAD');
	define('HTTP_METH_GET', 'GET');
	define('HTTP_METH_PUT', 'PUT');
	define('HTTP_METH_POST', 'POST');
	define('HTTP_METH_DELETE', 'DELETE');
}

/**
 * Purl, a wrapper around PHP's CURL functions.
 */
class Purl
{
	/**
	 * @var string $url The url of the resource
	 */
	public $url;
	
	/**
	 * @var string $method The method of the request
	 */
	public $method;
	
	/**
	 * @var bool $follow_redirects Whether 30x redirects are followed
	 */
	public $follow_redirects;
	
	/**
	 * @var array $authentication array('username' => $username, 'password' => $password) OR NULL
	 */
	public $authentication;
	
	/**
	 * @var array $request_headers An array of request headers
	 */
	public $request_headers;
	
	/**
	 * @var array $request_params An array of request parameters
	 */
	public $request_params;
	
	public function __construct()
	{
		$this->request_headers = array();
		$this->request_params = array();
	}
	
	/**
	 * Dispatch the Purl request.
	 * @return array(array $headers, string $body)
	 */
	public function dispatch()
	{
		$curl = curl_init();

		$encoded_params = "";
		foreach($this->request_params as $key => $value) {
			$encoded_params .= urlencode($key) . "=" . urlencode($value) . "&";
		}
		$encoded_params = substr($encoded_params, 0, -1);

		/* Vary based on HTTP method verb */
		switch($this->method) {
			case HTTP_METH_GET:
				curl_setopt($curl, CURLOPT_URL, $this->url . "?" . $encoded_params);
				break;
			case HTTP_METH_PUT:
			case HTTP_METH_POST:
				curl_setopt($curl, CURLOPT_URL, $this->url);
				curl_setopt($curl, CURLOPT_POST, count($params));
				curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded_params);
				break;
			default:
		}
			
		if($this->follow_redirects) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, FALSE);
			curl_setopt($curl, CURLOPT_NOBODY, TRUE);
			if (curl_exec($curl)) {
				$this->url = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
				$this->follow_redirects = false;
				return $this->dispatch();
			}
		}
		
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);

		$result = curl_exec($curl);
		curl_close($curl);
		
		list($headers, $body) = split("\r\n\r\n", $result);
		$headers = split("\n", $headers);
		
		/* Clean up headers */
		$cleaned_headers = array();
		foreach($headers as $header) {
			list($header_name, $header_value) = split(":", $header, 2);
			$cleaned_headers[$header_name] = $header_value;
		}
		
		return array('headers' => $cleaned_headers, 'body' => $body);
	}
}

