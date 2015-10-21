<?php
/*
Plugin Name: Gravitation Testimonials
Plugin URI: https://github.com/UlisesFreitas/gravitation-testimonials
Description: Gravitation Testimonials, is a plugin to display testimonials on your site, with shortcodes
Author: Ulises Freitas
Version: 1.0.0
Author URI: https://disenialia.com/
License: GPLv2
*/
/*-----------------------------------------------------------------------------*/
/*
	Gravitation Testimonials
    Copyright (C) 2015 Gravitation Testimonials

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301
    USA


	Disenialia©, hereby disclaims all copyright interest in the
	library Gravitation Testimonials (a library for display testimonials on Wordpress) 
	written by Ulises Freitas.
	
	Disenialia©, 21 October 2015
	CEO Ulises Freitas.
*/
/*-----------------------------------------------------------------------------*/
 


function gravitation_testimonials_install() {
 
    // Trigger our function that registers the custom post type
    gravitation_testimonials_create_post_type();
 
    // Clear the permalinks after the post type has been registered
    flush_rewrite_rules();
 
}
register_activation_hook( __FILE__, 'gravitation_testimonials_install' );

function gravitation_testimonials_deactivation() {
 
    // Our post type will be automatically removed, so no need to unregister it
 
    // Clear the permalinks to remove our post type's rules
    flush_rewrite_rules();
 
}
register_deactivation_hook( __FILE__, 'gravitation_testimonials_deactivation' );


