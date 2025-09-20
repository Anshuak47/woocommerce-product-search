<?php

// Example data for dropdowns
$author_terms = get_terms([
    'taxonomy' => 'product_brand',
    'hide_empty' => true,
    'fields' => 'id=>name'
]);

// Get product categories
$book_terms = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'fields' => 'id=>name'
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

// Get Product Tags
$product_tags = get_terms(array(
    'taxonomy' => 'product_tag',
    'hide_empty' => true,
    'fields' => 'id=>name'
));

// Get product formats
$product_formats = get_terms(array(
    'taxonomy' => 'pa_book-format',
    'hide_empty' => true,
    'fields' => 'id=>name'
));

?>
<div id="product-search-form" class="product-search-form">
    <form method="GET" action="<?php echo home_url();?>/search-results/" aria-labelledby="search-form-title" id="pres-search-form">
        <!-- Get title-search title value -->
        <input type="hidden" name="pps" value="pps">
        <input type="hidden" id="selected-post-id" name="book-title" value="">
        <input type="hidden" id="book-category" name="product-cat" value="">


        <div class="author-search">
            <label for="author-id">
                <?php
                    $author_label = esc_html("Author","product-search");
                    echo apply_filters('search_form_author_label', $author_label );
                ?>
            </label>
            <select name="author-id" id="author-dropdown" aria-describedby="author-desc">
                <option value=""><?php echo esc_html("Select Author","product-search"); ?></option>

                <?php foreach ($author_terms as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
            <span id="author-desc" class="sr-only">Choose an author from the list</span>
        </div>

        <div class="book-category-search">
            <label for="category">
                <?php
                    $book_category_label = esc_html("Book Category:","product-search");
                    echo apply_filters('search_form_category_label', $book_category_label );
                ?>
               
            
            </label>
           
            <select id="book-categories" name="category_id">;
                <option value="">Select Category...</option>;
                <?php
                // Usage
                $terms = get_hierarchical_terms('product_cat');

                foreach($terms as $id => $name) {
                    echo '<option value="' . $id . '">' . esc_html($name) . '</option>';
                }

                ?>
                </select>
                <span id="category-desc" class="sr-only">Choose a book category</span>
        </div>

        <div class="book-tag-search">
            <label for="product-tag">
                <?php
                    $book_tag_label = esc_html("Search by Tags","product-search");
                    echo apply_filters('search_form_tag_label', $book_tag_label );
                ?>
            </label>
            <select name="product-tag" id="tag-dropdown" aria-describedby="tag-desc">
                <option value="">Search by Tags</option>

                <?php foreach ($product_tags as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
            <span id="category-desc" class="sr-only">Choose a book tag</span>
        </div>


        <div class="searchby-title">
            <label for="title-search">
                <?php
                    $book_title_label = esc_html("Search by Title:","product-search");
                    echo apply_filters('search_form_title_label', $book_title_label );
                ?>
          
            </label>
            <select id="title-search" name="prod-title" style="width: 100%;">
                <option value=""><?php echo esc_html("Start typing to search...","product-search"); ?></option>
            </select>
            <span id="title-desc" class="sr-only">Type the book title you want to search for</span>
        </div>

        <div class="bookformat-search">
            <label for="format">
                <?php
                    $book_format_label = esc_html("Book Format:","product-search");
                    echo apply_filters('search_form_tag_label', $book_format_label );
                ?>
            
        
            </label>
            <select name="format" id="format" aria-describedby="format-desc">
                <option value=""><?php echo esc_html("Select Format","product-search"); ?></option>
                <?php foreach ($product_formats as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
            <span id="format-desc" class="sr-only">Choose the format of the book</span>
        </div>

        <button type="submit" class="search-button"><?php echo esc_html("Search","product-search");?></button>
    </form>
</div>
