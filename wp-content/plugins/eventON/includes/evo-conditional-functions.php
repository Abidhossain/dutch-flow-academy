<?php
/**
 *	EventON conditional functions
 * 	@since 4.7.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( !function_exists('is_event_type')){

	function is_event_type(){
		return is_tax( get_object_taxonomies( 'ajde_events' ) );
	}
}

if( !function_exists('is_event_taxonomy')){

	function is_event_taxonomy(){
		return is_tax( get_object_taxonomies( 'ajde_events' ) );
	}
}

if( !function_exists('is_event_tag')){

	function is_event_tag($term = ''){
		return is_tax( 'post_tag', $term );
	}
}
if( !function_exists('is_event')){

	function is_event($term = ''){
		return is_singular( array( 'ajde_events' ) );
	}
}

/*
 * check if the current theme support block
 * @since 4.1.2
 */
function evo_current_theme_is_fse_theme(){
	if ( function_exists( 'wp_is_block_theme' ) ) {
		return (bool) wp_is_block_theme();
	}
	if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
		return (bool) gutenberg_is_fse_theme();
	}

	return false;
}

/* check if hex color is dark or white
* @since 4.2 u4.6
*/
function eventon_is_hex_dark($hex){


	// Remove the '#' from the hex if it exists
  $hex = ltrim($hex, '#');

  // Ensure it's a valid 6-character hex
  if (strlen($hex) != 6 || !ctype_xdigit($hex)) {
      return 'Invalid hex color';
  }

  // Convert hex to RGB
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));

  // Calculate the luminance using ITU-R BT.709 formula
  $luma = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);

  // If the luminance is greater than 128, use black text, else use white text
  return ($luma > 128) ? true: false;

	
}

// convert HEX to rgb @4.6
function eventon_hex_to_rgb( $hex){
	if($hex[0] == '#')
      $hex = substr($hex, 1);

    if (strlen($hex) == 3){
      	$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    // Now extract the R, G, and B values
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 4));
    $b = hexdec(substr($hex, 4, 6));

    /*
    $r = hexdec($hex[0] . $hex[1]);
    $g = hexdec($hex[2] . $hex[3]);
    $b = hexdec($hex[4] . $hex[5]);
    */

    return $b + ($g << 0x8) + ($r << 0x10);
}

// convert RGB color to HSL @4.6
function eventon_rgb_to_hsl( $RGB){
	$r = 0xFF & ($RGB >> 0x10);
    $g = 0xFF & ($RGB >> 0x8);
    $b = 0xFF & $RGB;

    $r = ((float)$r) / 255.0;
    $g = ((float)$g) / 255.0;
    $b = ((float)$b) / 255.0;

    $maxC = max($r, $g, $b);
    $minC = min($r, $g, $b);

    $l = ($maxC + $minC) / 2.0;

    if($maxC == $minC){
      $s = 0;  $h = 0;
    }else{
      	if($l < .5){
        	$s = ($maxC - $minC) / ($maxC + $minC);
      	} else{
        	$s = ($maxC - $minC) / (2.0 - $maxC - $minC);
      	}
      	if($r == $maxC) $h = ($g - $b) / ($maxC - $minC);
      	if($g == $maxC) $h = 2.0 + ($b - $r) / ($maxC - $minC);
      	if($b == $maxC) $h = 4.0 + ($r - $g) / ($maxC - $minC);

      	$h = $h / 6.0; 
    }

    $h = (int)round(255.0 * $h);
    $s = (int)round(255.0 * $s);
    $l = (int)round(255.0 * $l);

    return (object) Array('hue' => $h, 'saturation' => $s, 'lightness' => $l);
 
}