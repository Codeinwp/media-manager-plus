<?php

global $wpsf_ubermedia_settings;

$wpsf_ubermedia_settings['sources'] = array(
	'section_id'    => 'sources',
	'section_title' => __( 'Available Sources', 'media-manager-plus' ),
	'section_order' => 1,
	'fields'        => array(
		array(
			'id'    => 'available',
			'title' => __( '', 'media-manager-plus' ),
			'desc'  => __( '', 'media-manager-plus' ),
			'type'  => 'custom',
			'std'   => ''
		),
	)
);

$wpsf_ubermedia_settings['extensions'] = array(
	'section_id'    => 'extensions',
	'section_title' => __( 'Extensions', 'media-manager-plus' ),
	'section_order' => 2,
	'fields'        => array(
		array(
			'id'    => 'useful-links',
			'title' => '',
			'desc'  => '',
			'type'  => 'custom',
			'std'   => ''
		)
	)
);

$wpsf_ubermedia_settings['general'] = array(
	'section_id'    => 'general',
	'section_title' => __( 'General Settings', 'media-manager-plus' ),
	'section_order' => 3,
	'fields'        => array(
		array(
			'id'    => 'show-connected',
			'title' => __( 'Show Connected Sources', 'media-manager-plus' ),
			'desc'  => __( 'Only show connected sources in side menu of the media popup', 'media-manager-plus' ),
			'type'  => 'checkbox',
			'std'   => 0
		),
		array(
			'id'    => 'safe-mode',
			'title' => __( 'Safe Mode', 'media-manager-plus' ),
			'desc'  => __( 'Safe mode filters sources for nude, explicit or NSFW media', 'media-manager-plus' ),
			'type'  => 'checkbox',
			'std'   => 1
		),
	)
);

$wpsf_ubermedia_settings['support'] = array(
	'section_id'    => 'support',
	'section_title' => 'Support',
	'section_order' => 4,
	'fields'        => array(
		array(
			'id'    => 'useful-links',
			'title' => 'Useful Links',
			'desc'  => '',
			'type'  => 'custom',
			'std'   => 'Website: <a href="http://dev7studios.com/media-manager-plus" target="_blank">Media Manager Plus</a><br />
            Created by: <a href="http://dev7studios.com" target="_blank">Dev7studios</a><br />
            Support: <a href="http://support.dev7studios.com/discussions/media-manager-plus-wordpress-plugin" target="_blank">Support Forums</a><br />
            Changelog: <a href="http://wordpress.org/extend/plugins/uber-media/changelog" target="_blank">Changelog</a><br />'
		)
	)
);