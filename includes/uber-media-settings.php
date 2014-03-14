<?php

global $wpsf_ubermedia_settings;
$plugin_l10n = 'uber-media';

$wpsf_ubermedia_settings[] = array(
    'section_id' => 'sources',
    'section_title' => 'Available Sources',
    'section_order' => 1,
    'fields' => array(
        array(
            'id' => 'available',
            'title' => __( '', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'custom',
            'std' => ''
        ),
     )
);

$wpsf_ubermedia_settings[] = array(
    'section_id' => 'extensions',
    'section_title' => 'Extensions',
    'section_order' => 2,
    'fields' => array(
        array(
            'id' => 'useful-links',
            'title' => '',
            'desc' => '',
            'type' => 'custom',
            'std' => ''
        )
    )
);

$wpsf_ubermedia_settings[] = array(
    'section_id' => 'general',
    'section_title' => 'General Settings',
    'section_order' => 3,
    'fields' => array(
        array(
            'id' => 'show-connected',
            'title' => __( 'Show Connected Sources', $plugin_l10n ),
            'desc' => __( 'Only show connected sources in side menu of the media popup', $plugin_l10n ),
            'type' => 'checkbox',
            'std' => 0
        ),
        array(
            'id' => 'safe-mode',
            'title' => __( 'Safe Mode', $plugin_l10n ),
            'desc' => __( 'Safe mode filters sources for nude, explicit or NSFW media', $plugin_l10n ),
            'type' => 'checkbox',
            'std' => 1
        ),
    )
);

$wpsf_ubermedia_settings[] = array(
    'section_id' => 'support',
    'section_title' => 'Support',
    'section_order' => 4,
    'fields' => array(
        array(
            'id' => 'useful-links',
            'title' => 'Useful Links',
            'desc' => '',
            'type' => 'custom',
            'std' => 'Website: <a href="http://dev7studios.com/media-manager-plus" target="_blank">Media Manager Plus</a><br />
            Created by: <a href="http://dev7studios.com" target="_blank">Dev7studios</a><br />
            Support: <a href="http://support.dev7studios.com/discussions/media-manager-plus-wordpress-plugin" target="_blank">Support Forums</a><br />
            Changelog: <a href="http://wordpress.org/extend/plugins/uber-media/changelog" target="_blank">Changelog</a><br />'
        )
    )
);

?>