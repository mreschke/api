<?php

return [

	// Driver (native or http)
	'driver' => env('API_DRIVER', 'native'),

	// Native Driver Info
	'host' => env('API_REDIS_HOST', '127.0.0.1'),
	'port' => env('API_REDIS_PORT', 6379),
	'database' => env('API_REDIS_DATABASE', 0),
	'prefix' => env('API_REDIS_PREFIX', 'api:mreschke/api'),

	// Http Driver Info
	'url' => env('API_URL', 'http://api.xendev1.dynatronsoftware.com'),
	'key' => env('API_KEY'),
	'secret' => env('API_SECRET'),
	'cache' => env('API_CACHE', false),

	// Is this an API rest server install
	'server' => env('API_SERVER', false),

];
