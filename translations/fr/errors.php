<?php

return array(
	'debug.function.include' => "&Agrave; partir du fichier inclus: :file => :output",
	'errors.debug.ts-message' => "** Xajax Error Log - :timestamp ** :message \n",
	'errors.debug.write-log' => "Xajax n'a pas pu écrire dans le fichier de log: :file",
	'errors.debug.message' => "Messages d'erreur PHP: :message",
	'errors.response.result.invalid' => "Une réponse invalide a été renvoyée lors du traitement de cette requête.",
	'errors.response.data.invalid' => "The xajax response object could not load commands as the data provided was not a valid array.",
	'errors.response.class.invalid' => "Invalid class (:name) specified for html control; should be %inline, %block or %flow.",
	'errors.response.control.invalid' => "Invalid control (:class) passed to addChild; should be derived from Xajax\\Response\\Control.",
	'errors.response.parameter.invalid' => "Invalid parameter passed to xajaxControl::addChildren; should be array of Xajax\\Response\\Control objects",
	// A afficher lorsque l'exception DetectUri est lancée.
	'errors.uri.detect.message' => "Xajax n'a pas pu détecter automatiquement l'URI de votre requête.",
	'errors.uri.detect.advice' => "Vous divriez indiquer l'URI explicitement lorsque vous créez l'objet Xajax.",
	'errors.request.conversion' => "Les données Xajax reçues n'ont pas pu être converties de l'UTF-8.",
	'errors.mismatch.content-types' => "On ne peut pas avoir des types de contenu différents dans une seule réponse: :type",
	'errors.mismatch.encodings' => "On ne peut pas avoir des encodages de caractères différents dans une seule réponse: :encoding",
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
