<?php
/*
Plugin Name: Dummy Text Generator
Plugin URI: http://code.tutsplus.com
Description: Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
Version: 1.0
*/

add_action( 'admin_head', 'add_tinymce_dummytext' );
function add_tinymce_dummytext() {
    global $typenow;

    // only on Post Type: post and page
    if( ! in_array( $typenow, array( 'post', 'page' ) ) ) {
        return ;
    }

    add_filter( 'mce_external_plugins', 'add_tinymce_dummytext_plugin' );
    // Add to line 1 form WP TinyMCE
    add_filter( 'mce_buttons', 'add_tinymce_dummytext_button' );
}

// include the js for tinymce
function add_tinymce_dummytext_plugin( $plugin_array ) {

    $plugin_array['dummytext_plugin'] = get_template_directory_uri().'/assets/js/admin/tinymce-plugin.js';

    return $plugin_array;
}

// Add the button key for address via JS
function add_tinymce_dummytext_button( $buttons ) {

    array_push( $buttons, 'dummytext_button' );
    // Print all buttons
    return $buttons;
}