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

	function edithop_main(){
	    echo "<div class='ultimate-container'>";
			$current = get_post();
			
			$postargs = array(
				'post_type' => $current->post_type,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				);
			$postQuery = new WP_Query($postargs);

			if($postQuery->have_posts()) {
				while ($postQuery->have_posts()) {
					$postQuery->the_post();
					if($current->ID == get_post()->ID) {
						echo "<div class='edithop-link'>". get_the_title() . "</div>";
					}
					else {
						echo "<div class='edithop-link'><a href='" . get_the_id() . "'>" . get_the_title() . "</a></div>";
					}
				}
			}
			wp_reset_query();

			/* Start Old and busted code here */
			// $parvar = $current->post_parent;
			// if($parvar) {
			// 	echo "<div class='hopper-parent-link'><a href='" . get_edit_post_link($parvar) . "'>" 
			// 	. get_post($parvar)->post_title . "</a></div>";
			// }

			// echo "<div class='hopper-current-link'>".get_post()->post_title. "</div>";


			// $args = array(
			// 			'post_type' => 'page',
	  //       			'child_of' => (get_post()->ID));
			// $children = get_pages($args);
			// if($children)
			// {
			// 	foreach ($children as $child) {
			// 		echo "<div class='hopper-child-link'> - <a href='" . get_edit_post_link($child->ID) . "'>" 
			// 		. get_post($child->ID)->post_title . "</a></div>";
			// 	}
			// }

	    echo "</div>";
	}

	function edithop_custom_box() {
		$screens = array( 'post', 'page' );

		foreach ( $screens as $screen ) {

		    add_meta_box(
		        'edit_hopper_box',
		        __( 'Edit Relative Posts', 'edit_hopper_textdomain' ),
		        'edithop_main',
		        $screen
		    );
		}
	}
	add_action( 'add_meta_boxes', 'edithop_custom_box' );

?>