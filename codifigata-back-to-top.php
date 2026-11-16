<?php
/**
 * Plugin Name:       Codifigata Back to Top
 * Description:       Lightweight back to top button with smooth scroll. No external files: CSS and JS are printed inline in the footer. Configure it from Settings, Back to Top.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Codifigata
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       codifigata-back-to-top
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'CODIFIGATA_BTT_VERSION', '1.0.0' );
define( 'CODIFIGATA_BTT_FILE', __FILE__ );
define( 'CODIFIGATA_BTT_OPTION', 'codifigata_btt_settings' );

require_once __DIR__ . '/includes/settings.php';

if ( is_admin() ) {
	require_once __DIR__ . '/includes/admin.php';
} else {
	require_once __DIR__ . '/includes/frontend.php';
}

add_action( 'init', 'codifigata_btt_load_textdomain' );

/**
 * Loads the bundled translations (Italian included).
 */
function codifigata_btt_load_textdomain() {
	load_plugin_textdomain( 'codifigata-back-to-top', false, dirname( plugin_basename( CODIFIGATA_BTT_FILE ) ) . '/languages' );
}
