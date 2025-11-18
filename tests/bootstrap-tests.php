<?php

/**
 * PHPUnit Bootstrap para LLM Manager Extension
 * 
 * Este archivo carga el autoloader del CPANEL y registra el namespace de tests.
 */

// Cargar autoloader del CPANEL
require_once __DIR__ . '/../../../CPANEL/vendor/autoload.php';

// Registrar autoload-dev de la extensión manualmente
$loader = require __DIR__ . '/../../../CPANEL/vendor/autoload.php';
$loader->addPsr4('Bithoven\\LLMManager\\Tests\\', __DIR__);
