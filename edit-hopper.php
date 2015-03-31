<?php
/*
Plugin Name: Edit Hopper
Plugin URI: https://github.com/Dilden/Edit-Hopper
Description: Edit Hopper is designed to make switching between the child and parent edit pages simpler in the WordPress admin interface. By creating a meta-box on your page, you can simply select the next page you would like to edit (after clicking update), instead of navigating back to page list view.
Version: 1.0.1
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
define('POST_TYPE_OPTIONS', serialize(array('public' => true,)));

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
        $all_post_types = get_post_types(unserialize(POST_TYPE_OPTIONS), 'names');
        foreach ($all_post_types as $post_type) {
            if( get_option( 'eh-enable-'.$post_type ) == 1 ) { array_push($screens, $post_type); }
        }
            
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

        $post_types = get_post_types(unserialize(POST_TYPE_OPTIONS), 'names');

        // variables for the field and option names 
        $hidden_name = "hidden-field";
        $hidden_val = "yup";

        // See if the user has posted us some information
        if ( isset($_POST[$hidden_name]) && $_POST[$hidden_name] == $hidden_val ) {

            foreach ($post_types as $post_type) {
                
                update_option('eh-enable-'.$post_type, 0);
                // Read their posted value
                if(isset($_POST['eh-enable'])) {
                    // Save the posted value in the database
                    $options = $_POST['eh-enable'];
                    foreach ($options as $option) {
                        if($option == $post_type) {
                            update_option('eh-enable-'.$post_type, 1);
                        }
                    }
                }
            }

            // Put an settings updated message on the screen
            ?>
            <div class="updated"><p><strong><?php _e('Settings saved.', 'menu-test' ); ?></strong></p></div>
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
                            <td>';

                            foreach ($post_types as $post_type) {
                                $our_post = get_post_type_object( $post_type );
                                $option = get_option('eh-enable-'.$post_type);

                                echo '<label for="eh-enable-'.$post_type.'">
                                        <input id="eh-enable-'.$post_type.'" type="checkbox" 
                                            value="'.$post_type.'" name="eh-enable[]"'. checked($option, 1, false). '>'. $our_post->labels->name .'</label><br/><br/>';
                            }
                        echo '</td>
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