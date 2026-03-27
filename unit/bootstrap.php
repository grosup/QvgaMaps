<?php
// PHPUnit Bootstrap file for Nokia Maps unit tests

// Register autoloader for classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = 'NokiaMaps\\';
    $base_dir = __DIR__ . '/../src/class/';

    // Check if the class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separator with directory separator
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Alternatively, manually require all class files
$classFiles = [
    __DIR__ . '/../src/class/Session.php',
    __DIR__ . '/../src/class/controller/NavigationController.php',
    __DIR__ . '/../src/class/Renderer.php',
    __DIR__ . '/../src/class/GeocodingService.php',
    __DIR__ . '/../src/class/controller/MapPageController.php',
    __DIR__ . '/../src/class/controller/SearchPageController.php',
    __DIR__ . '/../src/class/controller/StyleController.php',
];

foreach ($classFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