function gravitation_testimonials_stylesheet() {
	wp_enqueue_style( 'gravitation_testimonials_bootstrap', plugins_url( '/bootstrap/css/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'gravitation_testimonials_style', plugins_url( '/css/style.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'gravitation_testimonials_stylesheet' );

function gravitation_testimonials_scripts(){
	    
	    
	    wp_register_script('gravitation_testimonials_bootstrap_js',plugin_dir_url( __FILE__ ).'/bootstrap/js/bootstrap.min.js', array('jquery'), true);
	    wp_enqueue_script('gravitation_testimonials_bootstrap_js');
	    
}
add_action('wp_enqueue_scripts','gravitation_testimonials_scripts');


add_filter('widget_text', 'do_shortcode');
add_filter( 'manage_testimonials_posts_columns', 'gravitation_set_custom_edit_testimonials_columns' );
add_action( 'manage_testimonials_posts_custom_column' , 'gravitation_custom_testimonials_column', 10, 2 );

function gravitation_set_custom_edit_testimonials_columns($columns) {
    unset( $columns['author'] );
    unset( $columns['date'] );
    $columns['testimonials_image'] = __( 'Image', 'gravitation_testimonials' );
    $columns['gravitation_testimonials_company'] = __( 'Company', 'gravitation_testimonials' );
    $columns['gravitation_testimonials_website'] = __( 'Website', 'gravitation_testimonials' );
    $columns['gravitation_testimonials_shortcode'] = __( 'Shortcode', 'gravitation_testimonials' );

    return $columns;
}
function gravitation_custom_testimonials_column( $column, $post_id ) {
    switch ( $column ) {

        case 'testimonials_image' :
            $testimonial_image_thumbnail = get_the_post_thumbnail( $post_id, array(150,150) );
            
            if ( is_string( $testimonial_image_thumbnail ) && !empty( $testimonial_image_thumbnail ) )
                echo $testimonial_image_thumbnail;
            else
                
                echo '<img src="'.get_template_directory_uri(). '/images/testimonial.png'.'" alt="Testimonial"/>';
            break;
        case 'gravitation_testimonials_company':
        	$meta_company = get_post_meta( get_the_ID(), '_testimonials_post_company', true );
			
         	echo $meta_company;

        break;
        
        case 'gravitation_testimonials_website':
	        $meta_website = get_post_meta( get_the_ID(), '_testimonials_post_url', true );
	        echo '<a href="' . $meta_website . '" tsrget="_blank" rel="nofollow">' . $meta_website . '</a>';
        break;

        case 'gravitation_testimonials_shortcode' :
        	echo '[gravitation_testimonials ids="' . $post_id . '"]';
            break;

    }
}
function gravitation_testimonials_shortcode($atts, $content=null){
   
    extract(shortcode_atts(array(
	    'ids' => '',
	    'category' => '',
		'count' => '',
		'order' => 'DESC',
		'orderby' => 'menu_order',
        
    ), $atts)); 
	
	$args = array();
	
	//All Testimonials [gravitation_testimonials]
	if(!$ids && !$count && !$category){
		$args=array(
			
			'post_type' => 'testimonials',
			'order' => $order,
			'orderby' => $orderby,
			
		);
	}
	
	//Testimonials ids [gravitation_testimonials ids="1,2,3,5"]
	if( $ids && !$category ){
		$cids = explode(',', $ids);
		$aids = array();
		foreach($cids as $key => $value){	
			$aids[] = $value;
		}
		$count = count($cids);
		$args['post__in'] = implode(',', $aids);
		
		$args=array(
			
			'post_type' => 'testimonials',
			'post__in' => $aids,
			'posts_per_page' => intval($count),
			'order' => $order,
			'orderby' => $orderby,
		);
	}
	
	//Testimonials ids [gravitation_testimonials ids="1,2,3,5" category="customers"]
	if( $ids && $category ){
		$cids = explode(',', $ids);
		$aids = array();
		foreach($cids as $key => $value){	
			$aids[] = $value;
		}
		$count = count($cids);
		$args['post__in'] = implode(',', $aids);
		
		$args=array(
			
			'post_type' => 'testimonials',
			'post__in' => $aids,
			'posts_per_page' => intval($count),
			'order' => $order,
			'orderby' => $orderby,
			'tax_query' => array(
			'relation' => 'OR',
				array(
					'taxonomy' => 'gravitation_testimonials_cat',
					'field'    => 'slug',
					'terms'    => array( $atts['category'] ),
				),
			),
		);
	}
	
	//Testimonials ids [gravitation_testimonials count="3"]
	if( $count && !$category ){
		
		$args=array(
			
			'post_type' => 'testimonials',
			'posts_per_page' => intval($count),
			'order' => $order,
			'orderby' => $orderby,
			
		);
	}
	
	//Testimonials ids [gravitation_testimonials count="3" category="customers"]
	if( $count && $category ){
		
		
		$args=array(
			
			'post_type' => 'testimonials',
			'posts_per_page' => intval($count),
			'order' => $order,
			'orderby' => $orderby,
			'tax_query' => array(
			'relation' => 'OR',
				array(
					'taxonomy' => 'gravitation_testimonials_cat',
					'field'    => 'slug',
					'terms'    => array( $atts['category'] ),
				),
			),
		);
	}
	
	//Testimonials ids [gravitation_testimonials category="customers"]
	if( !$count && $category ){
		
		
		$args=array(
			
			'post_type' => 'testimonials',
			'order' => $order,
			'orderby' => $orderby,
			'tax_query' => array(
			'relation' => 'OR',
				array(
					'taxonomy' => 'gravitation_testimonials_cat',
					'field'    => 'slug',
					'terms'    => array( $atts['category'] ),
				),
			),
		);
	}
		
	$query = new WP_Query($args);
	
	if(!$count){
		$count = $query->post_count;
	}

	$html = '';
	
    if ($query->have_posts()){ 
	

	$html = '<div class="carousel-testimonials">
	
				<div class="row">
				<div class="col-md-offset-2 col-md-8">
				<div class="carousel slide" data-ride="carousel" id="testimonial-carousel-' . $category . '">
				
				<ol class="carousel-indicators">';
	        
		        
		        for($i=0;$i<$count;$i++){
			       if($i == 0){
			        	$html .= '<li data-target="#testimonial-carousel-' . $category .'" data-slide-to="0" class="active"></li>';
			        }else{
				    	$html .='<li data-target="#testimonial-carousel-' . $category .'" data-slide-to="'.$i.'"></li>';
			        }
		        }
				$html .='</ol>
        
        <div class="carousel-inner">';
        $m = 0;
        
        while($query->have_posts()){	
	        		
			$query->the_post();
		    $testimonial_imgArray = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ));
	        $testimonial_imgURL = $testimonial_imgArray[0];
	        
	        $meta_company = get_post_meta( get_the_ID(), '_testimonials_post_company', true );
			$meta_website = get_post_meta( get_the_ID(), '_testimonials_post_url', true );
						            
			if($m == 0){
				$class = 'item active';
			}else{
				$class = 'item';
			}
		  
          $html .= '<div class="'.$class.'">
		            <blockquote>
		              <div class="row">
		                <div class="col-sm-3 text-center">';
		                if(!empty($testimonial_imgURL)){  
							$html .='<img alt="'. get_the_title() .'" class="attachment-small wp-post-image" src="' .$testimonial_imgURL.'" title="'. get_the_title() .'" />';
						}else{ 
							$html .='<img alt="'. get_the_title() .'" class="attachment-small wp-post-image" src="' .get_template_directory_uri(). '/images/testimonial.png'. '"title="'. get_the_title() .'" />';
						}
						$html .= '</div>';
		                $html .= '<div class="col-sm-9">';
		                $html .= '<p>' . get_the_content() . '</p>';
		     			$html .=  '<cite>' . get_the_title();
		     			
		     			if($meta_company){
			     			$html .= ' : ' . $meta_company;
		     			}
		     			if($meta_website){
			     			$html .= ' : ' . $meta_website;
		     			}
		                 $html .= '</cite>
		                </div>
		              </div>
		            </blockquote>
          </div>';
          $m++;
        }
        $html .= '</div>
        
        
        <a data-slide="prev" href="#testimonial-carousel-' . $category .'" class="left carousel-control"><i class="fa fa-chevron-left"></i></a>
        <a data-slide="next" href="#testimonial-carousel-' . $category .'" class="right carousel-control"><i class="fa fa-chevron-right"></i></a>
      </div>                          
    </div>
    </div>
</div>';
	
	}
    
	wp_reset_query();
	
	return $html;

    
}
add_shortcode('gravitation_testimonials', 'gravitation_testimonials_shortcode');    		

