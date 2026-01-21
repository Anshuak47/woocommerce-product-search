<?php

// data for dropdowns
$author_terms = get_terms([
    'taxonomy' => 'product_brand',
    'hide_empty' => true,
    'fields' => 'id=>name'
]);

// Get product categories
$book_terms = get_terms([
    'taxonomy'      => 'product_cat',
    'hide_empty'    => true,
    'fields'        => 'id=>name',
]);

function get_hierarchical_terms($taxonomy) {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'orderby' => 'parent',
        'order' => 'ASC'
    ));
    // echo "<pre>".print_r($terms,1)."</pre>";
    $hierarchical_terms = array();
    
    // First, get all parent terms (parent = 0)
    foreach($terms as $term) {
        if($term->parent == 0) {
            $hierarchical_terms[$term->term_id] = $term->name;
            
            // Then get children of this parent
            $children = get_term_children($term->term_id, $taxonomy);
            foreach($children as $child_id) {
                $child = get_term($child_id, $taxonomy);
                $hierarchical_terms[$child->term_id] = ' '. $child->name;
            }
        }
    }
    
    return $hierarchical_terms;
}

// Get product formats
$product_formats = get_terms(array(
    'taxonomy' => 'pa_book-format',
    'hide_empty' => true,
    'fields' => 'id=>name'
));


// Get searched query
$selected_author = isset( $_GET['author_id'] ) ?  $_GET['author_id'] : [];
$selected_category = isset($_GET['product-cat']) ? (array) $_GET['product-cat'] : [];
$selected_category = array_map('intval', $selected_category);
$search_term = isset($_GET['prod-title']) ? sanitize_text_field( $_GET['prod-title'] ) : '';
$selected_title   = '';

if ($search_term) {
    $post = get_post($search_term);
    if ($post && !is_wp_error($post)) {
        $selected_title = $post->post_title;
    }
}



