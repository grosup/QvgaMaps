<?php

use PHPUnit\Framework\TestCase;
use NokiaMaps\View\MapView;
use NokiaMaps\Session\MapSession;
use NokiaMaps\Renderer\MapRenderer;

class MapViewTest extends TestCase
{
    private MapView $mapView;
    private MapSession $session;
    private MapRenderer $renderer;

    protected function setUp(): void
    {
        // Reset session for clean test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_write_close();
        session_start();

        $this->session = new MapSession();

        // Mock the renderer to avoid external dependencies
        $this->renderer = $this->createMock(MapRenderer::class);
        $this->renderer->method('reverseGeocode')->willReturn('Berlin, Germany');

        $this->mapView = new MapView($this->session, $this->renderer);
    }

    public function testRenderGeneratesCompleteHtml(): void
    {
        // Capture output
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check for complete HTML structure
        $this->assertStringContainsString('<!DOCTYPE html>', $output);
        $this->assertStringContainsString('<html>', $output);
        $this->assertStringContainsString('<head>', $output);
        $this->assertStringContainsString('<title>', $output);
        $this->assertStringContainsString('</title>', $output);
        $this->assertStringContainsString('</head>', $output);
        $this->assertStringContainsString('<body', $output);
        $this->assertStringContainsString('</body>', $output);
        $this->assertStringContainsString('</html>', $output);
    }

    public function testRenderIncludesMapImage(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('controller/render_map.php', $output);
        $this->assertStringContainsString('data-testid="map"', $output);
        $this->assertStringContainsString('<img', $output);
    }

    public function testRenderIncludesNavigationButtons(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check for navigation buttons
        $this->assertStringContainsString('name="left"', $output);
        $this->assertStringContainsString('name="right"', $output);
        $this->assertStringContainsString('name="up"', $output);
        $this->assertStringContainsString('name="down"', $output);
        $this->assertStringContainsString('name="zoom_in"', $output);
        $this->assertStringContainsString('name="zoom_out"', $output);

        // Check for test IDs
        $this->assertStringContainsString('data-testid="map-left"', $output);
        $this->assertStringContainsString('data-testid="map-right"', $output);
        $this->assertStringContainsString('data-testid="map-up"', $output);
        $this->assertStringContainsString('data-testid="map-down"', $output);
        $this->assertStringContainsString('data-testid="map-zoom-in"', $output);
        $this->assertStringContainsString('data-testid="map-zoom-out"', $output);
    }

    public function testRenderIncludesSearchForm(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        $this->assertStringContainsString('controller/search.php', $output);
        $this->assertStringContainsString('data-testid="search-address"', $output);
        $this->assertStringContainsString('data-testid="search-submit"', $output);
        $this->assertMatchesRegularExpression('/placeholder=["\'][^"\']*Street/i', $output); // Case-insensitive due to Nokia testing quirks
    }

    public function testRenderIncludesCoordinates(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check for coordinate display
        $this->assertStringContainsString('Lat:', $output);
        $this->assertStringContainsString('Lon:', $output);
        $this->assertStringContainsString('Zoom:', $output);

        // Get actual coordinates from session
        $coords = $this->session->getCoordinates();
        $this->assertStringContainsString((string) $coords['lat'], $output);
        $this->assertStringContainsString((string) $coords['lon'], $output);
        $this->assertStringContainsString((string) $coords['zoom'], $output);
    }

    public function testRenderIncludesStyleLinks(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check for style links
        $this->assertStringContainsString('streets-v12', $output);
        $this->assertStringContainsString('outdoors-v12', $output);
        $this->assertStringContainsString('satellite-v9', $output);

        // Active style should be highlighted
        $this->assertStringContainsString('data-testid="streets-v12"', $output);

        // Inactive styles should be links
        $this->assertMatchesRegularExpression('/<a[^>]*href="[^"]*outdoors-v12[^"]*"/', $output);
        $this->assertMatchesRegularExpression('/<a[^>]*href="[^"]*satellite-v9[^"]*"/', $output);
    }

    public function testRenderWithGeocodedLocation(): void
    {
        $this->renderer = $this->createMock(MapRenderer::class);
        $this->renderer->method('reverseGeocode')->willReturn('Paris, France');

        $this->mapView = new MapView($this->session, $this->renderer);

        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Should include geocoded location in title
        $this->assertStringContainsString('Paris', $output);
        $this->assertStringContainsString('France', $output);
        $this->assertStringContainsString('Lat:', $output);
        $this->assertStringContainsString('Lon:', $output);
    }

    public function testRenderWithoutGeocodedLocation(): void
    {
        $this->renderer = $this->createMock(MapRenderer::class);
        $this->renderer->method('reverseGeocode')->willReturn('');

        $this->mapView = new MapView($this->session, $this->renderer);

        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Should use coordinate-based title when geocoding fails
        $this->assertStringContainsString('Map', $output);
        $this->assertStringContainsString('Lat:', $output);
        $this->assertStringContainsString('Lon:', $output);
        $this->assertStringContainsString('Zoom:', $output);
    }

    public function testRenderResponsiveToCoordinateChanges(): void
    {
        // Change coordinates
        $this->session->setCoordinates(48.8566, 2.3522, 10);

        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Output should reflect new coordinates
        $this->assertStringContainsString('48.8566', $output);
        $this->assertStringContainsString('2.3522', $output);
        $this->assertStringContainsString('10', $output);
    }

    public function testRenderAfterStyleChange(): void
    {
        // Change map style
        $this->session->setMapStyle('satellite-v9');

        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check for style links with correct active state
        $this->assertMatchesRegularExpression(
            '/data-testid="satellite-v9"[^>]*class="active"/',
            $output,
        );
    }

    public function testRenderNokia225Optimized(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // Check Nokia 225 specific optimizations
        // Max width should be set for small screen
        $this->assertMatchesRegularExpression('/max-width:\s*320px/i', $output);

        // Font size should be small
        $this->assertMatchesRegularExpression('/font-size:\s*11px/i', $output);

        // Map image should have Nokia-optimized dimensions
        $this->assertMatchesRegularExpression('/width:\s*310px/i', $output);
        $this->assertMatchesRegularExpression('/height:\s*250px/i', $output);
    }

    public function testButtonStyleConsistency(): void
    {
        ob_start();
        $this->mapView->render();
        $output = ob_get_clean();

        // All navigation buttons should have consistent styling
        $buttonPattern = '/style="[^"]*width:30px[^"]*height:30px/';
        $matches = [];
        preg_match_all($buttonPattern, $output, $matches);

        // Should have multiple buttons with consistent size
        $this->assertGreaterThan(4, count($matches[0]));
    }
}
