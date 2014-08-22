<div class="wrap about-wrap mmp-welcome">
	<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), media_manager_plus()->get_value('version') ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), media_manager_plus()->get_value('version') ); ?></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-welcome' ), 'index.php' ) ) ); ?>">
			<?php _e( "What's New", 'media-manager-plus' ); ?>
		</a>
		<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-support' ), 'index.php' ) ) ); ?>">
			<?php _e( 'Support', 'media-manager-plus' ); ?>
		</a>
	</h2>

	<div class="changelog">
		<h3><?php _e( 'Introducing Extensions', 'media-manager-plus' ); ?></h3>
		<?php media_manager_plus()->extensions->get_extensions(); ?>
	</div>
	<div class="return-to-dashboard">
		<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp' ), 'upload.php' ) ) ); ?>"><?php _e( 'Go to Media Manager Plus Settings', 'media-manager-plus' ); ?></a>
	</div>
</div>