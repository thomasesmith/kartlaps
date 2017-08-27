<?php 

require_once __DIR__ . '/_config.php';

function autoload_classes($className) {
    $file = __DIR__ . '/classes/' . str_replace('\\', '/', $className) . '.php';

    if (file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');
