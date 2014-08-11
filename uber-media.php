<?php
/**
 * Plugin Name: Media Manager Plus
 * Plugin URI: http://dev7studios.com/plugins/media-manager-plus/
 * Description: Upgrade the WordPress Media Manager and add support for Flickr, Instagram, 500px etc.
 * Author: Dev7studios
 * Author URI: http://dev7studios.com
 * Version: 1.5
 * Text Domain: media-manager-plus
 * Domain Path: /languages
 *
 * Media Manager Plus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Media Manager Plus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Media Manager Plus. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package MMP
 * @category Core
 * @author Dev7studios
 * @version 1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Media_Manager_Plus' ) ) :

	if ( ! session_id() ) {
		session_start();
	}

	/**
	 * Main Class
	 *
	 * @since 1.5
	 */
	final class Media_Manager_Plus {
		/** Singleton *************************************************************/

		/**
		 * @var Media_Manager_Plus The one true Media_Manager_Plus
		 * @since 1.5
		 */
		private static $instance;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '1.5';

		/**
		 * Main Media_Manager_Plus Instance
		 *
		 * Insures that only one instance of Media_Manager_Plus exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.5
		 * @static
		 * @staticvar array $instance
		 * @uses Media_Manager_Plus::setup_globals() Setup the globals needed
		 * @uses Media_Manager_Plus::includes() Include the required files
		 * @uses Media_Manager_Plus::setup_actions() Setup the hooks and actions
		 * @return The one true Media_Manager_Plus
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Media_Manager_Plus ) ) {

				self::$instance = new Media_Manager_Plus;
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();

				// Setup objects
				self::$instance->sources  		= new Media_Manager_Plus_Sources;
				self::$instance->extensions  	= new Media_Manager_Plus_Extensions;
				self::$instance->settings	  	= new Media_Manager_Plus_Settings;
				self::$instance->templates	  	= new Media_Manager_Plus_Templates;
			}

			return self::$instance;
		} // END instance()

		/**
		 * Throw error on object clone
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @since 1.5
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'media-manager-plus' ), '1.0' );
		} // END __clone()

		/**
		 * Disable unserializing of the class
		 *
		 * @since 1.5
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'media-manager-plus' ), '1.0' );
		} // END __wakeup()

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since 1.5
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version
			if ( ! defined( 'MMP_VERSION' ) ) {
				define( 'MMP_VERSION', $this->version );
			}

			// Plugin Folder Path
			if ( ! defined( 'MMP_PLUGIN_DIR' ) ) {
				define( 'MMP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'MMP_PLUGIN_URL' ) ) {
				define( 'MMP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File
			if ( ! defined( 'MMP_PLUGIN_FILE' ) ) {
				define( 'MMP_PLUGIN_FILE', __FILE__ );
			}

			// Extensions URL
			if ( ! defined( 'MMP_EXTENSIONS_URL' ) ) {
				define( 'MMP_EXTENSIONS_URL', 'http://cdn.dev7studios.com/media-manager-plus/extensions.json?v=1.1' );
			}

			// oAuth Callback URL
			if ( ! defined( 'MMP_CALLBACK_URL' ) ) {
				define( 'MMP_CALLBACK_URL', get_admin_url() . 'upload.php?page=mmp' );
			}
		} // END setup_constants()

		/**
		 * Include required files
		 *
		 * @access private
		 * @since 1.5
		 * @return void
		 */
		private function includes() {
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-install.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-admin.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-sources.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-media.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-extensions.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-settings.php';
			require_once MMP_PLUGIN_DIR . 'includes/admin/class-templates.php';
			require_once MMP_PLUGIN_DIR . 'includes/functions.php';
		} // END includes()

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 1.5
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain(
				'media-manager-plus',
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages/'
			);
		} // END load_textdomain()

	}

endif; // END class_exists()

/**
 * The main function responsible for returning the one true Media_Manager_Plus
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $media_manager_plus = media_manager_plus(); ?>
 *
 * @since 1.5
 * @return object The one true Media_Manager_Plus Instance
 */
function media_manager_plus() {
	return Media_Manager_Plus::instance();
} // END media_manager_plus()

// Start it up!
media_manager_plus();