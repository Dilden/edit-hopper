<?php
/*
Plugin Name: Edit Hopper
Plugin URI: http://google.com
Description: Edit Hopper is designed to make switching betweeen the child and parent edit pages simpler in the admin interface.
Version: 0.0.1
Author: Dylan Hildenbrand
Author URI: http://closingtags.com/
License: GPL2

Copyright 2013 Dylan Hildenbrand  (email : dylan.hildenbrand@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'EDIT_HOPPER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

	class EditHopper {
		
		public function __construct() {

		}

		function init($screen) {
			add_meta_box(
		        'edit_hopper_box',
		        __( 'Edit Relative Posts', 'edit_hopper_textdomain' ),
		        array($this, 'edithop_main'),
		        $screen
		    );
		}

		function edithop_main(){
		    echo "<div class='eh_ultimate_container'>";
				$current = get_post();
				
				$postargs = array(
					'post_type' => $current->post_type,
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'orderby' => 'menu_order',
					'order' => 'DESC',
					);
				$postQuery = new WP_Query($postargs);

				if($postQuery->have_posts()) {
					while ($postQuery->have_posts()) {
						$postQuery->the_post();
						$linkPost = get_post();

						// This post has no parent
						if(empty($linkPost->post_parent)) {
							if($current->ID == $linkPost->ID) {
								echo "<div class='edithop-link'>". get_the_title() . "</div>";
							}
							else {
								$ehpostlink = get_edit_post_link(get_the_id());
								if(function_exists('wp_nonce_url')) {
									wp_nonce_url($ehpostlink, 'edit-hopper-post-link');
								}
								echo "<div class='edithop-link'><a href='" . $ehpostlink . "'>" . get_the_title() . "</a></div>";
							}
						}
						else {
							if($current->ID == $linkPost->ID) {
								echo "<div class='edithop-link'> - ". get_the_title() . "</div>";
							}
						}
					}
				}
				wp_reset_query();

		    echo "</div>";
		}
	}

	function eh_styles() {
		wp_enqueue_style('hopper-style', EDIT_HOPPER_PLUGIN_URL .' css/hopper-style.css');
	}
	add_action('admin_init', 'eh_styles');

	function edithop_custom_box() {
		$screens = array( 'post', 'page' );

		foreach ( $screens as $screen ) {
			$hopper = new EditHopper();
			$hopper->init($screen);
		}
	}
	add_action( 'add_meta_boxes', 'edithop_custom_box' );

?>