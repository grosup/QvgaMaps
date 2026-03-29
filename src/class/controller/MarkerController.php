<?php
/**
 * Marker Controller
 * Handles marker addition and removal actions
 */

namespace NokiaMaps\Controller;

use NokiaMaps\Session;

class MarkerController
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Handle marker actions (add/clear)
     */
    public function handle(): void
    {
        $action = $_POST['action'] ?? ($_GET['action'] ?? '');

        if ($action === 'clear') {
            $this->clearMarkers();
        } else {
            $this->addMarker();
        }

        $this->redirectToMap();
    }

    /**
     * Add a marker at current map center
     */
    private function addMarker(): void
    {
        $coords = $this->session->getCoordinates();
        $color = $this->session->getNextMarkerColor();

        $this->session->addMarker($coords['lat'], $coords['lon'], $color);
        $this->saveMarkersToCookie();
    }

    /**
     * Clear all markers
     */
    private function clearMarkers(): void
    {
        $this->session->clearMarkers();
        $this->saveMarkersToCookie();
    }

    /**
     * Save markers to cookie for persistence
     */
    private function saveMarkersToCookie(): void
    {
        $markers = $this->session->getMarkers();
        $markersJson = json_encode($markers);

        if (empty($markers)) {
            // Delete cookie when no markers
            setcookie('nokiamaps_markers', '', [
                'expires' => time() - 3600, // Expire in the past
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            // Set cookie to expire in 30 days
            setcookie('nokiamaps_markers', $markersJson, [
                'expires' => time() + 86400 * 30,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    /**
     * Redirect back to the main map page
     */
    private function redirectToMap(): void
    {
        header('Location: ../index.php');
        exit();
    }
}