$selected_format   = isset($_GET['format']) ? $_GET['format'] : [];
?>
<div id="product-search-form" class="product-search-form">
    <form method="GET" action="<?php echo home_url();?>/filter-test/" aria-labelledby="search-form-title" id="pres-search-form">
        <!-- Get title-search title value -->
        <input type="hidden" name="ppsf" value="ppsf">
        <!-- <input type="hidden" id="selected-post-id" name="book-title" value=""> -->
        <!-- <input type="hidden" id="book-category" name="product-cat" value=""> -->
        <div class="colored-top">
            <h2 class="form-title">Search Books </h2>
            <!-- Book Title --> 
            <div class="searchby-title">
                <label for="title-search">
            
                
                    <input type="text" name="prod-title" id="title-search" style="border-radius: 10px;padding: 10px;border: 1px solid #e2ad38;font-size:16px;" value="<?php if( !empty($search_term)) echo sanitize_text_field($search_term); ?>" placeholder="Enter Book Title">
                </label>
                <span id="title-desc" class="sr-only">Type the book title you want to search for</span>
            </div>
        </div>

        <div class="bottom-section">
            <!-- Book Category -->
            <div class="book-category-search">

                <div class="category-accordion">
                    <div class="accordion-header">
                        <div type="button" class="accordion-toggle" aria-expanded="false" aria-controls="category-content">
                            <div class="accordion-label"><?php echo esc_html("Book Category:","product-search"); ?></div>
                            <div class="accordion-icon">+</div>
                        </div>
                    </div>
                    <div id="category-content" class="accordion-content" hidden>
                    
                        <?php
                        // $terms = get_hierarchical_terms('product_cat');

                        $parent_terms = get_terms([
                            'taxonomy'   => 'product_cat',
                            'parent'     => 0,
                            'hide_empty' => true,
                        ]);


                        foreach( $parent_terms as $parent_term ){
                            
                            $checked_parent = in_array($parent_term->term_id, $selected_category) ? 'checked' : '';
                            echo '<div class="category-group">';

                            // Parent
                                echo '<div class="category-parent"><input type="checkbox" name="product-cat[]" value="' . esc_attr($parent_term->term_id) . '" ' . $checked_parent . '>
                                ' . esc_html($parent_term->name) . '
                            </label></div>';

                                // Children
                                $children = get_terms([
                                    'taxonomy'   => 'product_cat',
                                    'parent'     => $parent_term->term_id,
                                    'hide_empty' => true,
                                ]);

                                foreach ($children as $child) {
                                    $checked_child = in_array($child->term_id, $selected_category) ? 'checked' : '';
                                    echo '<label class="category-child">
                                    <input type="checkbox" name="product-cat[]" value="' . esc_attr($child->term_id) . '" ' . $checked_child . '>
                                    ' . esc_html($child->name) . '
                                </label>';
                                
                                }

                            echo '</div>';
                        
                        } 
                        
                        ?>
                    </div>
                </div>

                <script>
                document.querySelector('.accordion-toggle').addEventListener('click', function(e) {
                    e.preventDefault();
                    const content = document.getElementById('category-content');
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    
                    const accIcon = document.querySelector('.category-accordion .accordion-icon');
                    if (accIcon) {
                        accIcon.textContent = isExpanded ? '+' : '-';
                    }
                    this.setAttribute('aria-expanded', !isExpanded);
                    content.hidden = isExpanded;
                });
                </script>
                    <span id="category-desc" class="sr-only">Choose a book category</span>
            </div>

            <!-- Author Search -->
            <div class="author-search">
                <div class="author-accordion">

                <!-- Accordion header -->
                    <div class="accordion-header">
                        <div type="button" class="author-accordion-toggle" aria-expanded="false" aria-controls="author-content">
                            <div class="accordion-label"><?php echo esc_html("Author:","product-search"); ?></div>
                            <div class="accordion-icon">+</div>
                        </div>
                    </div>
                    <div id="author-content" aria-describedby="author-desc" hidden>
                    <input type="text"
                        id="author-search"
                        class="author-search-input"
                        placeholder="Search authors..."
                        aria-label="Search authors">

                        <?php

                        $limit = 10;
                        $index = 0;

                        foreach ($author_terms as $id => $name){

                            $hidden_class = ($index >= $limit) ? 'is-hidden' : '';
                            $checked = in_array($id, $selected_author ) ? 'checked' : '';
                                    echo '<label class="author-label taxonomy-item-author ' . $hidden_class . '"><input type="checkbox" name="author_id[]" value="' . esc_attr($id) . '" ' . $checked . '> ' . esc_html(trim($name)) . '</label>';
                            $index++;

                            }

                            if (count($author_terms) > $limit) :
                                ?>
                                <button type="button"
                                    class="load-more-author-taxonomy"
                                    data-target="#author-content"
                                    data-limit="10">
                                    Load more
                                </button>
                            <?php endif; 
                        ?>

                        <script>
                            document.querySelector('.author-accordion-toggle').addEventListener('click', function(e) {
                                e.preventDefault();
                                const content = document.getElementById('author-content');
                                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                                
                                const accIcon = document.querySelector('.author-accordion .accordion-icon');
                                if (accIcon) {
                                    accIcon.textContent = isExpanded ? '+' : '-';
                                }

                                this.setAttribute('aria-expanded', !isExpanded);
                                content.hidden = isExpanded;
                            });
                        </script>
                    
                        <span id="author-desc" class="sr-only">Choose an author from the list</span>
                    </div>
                </div>
            </div>

            <!-- Format Search -->
            <div class="book-format-search">
                <div class="format-accordion">

                    <div class="accordion-header">
                        <div type="button" class="format-accordion-toggle" aria-expanded="false" aria-controls="format-content">
                            <div class="accordion-label"><?php echo esc_html("Book Format","product-search"); ?></div>
                            <div class="accordion-icon">+</div>
                        </div>
                    </div>
                    <div id="format-content" class="accordion-content" hidden>
                        <?php 
                            foreach ($product_formats as $id => $name){
                            $checked = checked($selected_format, $id, false);
                                    echo '<label class="prod-format"><input type="checkbox" name="format[]" value="' . esc_attr($id) . '" ' . $checked . '> ' . esc_html(trim($name)) . '</label>';
                            }
                        ?>
                        <span id="format-desc" class="sr-only">Choose the format of the book</span>
                    </div>
                    
                    <script>
                    document.querySelector('.format-accordion-toggle').addEventListener('click', function(e) {
                        e.preventDefault();
                        const content = document.getElementById('format-content');
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';
                        
                        const accIcon = document.querySelector('.author-accordion .accordion-icon');
                        if (accIcon) {
                            accIcon.textContent = isExpanded ? '+' : '−';
                        }
                        this.setAttribute('aria-expanded', !isExpanded);
                        content.hidden = isExpanded;
                    });
                    </script>
                </div>
            </div>
            <div class="search-buttons"> 
                <button type="submit" class="search-button"><?php echo esc_html("Search","product-search");?></button>
                <button type="reset" class="clear-button"><?php echo esc_html("Reset Filters","product-search");?></button>
            </div>
           
        </div>
        
    </form>
</div>
