<?php

spl_autoload_register(function($className) {
    if (0 === strpos($className, 'Phlexy\\')) {
        require dirname(__DIR__) . '/' . strtr($className, '\\', '/') . '.php';
    }
});