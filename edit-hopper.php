<?php
/*
Plugin Name: Edit Hopper
Plugin URI: http://closingtags.com/
Description: Edit Hopper is designed to make switching betweeen the child and parent edit pages simpler in the WordPress admin interface. By creating a meta-box on your page, you can simply select the next page you would like to edit (after clicking update), instead of navigating back to page list view.
Version: 1.0
Author: Dylan Hildenbrand
Author URI: http://closingtags.com/
License: GPL2

Copyright 2015 Dylan Hildenbrand  (email : dylan.hildenbrand@gmail.com)

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
		        __( 'Edit Relative Pages', 'edit_hopper_textdomain' ),
		        array($this, 'start_view'),
		        $screen,
		        'side',
		        'default'
		    );
		}

		function start_view() {
			$post_now = get_post();
			echo "<div class='eh_ultimate_container'>";
			$this->edithop_main($post_now, 0);
			echo "</div>";
		}

		function edithop_main($current = '', $level = 0){
		    	
		    	if($level == 0) {
		    		$postargs = array(
						'post_type' => $current->post_type,
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'orderby' => 'menu_order',
						'order' => 'DESC',
						'post_parent' => $level
						);
		    	}
		    	else {
		    		$postargs = array(
						'post_type' => $current->post_type,
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'orderby' => 'menu_order',
						'order' => 'DESC',
						'post_parent' => $current->ID
						);
		    	}
				
				$postQuery = get_posts($postargs);

				foreach ($postQuery as $child_post) {
					echo "<div class='edithop-link'>";
					for ($i=0; $i < $level; $i++) { 
						echo " - ";
					}

					if(get_the_id() == $child_post->ID) {
						echo $child_post->post_title;
					}
					else {
						$ehpostlink = get_edit_post_link($child_post->ID);
						if(function_exists('wp_nonce_url')) {
							wp_nonce_url($ehpostlink, 'edit-hopper-post-link');
						}
						echo "<a href='" . $ehpostlink . "'>" . $child_post->post_title . "</a>";
					}
					echo "</div>";
					$this->edithop_main($child_post, $level+1);
				}
		}
	}

	function eh_styles() {
		wp_enqueue_style('hopper-style', EDIT_HOPPER_PLUGIN_URL .' css/hopper-style.css');
	}
	add_action('admin_init', 'eh_styles');

	function edithop_custom_box() {
		$screens = array( 'page' );

		foreach ( $screens as $screen ) {
			$hopper = new EditHopper();
			$hopper->init($screen);
		}
	}
	add_action( 'add_meta_boxes', 'edithop_custom_box' );

?>