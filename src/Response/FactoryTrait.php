<?php

/**
 * FactoryTrait.php - Trait for Xajax Response Factory
 *
 * Make functions to create Xajax Response objects available to Xajax classes.
 *
 * @package xajax-core
 * @author Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @copyright 2016 Thierry Feuzeu <thierry.feuzeu@gmail.com>
 * @license https://opensource.org/licenses/BSD-2-Clause BSD 2-Clause License
 * @link https://github.com/lagdo/xajax-core
 */

namespace Xajax\Response;

use \Xajax\Xajax;

trait FactoryTrait
{
	/**
	 * The global response instance
	 *
	 * @var \Xajax\Response\Response
	 */
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
	 * @return \Xajax\Response\Response		The new Xajax response object
	 */
	public function newResponse()
	{
		return new Response();
	}
}
