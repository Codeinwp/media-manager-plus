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