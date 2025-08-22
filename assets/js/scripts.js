var $ = jQuery;
jQuery(document).ready(function (e) {
  jQuery("#author-dropdown").select2({
    tags: true,
    tokenSeparators: [",", " "],
    placeholder: "Type to search or add new",
    allowClear: true,
  });
});

jQuery(document).ready(function ($) {
  $("#book-categories").select2({
    placeholder: "Select a category",
    allowClear: true,
    width: "100%",
    templateResult: function (option) {
      // Custom styling for indented options
      if (option.text && option.text.startsWith(" ")) {
        return $(
          '<span style="padding-left: 20px;">' + option.text + "</span>"
        );
      }
      return option.text;
    },
  });
});

// Autocomplete search
jQuery(document).ready(function ($) {
  $("#term-search")
    .select2({
      ajax: {
        url: "/wp-admin/admin-ajax.php",
        dataType: "json",
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            action: "search_posts",
          };
        },
        processResults: function (data) {
          $("#term-search").empty();
          return {
            results: data,
            pagination: {
              more: false, // Disable pagination
            },
          };
        },
        cache: false,
      },
      placeholder: "Search posts...",
      minimumInputLength: 3,
      allowClear: true,
    })
    .on("select2:open", function (e) {
      // Clear the search box when opened
      $(".select2-search__field").val("");
    })
    .on("select2:select", function (e) {
      var data = e.params.data;

      // Update hidden fields
      $("#selected-post-id").val(data.post_id);

      // Update select element attributes
      $(this).attr("data-post-id", data.post_id);
    })
    .on("select2:clear", function (e) {
      $(this).empty();
      $("#selected-post-id").val("");
      $("#term-search").empty();

      // Clear ALL hidden fields and data attributes
      $(this).removeAttr("data-post-id");

      $(this).append('<option value="">Start typing to search...</option>');

      console.log("Cleared all fields");
    })
    .on("change", function (e) {
      console.log("CHANGE event:", $(this).val());

      // If empty value, clear everything
      if (!$(this).val()) {
        $("#term-search").empty();
        $("#selected-post-id").val("");
        $(this).removeAttr("data-post-id");
      }
    });
});
