<?php

 /**
 * Plugin Name: Categories Multiple Images
 * Description: Categories Multiple Images Plugin allows you to add multiple images to categories or any other custom taxonomy.
 * Version: 1.0.0
 * Author: Lior Broshi, Binternet
 * Author URI: http://www.binternet.co.il
 * Text Domain: cmi
 * License: GPL2
 */
 
 /*  Copyright 2015  Lior Broshi (binternet)  (email : lior@binternet.co.il)

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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/**
 * Categories_Multiple_Images
 * 
 * @author Lior Broshi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class Categories_Multiple_Images {
    
    /**
     * Categories_Multiple_Images::__construct()
     * 
     * @return void
     */
    function __construct() {
        
        $this->settings();
        
        # Register
        add_action( 'admin_menu', array( $this, 'options_menu') );
        add_action( 'admin_init', array( $this, 'register_hooks' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ), 11 );
    }
    
    /**
     * Categories_Multiple_Images::set_locations()
     * Get settings
     * @return void
     */
    function settings() {
        
        # Plugin Settings
        $this->settings = array(
            'total_images'          =>  $this->validate( get_option( 'cmi_total_images' ), 'total_images' ),
            'exclude_taxonomies'    =>  $this->validate( get_option( 'cmi_exclude_taxonomies' ), 'array' )
        );

        # Directories and paths
        $this->plugin_dir = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        
        # Disabled
        $this->disabled_taxonomies = array('nav_menu', 'link_category', 'post_format');
        
        # Placeholder image
        $this->placeholder_image = $this->get_placeholder_image();
    }
    
    static function get_placeholder_image() {
        return plugin_dir_url( __FILE__ ) . 'assets/img/placeholder.png';
    }
    
    /**
     * Categories_Multiple_Images::register_hooks()
     * Register WP hooks for the plugin
     * @return void
     */
    function register_hooks() {
        
        # Register `add/edit` hook for every taxonomy, unless it's disabled.
        foreach ( get_taxonomies() as $taxonomy ) {
            if ( in_array( $taxonomy, $this->settings['exclude_taxonomies'] ) OR in_array( $taxonomy, $this->disabled_taxonomies ) ) {
                continue;
            }
            
            add_action( $taxonomy . '_add_form_fields', array( $this, 'add_taxonomy_field' ) );
            add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_texonomy_field' ) );
        }
        
        # Register `create/edit` hook for all taxonomies (handles the form and saves the data)
        add_action( 'create_term', array( $this, 'save_taxonomy_images' ) );
        add_action( 'edit_term', array( $this, 'save_taxonomy_images' ) );

    }
    
    /**
     * Categories_Multiple_Images::options_menu()
     * 
     * @return void
     */
    function options_menu() {
    	add_options_page( __('Categories Multiple Images Settings'), __('Categories Multiple Images'), 'manage_options', 'cmi-options', array( $this, 'options_page' ) );
    }
    
    /**
     * Categories_Multiple_Images::register_settings()
     * Sets the settings fields we need for this plugin
     * @return void
     */
    function register_settings() {
    	
        # What are we saving
        register_setting( 'cmi_options', 'cmi_total_images' );
        register_setting( 'cmi_options', 'cmi_exclude_taxonomies' );
        
        # Section and fields
        add_settings_section( 'cmi_section', NULL, array( $this, 'options_section' ), 'cmi-options' );
        add_settings_field( 'cmi_total_images', 'Total Images', array( $this, 'options_inputs' ), 'cmi-options', 'cmi_section', array( 'input' => 'total_images' ) );
        add_settings_field( 'cmi_taxonomies', 'Exclude Taxonomies', array( $this, 'options_inputs' ), 'cmi-options', 'cmi_section', array( 'input' => 'exclude_taxonomies' ) );
    }
    
    /**
     * Categories_Multiple_Images::options_section()
     * 
     * @return void
     */
    function options_section() {
        // Nada
    }
    
    /**
     * Categories_Multiple_Images::options_inputs()
     * Echos the needed inputs for the options screen 
     * @param mixed $args
     * @return
     */
    function options_inputs( $args = NULL ) {
        
        if ( empty( $args['input'] ) ) {
            return FALSE;
        }
        
        switch ( $args['input'] ) {
            case 'total_images':
                $html = '<input type="number" name="cmi_total_images" value="' . $this->settings['total_images'] .'">';
            break;
            
            case 'exclude_taxonomies':
                
                foreach ( get_taxonomies() as $tax ) {
                    
                    if ( in_array( $tax, $this->disabled_taxonomies ) ) {
                        continue;
                    }
                    
                    $checked = ( in_array( $tax, $this->settings['exclude_taxonomies'] ) ) ? ' checked="checked"' : NULL;
                    $html .= '<input type="checkbox" name="cmi_exclude_taxonomies[' . $tax . ']" value="' . $tax . '"' . $checked . ' />' . $tax . '<br />';
                }
            

            break;
        }
        
        echo $html;
    }

       
    /**
     * Categories_Multiple_Images::options_page()
     * Draws the options page
     * @return void
     */
    function options_page() {
        
    	if ( FALSE == current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    	}
        
        ?>
        
    	<div class="wrap">
        
    		<h2><?php _e('Categories Multiple Images'); ?></h2>
    		<form method="POST" action="options.php">
                <?php settings_fields( 'cmi_options' );
                    do_settings_sections( 'cmi-options' );
                    submit_button();
                ?>
    		</form>
    	</div>
        
        <?
    }
    
    /**
     * Categories_Multiple_Images::include_assets()
     * CSS/JS inclusion
     * @param string $mode
     * @return void
     */
    function include_assets( $mode = 'add' ) {
        
        wp_enqueue_script( 'cmi-main', $this->plugin_url . 'assets/js/main.js', array(), FALSE, TRUE );
  
        $html = <<<HTML
            <script>
                cmi_config = {
                    mode: '{$mode}',
                    placeholder: '{$this->placeholder_image}'
                }
            </script>   
        
HTML;
    
        echo $html;
    }
    
    /**
     * Categories_Multiple_Images::add_taxonomy_field()
     * Draws the form field in the `add` screen
     * @return void
     */
    function add_taxonomy_field( $tax_slug ) {

        # Call all the required assets for media upload
        wp_enqueue_media();
        
        # Don't show incase we're excluding this taxonomy
        if ( in_array( $tax_slug, $this->settings['exclude_taxonomies'] ) ) {
            return FALSE;
        }
        
        # Include plugin's assets
        $this->include_assets();
        
        $html = '';
        for ( $i = 1, $n = $this->settings['total_images']; $i <= $n; $i++ ) {
            $html .= '
                <div class="form-field">
                    <label for="taxonomy_image">' . __('Image') . '#' . $i . '</label>
                    <input type="text" name="cmi_taxonomy_image' . $i  .'" id="taxonomy_image" value="" />
                    <br/>
                    <button class="cmi-button-upload image' . $i .' button">' . __('Upload/Add image') . '</button>
                </div>';
        }
        
        echo $html;
    }
    
    /**
     * Categories_Multiple_Images::save_taxonomy_images()
     * Saves the images into the database
     * @param mixed $term_id
     * @return void
     */
    function save_taxonomy_images( $term_id ) {
        
        for ( $i = 1, $n = $this->settings['total_images']; $i <= $n; $i++ ) {
            update_option( "cmi_taxonomy_image{$i}_{$term_id}", $_POST["cmi_taxonomy_image{$i}"] );
        }
    }
    
    /**
     * Categories_Multiple_Images::edit_texonomy_field()
     * Draws the form field in the `edit` screen
     * @param mixed $taxonomy
     * @return void
     */
    function edit_texonomy_field( $taxonomy ) {
        
        # Call all the required assets for media upload
        wp_enqueue_media();
        
        $this->include_assets('edit');
        
        $html = '';
        
        #print_r($taxonomy);
        $term_id = $taxonomy->term_id;
        
        for ( $i = 1, $n = $this->settings['total_images']; $i <= $n; $i++ ) {
            
            $image = get_option( "cmi_taxonomy_image{$i}_{$term_id}" );
            $html .='
            	<tr class="form-field">
            		<th scope="row" valign="top"><label for="">' . __('Image') . '#' . $i . '</label></th>
            		<td><img class="taxonomy-image" src="' . $image . '"/>
                    <br/><input type="text" name="cmi_taxonomy_image' . $i . '" value="'. $image.'" /><br />
            		<button class="cmi-button-upload button">' . __('Upload/Add image') . '</button>
            		<button class="cmi-button-remove button">' . __('Remove image') . '</button>
            		</td>
            	</tr>';
        }
        
        echo $html;

    }
    
    /**
     * Categories_Multiple_Images::get_image()
     * Returns the image(s)
     * @param mixed $term_id                Term ID
     * @param mixed $image_number           Specific Image Number
     * @param string $size                  Image Size [thumb, full etc...]
     * @param bool $return_placeholder      Use image placeholder incase no image was found
     * @return
     */
    static function get_image( $term_id = NULL, $image_number = NULL, $size = 'full', $return_placeholder = FALSE ) {
        
        if ( ! is_numeric( $term_id ) ) {
            return FALSE;
        }
        
    	if ( empty( $term_id ) ) {
    	   
            if ( is_category() ) {
                $term_id = get_query_var('cat');
            }
            elseif ( is_tax() ) {
                $current_term = get_term_by( 'slug', get_query_var('term'), get_query_var('taxonomy') );
                $term_id = $current_term->term_id;
            }
            
            if ( empty( $term_id ) ) {
                # Something is wrong, stop
                return FALSE;
            }
            
    	}
        
        $placeholder_image = self::get_placeholder_image();
        
        # Load the image(s)
        if ( empty( $image_number ) ) {
            
            # Get them all
            $total_images = self::validate( get_option( 'cmi_total_images' ), 'total_images' );
            $images = array();
            
            for ( $i = 1, $n = $total_images; $i <= $n; $i++ ) {
                
                # Get that image
                $image = get_option( "cmi_taxonomy_image{$i}_{$term_id}" );
                
                if ( empty( $image ) ) {
                    
                    # Wops, no such image, placeholder?
                    $image = ( TRUE == $return_placeholder ) ? $placeholder_image : $image;
                    
                }
                elseif ( ! empty( $size ) ) {
                    
                    # Get specific size
                    $thumb_id = self::get_attachment_id_from_url( $image );

                    if ( ! empty( $thumb_id ) ) {
                        # Gotcha
                        $attachment = wp_get_attachment_image_src( $thumb_id, $size );
                        $image = $attachment[0];
                    }
                    
                }
                
                # Add to result array
                $images[] = $image;
            }
            
            return $images;
        }
        else {
            
            # Get specific image
            
            $image = get_option( "cmi_taxonomy_image{$image_number}_{$term_id}" );
            
            if ( empty( $image ) ) {
                
                # Wops, no such image, placeholder?
                $image = ( TRUE == $return_placeholder ) ? $placeholder_image : $image;
                
            }
            elseif ( ! empty( $size ) ) {
                
                # Get specific size
                $thumb_id = self::get_attachment_id_from_url( $image );

                if ( ! empty( $thumb_id ) ) {
                    # Gotcha
                    $attachment = wp_get_attachment_image_src( $thumb_id, $size );
                    $image = $attachment[0];
                }
                
            }
            
            return $image;
        }
        
    }
    
    static function get_attachment_id_from_url($url) {
        global $wpdb;
        $id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = '%s'", array( $url ) ) );
        return ( ! empty($id) ) ? $id : NULL;
    }
    
    /**
     * Categories_Multiple_Images::validate()
     * 
     * @param mixed $value
     * @param mixed $what
     * @return
     */
    static function validate($value,$what) {
        
        if ( empty( $what ) ) {
            return;
        }
        
        switch ( $what ) {
            case 'total_images':
                # At least 1 image
                if ( empty( $value ) OR ! is_numeric( $value ) ) {
                    return 1;
                }
            break;
            
            case 'array':
                # Validate array
                if ( ! is_array( $value ) ) {
                    return array();
                }
                
            break;
            default:
                return $value;
            break;
        }
        
        
        return $value;
        
        
    }
    
}

# And away we go...
$categories_multiple_images = new Categories_Multiple_Images();