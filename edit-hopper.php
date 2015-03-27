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
			add_meta_box(
		        'edit_hopper_box',
		        __( "Edit ".ucfirst($screen)."s", 'edit_hopper_textdomain' ),
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
                $screens = array();
            
                if( get_option( 'eh-enable-posts' ) == 1 ) { array_push($screens, "post"); }
                if( get_option( 'eh-enable-pages' ) == 1 ) { array_push($screens, "page"); }
            
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
            
            // variables for the field and option names 
            $enable_posts_name = 'eh-enable-posts';
            $enable_pages_name = 'eh-enable-pages';
            $hidden_name = "hidden-field";
            $hidden_val = "yup";
            
            // Read in existing option value from database
            $enable_posts_val = get_option( $enable_posts_name );
            $enable_pages_val = get_option( $enable_pages_name );
            
            // See if the user has posted us some information
            if ( isset($_POST[$hidden_name]) && $_POST[$hidden_name] == $hidden_val ) {
                // Read their posted value
                $enable_posts_val = 0;
                if( isset( $_POST[$enable_posts_name] ) ) {
                    $enable_posts_val = sanitize_text_field($_POST[ $enable_posts_name ]);
                }
                $enable_pages_val = 0;
                if( isset( $_POST[$enable_pages_name] ) ) {
                    $enable_pages_val = sanitize_text_field($_POST[ $enable_pages_name ]);
                }

                // Save the posted value in the database
                update_option( $enable_posts_name, $enable_posts_val );
                update_option( $enable_pages_name, $enable_pages_val );

                // Put an settings updated message on the screen
                ?>
                <div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
                <?php
            }
        
            
            echo '<div class="wrap">
                    <h2>Edit Hopper Settings</h2>
                    
                    <form action="" method="post">
                        <input id="hidden-field" type="hidden" value="yup" name="hidden-field" />
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label>

                                        Enable Edit Hopper for:

                                    </label>
                                </th>
                                <td>
                                    <label for="'.$enable_posts_name.'">
                                        <input id="'.$enable_posts_name.'" type="checkbox" 
                                            value="1" name="'.$enable_posts_name.'"
                                            '.($enable_posts_val == 1 ? 'checked="checked"' : '').'></input>
                                        Posts
                                    </label>
                                    <br/><br/>
                                    <label for="'.$enable_pages_name.'">
                                        <input id="'.$enable_pages_name.'" type="checkbox" 
                                            value="1" name="'.$enable_pages_name.'"
                                            '.($enable_pages_val == 1 ? 'checked="checked"' : '').'></input>
                                        Pages
                                    </label>
                                </td>

                            </tr>
                        </table>
                        <p class="submit">

                            <input id="submit" class="button button-primary" type="submit" value="Save Changes" name="submit"></input>

                        </p>
                    </form>
                </div>';
        }
        add_action( 'admin_menu', 'edithop_menu' );
?>