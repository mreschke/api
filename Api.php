<?php namespace Mreschke\Api;

use GuzzleHttp\Client as Guzzle;

/**
 * Mreschke Api
 * @copyright 2015 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
*/
class Api implements ApiInterface
{

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

	public function verify($headers, $method, $url)
	{
		$parts = $this->parseHeader($headers['authorization'][0]);

		$key = $parts['id'];

		// Hit db, get secret
		$secret = 'secret1';

		if (time() <= $parts['timestamp'] + 120) { #time buffer
			dd('x');

			$params['timestamp'] = $parts['timestamp'];

			if (isset($parts['ext'])) {
				$params['ext'] = $parts['ext'];
			}

			// Generate the MAC
			$test = $this->generateMac($secret, $params);

			// Test against the received MAC
			return ($test === $parts['mac']);

		}		

		$hawk = $this->generateHeader($key, $secret, $method, $url);

		dd($hawk);

	}

	/**
	 * Generate the MAC
	 * @param  string $secret The shared secret
	 * @param  array  $params The MAC data parameters
	 * @return string         The base64 encode MAC
	 */
	private function generateMac($secret = '', $params = array())
	{
		$default = array(
			'timestamp'	=>	time(),
			'method'	=>	'GET',
			'path'	=>	'',
			'host'	=>	'',
			'port'	=>	80,
			'ext'	=>	null
		);

		// Only include the necessary parameters
		foreach (array_keys($default) as $key)
		{
			if (isset($params[$key]))
			{
				$default[$key] = $params[$key];
			}
		}

		// Nuke the ext key if it isn't being used
		if ($default['ext'] === null)
		{
			unset($default['ext']);
		}

		// Ensure the method parameter is uppercase
		$default['method'] = strtoupper($default['method']);

		// Generate the data string
		$data = implode("\n", $default);

		// Generate the hash
		$hash = hash_hmac('sha256', $data, $secret);

		// Return base64 value
		return base64_encode($hash);
	}

	/**
	 * Generate the full Hawk header string
	 * @param  string $key    The identifier key
	 * @param  string $secret The shared secret
	 * @param  array  $params The MAC data parameters
	 * @return string         The Hawk header string
	 */
	private function generateHeader($key = '', $secret = '', $method = 'GET', $url = array(), $appData = array())
	{

		$url = parse_url($url);

		if ( ! isset($url['port']))
		{
			$params['port'] = ($url['scheme'] === 'https') ? 443 : 80;
		} else {
			$params['port'] = $url['port'];
		}

		$params['host'] = $url['host'];
		$params['path'] = $url['path'] . (isset($url['query']) ? $url['query'] : '');
		$params['method'] = $method;
		$params['ext'] = (count($appData) > 0) ? http_build_query($appData) : null;
		$params['timestamp'] = (isset($params['timestamp'])) ? $params['timestamp'] : time();
		#die(var_dump($params));

		// Generate the MAC address
		$mac = $this->generateMac($secret, $params);

		// Make the header string
		$header = 'Hawk id="'.$key.'", ts="'.$params['timestamp'].'", ';
		$header .= (isset($params['ext'])) ? 'ext="'.$params['ext'].'", ' : '';
		$header .= 'mac="'.$mac.'"';

		return $header;
	}

	/**
	 * Parse the Hawk header string into an array of parts
	 * @param  string $hawk The Hawk header
	 * @return array        The induvidual parts of the Hark header
	 */
	private function parseHeader($hawk = '')
	{
		$segments = explode(', ', substr(trim($hawk), 5, -1));

		$parts['id'] = substr($segments[0], 4, strlen($segments[0])-5);
		$parts['timestamp'] = substr($segments[1], 4, strlen($segments[1])-5);
		$parts['mac'] = (count($segments) === 4) ? substr($segments[3], 5, strlen($segments[3])) : substr($segments[2], 5, strlen($segments[2]));
		$parts['ext'] = (count($segments) === 4) ? substr($segments[2], 5, strlen($segments[2])-6) : null;

		if ($parts['ext'] === null)
		{
			unset($parts['ext']);
		}

		return $parts;
	}

	/**
	 * Verify the received Hawk header
	 * @param  string $hawk   The Hawk header string
	 * @param  array  $params The MAC data parameters
	 * @param  string $secret The shared secret
	 * @return bool           True if the header validates, otherwise false
	 */
	public function verifyHeader($hawk = '', $params = array(), $secret = '')
	{
		// Parse the header
		$parts =  $this->parseHeader($hawk);

		if (time() <= $parts['timestamp'] + 120) { #time buffer

			$params['timestamp'] = $parts['timestamp'];

			if (isset($parts['ext'])) {
				$params['ext'] = $parts['ext'];
			}

			// Generate the MAC
			$test = $this->generateMac($secret, $params);

			// Test against the received MAC
			return ($test === $parts['mac']);

		}
	}


	

}