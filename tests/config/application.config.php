<?php

return array(
    'modules' => array(
        'Address',
    	'Dal',
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            'Address' => __DIR__ .'/../../',
        	'Dal' => __DIR__ . '/../../vendor/buse974/dal',
        ),
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            __DIR__.'/autoload/{,*.}{global,local}.php',
        ),
    ),
);
