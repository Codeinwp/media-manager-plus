<?php

class Media_Manager_Plus_Media {

	function __construct() {
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ), 99 );
		add_filter( 'media_view_strings', array( $this, 'custom_media_string' ), 10, 2 );
		add_action( 'wp_ajax_uber_pre_insert', array( $this, 'pre_insert' ) );
	}

	public function pre_insert() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uber_media' ) ) {
			return 0;
		}
		if ( ! isset( $_POST['imgsrc'] ) ) {
			return 0;
		}

		$response['error']   = false;
		$response['message'] = 'success';
		$response['imgsrc']  = $_POST['imgsrc'];
		$response['fields']  = $_POST;

		echo json_encode( apply_filters( 'uber_media_pre_insert', $response ) );
		die;
	} // END pre_insert()

	public function custom_media_string( $strings, $post ) {
		$strings['mmp_sources']     = media_manager_plus()->sources->get_sources( true );
		$strings['mmp_menu']        = apply_filters( 'mmp_default_menu', 'default' );
		$strings['mmp_menu_prefix'] = apply_filters( 'mmp_menu_prefix', __( 'Insert from ', 'media-manager-plus' ) );
		$strings['mmp_defaults']    = apply_filters( 'mmp_default_settings', array() );
		$strings['mmp_extensions']  = media_manager_plus()->extensions->get_installed_extensions();
		$strings['mmp_l10n']		= $this->get_js_l10n( $post );

		return $strings;
	} // END custom_media_string()

	public function print_media_templates() {
		?>
		<script type="text/html" id="tmpl-uberimage">
			<img id="{{ data.id }}" class="<# if ( typeof(data.folder) !== 'undefined' ) { #>folder<# } else { #>image<# } #>" src="{{ data.thumbnail }}" alt="{{ data.caption }}" title="{{ data.caption }}" data-full="{{ data.full }}" data-link="{{ data.link }}" />
			<# if ( typeof(data.folder) !== 'undefined' ) { #>
				<p>{{ data.caption }}</p>
				<# } #>
					<a class="check" id="check-link-{{ data.id }}" href="#" title="Deselect">
						<div id="check-{{ data.id }}" class="media-modal-icon"></div>
					</a>
		</script>
		<script type="text/html" id="tmpl-uberimage-settings">
			<div class="attachment-info">
				<h3>{{{ data.selected_image.title }}}</h3>
				<span id="uberload" class="spinner" style="display: block"></span>
				<input id="full-uber" type="hidden" value="{{ data.selected_image.dataset.full }}" />
				<input id="uber-id" type="hidden" value="{{ data.selected_image.id }}" />

				<div class="thumbnail">
				</div>
			</div>
			<?php do_action('uber_media_settings_before'); ?>
			<?php if ( ! apply_filters( 'disable_captions', '' ) ) : ?>
				<label class="setting caption">
				<span><?php _e( 'Caption', 'media-manager-plus' ); ?></span>
				<textarea id="caption-uber" data-setting="caption"></textarea>
			</label>
			<?php endif; ?>
			<label class="setting alt-text">
				<span><?php _e( 'Title', 'media-manager-plus' ); ?></span>
				<input id="title-uber" type="text" data-setting="title" value="{{{ data.selected_image.title }}}" />
				<input name="original-title" type="hidden" value="{{{ data.selected_image.title }}}" />
			</label>
			<label class="setting alt-text">
				<span><?php _e( 'Alt Text', 'media-manager-plus' ); ?></span>
				<input id="alt-uber" type="text" data-setting="alt" value="{{{ data.selected_image.title }}}" />
			</label>
			<div class="setting align">
				<span><?php _e( 'Align', 'media-manager-plus' ); ?></span>
				<select class="alignment" data-setting="align" name="uber-align">
					<option value="left"> <?php esc_attr_e( 'Left', 'media-manager-plus' ); ?> </option>
					<option value="center"> <?php esc_attr_e( 'Center', 'media-manager-plus' ); ?> </option>
					<option value="right"> <?php esc_attr_e( 'Right', 'media-manager-plus' ); ?> </option>
					<option selected="selected" value="none"> <?php esc_attr_e( 'None', 'media-manager-plus' ); ?> </option>
				</select>
			</div>
			<div class="setting link-to">
				<span><?php _e('Link To', 'media-manager-plus'); ?></span>
				<select class="link-to" data-setting="link-to" name="uber-link">
					<option value="{{ data.selected_image.dataset.full }}"> <?php esc_attr_e( 'Image URL', 'media-manager-plus' ); ?> </option>
					<option value="{{ data.selected_image.dataset.link }}"> <?php esc_attr_e( 'Page URL', 'media-manager-plus' ); ?> </option>
					<option selected="selected" value="none"> <?php esc_attr_e( 'None', 'media-manager-plus' ); ?> </option>
				</select>
			</div>
			<?php do_action('uber_media_settings_after'); ?>
		</script>
	<?php
	} // END print_media_templates()

	/**
	 * Gets the translatable strings for the javascript file
	 */
	public function get_js_l10n( $post ){
		$hier = $post && is_post_type_hierarchical( $post->post_type );
		return array(
			'disconnect'	=>	__( 'Disconnect', 'media-manager-plus' ),
			'connect'		=> 	__( 'Connect', 'media-manager-plus' ),
			'connecting'	=>	__( 'Connecting', 'media-manager-plus' ),
			'importing'		=>	__( 'Importing', 'media-manager-plus' ),
			'inserting'		=>	__( 'Inserting', 'media-manager-plus' ),
			'insert'		=>	$hier ? __( 'Insert into page', 'media-manager-plus' ) : __( 'Insert into post', 'media-manager-plus' ),
			'imported'		=>	__( 'imported', 'media-manager-plus' ),
			'import'		=>	__( 'Import', 'media-manager-plus' ),
			'image'			=>	__( 'image', 'media-manager-plus' ),
			'images'		=>	__( 'images', 'media-manager-plus' )
		);
	} // END get_js_l10n()
}

new Media_Manager_Plus_Media;