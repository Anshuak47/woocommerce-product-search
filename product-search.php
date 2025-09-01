<?php
/**
 * Plugin Name: Product Search
 * Plugin URI: https://https://journeywebsites.com/product-search
 * Description: A plugin to enable product search functionality.
 * Version: 1.0.0
 * Author: Journey Websites
 * Author URI: https://journeywebsites.com
 * License:
 */


add_action("init","pres_product_search_shortcode", 10,1);
function pres_product_search_shortcode(){
    add_shortcode("product-search","pres_product_search_callback");
}

function pres_product_search_enqueue_styles() {
    wp_enqueue_style(
        'product-search-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(),
        time()
    );

    wp_enqueue_script(
        'product-search-script',
        plugin_dir_url(__FILE__) . 'assets/js/scripts.js',
        array('jquery'),
        time(),
        true
    );

    wp_enqueue_script('select2');
    wp_enqueue_style('select2');
}
add_action('wp_enqueue_scripts', 'pres_product_search_enqueue_styles');


function pres_product_search_callback( $atts ){
    $atts = shortcode_atts(
        array(
            'id' => '',
            'category' => array('category'),
        ),
        $atts,
        'product-search'
    );

    $template_path = plugin_dir_path(__FILE__) . 'templates/search-form.php';

    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        return ob_get_clean();
    } else {
        return '<p>Product search template not found.</p>';
    }
}

// Autocomplete search
add_action('wp_ajax_search_posts', 'handle_post_search');
add_action('wp_ajax_nopriv_search_posts', 'handle_post_search');

function handle_post_search() {
    $search_term = sanitize_text_field($_GET['q']);
    
    $posts = get_posts(array(
        'post_type' => 'product', // Change to your post type
        's' => $search_term,
        'posts_per_page' => 20,
        'post_status' => 'publish'
    ));
    
    $results = array();
    foreach($posts as $post) {
        $results[] = array(
            'id' => $post->ID,
            'text' => $post->post_title,
            'post_id' => $post->ID, // Add post ID as separate field
          
            'post_url' => get_permalink($post->ID)
        );
    }
    
    wp_send_json($results);
}

