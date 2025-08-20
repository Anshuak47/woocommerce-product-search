<?php

function pres_product_search_callback( $atts ){
    $atts = shortcode_atts(
        array(
            'id' => '',
            'category' => array('category'),
        ),
        $atts,
        'product-search'
    );

    
}