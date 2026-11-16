<?php
/**
 * Settings, sanitization, color helpers and CSS builder.
 *
 * @package Codifigata_Back_To_Top
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default settings.
 *
 * @return array
 */
function codifigata_btt_defaults() {
	return array(
		'color'    => '',        // Empty = automatic (Elementor global color, then theme.json palette).
		'style'    => 'soft',    // soft | solid | outline.
		'shape'    => 'rounded', // rounded | circle | square.
		'icon'     => 'chevron', // chevron | arrow.
		'position' => 'right',   // right | left.
		'size'     => 44,        // Desktop size in px.
		'gap'      => 24,        // Distance from the screen edges in px.
		'offset'   => 400,       // Scroll in px after which the button appears.
		'mobile'   => 1,         // Show on screens narrower than 768px.
	);
}

/**
 * Allowed values for the choice fields.
 *
 * @return array
 */
function codifigata_btt_choices() {
	return array(
		'style'    => array( 'soft', 'solid', 'outline' ),
		'shape'    => array( 'rounded', 'circle', 'square' ),
		'icon'     => array( 'chevron', 'arrow' ),
		'position' => array( 'right', 'left' ),
	);
}

/**
 * Numeric limits: field => array( min, max ).
 *
 * @return array
 */
function codifigata_btt_limits() {
	return array(
		'size'   => array( 32, 72 ),
		'gap'    => array( 0, 80 ),
		'offset' => array( 0, 5000 ),
	);
}

/**
 * Sanitizes the settings array. Used both on save and on read.
 *
 * @param mixed $input Raw settings.
 * @return array
 */
function codifigata_btt_sanitize( $input ) {
	$defaults = codifigata_btt_defaults();
	$input    = is_array( $input ) ? $input : array();
	$out      = array();

	$color        = isset( $input['color'] ) ? sanitize_hex_color( trim( (string) $input['color'] ) ) : '';
	$out['color'] = $color ? strtolower( $color ) : '';

	foreach ( codifigata_btt_choices() as $key => $allowed ) {
		$value       = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
		$out[ $key ] = in_array( $value, $allowed, true ) ? $value : $defaults[ $key ];
	}

	foreach ( codifigata_btt_limits() as $key => $range ) {
		$value       = isset( $input[ $key ] ) && is_numeric( $input[ $key ] ) ? (int) $input[ $key ] : $defaults[ $key ];
		$out[ $key ] = max( $range[0], min( $range[1], $value ) );
	}

	$out['mobile'] = empty( $input['mobile'] ) ? 0 : 1;

	return $out;
}

/**
 * Returns the current settings, merged with the defaults.
 *
 * Developers can override any value with the "codifigata_btt_settings" filter.
 *
 * @return array
 */
function codifigata_btt_get_settings() {
	$saved    = get_option( CODIFIGATA_BTT_OPTION, array() );
	$settings = wp_parse_args( is_array( $saved ) ? $saved : array(), codifigata_btt_defaults() );

	return codifigata_btt_sanitize( apply_filters( 'codifigata_btt_settings', $settings ) );
}

/**
 * Converts a hex color to an RGB array.
 *
 * @param string $hex Hex color (#abc or #aabbcc).
 * @return int[]
 */
function codifigata_btt_hex_to_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Mixes two hex colors. Same result as CSS color-mix(in srgb, $a $weight%, $b).
 *
 * @param string $a      First color.
 * @param string $b      Second color.
 * @param float  $weight Share of the first color, from 0 to 1.
 * @return string
 */
function codifigata_btt_mix( $a, $b, $weight ) {
	$ca  = codifigata_btt_hex_to_rgb( $a );
	$cb  = codifigata_btt_hex_to_rgb( $b );
	$out = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$out[ $i ] = (int) round( $ca[ $i ] * $weight + $cb[ $i ] * ( 1 - $weight ) );
	}
	return vsprintf( '#%02x%02x%02x', $out );
}

/**
 * Tells whether a color is light, to pick a readable icon color on solid buttons.
 *
 * @param string $hex Hex color.
 * @return bool
 */
function codifigata_btt_is_light( $hex ) {
	list( $r, $g, $b ) = codifigata_btt_hex_to_rgb( $hex );
	return ( ( $r * 299 + $g * 587 + $b * 114 ) / 1000 ) >= 165;
}

/**
 * Builds the button CSS for the given settings.
 *
 * Modern browsers use color-mix() on the live color (so the automatic mode follows
 * the site palette); older browsers get precomputed fallbacks.
 *
 * @param array  $s   Sanitized settings.
 * @param string $sel CSS selector of the button.
 * @return string
 */
