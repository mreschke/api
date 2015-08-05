<?php namespace Mreschke\Api;

/**
 * Mreschke Api
 * @copyright 2015 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
*/
class Api implements ApiInterface
{

	/**
	 * Get api client by key
	 * @param  string $key
	 * @return array
	 */
	public static function getClient($key)
	{
		// in redis find by key
		return [
			'key' => $key,
			'secret' => 'secret1'
		];
	}

	/**
	 * Generate base64 encoded sha246 MAC signature
	 * @param  string $method    Http method
	 * @param  string $url       Full api url
	 * @param  string $key       User api key
	 * @param  string $secret    User secret
	 * @param  string $timestamp Unix timestamp
	 * @param  array  $data      Post data
	 * @return string
	 */
	public static function getMacSignature($method, $url, $key, $secret, $timestamp, $data = null)
	{
		// Convert $data into json string
		if (isset($data)) $data = json_encode($data);

		// Build our string data from method+url+timestamp+key+json(data)
		$signatureData = strtoupper($method).$url.$timestamp.$key.$data;
		#dump($signatureData);

		$signature = hash_hmac('sha256', $signatureData, $secret);

		return base64_encode($signature);
	}

}
