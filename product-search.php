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
    $results = array();
    $posts = get_posts(array(
        'post_type' => 'product', // Change to your post type
        's' => $search_term,
        'posts_per_page' => 20,
        'post_status' => 'publish'
    ));
    
    foreach($posts as $post) {
        $results[] = array(
            'text' => $post->post_title,
            'id' => $post->ID, // Add post ID as separate field
        );
    }
    
    wp_send_json($results);
}

function themeslug_query_vars( $vars ) {
	$vars[] = 'author-id';
    $vars[] = 'product-cat';
    $vars[] = 'product-tag';
    $vars[] = 'format';
    // $vars[] = 'pps';

    return $vars;

}
add_filter( 'query_vars', 'themeslug_query_vars' );


add_filter( 'woocommerce_shortcode_products_query', 'pres_woocommerce_shortcode_products_query',10,1 );
function pres_woocommerce_shortcode_products_query($query){

    $query['pps_rand'] = uniqid();
    return $query;
}

add_action( 'pre_get_posts', 'pres_search_products', 10,1);
function pres_search_products( $query ) {
        
    if ( !is_admin() && $query->get('post_type') == 'product' && is_page('search-results')) {

        // Force WooCommerce products
       
        $tax_query = array();
        if($query->query_vars['pps_rand']   ){
        
            // Category filter
            if ( $cat = get_query_var( 'product-cat' ) ) {
                $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => (int) $cat,
                );
            }

            // Tag filter
            if ( $tag = get_query_var( 'product-tag' ) ) {
                $tax_query[] = array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => (int) $tag,
                );
            }

            // Author/Brand filter
            if ( $author = get_query_var( 'author-id' ) ) {
                $tax_query[] = array(
                    'taxonomy' => 'product_brand',
                    'field'    => 'term_id',
                    'terms'    => (int) $author,
                );
            }

            // Format filter
            if ( $format = get_query_var( 'format' ) ) {
                $tax_query[] = array(
                    'taxonomy' => 'pa_book-format',
                    'field'    => 'term_id',
                    'terms'    => (int) $format,
                );
            }

            // Apply tax_query if we have any
            if ( ! empty( $tax_query ) ) {
                $query->set( 'tax_query', array_merge(
                    array( 'relation' => 'OR' ),
                    $tax_query
                ));
            }

            // Title filter (search string, not just ID)
             $title_search = $_GET['book-title'] ;
            if (!empty($_GET['book-title' ])) {
                $prod_title = sanitize_text_field(  $_GET['book-title' ] );
                $query->set( 's',  $prod_title  );
            
            }

            if ( ! empty( $tax_query ) || ! empty( $title_search ) ) {
                // First set tax query normally
                if ( ! empty( $tax_query ) ) {
                    $query->set( 'tax_query', array_merge(
                        array( 'relation' => 'OR' ),
                        $tax_query
                    ));
                }

                if ( ! empty( $title_search ) ) {
                    // Hook into WHERE to OR title condition with tax_query
                    add_filter( 'posts_where', function( $where, $wp_query ) use ( $title_search ) {
                        global $wpdb;

                        // Build title search SQL
                        $title_sql = $wpdb->prepare("{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $title_search ) . '%');

                        // Replace "AND" with "OR"
                        $where = preg_replace(
                            "/\)\s+AND\s+\(/",
                            ") OR (",
                            $where,
                            1
                        );

                        // Append title search explicitly
                        $where .= " OR {$title_sql}";

                        return $where;
                    }, 10, 2);
                
                }
            }
           
        }
    }

}
