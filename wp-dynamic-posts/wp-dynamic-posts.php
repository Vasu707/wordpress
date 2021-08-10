<?php
/*
	Plugin Name: WP Dynamic Posts
	description: A plugin to fetch posts dynamically in custom html format
	Plugin URI: 
	Version: 1.1
	Author: Vasu Coder
	Author URI: mailto:vcoder777@gmail.com
	License: GPL2
*/

	// Activation Handler
	require_once('activation.php');
	
	register_activation_hook(__FILE__, 'fetchPosts_activate');
	
	// Shortcodes Handler
	require_once('shortcodes.php');