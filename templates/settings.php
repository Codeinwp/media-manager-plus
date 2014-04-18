<?php
global $wpsf_ubermedia_settings;
$wpsf_ubermedia_settings = apply_filters( 'uber_media_settings', $wpsf_ubermedia_settings );
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'sources';
?>
<div class="wrap">
	<div id="icon-upload" class="icon32"></div>
	<h2>
		<?php _e( 'Media Manager Plus', 'media-manager-plus' ); ?>
		<?php echo apply_filters( 'uber_media_title_version', "<span class='uber-version'>v " . MMP_VERSION . "</span>" ); ?>
	</h2>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $wpsf_ubermedia_settings as $tab ) {
			// Hide Extensions tab if none available
			$extensions = media_manager_plus()->extensions->available_extensions();
			if ( count( $extensions ) == 0 && $tab['section_id'] == 'extensions' ) {
				continue;
			}
			?>
			<a href="?page=<?php echo $_GET['page']; ?>&tab=<?php echo $tab['section_id']; ?>" class="nav-tab<?php echo( $active_tab == $tab['section_id'] ? ' nav-tab-active' : '' ); ?>"><?php echo $tab['section_title']; ?></a>
		<?php } ?>
	</h2>

	<form action="options.php" method="post">
		<?php settings_fields( media_manager_plus()->settings->wpsf->get_option_group() ); ?>
		<?php media_manager_plus()->settings->do_settings_sections( media_manager_plus()->settings->wpsf->get_option_group() ); ?>
	</form>
</div>