<?php
/*
Plugin Name: Dark Admin
Plugin URI: http://wordpress.org/extend/plugins/dark-admin/
Description: This is a plugin to break the wp-admin UI, and is not recommended for non-savvy users.
Version: 2.3
Author: MP6 Team & Michael Arestad
Author URI: http://wordpress.org
Text Domain: dark-admin
Domain Path: /languages
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


if ( ! defined( 'darkAdmin' ) )
	define( 'darkAdmin', true );


// register Open Sans stylesheet
add_action( 'init', 'darkAdmin_register_open_sans' );
function darkAdmin_register_open_sans() {
	wp_register_style(
		'open-sans',
		'//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600&subset=latin-ext,latin',
		plugins_url( 'css/opensans.css', __FILE__ ),
		false,
		'20130605'
	);
}

// register Dark Admin admin color scheme
function darkAdmin_register_admin_color_scheme() {

	global $wp_styles, $_wp_admin_css_colors;
	wp_admin_css_color(
		'darkadmin',
		__( 'DarkAdmin' ),
		plugins_url( 'css/colors-mp6.css', __FILE__ ),
		array( '#222', '#333', '#0074a2', '#2ea2cc' )
	);
	$_wp_admin_css_colors[ 'darkadmin' ]->icon_colors = array( 'base' => '#999', 'focus' => '#2ea2cc', 'current' => '#fff' );

	// set modification time
	$wp_styles->registered[ 'colors' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/colors-mp6.css' );

	// set dependencies
	$wp_styles->registered[ 'colors' ]->deps[] = 'open-sans';
	$wp_styles->registered[ 'colors' ]->deps[] = 'dashicons';

}
remove_action( 'admin_init', 'register_admin_color_schemes', 1);
add_action( 'admin_init', 'darkAdmin_register_admin_color_scheme' );

// remove WP's default color schemes
// load Dark Admin css on login screen
add_action( 'login_init', 'darkAdmin_replace_wp_default_styles' );
add_action( 'login_init', 'darkAdmin_fix_login_color_scheme' );
function darkAdmin_fix_login_color_scheme() {

	global $wp_styles;

	$wp_styles->registered[ 'colors-fresh' ]->src = plugins_url( 'css/colors-mp6.css', __FILE__ );
	$wp_styles->registered[ 'colors-fresh' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/colors-mp6.css' );
	$wp_styles->registered[ 'colors-fresh' ]->deps[] = 'open-sans';
	$wp_styles->registered[ 'colors-fresh' ]->deps[] = 'dashicons';

}

// replace default `admin-bar.css` with Dark Admin's
add_action( 'init', 'darkAdmin_replace_admin_bar_style' );
function darkAdmin_replace_admin_bar_style() {

	global $wp_styles;

	if ( ! isset( $wp_styles->registered[ 'admin-bar' ] ) )
		return;

	$wp_styles->registered[ 'admin-bar' ]->src = plugins_url( 'css/dark-admin.css', __FILE__ );
	$wp_styles->registered[ 'admin-bar' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/dark-admin.css' );
	$wp_styles->registered[ 'admin-bar' ]->deps[] = 'open-sans';
	$wp_styles->registered[ 'admin-bar' ]->deps[] = 'dashicons';
	$wp_styles->registered[ 'admin-bar' ]->extra[ 'suffix' ] = '';

}

// replace some default css files with ours
add_action( 'admin_init', 'darkAdmin_replace_wp_default_styles' );
function darkAdmin_replace_wp_default_styles() {

	global $wp_styles;
	$wp_styles->registered[ 'buttons' ]->src = plugins_url( 'css/buttons.css', __FILE__ );
	$wp_styles->registered[ 'buttons' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/buttons.css' );
	$wp_styles->registered[ 'editor-buttons' ]->src = plugins_url( 'css/editor.css', __FILE__ );
	$wp_styles->registered[ 'editor-buttons' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/editor.css' );
	$wp_styles->registered[ 'media' ]->src = plugins_url( 'css/media.css', __FILE__ );
	$wp_styles->registered[ 'media' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/media.css' );
	$wp_styles->registered[ 'media-views' ]->src = plugins_url( 'css/media-views.css', __FILE__ );
	$wp_styles->registered[ 'media-views' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/media-views.css' );
	$wp_styles->registered[ 'media-views' ]->extra[ 'suffix' ] = '';
	$wp_styles->registered[ 'wp-admin' ]->src = plugins_url( 'css/dark-admin.css', __FILE__ );
	$wp_styles->registered[ 'wp-admin' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/dark-admin.css' );
	$wp_styles->registered[ 'wp-admin' ]->deps[] = 'open-sans';
	$wp_styles->registered[ 'wp-admin' ]->deps[] = 'dashicons';
	$wp_styles->registered[ 'wp-admin' ]->extra[ 'suffix' ] = '';
	$wp_styles->registered[ 'wp-pointer' ]->src = plugins_url( 'css/wp-pointer.css', __FILE__ );
	$wp_styles->registered[ 'wp-pointer' ]->ver = filemtime( plugin_dir_path( __FILE__ ) . 'css/wp-pointer.css' );

}

// load Dark Admin's `dialog.css`
add_filter( 'tiny_mce_before_init', 'darkAdmin_mce_init' );
function darkAdmin_mce_init( $mce_init ) {

	// make sure we don't override other custom `content_css` files
	$content_css = plugins_url( 'css/tinymce-content.css', __FILE__ );
	if ( isset( $mce_init[ 'content_css' ] ) )
		$content_css .= ',' . $mce_init[ 'content_css' ];

	$mce_init[ 'content_css' ] = $content_css;
	$mce_init[ 'popup_css' ] = plugins_url( 'css/tinymce-dialog.css', __FILE__ );

	return $mce_init;

}

// load Dark Admin's `wp-admin.css` if it's force printed
add_filter( 'style_loader_tag', 'darkAdmin_fix_style_tag_href' );
function darkAdmin_fix_style_tag_href( $handle ) {

	// fix force print in `wp-mce-help.php`
	if ( strpos( $handle, admin_url( '/css/wp-admin.css' ) ) !== false ) {
		$handle = str_replace( admin_url( '/css/wp-admin.css' ), plugins_url( 'css/wp-admin.css', __FILE__ ), $handle );
	}

	return $handle;

}

add_filter( 'get_user_option_admin_color', 'darkAdmin_force_admin_color' );
function darkAdmin_force_admin_color( $color_scheme ) {

        global $_wp_admin_css_colors;

        // if setting is `fresh`, `classic` or doesn't exist, change it to `mp6`
        if ( ! isset( $_wp_admin_css_colors[ $color_scheme ] ) ) {
                $color_scheme = 'darkadmin';
        }

        return $color_scheme;

}

// Add an `dark-admin` body class to the front-end
add_filter( 'body_class', 'darkAdmin_add_body_class_frontend' );
function darkAdmin_add_body_class_frontend( $classes ) {
	$classes[] = 'dark-admin';
	return $classes;
}

// Add body classes to the back-end
add_filter( 'admin_body_class', 'darkAdmin_add_body_class_backend' );
function darkAdmin_add_body_class_backend( $classes ) {

	if ( is_multisite() )
		$classes .= ' multisite';

	if ( is_network_admin() )
		$classes .= ' network-admin';

	return $classes . ' dark-admin no-svg ';

}

// override WP's default toolbar top margin
add_action( 'wp_head', 'dark-admin_override_toolbar_margin', 11 );
function darkAdmin_override_toolbar_margin() {
	if ( is_admin_bar_showing() ) : ?>
<style type="text/css" media="screen">
	html { margin-top: 32px !important; }
	* html body { margin-top: 32px !important; }
	@media screen and ( max-width: 782px ) {
		html { margin-top: 46px !important; }
		* html body { margin-top: 46px !important; }
	}
</style>
<?php endif;
}
