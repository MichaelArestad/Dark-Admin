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

class DarkAdmin {
	function __construct() {
		if ( ! defined( 'DARKADMIN' ) ) {
			define( 'DARKADMIN', true );
		}
		add_action( 'init', array( $this, 'register_open_sans' ) );
		remove_action( 'admin_init', 'register_admin_color_schemes', 1 );
		add_action( 'admin_init', array( $this, 'register_admin_color_scheme' ) );
		add_action( 'login_init', array( $this, 'replace_wp_default_styles' ) );
		add_action( 'login_init', array( $this, 'fix_login_color_scheme' ) );
		add_action( 'init', array( $this, 'replace_admin_bar_style' ) );
		add_action( 'admin_init', array( $this, 'replace_wp_default_styles' ) );
		add_action( 'wp_head', array( $this, 'override_toolbar_margin' ), 11 );

		add_filter( 'tiny_mce_before_init', array( $this, 'mce_init' ) );
		add_filter( 'style_loader_tag', array( $this, 'fix_style_tag_href' ) );
		add_filter( 'get_user_option_admin_color', array( $this, 'force_admin_color' ) );
		add_filter( 'body_class', array( $this, 'add_body_class_frontend' ) );
		add_filter( 'admin_body_class', array( $this, 'add_body_class_backend' ) );
	}

	public function register_open_sans() {
		wp_register_style(
			'open-sans',
			'//fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,400,600&subset=latin-ext,latin',
			plugins_url( 'css/opensans.css', __FILE__ ),
			false,
			'20130605'
		);
	}

	public function register_admin_color_scheme() {
		global $wp_styles, $_wp_admin_css_colors;
		wp_admin_css_color(
			'darkadmin',
			__( 'DarkAdmin' ),
			plugins_url( 'css/colors-mp6.css', __FILE__ ),
			array( '#222', '#333', '#0074a2', '#2ea2cc' )
		);
		$_wp_admin_css_colors['darkadmin']->icon_colors = array( 'base' => '#999', 'focus' => '#2ea2cc', 'current' => '#fff' );
		// set modification time
		$wp_styles->registered['colors']->ver    = filemtime( plugin_dir_path( __FILE__ ) . 'css/colors-mp6.css' );
		// set dependencies
		$wp_styles->registered['colors']->deps[] = 'open-sans';
		$wp_styles->registered['colors']->deps[] = 'dashicons';
	}

	public function fix_login_color_scheme() {
		global $wp_styles;
		$wp_styles->registered['colors-fresh']->src    = plugins_url( 'css/colors-mp6.css', __FILE__ );
		$wp_styles->registered['colors-fresh']->ver    = filemtime( plugin_dir_path( __FILE__ ) . 'css/colors-mp6.css' );
		$wp_styles->registered['colors-fresh']->deps[] = 'open-sans';
		$wp_styles->registered['colors-fresh']->deps[] = 'dashicons';
	}

	public function replace_wp_default_styles() {
		global $wp_styles;
		$wp_styles->registered['buttons']->src                 = plugins_url( 'css/buttons.css', __FILE__ );
		$wp_styles->registered['buttons']->ver                 = filemtime( plugin_dir_path( __FILE__ ) . 'css/buttons.css' );
		$wp_styles->registered['editor-buttons']->src          = plugins_url( 'css/editor.css', __FILE__ );
		$wp_styles->registered['editor-buttons']->ver          = filemtime( plugin_dir_path( __FILE__ ) . 'css/editor.css' );
		$wp_styles->registered['media']->src                   = plugins_url( 'css/media.css', __FILE__ );
		$wp_styles->registered['media']->ver                   = filemtime( plugin_dir_path( __FILE__ ) . 'css/media.css' );
		$wp_styles->registered['media-views']->src             = plugins_url( 'css/media-views.css', __FILE__ );
		$wp_styles->registered['media-views']->ver             = filemtime( plugin_dir_path( __FILE__ ) . 'css/media-views.css' );
		$wp_styles->registered['media-views']->extra['suffix'] = '';
		$wp_styles->registered['wp-admin']->src                = plugins_url( 'css/dark-admin.css', __FILE__ );
		$wp_styles->registered['wp-admin']->ver                = filemtime( plugin_dir_path( __FILE__ ) . 'css/dark-admin.css' );
		$wp_styles->registered['wp-admin']->deps[]             = 'open-sans';
		$wp_styles->registered['wp-admin']->deps[]             = 'dashicons';
		$wp_styles->registered['wp-admin']->extra['suffix']    = '';
		$wp_styles->registered['wp-pointer']->src              = plugins_url( 'css/wp-pointer.css', __FILE__ );
		$wp_styles->registered['wp-pointer']->ver              = filemtime( plugin_dir_path( __FILE__ ) . 'css/wp-pointer.css' );
	}

	public function replace_admin_bar_style() {
		global $wp_styles;
		if ( ! isset( $wp_styles->registered['admin-bar'] ) ) {
			return;
		}
		$wp_styles->registered['admin-bar']->src             = plugins_url( 'css/dark-admin.css', __FILE__ );
		$wp_styles->registered['admin-bar']->ver             = filemtime( plugin_dir_path( __FILE__ ) . 'css/dark-admin.css' );
		$wp_styles->registered['admin-bar']->deps[]          = 'open-sans';
		$wp_styles->registered['admin-bar']->deps[]          = 'dashicons';
		$wp_styles->registered['admin-bar']->extra['suffix'] = '';
	}

	public function mce_init( $mce_init ) {
		// make sure we don't override other custom `content_css` files
		$content_css = plugins_url( 'css/tinymce-content.css', __FILE__ );
		if ( isset( $mce_init['content_css'] ) ) {
			$content_css .= ',' . $mce_init['content_css'];
		}

		$mce_init['content_css'] = $content_css;
		$mce_init['popup_css'] = plugins_url( 'css/tinymce-dialog.css', __FILE__ );

		return $mce_init;
	}

	public function fix_style_tag_href( $handle ) {
		if ( strpos( $handle, admin_url( '/css/wp-admin.css' ) ) !== false ) {
			$handle = str_replace( admin_url( '/css/wp-admin.css' ), plugins_url( 'css/wp-admin.css', __FILE__ ), $handle );
		}
		return $handle;
	}

	public function force_admin_color( $color_scheme ) {
		global $_wp_admin_css_colors;
		// if setting is `fresh`, `classic` or doesn't exist, change it to `mp6`
		if ( ! isset( $_wp_admin_css_colors[ $color_scheme ] ) ) {
				$color_scheme = 'darkadmin';
		}
		return $color_scheme;
	}

	public function add_body_class_frontend( $classes ) {
		$classes[] = 'dark-admin';
		return $classes;
	}

	public function add_body_class_backend( $classes ) {
		if ( is_multisite() ) {
			$classes .= ' multisite';
		}
		if ( is_network_admin() ) {
			$classes .= ' network-admin';
		}
		return $classes . ' dark-admin no-svg ';
	}

	public function override_toolbar_margin() {
		if ( is_admin_bar_showing() ) {
			echo <<<HTML
<style type="text/css" media="screen">
	html { margin-top: 32px !important; }
	* html body { margin-top: 32px !important; }
	@media screen and ( max-width: 782px ) {
		html { margin-top: 46px !important; }
		* html body { margin-top: 46px !important; }
	}
</style>
HTML;
		}
	}

}

new DarkAdmin();