<?php
/**
 * Devvly Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Devvly
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_DEVVLY_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'devvly-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_DEVVLY_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Thumbnails
 * Fixed pixelated gallery thumbs for woocommerce
 */
add_action('after_setup_theme', 'astra_child_woocommerce_support');
function astra_child_woocommerce_support()
{
    add_theme_support('woocommerce', array(
        'gallery_thumbnail_image_width' => 250,
    ));
}

add_filter('woocommerce_get_image_size_gallery_thumbnail', function ($size) {
    return array(
        'width' => 250,
        'height' => 250,
        'crop' => 1,
    );
});