add_action('admin_menu' , 'gravitation_testimonials_help_admin_menu'); 
function gravitation_testimonials_help_admin_menu() {
    add_submenu_page('edit.php?post_type=testimonials', __('Help', 'gravitation_testimonials'), __('Help', 'gravitation_testimonials'), 'administrator', basename(__FILE__), 'gravitation_testimonials_help_page');	
}
		
function gravitation_testimonials_help_page() { ?>

		<div id="custom-branding-general" class="wrap">
				
				<h2><?php esc_html_e('Help GV. Testimonials','gravitation_testimonials'); ?></h2>
			<div class="metabox-holder">
				<div class="postbox">
				<div class="inside">
					<p><?php _e('For Gravitation Testimonials to work you have to create a Testimonial Category then create a Testimonial over Add New Testimonial','gravitation_testimonials'); ?></p>
					<hr>
					<p><?php _e('Type of shortcodes:','gravitation_testimonials'); ?></p>
					<p><?php _e('Pages and Posts','gravitation_testimonials'); ?></p>
					
					<p><?php _e('Show all testimonials: <strong>[gravitation_testimonials]</strong>','gravitation_testimonials'); ?></p>
					
					<p><?php _e('Show "x" testimonials: <strong>[gravitation_testimonials count="x"]</strong> ,where "x" is a number <strong>[gravitation_testimonials count="3"]</strong>','gravitation_testimonials'); ?></p>
					
					<p><?php _e('Show all testimonials of one "category" : <strong>[gravitation_testimonials category="customers"]</strong> ,where "customers" is a category created on Testimonials category','gravitation_testimonials'); ?></p>
					<p><?php _e('Combined show "x" testimonials of one "category" : <strong>[gravitation_testimonials count="x" category="customers"]</strong> ,where "x" is a number and "customers" is a category created on Testimonial category','gravitation_testimonials'); ?></p>
					
					<ol>
						<li><strong>[gravitation_testimonials]</strong> Display All Testimonials</li>
						<li><strong>[gravitation_testimonials count="3"]</strong> Display 3 Testimonials of the selected category on Home page</li>
						<li><strong>[gravitation_testimonials category="customers"]</strong> Display All Testimonials of "Customers"</li>
						<li><strong>[gravitation_testimonials count="2" category="customers"]</strong> Display 2 Testimonials of "Customers" Category</li>
						<li><strong>[gravitation_testimonials category="customers" ids="1,3,6"]</strong> Display All selected "ids" Testimonials of "Customers"</li>
					</ol>
            
    			</div>
  			</div>
		</div>
		</div>
<?php 
}
	
