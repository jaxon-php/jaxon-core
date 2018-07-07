<?php

return [
    'errors' => [
        'debug' => [
            'ts-message' => "** Registro de errores de Jaxon - :timestamp ** :message \n",
            'write-log' => "Jaxon es incapaz de escribir en el archivo de registro de errores: :file",
            'message' => "Mensajes de error de PHP: :message",
        ],
        'response' => [
            'result.invalid' => "Una respuesta invalida fue devuelta al procesar esta solicitud.",
            'data.invalid' => "El objeto de respuesta Jaxon no podía cargar comandos ya que los datos proporcionados no eran válidos.",
        ],
        // A afficher lorsque l'exception DetectUri est lancée.
        'uri' => [
            'detect' => [
                'message' => "Jaxon no pudo identificar automáticamente la URI de la solicitud.",
                'advice' => "Por favor ajuste la URI de la solicitud explícita cuando instancia el objeto Jaxon.",
            ],
        ],
        'request' => [
            'conversion' => "Los datos entrantes Jaxon no se pueden convertir de UTF-8.",
        ],
        'mismatch' => [
            'content-types' => "No se puede mezclar tipos de contenido en una sola respuesta: :type",
            'encodings' => "No se puede mezclar la codificación de caracteres en una sola respuesta: :encoding",
            'entities' => "No se puede mezclar entidades de salida (verdadero / falso) en una sola respuesta: :entities",
            'types' => "No se puede mezclar tipos de respuesta al procesar una sola petición: :class",
        ],
        'events' => [
            'invalid' => "Solicitud de evento invalida recibida; No hay eventos registrados con el nombre :name.",
        ],
        'functions' => [
            'invalid' => "Solicitud de función invalida recibida; no hay procesador de peticiones con el nombre :name.",
            'invalid-declaration' => "Declaración de funcion invalida.",
        ],
        'objects' => [
            'invalid' => "Solicitud de objeto invalida recibida; Sin objeto :class o metodo :method encontrado.",
            'instance' => "Para registrar un objeto, por favor de proveer una instancia de la clase deseada.",
        ],
        'register' => [
            'method' => "Fallo al localizar metodo de registro con los siguientes argumentos: :args",
            'invalid' => "Intento de registrar plugin invalido: :name; " .
                "debe derivarse de Jaxon\\Plugin\\Request o Jaxon\\Plugin\\Response.",
        ],
        'component' => [
            'load' => "El componente de javascript :name no puede ser incluido. quizas la URL es incorrecta?\\nURL: :url",
        ],
        'output' => [
            'already-sent' => "La salida ha sido enviada al navegador en :location.",
            'advice' => "Asegúrese que el comando \$jaxon->processRequest() fue colocado antes de esto.",
        ],
        'magic' => [
            'get' => "Intentando leer propiedad desconocida :name con sobrecarga __get en línea :line en archivo :file.",
            'set' => "Intentando escribir propiedad desconocida :name con sobrecarga __set en línea :line en archivo :file.",
        ],
    ],
];
