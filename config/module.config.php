<?php

return array(
		'address-conf' => array(
				'adapter' => 'db-adapter',
				'geoloc' => array(
						'url' => 'https://maps.googleapis.com/maps/api/',
						'adapter' => 'http-adapter',
				),
		),
		'service_manager' => array(
				'invokables' => array(
						'geoloc' => 'Address\Geoloc\Geoloc',
				),
		),
);
