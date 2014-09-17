<?php

return array(
		'address-conf' => array(
				'adapter' => 'db-adapter',
				'geoloc' => array(
						'url' => 'https://maps.googleapis.com/maps/api/',
						'adapter' => 'http-adapter',
				),
		),
		'dal-conf' => array(
				'adapter' => 'db-adapter',
				'cache'   => 'storage_memcached',
				'log'     => 'log-system',
				'namespace' => array(
						'addr' => array(
								'service' => 'Address\\Service',
								'mapper'  => 'Address\\Mapper',
								'model'   => 'Address\\Model',
						)
				)
		),
		'service_manager' => array(
				'invokables' => array(
						'geoloc' => 'Address\Geoloc\Geoloc',
				),
		),
);
