<?php
/**
 * Marker Endpoint
 * Handles marker-related actions and redirects to main page
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../autoloader.php';

use NokiaMaps\Controller\MarkerController;
use NokiaMaps\Session;

// Initialize session
$session = new Session();

// Handle marker request
$controller = new MarkerController($session);
$controller->handle();
