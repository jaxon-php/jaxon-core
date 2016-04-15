<?php

namespace Xajax\Response;

use \Xajax\Xajax;

trait FactoryTrait
{
	protected $response = null;

	/**
	 * Set the global Xajax response object
	 *
	 * @return void
	 */
	public function setGlobalResponse()
	{
		$this->response = Xajax::getGlobalResponse();
	}

	/**
	 * Create a new Xajax response object
	 *
	 * @return object		The new Xajax response object
	 */
	public function newResponse()
	{
		return new Response();
	}
}
