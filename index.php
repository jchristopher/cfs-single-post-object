<?php
/*
Plugin Name: CFS - Single Post Object Add-on
Description: The Single Post Object field type for Custom Field Suite (add-on).
Version: 1.0
Author: Jonathan Christopher
Author URI: http://irontoiron.com/
License: GPL2
*/

$cfs_single_post_object = new cfs_single_post_object();

class cfs_single_post_object {
	function __construct() {
		add_filter( 'cfs_field_types', array( $this, 'cfs_field_types' ) );
	}

	function cfs_field_types( $field_types ) {
		$field_types['single'] = dirname( __FILE__ ) . '/single-post-object.php';
		return $field_types;
	}
}
