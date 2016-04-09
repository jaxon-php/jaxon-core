<?php

namespace Xajax\Utils;

// use Symfony\Component\Translation\Translator;
// use Symfony\Component\Translation\MessageSelector;
// use Symfony\Component\Translation\Loader\PhpFileLoader;

class Translator
{
	protected $xTranslator;
	protected $xConfig;
	protected $sDefaultLocale = 'en';
	protected $sResourceDir;
	// Translations array
	protected $aMessages;

	public function __construct($sResourceDir, $xConfig)
	{
		// Set the translation resource directory
		$this->sResourceDir = trim($sResourceDir);

		// Set the config manager
		$this->xConfig = $xConfig;

		/*$this->xTranslator = new Translator($this->defaultLocale, new MessageSelector());
		$this->xTranslator->setFallbackLocales(array($this->defaultLocale));
		$this->xTranslator->addLoader('php', new PhpFileLoader());*/

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
			'errors.output.already-sent' => "Output has already been sent to the browser at :location.",
			'errors.output.advice' => "Please make sure the command \$xajax->processRequest() is placed before this.",
		);
	}

	/**
	 * Get a translated string
	 *
	 * @param string		$sText				The key of the translated string
	 * @param string		$aPlaceHolders		The placeholders of the translated string
	 * @param string		$sLanguage			The language of the translated string
	 *
	 * @return string		The translated string
	 */
	public function trans($sText, array $placeholders = array(), $sLanguage = null)
	{
		$sText = trim((string)$sText);
		/* if(!$sLanguage)
		{
			$sLanguage = $this->xConfig->getOption('language');
		}
		if(!$sLanguage)
		{
			$sLanguage = $this->sDefaultLocale;
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