if( ! function_exists( 'gravitation_testimonials_create_post_type' ) ) :
	function gravitation_testimonials_create_post_type() {
		
		$labels = array(
		'name'                => _x( 'GV. Testimonials', 'Post Type General Name', 'gravitation_testimonials' ),
		'singular_name'       => _x( 'testimonials', 'Post Type Singular Name', 'gravitation_testimonials' ),
		'menu_name'           => __( 'GV. Testimonials', 'gravitation_testimonials' ),
		'name_admin_bar'      => __( 'GV. Testimonials', 'gravitation_testimonials' ),
		'parent_item_colon'   => __( 'Parent testimonial:', 'gravitation_testimonials' ),
		'all_items'           => __( 'All testimonials', 'gravitation_testimonials' ),
		'add_new_item'        => __( 'Add testimonial', 'gravitation_testimonials' ),
		'add_new'             => __( 'Add New', 'gravitation_testimonials' ),
		'new_item'            => __( 'New testimonial', 'gravitation_testimonials' ),
		'edit_item'           => __( 'Edit testimonial', 'gravitation_testimonials' ),
		'update_item'         => __( 'Update testimonial', 'gravitation_testimonials' ),
		'view_item'           => __( 'View testimonial', 'gravitation_testimonials' ),
		'search_items'        => __( 'Search testimonial', 'gravitation_testimonials' ),
		'not_found'           => __( 'Testimonials Not found', 'gravitation_testimonials' ),
		'not_found_in_trash'  => __( 'Testimonials Not found in Trash', 'gravitation_testimonials' ),
	);
	
	$args = array(
		'label'               => __( 'Gv. Testimonials', 'gravitation_testimonials' ),
		'description'         => __( 'Gv. Testimonials Creator simple responsive testimonials items', 'gravitation_testimonials' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-testimonial',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'rewrite'             => true,
		'has_archive'         => true, //TODO
		'exclude_from_search' => false, //true show on query search
		'publicly_queryable'  => true,
		'query_var' => true,
		'capability_type'     => 'post',
		'register_meta_box_cb' => 'gravitation_testimonials_add_post_type_metabox'
	);

		register_post_type( 'testimonials', $args );
		//flush_rewrite_rules();
 
		register_taxonomy( 'gravitation_testimonials_cat', // register custom taxonomy - category
			'testimonials',
			array(
				'hierarchical' => true,
				'show_in_nav_menus'   => true,
				'labels' => array(
					'name' => 'Testimonials category',
					'singular_name' => 'testimonials category',
				)
			)
		);
		
	}
	
	
	add_action( 'init', 'gravitation_testimonials_create_post_type' );
 
 
	function gravitation_testimonials_add_post_type_metabox() { // add the meta box
		add_meta_box( 'gravitation_testimonials_metabox', 'Additionl information about this testimonial', 'gravitation_testimonials_metabox', 'testimonials', 'normal' );
	}
 
	function gravitation_testimonials_metabox() {
		global $post;

		echo '<input type="hidden" name="testimonials_post_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
 
		$testimonials_post_company = get_post_meta($post->ID, '_testimonials_post_company', true);
		$testimonials_post_url = get_post_meta($post->ID, '_testimonials_post_url', true);
		
		echo '<table class="form-table">
			<tr>
				<th>';
				?>
					<label><?php  _e('Company','gravitation_testimonials'); ?></label>
				<?php
				echo '</th>
				<td>
					<input type="text" name="testimonials_post_company" class="regular-text" value="' . $testimonials_post_company . '"> 
				</td>
			</tr>
			<tr>
				<th>';
				?>
					<label><?php _e('Website','gravitation_testimonials'); ?></label>
				<?php
				echo '</th>
				<td>
					<input type="text" name="testimonials_post_url" class="regular-text" value="' . $testimonials_post_url . '"> 
				</td>
			</tr>
			
		</table>';
	
	}
 
function gravitation_testimonials_post_save_meta( $post_id, $post ) { // save the data

		 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		  return;
 
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
 
		if ( ! isset( $_POST['testimonials_post_noncename'] ) ) { // Check if our nonce is set.
			return;
		}
 
		if( !wp_verify_nonce( $_POST['testimonials_post_noncename'], plugin_basename(__FILE__) ) ) { // Verify that the nonce is valid.
			return $post->ID;
		}
 
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if( !wp_verify_nonce( $_POST['testimonials_post_noncename'], plugin_basename(__FILE__) ) ) {
			return $post->ID;
		}
 
		// is the user allowed to edit the post or page?
		if( ! current_user_can( 'edit_post', $post->ID )){
			return $post->ID;
		}
		// ok, we're authenticated: we need to find and save the data
		// we'll put it into an array to make it easier to loop though
 
		$testimonials_post_meta['_testimonials_post_company'] = $_POST['testimonials_post_company'];
		$testimonials_post_meta['_testimonials_post_url'] = $_POST['testimonials_post_url'];
 
		// add values as custom fields
		foreach( $testimonials_post_meta as $key => $value ) { // cycle through the $testimonials_post_meta array

			$value = implode(',', (array)$value); // if $value is an array, make it a CSV (unlikely)
			if( get_post_meta( $post->ID, $key, FALSE ) ) { // if the custom field already has a value
				update_post_meta($post->ID, $key, $value);
			} else { // if the custom field doesn't have a value
				add_post_meta( $post->ID, $key, $value );
			}
			if( !$value ) { // delete if blank
				delete_post_meta( $post->ID, $key );
			}
		}
	}
	add_action( 'save_post', 'gravitation_testimonials_post_save_meta', 1, 2 ); // save the custom fields

endif; // end of function_exists()
 
 


