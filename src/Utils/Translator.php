<?php

namespace Xajax\Utils;

// use Symfony\Component\Translation\Translator;
// use Symfony\Component\Translation\MessageSelector;
// use Symfony\Component\Translation\Loader\PhpFileLoader;

/*
	File: Translator.php

	Contains the Translator class.

	Title: Translator class

	Please see <copyright.php> for a detailed description, copyright
	and license information.
*/

/*
	@package Xajax
	@version $Id: Translator.php 362 2007-05-29 15:32:24Z calltoconstruct $
	@copyright Copyright (c) 2005-2007 by Jared White & J. Max Wilson
	@copyright Copyright (c) 2008-2010 by Joseph Woolley, Steffen Konerow, Jared White  & J. Max Wilson
	@license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

class Translator
{
	protected $xTranslator;
	protected $sLanguage;
    protected $sDefaultLocale = 'en';
    protected $sResourceDir;
	// Translations array
	protected $aMessages;

    public function __construct($sResourceDir)
    {
        // Set the translations resource directory
        $this->sResourceDir = $sResourceDir;
        /*$this->xTranslator = new Translator($this->defaultLocale, new MessageSelector());
        $this->xTranslator->setFallbackLocales(array($this->defaultLocale));
        $this->xTranslator->addLoader('php', new PhpFileLoader());*/
        // Set the default language
        $this->configure('language', $this->sDefaultLocale);

		$this->aMessages = array(
			'debug.function.include' => "From include file: :file => :output",
			'errors.debug.ts-message' => "** Xajax Error Log - :timestamp ** :message \n",
			'errors.debug.write-log' => "Xajax was unable to write to the error log file: :file",
			'errors.debug.message' => "PHP Error Messages: :message",
			'errors.response.result.invalid' => "An invalid response was returned while processing this request.",
			'errors.response.data.invalid' => "The xajax response object could not load commands as the data provided was not a valid array.",
			'errors.response.class.invalid' => "Invalid class (:name) specified for html control; should be %inline, %block or %flow.",
			'errors.response.control.invalid' => "Invalid control (:class) passed to addChild; should be derived from Xajax\\Response\\Control.",
			'errors.response.parameter.invalid' => "Invalid parameter passed to xajaxControl::addChildren; should be array of Xajax\\Response\\Control objects",
			// A afficher lorsque l'exception DetectUri est lancée.
			'errors.uri.detect.message' => 'Xajax Error: Xajax failed to automatically identify your Request URI.',
			'errors.uri.detect.advice' => 'Please set the Request URI explicitly when you instantiate the Xajax object.',
			'errors.request.conversion' => "The incoming xajax data could not be converted from UTF-8.",
			'errors.mismatch.content-types' => "Cannot mix content types in a single response: :type",
			'errors.mismatch.encodings' => "Cannot mix character encodings in a single response: :encoding",
			'errors.mismatch.entities' => "Cannot mix output entities (true/false) in a single response: :entities",
			'errors.mismatch.types' => "Cannot mix response types while processing a single request: :class",
			'errors.events.invalid' => "Invalid event request received; no event was registered with the name :name.",
			'errors.functions.invalid' => "Invalid function request received; no request processor found with the name :name.",
			'errors.functions.invalid-declaration' => "Invalid function declaration for UserFunction.",
			'errors.objects.invalid' => "Invalid object request received; no object :class or method :method found.",
			'errors.objects.instance' => "To register a callable object, please provide an instance of the desired class.",
			'errors.register.method' => "Failed to locate registration method for the following: :args",
			'errors.register.invalid' => "Attempt to register invalid plugin: :name; " .
				"should be derived from Xajax\\Plugin\\Request or Xajax\\Plugin\\Response.",
			'errors.component.load' => "Error: the :name Javascript component could not be included. Perhaps the URL is incorrect?\\nURL: :url",
		);
    }

	/*
		Function: configure
		
		Called by the main xajax object as configuration options are set.  See also:
		<Xajax::configure>.  The <Xajax\Utils\Translator> tracks the following configuration
		options.
		Parameters:
		
		- language (string, default 'en'): The currently selected language.
	*/
	public function configure($sName, $mValue)
	{
		if($sName == 'language')
        {
			$this->sLanguage = $mValue;
            // Register the language translation files
            /*$aFiles = array('errors.php');
            $sLanguage = substr($this->sLanguage, 0, 2);
            foreach($aFiles as $sFile)
            {
                $sFilePath = $this->sResourceDir . "/$sLanguage/$sFile";
                if(file_exists($sFilePath))
                {
                    $this->xTranslator->addResource('php', $sFilePath, $sLanguage);
                }
            }*/
		}
	}

	/*
		Function: trans

		Parameters

		$sText - (string):  The text to translate.
		$aPlaceHolders - (array): The placeholders in the text.
		$sLanguage - (string): The language to translate to.

		Returns:

		string : The translated text.
	*/
    public function trans($sText, array $placeholders = array(), $sLanguage = null)
    {
		$sText = trim((string)$sText);
        /* if(!$sLanguage)
        {
            $sLanguage = $this->sLanguage;
        }
        return $this->xTranslator->trans($sText, $placeholders, 'messages', $sLanguage); */
		if(!array_key_exists($sText, $this->aMessages))
		{
		   return $sText;
		}
		$message = $this->aMessages[$sText];
		foreach($placeholders as $name => $value)
		{
			$message = str_replace(":$name", $value, $message);
		}
		return $message;
    }
}
