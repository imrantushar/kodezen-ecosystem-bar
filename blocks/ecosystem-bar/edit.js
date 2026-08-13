/**
 * Kodezen Ecosystem Bar — block editor UI (no build step).
 *
 * Uses the wp.* globals directly (no JSX / bundler). The block is dynamic:
 * `save` returns null and the frontend renders via PHP (keb_build_bar). The
 * editor preview is fetched from our own permissive REST route (keb/v1/preview)
 * — not core's ServerSideRender — so editor-injected global attributes
 * (metadata, lock, etc.) can never fail schema validation.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var RawHTML = wp.element.RawHTML;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var c = wp.components;
	var UnitControl = c.__experimentalUnitControl || c.UnitControl || null;
	var apiFetch = wp.apiFetch;
	var __ = wp.i18n.__;

	registerBlockType( 'kodezen/ecosystem-bar', {
		// Declared client-side too so controls hydrate with the right defaults.
		attributes: {
			bg: { type: 'string', default: '#ffffff' },
			width: { type: 'string', default: 'full' },
			barAlign: { type: 'string', default: 'justify' },
			height: { type: 'number', default: 56 },
			newTab: { type: 'boolean', default: false },
			fixed: { type: 'boolean', default: false },
			hoverBg: { type: 'string', default: '#f0f1f3' },
			activeHighlight: { type: 'boolean', default: true },
			activeBg: { type: 'string', default: '#eef2ff' },
			activeIndicator: { type: 'string', default: '#006bff' },
			borderColor: { type: 'string', default: '#e5e7eb' },
			borderWidth: { type: 'number', default: 1 },
			visibility: { type: 'string', default: 'no-mobile' },
			moreLabel: { type: 'string', default: 'More' },
			moreShowIcon: { type: 'boolean', default: true },
			moreText: { type: 'string', default: '#141a24' },
			moreBg: { type: 'string', default: '#ffffff' },
			moreBorder: { type: 'string', default: '#d9dce2' },
			moreBorderWidth: { type: 'number', default: 1 },
			moreIconColor: { type: 'string', default: '#006bff' }
		},

		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = useBlockProps( { style: { margin: 0 } } );
			var wl = String( a.width ).toLowerCase();
			var widthMode = ( wl === 'full' || wl === 'content' || wl === 'wide' ) ? wl : 'custom';
			var barAlign = a.barAlign || 'justify';

			var state = useState( '' );
			var html = state[ 0 ];
			var setHtml = state[ 1 ];
			var loadingState = useState( true );
			var loading = loadingState[ 0 ];
			var setLoading = loadingState[ 1 ];

			// Fetch the rendered preview whenever a styling attribute changes.
			useEffect( function () {
				var cancelled = false;
				setLoading( true );
				apiFetch( {
					path: '/keb/v1/preview',
					method: 'POST',
					data: {
						bg: a.bg,
						width: a.width,
						barAlign: a.barAlign,
						height: a.height,
						newTab: a.newTab,
						// Never preview as fixed inside the editor (it would overlay the canvas).
						fixed: false,
						// Always visible in the editor, even if hidden on mobile on the frontend.
						visibility: 'all',
						// Force the first item active so the active style is visible here.
						__preview_active: true,
						hoverBg: a.hoverBg,
						activeHighlight: a.activeHighlight,
						activeBg: a.activeBg,
						activeIndicator: a.activeIndicator,
						borderColor: a.borderColor,
						borderWidth: a.borderWidth,
						moreLabel: a.moreLabel,
						moreShowIcon: a.moreShowIcon,
						moreText: a.moreText,
						moreBg: a.moreBg,
						moreBorder: a.moreBorder,
						moreBorderWidth: a.moreBorderWidth,
						moreIconColor: a.moreIconColor
					}
				} ).then( function ( res ) {
					if ( ! cancelled ) {
						setHtml( ( res && res.html ) || '' );
						setLoading( false );
					}
				} ).catch( function () {
					if ( ! cancelled ) {
						setLoading( false );
					}
				} );
				return function () { cancelled = true; };
			}, [ a.bg, a.width, a.barAlign, a.height, a.newTab, a.hoverBg, a.activeHighlight, a.activeBg, a.activeIndicator, a.borderColor, a.borderWidth, a.moreLabel, a.moreShowIcon, a.moreText, a.moreBg, a.moreBorder, a.moreBorderWidth, a.moreIconColor ] );

			var inspector = el(
				InspectorControls,
				null,
				el(
					c.PanelBody,
					{ title: __( 'Layout', 'kodezen-ecosystem-bar' ), initialOpen: true },
					el( c.SelectControl, {
						label: __( 'Alignment', 'kodezen-ecosystem-bar' ),
						value: barAlign,
						options: [
							{ label: __( 'Justify (spread evenly)', 'kodezen-ecosystem-bar' ), value: 'justify' },
							{ label: __( 'Left', 'kodezen-ecosystem-bar' ), value: 'left' },
							{ label: __( 'Center', 'kodezen-ecosystem-bar' ), value: 'center' },
							{ label: __( 'Right', 'kodezen-ecosystem-bar' ), value: 'right' }
						],
						onChange: function ( v ) { set( { barAlign: v } ); }
					} ),
					el( c.RangeControl, {
						label: __( 'Bar height (px)', 'kodezen-ecosystem-bar' ),
						value: a.height,
						min: 36,
						max: 96,
						onChange: function ( v ) { set( { height: v || 56 } ); }
					} ),
					el( c.SelectControl, {
						label: __( 'Logo container width', 'kodezen-ecosystem-bar' ),
						value: widthMode,
						help: __( 'Content / Wide use your theme sizes; the background always stays full width.', 'kodezen-ecosystem-bar' ),
						options: [
							{ label: __( 'Full width', 'kodezen-ecosystem-bar' ), value: 'full' },
							{ label: __( 'Content width (theme)', 'kodezen-ecosystem-bar' ), value: 'content' },
							{ label: __( 'Wide width (theme)', 'kodezen-ecosystem-bar' ), value: 'wide' },
							{ label: __( 'Custom', 'kodezen-ecosystem-bar' ), value: 'custom' }
						],
						onChange: function ( v ) {
							if ( 'custom' === v ) {
								set( { width: widthMode === 'custom' ? a.width : '1140px' } );
							} else {
								set( { width: v } );
							}
						}
					} ),
					'custom' === widthMode && ( UnitControl
						? el( UnitControl, {
							label: __( 'Custom width', 'kodezen-ecosystem-bar' ),
							value: a.width,
							units: [
								{ value: 'px', label: 'px' },
								{ value: '%', label: '%' },
								{ value: 'vw', label: 'vw' }
							],
							onChange: function ( v ) { set( { width: v || '1140px' } ); }
						} )
						: el( c.TextControl, {
							label: __( 'Custom width', 'kodezen-ecosystem-bar' ),
							value: a.width,
							help: __( 'e.g. 1140px or 90%', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { width: v } ); }
						} )
					),
					el( c.ToggleControl, {
						label: __( 'Open links in a new tab', 'kodezen-ecosystem-bar' ),
						checked: !! a.newTab,
						onChange: function ( v ) { set( { newTab: v } ); }
					} ),
					el( c.ToggleControl, {
						label: __( 'Pin to top of page (fixed)', 'kodezen-ecosystem-bar' ),
						help: __( 'Applies on the frontend. Preview here always shows inline.', 'kodezen-ecosystem-bar' ),
						checked: !! a.fixed,
						onChange: function ( v ) { set( { fixed: v } ); }
					} ),
					el( c.SelectControl, {
						label: __( 'Show on', 'kodezen-ecosystem-bar' ),
						value: a.visibility || 'no-mobile',
						help: __( 'The bar is hidden on smaller screens on the frontend. It always shows here in the editor.', 'kodezen-ecosystem-bar' ),
						options: [
							{ label: __( 'All devices', 'kodezen-ecosystem-bar' ), value: 'all' },
							{ label: __( 'Desktop + tablet (hide phones < 782px)', 'kodezen-ecosystem-bar' ), value: 'no-mobile' },
							{ label: __( 'Desktop only (hide < 1024px)', 'kodezen-ecosystem-bar' ), value: 'desktop-only' }
						],
						onChange: function ( v ) { set( { visibility: v } ); }
					} ),
					el( c.RangeControl, {
						label: __( 'Bottom border width (px)', 'kodezen-ecosystem-bar' ),
						value: a.borderWidth,
						min: 0,
						max: 8,
						onChange: function ( v ) { set( { borderWidth: ( v === undefined ? 1 : v ) } ); }
					} )
				),
				el( PanelColorSettings, {
					title: __( 'Bar color', 'kodezen-ecosystem-bar' ),
					initialOpen: false,
					colorSettings: [
						{
							value: a.bg,
							label: __( 'Background', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { bg: v || '#ffffff' } ); }
						},
						{
							value: a.borderColor,
							label: __( 'Bottom border', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { borderColor: v || '#e5e7eb' } ); }
						}
					]
				} ),
				el(
					c.PanelBody,
					{ title: __( 'More button', 'kodezen-ecosystem-bar' ), initialOpen: false },
					el( c.TextControl, {
						label: __( 'Button label', 'kodezen-ecosystem-bar' ),
						value: a.moreLabel,
						onChange: function ( v ) { set( { moreLabel: v } ); }
					} ),
					el( c.ToggleControl, {
						label: __( 'Show apps icon', 'kodezen-ecosystem-bar' ),
						checked: !! a.moreShowIcon,
						onChange: function ( v ) { set( { moreShowIcon: v } ); }
					} ),
					el( c.RangeControl, {
						label: __( 'Border width (px)', 'kodezen-ecosystem-bar' ),
						value: a.moreBorderWidth,
						min: 0,
						max: 4,
						onChange: function ( v ) { set( { moreBorderWidth: ( v === undefined ? 1 : v ) } ); }
					} )
				),
				el( PanelColorSettings, {
					title: __( 'More button colors', 'kodezen-ecosystem-bar' ),
					initialOpen: false,
					colorSettings: [
						{
							value: a.moreBg,
							label: __( 'Background', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { moreBg: v || '#ffffff' } ); }
						},
						{
							value: a.moreText,
							label: __( 'Text', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { moreText: v || '#141a24' } ); }
						},
						{
							value: a.moreBorder,
							label: __( 'Border', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { moreBorder: v || '#d9dce2' } ); }
						},
						{
							value: a.moreIconColor,
							label: __( 'Icon', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { moreIconColor: v || '#006bff' } ); }
						}
					]
				} ),
				el(
					c.PanelBody,
					{ title: __( 'Hover & active', 'kodezen-ecosystem-bar' ), initialOpen: false },
					el( c.ToggleControl, {
						label: __( 'Highlight current product', 'kodezen-ecosystem-bar' ),
						help: __( 'Marks the item matching the current site as active (e.g. Kodezen on kodezen.com).', 'kodezen-ecosystem-bar' ),
						checked: !! a.activeHighlight,
						onChange: function ( v ) { set( { activeHighlight: v } ); }
					} )
				),
				el( PanelColorSettings, {
					title: __( 'Hover & active colors', 'kodezen-ecosystem-bar' ),
					initialOpen: false,
					colorSettings: [
						{
							value: a.hoverBg,
							label: __( 'Hover background', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { hoverBg: v || '#f0f1f3' } ); }
						},
						{
							value: a.activeBg,
							label: __( 'Active background', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { activeBg: v || '#eef2ff' } ); }
						},
						{
							value: a.activeIndicator,
							label: __( 'Active indicator', 'kodezen-ecosystem-bar' ),
							onChange: function ( v ) { set( { activeIndicator: v || '#006bff' } ); }
						}
					]
				} )
			);

			var body;
			if ( html ) {
				body = el( RawHTML, null, html );
			} else {
				body = el(
					'div',
					{ style: { padding: '14px', font: '13px sans-serif', color: '#6b7280' } },
					loading
						? __( 'Loading preview…', 'kodezen-ecosystem-bar' )
						: __( 'Preview unavailable — the bar still renders on the frontend.', 'kodezen-ecosystem-bar' )
				);
			}

			return el( Fragment, null, inspector, el( 'div', blockProps, body ) );
		},

		// Dynamic block — rendered by PHP (keb_block_render).
		save: function () {
			return null;
		}
	} );
} )( window.wp );
