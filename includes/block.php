<?php
/**
 * Gutenberg block registration for the ecosystem bar.
 *
 * A dynamic block that reuses the same PHP renderer (keb_build_bar) as the
 * shortcode, so the block and shortcode always produce identical markup. The
 * editor UI is a no-build script (plain JS using the wp.* globals) and the
 * editor preview is rendered server-side via ServerSideRender.
 *
 * @package Kodezen_Ecosystem_Bar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the editor script and the dynamic block.
 */
function keb_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return; // Classic-only site; shortcode still works.
	}

	wp_register_script(
		'keb-block-editor',
		KEB_URL . 'blocks/ecosystem-bar/edit.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
		KEB_VERSION,
		true
	);

	register_block_type(
		KEB_PATH . 'blocks/ecosystem-bar',
		array( 'render_callback' => 'keb_block_render' )
	);
}
add_action( 'init', 'keb_register_block' );

/**
 * Register a permissive REST endpoint used for the editor preview.
 *
 * We render the preview through our own route (instead of core's strict
 * ServerSideRender endpoint) so that global attributes the editor injects
 * (metadata, lock, etc.) can't fail schema validation. Only logged-in users who
 * can edit posts may call it, and it only returns already-public bar markup.
 */
function keb_register_rest() {
	register_rest_route(
		'keb/v1',
		'/preview',
		array(
			'methods'             => 'POST',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
			'callback'            => static function ( WP_REST_Request $request ) {
				$attrs = $request->get_json_params();
				if ( ! is_array( $attrs ) ) {
					$attrs = array();
				}
				return array( 'html' => keb_block_render( $attrs ) );
			},
		)
	);
}
add_action( 'rest_api_init', 'keb_register_rest' );

/**
 * Server-side render callback for the block.
 *
 * Maps block attributes onto keb_build_bar() args.
 *
 * @param array $attributes Block attributes (already merged with block.json defaults).
 * @return string
 */
function keb_block_render( $attributes ) {
	$a = wp_parse_args(
		(array) $attributes,
		array(
			'bg'            => '#ffffff',
			'width'         => 'full',
			'barAlign'      => 'justify',
			'height'        => 56,
			'newTab'          => false,
			'fixed'           => false,
			'hoverBg'         => '#f0f1f3',
			'activeHighlight' => true,
			'activeBg'        => '#eef2ff',
			'activeIndicator' => '#006bff',
			'borderColor'     => '#e5e7eb',
			'borderWidth'     => 1,
			'visibility'      => 'no-mobile',
			'moreLabel'       => 'More',
			'moreShowIcon'    => true,
			'moreText'        => '#141a24',
			'moreBg'          => '#ffffff',
			'moreBorder'      => '#d9dce2',
			'moreBorderWidth' => 1,
			'moreIconColor'   => '#006bff',
		)
	);

	// In the editor preview, force the first item active so the active style is
	// visible (the real active item is domain-detected on the frontend).
	$active_index = ( ! empty( $a['__preview_active'] ) && ! empty( $a['activeHighlight'] ) ) ? 0 : null;

	return keb_build_bar(
		array(
			'inline'          => empty( $a['fixed'] ),
			'bg'              => $a['bg'],
			'width'           => $a['width'],
			'align'           => $a['barAlign'],
			'height'          => (int) $a['height'],
			'new_tab'         => ! empty( $a['newTab'] ),
			'hover_bg'         => $a['hoverBg'],
			'active_highlight' => ! empty( $a['activeHighlight'] ),
			'active_bg'        => $a['activeBg'],
			'active_indicator' => $a['activeIndicator'],
			'active_index'     => $active_index,
			'border_color'    => $a['borderColor'],
			'border_width'    => (int) $a['borderWidth'],
			'visibility'      => $a['visibility'],
			'more_label'      => $a['moreLabel'],
			'more_show_icon'  => ! empty( $a['moreShowIcon'] ),
			'more_text'       => $a['moreText'],
			'more_bg'         => $a['moreBg'],
			'more_border'     => $a['moreBorder'],
			'more_border_w'   => (int) $a['moreBorderWidth'],
			'more_icon_color' => $a['moreIconColor'],
		)
	);
}
