<?php
/*
Plugin Name: Debug Bar - Sidebars & Widgets
*/

class DBSW {

	public function __construct() {
		add_filter( 'debug_bar_panels', array( $this, 'debug_bar_panels' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function debug_bar_panels( $panels ) {
		require_once dirname( __FILE__ ) . '/panels/class-debug-bar-sidebars.php';

		$panels[] = new DBSW_Debug_Bar_Sidebars();

		return $panels;
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'dbsw-debugbar', plugins_url( 'css/debugbar.css', __FILE__ ) );
	}

}

new DBSW();
