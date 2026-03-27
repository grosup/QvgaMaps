<?php
/**
 * Map Application Entry Point
 * Uses MVC pattern: Controller prepares data, View template handles presentation
 */

require_once 'config.php';
require_once 'class/Session.php';
require_once 'class/Renderer.php';
require_once 'class/controller/MapPageController.php';

use NokiaMaps\Session\Session;
use NokiaMaps\Renderer\Renderer;
use NokiaMaps\Controller\MapPageController;

// Initialize session
$session = new Session();

// Initialize renderer using token from config
$renderer = new Renderer($session, MAPBOX_TOKEN);

// Use controller to handle the request and render the page
$controller = new MapPageController($session, $renderer);
$controller->render();
