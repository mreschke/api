<?php namespace Mreschke\Api\Http\Controllers;

use Parsedown;
use Mreschke\Helpers\Guest;
use Mreschke\Api\ApiInterface;
use Laravel\Lumen\Routing\Controller as Controller;

class ServerController extends Controller
{
    protected $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    /**
     * Show the readme
     * @return Response
     */
    public function index()
    {
        $browser = Guest::getBrowser();
        $isCurl = preg_match("/curl/i", $browser);

        #$content = $this->api->readme();
        #return $isCurl ? $content : view('api::server.index', compact('content'));
        return "welcome to api";
    }
}
