<?php
/**
 * Plugin Name:       Kodezen Ecosystem Bar
 * Plugin URI:        https://kodezen.com
 * Description:        Lightweight bar linking to the Kodezen ecosystem products. Shortcode + Gutenberg block; inline styles, no external requests.
 * Version:           1.2.1
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Kodezen
 * Author URI:        https://kodezen.com
 * License:           GPL-2.0-or-later
 * Text Domain:       kodezen-ecosystem-bar
 *
 * @package Kodezen_Ecosystem_Bar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KEB_VERSION', '1.2.1' );
define( 'KEB_PATH', plugin_dir_path( __FILE__ ) );
define( 'KEB_URL', plugin_dir_url( __FILE__ ) );

require_once KEB_PATH . 'includes/render.php';
require_once KEB_PATH . 'includes/block.php';

/**
 * Render the bar at the very top of <body> on the frontend.
 *
 * The renderer guards itself against running twice, so we can safely register
 * both hooks: wp_body_open is preferred, and wp_footer is a fallback for themes
 * that never fire wp_body_open (the CSS keeps the bar position:fixed at the top
 * either way).
 */
/**
 * Shortcode: [kodezen_ecosystem_bar]
 *
 * The bar renders ONLY where this shortcode is placed. Add inline="no" for a
 * fixed top bar at that spot.
 */
add_shortcode( 'kodezen_ecosystem_bar', 'keb_shortcode' );

/**
 * Auto-render is OFF by default (shortcode-only). Opt back in with:
 *   add_filter( 'keb_auto_render', '__return_true' );
 */
if ( apply_filters( 'keb_auto_render', false ) ) {
	add_action( 'wp_body_open', 'keb_render_bar', 0 );
	add_action( 'wp_footer', 'keb_render_bar' );
}
