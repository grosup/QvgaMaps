<?php
/**
 * Search Endpoint
 * Simple wrapper that instantiates SearchController and handles the request
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../class/GeocodingService.php';
require_once __DIR__ . '/../class/controller/SearchController.php';

use NokiaMaps\Controller\SearchController;

session_start();

// Create controller and handle request
$controller = new SearchController(MAPBOX_TOKEN);
$controller->handle();
