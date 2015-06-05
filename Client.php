<?php namespace Mreschke\Api;

use App;
use GuzzleHttp\Client as Guzzle;

/**
 * Mreschke Api Client
 * @copyright 2015 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
*/
class Client
{
	public $vendor;
	public $baseUrl;
	public $version;
	public $key;
	public $secret;

	public function __construct($vendor, $baseUrl, $version, $key, $secret)
	{
		$this->vendor = $vendor;
		$this->baseUrl = $baseUrl;
		$this->version = $version;
		$this->key = $key;
		$this->secret = $secret;
	}

	/**
	 * Send request to the API server
	 * @param  string $method Http method
	 * @param  string $path   api path after base url
	 * @param  array  $data   post data
	 * @return json
	 */
	protected function request($method, $path, $data = null)
	{
		$method = strtolower($method);
		$url = $this->baseUrl.$path;

		// Mac Signature - method + url + timestamp + key + data hash
		$timestamp = time();
		
		//FIXME dep inject ?? static??
		$mac = Api::getMacSignature($method, $url, $this->key, $this->secret, $timestamp, $data); 

		// Authorization Header
		$authHeader = "Api $this->key:$timestamp:$mac";

		$options = [
			'headers' => [
				'Content-Type' => "application/vnd.api.".$this->version."+json",
				'Authorization' => $authHeader,
			]
		];
		
		$client = new Guzzle();
		$response = $client->$method($url, $options);
		return (string) $response->getBody();
	}

	public function get($path, $data = null)
	{
		return $this->request('GET', $path, $data);
	}

	public function post($path, $data = null)
	{
		return $this->request('POST', $path, $data);
	}

	public function put($path, $data = null)
	{
		return $this->request('PUT', $path, $data);
	}

	public function patch($path, $data = null)
	{
		return $this->request('PATCH', $path, $data);
	}

	public function delete($path, $data = null)
	{
		return $this->request('DELETE', $path, $data);
	}	

}
