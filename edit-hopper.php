<?php
/*
Plugin Name: Edit Hopper
Plugin URI: http://closingtags.com/
Description: Edit Hopper is designed to make switching between the child and parent edit pages simpler in the WordPress admin interface. By creating a meta-box on your page, you can simply select the next page you would like to edit (after clicking update), instead of navigating back to page list view.
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
			$box_title = "Edit something";
                        switch ($screen) {
                            case "post":
                                $box_title = "Edit Posts";
                                break;
                            case "page":
                                $box_title = "Edit Pages";
                            default:
                                break;
                        }
			add_meta_box(
		        'edit_hopper_box',
		        __( $box_title, 'edit_hopper_textdomain' ),
		        array($this, 'start_view'),
		        $screen,
		        'side',
		        'default'
		    );
		}

		function start_view() {
			$post_now = get_post(); // Need the post currently being edited to leave it unlinked
			echo "<div class='eh_ultimate_container'>";
			$this->edithop_main($post_now, 0);
			echo "</div>";
		}

		function edithop_main($current = '', $level = 0){
		    	
		    	if($level == 0) {
		    		// Display the currently editing post
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
						echo $child_post->post_title; // Don't print title as a link if it's the post being edited
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
		$screens = array( 'page', 'post' );

		foreach ( $screens as $screen ) {
			$hopper = new EditHopper();
			$hopper->init($screen);
		}
	}
	add_action( 'add_meta_boxes', 'edithop_custom_box' );

        
        function edithop_menu() {
            add_options_page( 
                'Edit Hopper Options', 
                'Edit Hopper', 
                'manage_options', 
                'edit-hopper-options-menu', 
                'edithop_options' 
            );
        }
        function edithop_options() {
            if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }
            echo '<div class="wrap">';
            echo '<p>Here is where the form would go if I actually had options.</p>';
            echo '</div>';
        }
        add_action( 'admin_menu', 'edithop_menu' );
?>