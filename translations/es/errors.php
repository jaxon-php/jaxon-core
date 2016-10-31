<?php

return array(
    'debug.function.include' => "Del archivo incluido: :file => :output",
    'errors.debug.ts-message' => "** Registro de errores de Jaxon - :timestamp ** :message \n",
    'errors.debug.write-log' => "Jaxon es incapaz de escribir en el archivo de registro de errores: :file",
    'errors.debug.message' => "Mensajes de error de PHP: :message",
    'errors.response.result.invalid' => "Una respuesta invalida fue devuelta al procesar esta solicitud.",
    'errors.response.data.invalid' => "El objeto de respuesta Jaxon no podía cargar comandos ya que los datos proporcionados no eran válidos.",    
    'errors.uri.detect.message' => "Jaxon no pudo identificar automáticamente la URI de la solicitud.",
    'errors.uri.detect.advice' => "Por favor ajuste la URI de la solicitud explícita cuando instancia el objeto Jaxon.",
    'errors.request.conversion' => "Los datos entrantes Jaxon no se pueden convertir de UTF-8.",
    'errors.mismatch.content-types' => "No se puede mezclar tipos de contenido en una sola respuesta: :type",
    'errors.mismatch.encodings' => "No se puede mezclar la codificación de caracteres en una sola respuesta: :encoding",
    'errors.mismatch.entities' => "No se puede mezclar entidades de salida (verdadero / falso) en una sola respuesta: :entities",
    'errors.mismatch.types' => "No se puede mezclar tipos de respuesta al procesar una sola petición: :class",
    'errors.events.invalid' => "Solicitud de evento invalida recibida; No hay eventos registrados con el nombre :name.",
    'errors.functions.invalid' => "Solicitud de función invalida recibida; no hay procesador de peticiones con el nombre :name.",
    'errors.functions.invalid-declaration' => "Declaración de funcion invalida.",
    'errors.objects.invalid' => "Solicitud de objeto invalida recibida; Sin objeto :class o metodo :method encontrado.",
    'errors.objects.instance' => "Para registrar un objeto, por favor de proveer una instancia de la clase deseada.",
    'errors.register.method' => "Fallo al localizar metodo de registro con los siguientes argumentos: :args",
    'errors.register.invalid' => "Intento de registrar plugin invalido: :name; " .
        "debe derivarse de Jaxon\\Plugin\\Request o Jaxon\\Plugin\\Response.",
    'errors.component.load' => "El componente de javascript :name no puede ser incluido. quizas la URL es incorrecta?\\nURL: :url",
    'errors.output.already-sent' => "La salida ha sido enviada al navegador en :location.",
    'errors.output.advice' => "Asegúrese que el comando \$jaxon->processRequest() fue colocado antes de esto.",
);
