<?php
/**
 * Ecosystem product list.
 *
 * Each entry:
 *   name      => Accessible name (used for title / aria-label; the logo itself
 *                carries the wordmark).
 *   url       => Canonical product URL.
 *   important => true  => rendered inline in the bar.
 *                false => folded into the "More" dropdown.
 *   logo      => Slug of an SVG file in assets/logos/ (e.g. 'storeengine' =>
 *                assets/logos/storeengine.svg). The SVG is inlined at render time
 *                (no HTTP request). If the file is missing, a text fallback of
 *                `name` is shown instead.
 *
 * The bundled logos are full brand wordmarks with dark ink, so the bar defaults
 * to a light background. Reorder items by array order; flip `important` to move a
 * product between the inline bar and the dropdown. To add a product, drop its SVG
 * into assets/logos/ and add a row here.
 *
 * @package Kodezen_Ecosystem_Bar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	// --- Important: the first-view lineup (rendered inline, in this order) ---
	array(
		'name'      => 'Kodezen',
		'url'       => 'https://kodezen.com/',
		'important' => true,
		'logo'      => 'kodezen',
	),
	array(
		'name'      => 'Academy LMS',
		'url'       => 'https://academylms.net/',
		'important' => true,
		'logo'      => 'academy',
	),
	array(
		'name'      => 'aBlocks',
		'url'       => 'https://ablocks.pro/',
		'important' => true,
		'logo'      => 'ablocks',
	),
	array(
		'name'      => 'StoreEngine',
		'url'       => 'https://storeengine.pro/',
		'important' => true,
		'logo'      => 'storeengine',
	),
	array(
		'name'      => 'ZenCommunity',
		'url'       => 'https://zencommunity.pro/',
		'important' => true,
		'logo'      => 'zencommunity',
	),
	array(
		'name'      => 'gemCRM',
		'url'       => 'https://gemcrm.net/',
		'important' => true,
		'logo'      => 'gemcrm',
	),
	array(
		'name'      => 'gemBooking',
		'url'       => 'https://gembooking.net/',
		'important' => true,
		'logo'      => 'gembooking',
	),
	array(
		'name'      => 'GameEngine',
		'url'       => 'https://gameengine.pro/',
		'important' => true,
		'logo'      => 'gameengine',
	),

	// --- Less important: folded into the "More" dropdown ---
	array(
		'name'      => 'ECM',
		'url'       => 'https://easycontentmanager.pro/',
		'important' => false,
		'logo'      => 'ecm',
	),
	array(
		'name'      => 'GemBoards',
		'url'       => 'https://gemboards.com/',
		'important' => false,
		'logo'      => 'gemboards',
	),
	array(
		'name'      => 'QuizPress',
		'url'       => 'https://quizpress.pro/',
		'important' => false,
		'logo'      => 'quizpress',
	),
	array(
		'name'      => 'Template Kits',
		'url'       => 'https://template-kits.com/',
		'important' => false,
		'logo'      => 'template-kits',
	),
	array(
		'name'      => 'Zaplane',
		'url'       => 'https://zaplane.app/',
		'important' => false,
		'logo'      => 'zaplane',
	),
	array(
		'name'      => 'TruePlayer',
		'url'       => 'https://true-player.net/',
		'important' => false,
		'logo'      => 'trueplayer',
	),
	array(
		'name'      => 'WP Map Block',
		'url'       => 'https://wpmapblock.com/',
		'important' => false,
		'logo'      => 'wp-map-block',
	),
	array(
		'name'      => 'ZenAppBuilder',
		'url'       => 'https://zenappbuilder.com/',
		'important' => false,
		'logo'      => 'zenappbuilder',
	),
);
