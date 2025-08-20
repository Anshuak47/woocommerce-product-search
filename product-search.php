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

include("/templates/search-form.php");
add_action("init","pres_product_search_shortcode", 10,1);
function pres_product_search_shortcode(){
    add_shortcode("product-search","pres_product_search_callback");
}

