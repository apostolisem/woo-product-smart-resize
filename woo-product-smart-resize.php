<?php
/*
Plugin Name: WPCARE: Woo Product Images Smart Resize
Plugin URI: https://wpcare.gr
Description: Resizes the product photos to make every product in the catalog look evenly.
Version: 1.0.0
Author: WordPress Care
Author URI: https://wpcare.gr
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: woo-product-smart-resize
*/

if ( ! defined( 'ABSPATH' ) ) exit; // exit if accessed directly

function wpcare_smart_resize( $params ) {

  $allowed = array('jpg', 'jpeg');
  $filename = $params['file'];
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if (!in_array($ext, $allowed)) {
      return $params;
  }

  $w = 1024;
  $h = 1024;
  //$mode = $_GET['mode']=='fit'?'fit':'fill';
  $mode = fit;
  //if ($w <= 1 || $w >= 2048) $w = 100;
  //if ($h <= 1 || $h >= 2048) $h = 100;

  // Source image
  $src = imagecreatefromjpeg($params['file']);

  // Destination image with white background
  $dst = imagecreatetruecolor($w, $h);
  imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));

  // All Magic is here
  scale_image($src, $dst, $mode);
  imagejpeg($dst,$params['file']);
  return $params;
}

add_filter( 'wp_handle_upload', 'wpcare_smart_resize' );

function scale_image($src_image, $dst_image, $op = 'fit') {
    $src_width = imagesx($src_image);
    $src_height = imagesy($src_image);

    $dst_width = imagesx($dst_image);
    $dst_height = imagesy($dst_image);

    // Try to match destination image by width
    $new_width = $dst_width;
    $new_height = round($new_width*($src_height/$src_width));
    $new_x = 0;
    $new_y = round(($dst_height-$new_height)/2);

    // FILL and FIT mode are mutually exclusive
    if ($op =='fill')
        $next = $new_height < $dst_height; else $next = $new_height > $dst_height;

    // If match by width failed and destination image does not fit, try by height
    if ($next) {
        $new_height = $dst_height;
        $new_width = round($new_height*($src_width/$src_height));
        $new_x = round(($dst_width - $new_width)/2);
        $new_y = 0;
    }

    // Copy image on right place
    imagecopyresampled($dst_image, $src_image , $new_x, $new_y, 0, 0, $new_width, $new_height, $src_width, $src_height);
}
