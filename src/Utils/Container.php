<?php

namespace Xajax\Utils;

class Container
{
	// The Dependency Injection Container
	private $di = null;

	// The only instance of the Container (Singleton)
	private static $xInstance = null;

	public static function getInstance()
	{
		if(!self::$xInstance)
		{
			self::$xInstance = new Container();    
		}
		return self::$xInstance;
	}

	private function __construct()
	{
		$this->di = new \Pimple\Container();
    }

	/**
	 * Set the parameters and create the objects in the dependency injection container
	 *
	 * @param string		$sTranslationDir	The translation directory
	 * @param string		$sTemplateDir		The template directory
	 *
	 * @return void
	 */
	public function init($sTranslationDir, $sTemplateDir)
    {
    	/*
    	 * Parameters
    	 */
    	// Translation directory
    	$this->di['translation_dir'] = $sTranslationDir;
    	// Template directory
    	$this->di['template_dir'] = $sTemplateDir;

    	/*
    	 * Services
    	 */
    	// Config manager
    	$this->di['config'] = function($c){
			return new Config();
		};
    	// Minifier
    	$this->di['minifier'] = function($c){
			return new Minifier();
		};
    	// Translator
    	$this->di['translator'] = function($c){
			return new Translator($c['translation_dir'], $c['config']);
		};
    	// Template engine
    	$this->di['template'] = function($c){
			return new Template($c['template_dir']);
		};
    }

	/**
	 * Get the config manager
	 *
	 * @return object		The config manager
	 */
	public function getConfig()
    {
    	return $this->di['config'];
	}

	/**
	 * Get the minifier
	 *
	 * @return object		The minifier
	 */
	public function getMinifier()
    {
    	return $this->di['minifier'];
	}

	/**
	 * Get the translator
	 *
	 * @return object		The translator
	 */
	public function getTranslator()
    {
    	return $this->di['translator'];
	}

	/**
	 * Get the template engine
	 *
	 * @return object		The template engine
	 */
	public function getTemplate()
    {
    	return $this->di['template'];
	}
}
