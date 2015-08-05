<?php namespace Mreschke\Api;

/**
 * Mreschke Api Server
 * @copyright 2015 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
*/
class Server
{
	protected $api;

	public function __construct(ApiInterface $api)
	{
		$this->api = $api;
	}

	/**
	 * Verify the request mac header signature
	 * @param  string $authHeader Request api hmac authorization header
	 * @param  string $method     Http method
	 * @param  string $url        Aip url
	 * @return boolean
	 */
	public function verify($authHeader, $method, $url)
	{
		try {
			if (strlen($authHeader) > 50) {
				if (substr($authHeader, 0, 4) == 'Api ') {
					$authHeader = substr($authHeader, 4);
					list($key, $timestamp, $mac) = explode(':', $authHeader);

					// Check timestamp is within buffer to prevent replay attacks
					if (time() <= $timestamp + 60) {
						
						// Get users secret by key
						$client = $this->api->getClient($key);

						$verify = $this->api->getMacSignature($method, $url, $key, $client['secret'], $timestamp);

						if ($mac === $verify) {
							return $client;
						}
					}
				}
			}
		} catch (\Exception $e) {

		}
	}

}


