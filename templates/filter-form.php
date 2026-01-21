<?php
// Get product formats
$product_formats = get_terms(array(
    'taxonomy' => 'pa_book-format',
    'hide_empty' => true,
    'fields' => 'id=>name'
));

?>
<div class="search-filter-container">
    <div class="search-filter-wrap">
        <?php echo do_shortcode("[product-search]"); ?>
    </div>
    <div class="pps-product-section">

        
         <?php 
            $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

            // WP_Query for WooCommerce products
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => 20,
                'paged'          => $paged,
                'orderby' => 'menu_order title',
                'order'   => 'ASC',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => array('exclude-from-catalog', 'exclude-from-search'),
                        'operator' => 'NOT IN',
                    ),
                ),
            );

            // Show results if either of the categories match the query
            $or_query = array('relation' => 'OR');

            if ( ! empty( $_GET['prod-title'] ) ) {
                $args['s'] =  sanitize_text_field( $_GET['prod-title'] );

            }
            // If format filter is set, add tax_query
            if ( $format = $_GET['format'] ) {
                $or_query[] = array(
                    'taxonomy' => 'pa_book-format',
                    'field'    => 'term_id',
                    'terms'    => $format
                );
            }

            // Author/Brand filter
            if ( $author = $_GET['author_id'] ) {
                
                $or_query[] = array(
                    'taxonomy' => 'product_brand',
                    'field'    => 'term_id',
                    'terms'    => $author,
                );
            }

            // Category filter
            if ( $cat = $_GET['product-cat'] ) {
                $or_query[] = array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $cat,
                    
                );
            }

            if ( count($or_query) > 1 ) {
                $args['tax_query'][] = $or_query;
            }
            
            $loop = new WP_Query( $args );
            
            if ( $loop->have_posts() ) {
                
                echo "<div class='search-results'>";      
                    echo "<div class='pps-product-loop'>";
                    while ( $loop->have_posts() ) : $loop->the_post();
                        $prod_id = get_the_ID();
                        $product_obj = wc_get_product( $prod_id );
                        ?>
                        <div class="product-item">
                    
                            <div class="product-thumbnail">
                                <?php if (has_post_thumbnail()) { ?>
                                    <a href="<?php the_permalink(); ?>"><?php echo the_post_thumbnail('medium');?></a>
                                    
                                    <?php } ?>
                            </div>
                            
                            <div class="prod-details">
                                <div class="product-title">
                                    <h2 class="woocommerce-loop-product__title pres-prod-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                </div>
                                <div class="product-price">
                                
                                    <?php echo $product_obj->get_price_html(); // Display product price ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        endwhile;
                        echo "</div>";
                            // Pagination
                        if( $loop->max_num_pages > 1 ){
                            echo '<div class="woocommerce-pagination">';
                            echo paginate_links( array(
                                'total'   => $loop->max_num_pages,
                                'current' => $paged,
                            ) );
                            echo '</div>'; 
                        }
                    echo "</div>";
                    
                

                } else {
                    echo __( 'No products found' );
                }

                wp_reset_postdata();
            echo "</div>";

         ?>
    </div>
</div>


<!-- Next :  Style it properly -->