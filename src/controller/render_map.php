<?php
/**
 * Map image renderer - OOP implementation
 * Outputs PNG image directly using MapRenderer
 */

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../class/Session.php';
require_once __DIR__ . '/../class/Renderer.php';

use NokiaMaps\Session\Session;
use NokiaMaps\Renderer\Renderer;

// Initialize session
$session = new Session();

// Create renderer using token from config
$renderer = new Renderer($session, MAPBOX_TOKEN);

// Output map image
$renderer->renderImage();
