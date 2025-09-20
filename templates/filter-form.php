<?php
// Get product formats
$product_formats = get_terms(array(
    'taxonomy' => 'pa_book-format',
    'hide_empty' => true,
    'fields' => 'id=>name'
));
?>
<div class="search-filter-container">
    <div id="product-search-form" class="product-search-form">
        <form method="GET" action="<?php echo home_url();?>/filter-test/" aria-labelledby="search-form-title" id="pres-search-filter-form">
            <!-- Get title-search title value -->
             <?php

                $selected_id = isset($_GET['book-title']) ? intval($_GET['book-title']) : '';
                $selected_title = '';
                if ( $selected_id ) {
                    $post_obj = get_post( $selected_id );
                    if ( $post_obj ) {
                        $selected_title = esc_html( $post_obj->post_title );
                    }
                }

                
                $book_title_label = esc_html("Search by Title:","product-search");
                echo apply_filters('search_form_title_label', $book_title_label );
            ?>
            <input type="hidden" name="ppsf" value="ppsf">
            <input type="hidden" id="selected-post-id" name="book-title" value="<?php if($selected_id) echo $selected_id; ?>">

            <div class="searchby-title">
                <label for="title-search">
                    
            
                </label>
                <select id="title-search" style="width:100%;">
                    <?php if ( $selected_id && $selected_title ): ?>
                        <option value="<?php echo $selected_id; ?>" selected="selected">
                            <?php echo $selected_title; ?>
                        </option>
                    <?php else: ?>
                        <option value=""><?php echo esc_html("Start typing to search...","product-search"); ?></option>
                    <?php endif; ?>
                </select>
                <span id="title-desc" class="sr-only">Type the book title you want to search for</span>
            </div>

            <div class="bookformat-search">

                    <?php
                        $book_format_label = esc_html("Book Format:","product-search");
                        echo '<h3>'.apply_filters('search_form_tag_label', $book_format_label ).'</h3>';
                    ?>
                    <p>
                    <?php foreach ($product_formats as $id => $name): ?>
                        <input class="format-checkbox" value="<?php echo htmlspecialchars($id); ?>" name="format" id="<?php echo htmlspecialchars($id); ?>" type="checkbox" <?php checked( isset($_GET['format']) && $_GET['format'] == $id ); ?>></input>
                        <label for="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></label><br>
                    <?php endforeach; ?>
                    </p>
                <span id="format-desc" class="sr-only">Choose the format of the book</span>
            </div>

            <button type="submit" class="search-button"><?php echo esc_html("Search","product-search");?></button>
        </form>
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
            );
            if ( ! empty( $_GET['book-title'] ) ) {
                $args['p'] = intval( $_GET['book-title'] );

            }
            // If format filter is set, add tax_query
            if ( ! empty( $_GET['format'] ) ) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'pa_book-format',
                        'field'    => 'term_id',
                        'terms'    => intval( $_GET['format'] ),
                    ),
                );
            }

            $loop = new WP_Query( $args );

            if ( $loop->have_posts() ) {
                
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
                                <h2 class="woocommerce-loop-product__title"><a href="<?php the_permalink(); ?>" class="pres-prod-title"><?php the_title(); ?></a></h2>
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
                echo '<div class="woocommerce-pagination">';
                echo paginate_links( array(
                    'total'   => $loop->max_num_pages,
                    'current' => $paged,
                ) );
                echo '</div>'; 

            } else {
                echo __( 'No products found' );
            }

            wp_reset_postdata();
         

         ?>
    </div>
</div>


<!-- Next :  Style it properly -->