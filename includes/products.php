<?php
/**
 * Ecosystem product list.
 *
 * Each entry:
 *   name      => Accessible name (used for title / aria-label; the logo itself
 *                carries the wordmark).
 *   url       => Canonical product URL. TODO: replace placeholders with real URLs.
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
		'url'       => 'https://kodezen.com',
		'important' => true,
		'logo'      => 'kodezen',
	),
	array(
		'name'      => 'Academy LMS',
		'url'       => 'https://kodezen.com/academy',
		'important' => true,
		'logo'      => 'academy',
	),
	array(
		'name'      => 'aBlocks',
		'url'       => 'https://kodezen.com/ablocks',
		'important' => true,
		'logo'      => 'ablocks',
	),
	array(
		'name'      => 'StoreEngine',
		'url'       => 'https://storeengine.pro',
		'important' => true,
		'logo'      => 'storeengine',
	),
	array(
		'name'      => 'ZenCommunity',
		'url'       => 'https://kodezen.com/zencommunity',
		'important' => true,
		'logo'      => 'zencommunity',
	),
	array(
		'name'      => 'gemCRM',
		'url'       => 'https://kodezen.com/gemcrm',
		'important' => true,
		'logo'      => 'gemcrm',
	),

	array(
		'name'      => 'gemBooking',
		'url'       => 'https://kodezen.com/gembooking',
		'important' => true,
		'logo'      => 'gembooking',
	),
	array(
		'name'      => 'GameEngine',
		'url'       => 'https://kodezen.com/gameengine',
		'important' => true,
		'logo'      => 'gameengine',
	),

	// --- Less important: folded into the "More" dropdown ---
	array(
		'name'      => 'ECM',
		'url'       => 'https://kodezen.com/ecm',
		'important' => false,
		'logo'      => 'ecm',
	),
	array(
		'name'      => 'GemBoards',
		'url'       => 'https://kodezen.com/gemboards',
		'important' => false,
		'logo'      => 'gemboards',
	),
	array(
		'name'      => 'QuizPress',
		'url'       => 'https://kodezen.com/quizpress',
		'important' => false,
		'logo'      => 'quizpress',
	),
	array(
		'name'      => 'Template Kits',
		'url'       => 'https://kodezen.com/template-kits',
		'important' => false,
		'logo'      => 'template-kits',
	),
	array(
		'name'      => 'Zaplane',
		'url'       => 'https://kodezen.com/zaplane',
		'important' => false,
		'logo'      => 'zaplane',
	),
);
