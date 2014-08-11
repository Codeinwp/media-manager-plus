<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to Media Manager Plus', 'media-manager-plus' ), Media_Manager_Plus::get_value('version') ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Media Manager Plus %s upgrades the WordPress media manager with third party image sources.', 'media-manager-plus' ), MMP_VERSION ); ?></div>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-welcome' ), 'index.php' ) ) ); ?>">
			<?php _e( "What's New", 'media-manager-plus' ); ?>
		</a>
		<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp-support' ), 'index.php' ) ) ); ?>">
			<?php _e( 'Support', 'media-manager-plus' ); ?>
		</a>
	</h2>

	<div class="changelog">
		<h3><?php _e( 'Get Some Help', 'media-manager-plus' ); ?></h3>

		<div class="feature-section col three-col">
			<div>
				<h4><?php _e( 'Website', 'media-manager-plus' ); ?></h4>

				<p>
					<a target="_blank" href="http://dev7studios.com/media-manager-plus">Media Manager Plus</a>
				</p>
				<h4><?php _e( 'Created by', 'media-manager-plus' ); ?></h4>

				<p>
					<a target="_blank" href="http://dev7studios.com">Dev7studios</a>
				</p>
				<h4><?php _e( 'Support', 'media-manager-plus' ); ?></h4>

				<p>
					<a target="_blank" href="http://support.dev7studios.com/discussions/media-manager-plus-wordpress-plugin"><?php _e( 'Support Forums', 'media-manager-plus' ); ?></a>
				</p>
				<h4><?php _e( 'Changelog', 'media-manager-plus' ); ?></h4>

				<p>
					<a target="_blank" href="http://wordpress.org/extend/plugins/uber-media/changelog"><?php _e( 'Changelog', 'media-manager-plus' ); ?></a>
				</p>
			</div>
			<div>
				<h4><?php _e( 'Watch The Video', 'media-manager-plus' ); ?></h4>

				<div class='video'>
					<object width='532' height='325'>
						<param name='movie' value='http://www.youtube.com/v/dR0sPNSICfk?fs=1'></param>
						<param name='allowFullScreen' value='true'></param>
						<param name='allowscriptaccess' value='never'></param>
						<embed src='http://www.youtube.com/v/dR0sPNSICfk?fs=1' type='application/x-shockwave-flash' allowscriptaccess='never' allowfullscreen='true' width='532' height='325'></embed>
					</object>
				</div>
			</div>
		</div>
	</div>
	<div class="return-to-dashboard">
		<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'mmp' ), 'upload.php' ) ) ); ?>"><?php _e( 'Go to Media Manager Plus Settings', 'media-manager-plus' ); ?></a>
	</div>
</div>