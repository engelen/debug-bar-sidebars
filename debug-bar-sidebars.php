<?php
/*
Plugin Name: Debug Bar - Sidebars & Widgets
Version: 1.0
Description: Debug Bar extension that adds information about the sidebars and widgets on the current page. Integrates with Content Aware Sidebars plugin.
Author: Jesper van Engelen
Author URI: http://jespervanengelen.com
Text Domain: dbsw
License: GPLv2

Copyright 2014	Jesper van Engelen	contact@jepps.nl

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if access directly

/**
 * Main plugin class
 *
 * @since 1.0
 */
class DBSW {

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_filter( 'debug_bar_panels', array( $this, 'debug_bar_panels' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'plugins_loaded', array( $this, 'after_setup' ) );
	}

	/**
	 * Add the debug bar panel for sidebars and widgets
	 *
	 * @see filter:debug_bar_panels
	 * @since 1.0
	 */
	public function debug_bar_panels( $panels ) {
		require_once dirname( __FILE__ ) . '/panels/class-debug-bar-sidebars.php';

		$panels[] = new DBSW_Debug_Bar_Sidebars();

		return $panels;
	}

	/**
	 * Register and enqueue scripts and styles on front-end
	 *
	 * @see action:wp_enqueue_scripts
	 * @since 1.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'dbsw-debugbar', plugins_url( 'css/debugbar.css', __FILE__ ) );
	}

	/**
	 * Allow other plugins to hook into this plugin
	 * Should be called on the plugins_loaded action
	 *
	 * @see action:plugins_loaded
		 * @since 1.0
	 */
	public function after_setup() {
		/**
		 * Fires after this plugin is setup, which should be on plugins_loaded
		 * Should be used to access the main plugin class instance, possibly store a reference to it for later use
		 * and remove any plugin action and filter hooks
		 *
		 * @since 1.0
		 *
		 * @param DBSW Main plugin class instance
		 */
		do_action( 'dbsw/after_setup', $this );
	}

}

new DBSW();
