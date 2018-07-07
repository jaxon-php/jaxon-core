<?php

return [
    'errors' => [
        'debug' => [
            'ts-message' => "** Log d'erreur Jaxon - :timestamp ** :message \n",
            'write-log' => "Jaxon n'a pas pu écrire dans le fichier de log: :file",
            'message' => "Messages d'erreur PHP: :message",
        ],
        'response' => [
            'result.invalid' => "Une réponse invalide a été renvoyée lors du traitement de la requête.",
            'data.invalid' => "La réponse Jaxon ne peut traiter les commandes car les données fournies sont invalides.",
        ],
        // A afficher lorsque l'exception DetectUri est lancée.
        'uri' => [
            'detect' => [
                'message' => "Jaxon n'a pas pu détecter automatiquement l'URI de votre requête.",
                'advice' => "Vous devriez indiquer l'URI explicitement lorsque vous créez l'objet Jaxon.",
            ],
        ],
        'request' => [
            'conversion' => "Les données Jaxon reçues n'ont pas pu être converties de l'UTF-8.",
        ],
        'mismatch' => [
            'content-types' => "Il ne peut y avoir des types de contenu différents dans une seule réponse: :type",
            'encodings' => "Il ne peut y avoir des encodages de caractères différents dans une seule réponse: :encoding",
            'entities' => "Il ne peut y avoir des output entities (vrai/faux) différents dans une seule réponse: :entities",
            'types' => "Il ne peut y avoir des types de réponse différents dans le traitement d'une requête : :class",
        ],
        'events' => [
            'invalid' => "La requête indique un évènement invalide; il n'existe pas d'évènement :name.",
        ],
        'functions' => [
            'invalid' => "La requête indique une fonction invalide; il n'existe pas de fonction :name.",
            'invalid-declaration' => "La déclaration de fonction est invalide.",
        ],
        'objects' => [
            'invalid' => "La requête indique un objet invalide; il n'existe pas de classe :class ou de méthode :method.",
            'instance' => "Pour enregistrer un objet, vous devez fournir une instance de la classe correspondante.",
        ],
        'register' => [
            'method' => "Une fonction d'enregistrement n'a pas pu être trouvée pour cet élément: :args",
            'invalid' => "Tentative d'enregistrer un plugin invalide: :name; " .
                "le plugin doit dériver de Jaxon\\Plugin\\Request ou Jaxon\\Plugin\\Response.",
        ],
        'component' => [
            'load' => "Le composant javascript :name n'a pas pu être inclus. Cette URL serait-elle incorrecte ?\\nURL: :url",
        ],
        'output' => [
            'already-sent' => "La sortie a déjà été envoyée au navigateur à :location.",
            'advice' => "Assurez-vous que la commande \$jaxon->processRequest() est placée avant ceci.",
        ],
        'magic' => [
            'get' => "Accès à la propriété inconnue :name avec la surcharge magique __get à la ligne :line du fichier :file.",
            'set' => "Accès à la propriété inconnue :name avec la surcharge magique __set à la ligne :line du fichier :file.",
        ],
    ],
];
