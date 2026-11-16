<?php
/**
 * Removes the plugin data on uninstall.
 *
 * @package Codifigata_Back_To_Top
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'codifigata_btt_settings' );
