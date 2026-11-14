<?php
/**
 * Plugin Name:       Arezoo Core
 * Description:       نمونه‌کارها، ویجت‌های المنتور، ۹۶ تم رنگی، دموها و بخش‌های سایت برای قالب Arezoo.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Arezoo Yousefi
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       arezoo-core
 * Requires Plugins:  elementor
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AY_VERSION', '1.0.0' );
define( 'AY_FILE', __FILE__ );
define( 'AY_DIR', plugin_dir_path( __FILE__ ) );
define( 'AY_URL', plugin_dir_url( __FILE__ ) );
define( 'AY_MIN_ELEMENTOR', '3.24.0' );

require_once AY_DIR . 'includes/helpers.php';
require_once AY_DIR . 'includes/content.php';
require_once AY_DIR . 'includes/palettes.php';
require_once AY_DIR . 'includes/parts.php';
require_once AY_DIR . 'includes/builder.php';
require_once AY_DIR . 'includes/demos.php';
require_once AY_DIR . 'includes/setup.php';
require_once AY_DIR . 'includes/admin.php';

/**
 * Elementor integration loads only when Elementor is present and recent enough.
 * Portfolio content keeps working without it, so entries never disappear.
 */
function ay_elementor_ready() {
	return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, AY_MIN_ELEMENTOR, '>=' );
}

add_action( 'plugins_loaded', function () {
	if ( ay_elementor_ready() ) {
		require_once AY_DIR . 'includes/elementor/loader.php';
	}
}, 20 );

register_activation_hook( __FILE__, function () {
	ay_register_content();
	flush_rewrite_rules();
	if ( false === get_option( 'arezoo_settings' ) ) {
		add_option( 'arezoo_settings', ay_default_settings() );
	}
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
