<?php
/**
 * Style Endpoint
 * Simple wrapper that instantiates StyleController and handles the request
 */

require_once __DIR__ . '/../class/Session.php';
require_once __DIR__ . '/../class/controller/StyleController.php';

use NokiaMaps\Session\Session;
use NokiaMaps\Controller\StyleController;

session_start();

// Create controller and handle request
$session = new Session();
$controller = new StyleController($session);
$controller->handle();
