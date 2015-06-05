<?php namespace Mreschke\Api\Http\Controllers;

use Mreschke\Api\ApiInterface;
use Mreschke\Api\Http\Controllers\Controller;

class ApiController extends Controller {

	protected $keystone;

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
		return view('api::index');
	}

}