function codifigata_btt_css( $s, $sel = '#codifigata-btt' ) {
	$fallback = '#1d4f91';
	$hex      = $s['color'] ? $s['color'] : $fallback;
	$main     = $s['color'] ? $s['color'] : 'var(--e-global-color-primary, var(--wp--preset--color--primary, ' . $fallback . '))';
	$on_main  = ( $s['color'] && codifigata_btt_is_light( $s['color'] ) ) ? '#1a1a1a' : '#ffffff';

	$tint12 = codifigata_btt_mix( $hex, '#ffffff', 0.12 );
	$tint20 = codifigata_btt_mix( $hex, '#ffffff', 0.20 );
	$tint22 = codifigata_btt_mix( $hex, '#ffffff', 0.22 );
	$dark   = codifigata_btt_mix( $hex, '#000000', 0.85 );

	$size   = (int) $s['size'];
	$gap    = (int) $s['gap'];
	$m_size = min( 72, $size + 2 );
	$m_gap  = min( $gap, 16 );
	$icon   = (int) round( $size * 0.45 );
	$side   = 'left' === $s['position'] ? 'left' : 'right';

	if ( 'circle' === $s['shape'] ) {
		$radius = '50%';
	} elseif ( 'square' === $s['shape'] ) {
		$radius = '4px';
	} else {
		$radius = (int) round( $size * 0.27 ) . 'px';
	}

	switch ( $s['style'] ) {
		case 'solid':
			$look  = "border:1px solid {$hex};border-color:var(--cbtt-main);";
			$look .= "background:{$hex};background:var(--cbtt-main);color:{$on_main};";
			$hover = "background:{$dark};background:color-mix(in srgb, var(--cbtt-main) 85%, #000);";
			break;
		case 'outline':
			$look  = "border:2px solid {$hex};border-color:var(--cbtt-main);";
			$look .= 'background:#fff;color:var(--cbtt-main);';
			$hover = "background:{$tint12};background:color-mix(in srgb, var(--cbtt-main) 12%, #fff);";
			break;
		default: // soft.
			$look  = "border:1px solid {$tint22};border:1px solid color-mix(in srgb, var(--cbtt-main) 22%, #fff);";
			$look .= "background:{$tint12};background:color-mix(in srgb, var(--cbtt-main) 12%, #fff);color:var(--cbtt-main);";
			$hover = "background:{$tint20};background:color-mix(in srgb, var(--cbtt-main) 20%, #fff);";
	}

	$mobile = $s['mobile']
		? "{$sel}{--cbtt-size:{$m_size}px;--cbtt-gap:{$m_gap}px}"
		: "{$sel}{display:none!important}";

	$css  = "{$sel}{--cbtt-main:{$main};--cbtt-size:{$size}px;--cbtt-gap:{$gap}px;";
	$css .= "position:fixed;{$side}:calc(var(--cbtt-gap) + env(safe-area-inset-{$side}, 0px));";
	$css .= 'bottom:calc(var(--cbtt-gap) + env(safe-area-inset-bottom, 0px));z-index:9990;';
	$css .= 'display:flex;align-items:center;justify-content:center;';
	$css .= 'width:var(--cbtt-size);height:var(--cbtt-size);min-width:0;min-height:0;margin:0;padding:0;';
	$css .= "border-radius:{$radius};{$look}";
	$css .= 'box-shadow:0 4px 14px rgba(15,40,80,.12);cursor:pointer;line-height:1;';
	$css .= 'opacity:0;visibility:hidden;transform:translateY(10px);';
	$css .= 'transition:opacity .25s ease, transform .25s ease, visibility .25s, background-color .2s ease;';
	$css .= '-webkit-tap-highlight-color:transparent}';
	$css .= "{$sel}.is-visible{opacity:1;visibility:visible;transform:translateY(0)}";
	$css .= "{$sel}:hover,{$sel}:focus{{$hover}}";
	$css .= "{$sel}:focus{outline:none}";
	$css .= "{$sel}:focus-visible{outline:2px solid var(--cbtt-main);outline-offset:3px}";
	$css .= "{$sel} svg{width:{$icon}px;height:{$icon}px;display:block;pointer-events:none}";
	$css .= "@media (max-width:767px){{$mobile}}";
	$css .= "@media (prefers-reduced-motion:reduce){{$sel}{transition:opacity .01s, visibility .01s}{$sel},{$sel}.is-visible{transform:none}}";
	$css .= "@media print{{$sel}{display:none!important}}";

	return $css;
}

/**
 * Returns the SVG icon markup.
 *
 * @param string $icon Icon key.
 * @return string
 */
function codifigata_btt_icon( $icon ) {
	$path = 'arrow' === $icon ? 'M12 19V5M5 12l7-7 7 7' : 'M6 15l6-6 6 6';
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="' . esc_attr( $path ) . '"/></svg>';
}
