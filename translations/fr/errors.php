<?php

return array(
    'debug.function.include' => "&Agrave; partir du fichier inclus: :file => :output",
    'errors.debug.ts-message' => "** Log d'erreur Jaxon - :timestamp ** :message \n",
    'errors.debug.write-log' => "Jaxon n'a pas pu écrire dans le fichier de log: :file",
    'errors.debug.message' => "Messages d'erreur PHP: :message",
    'errors.response.result.invalid' => "Une réponse invalide a été renvoyée lors du traitement de la requête.",
    'errors.response.data.invalid' => "La réponse Jaxon ne peut traiter les commandes car les données fournies sont invalides.",
    // A afficher lorsque l'exception DetectUri est lancée.
    'errors.uri.detect.message' => "Jaxon n'a pas pu détecter automatiquement l'URI de votre requête.",
    'errors.uri.detect.advice' => "Vous devriez indiquer l'URI explicitement lorsque vous créez l'objet Jaxon.",
    'errors.request.conversion' => "Les données Jaxon reçues n'ont pas pu être converties de l'UTF-8.",
    'errors.mismatch.content-types' => "Il ne peut y avoir des types de contenu différents dans une seule réponse: :type",
    'errors.mismatch.encodings' => "Il ne peut y avoir des encodages de caractères différents dans une seule réponse: :encoding",
    'errors.mismatch.entities' => "Il ne peut y avoir des output entities (vrai/faux) différents dans une seule réponse: :entities",
    'errors.mismatch.types' => "Il ne peut y avoir des types de réponse différents dans le traitement d'une requête : :class",
    'errors.events.invalid' => "La requête indique un évènement invalide; il n'existe pas d'évènement :name.",
    'errors.functions.invalid' => "La requête indique une fonction invalide; il n'existe pas de fonction :name.",
    'errors.functions.invalid-declaration' => "Une fonction invalide a été déclarée.",
    'errors.objects.invalid' => "La requête indique un objet invalide; il n'existe pas de classe :class ou de méthode :method.",
    'errors.objects.instance' => "Pour enregistrer un objet, vous devez fournir une instance de la classe correspondante.",
    'errors.register.method' => "Une fonction d'enregistrement n'a pas pu être trouvée pour cet élément: :args",
    'errors.register.invalid' => "Tentative d'enregistrer un plugin invalide: :name; " .
        "le plugin doit dériver de Jaxon\\Plugin\\Request ou Jaxon\\Plugin\\Response.",
    'errors.component.load' => "Le composant javascript :name n'a pas pu être inclus. Cette URL serait-elle incorrecte ?\\nURL: :url",
    'errors.output.already-sent' => "La sortie a déjà été envoyée au navigateur à :location.",
    'errors.output.advice' => "Assurez-vous que la commande \$jaxon->processRequest() est placée avant ceci.",
);
