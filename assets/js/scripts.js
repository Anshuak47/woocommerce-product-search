var $ = jQuery;
jQuery(document).ready(function (e) {
  jQuery("#author-dropdown")
    .select2({
      tags: true,
      tokenSeparators: [",", " "],
      placeholder: "Type or Select Author",
      allowClear: true,
    })
    .on("select2:clear", function (e) {
      $(this).empty();
      $("#author-id").val("");
      $("#author-dropdown").empty();

      console.log("Cleared all fields");
    })
    .on("select2:select", function (e) {
      // Update hidden fields
      $("#author-id").val($(this).val());
    });

  // Book category select2
  $("#book-categories")
    .select2({
      placeholder: "Select a book category",
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
    })
    .on("select2:select", function (e) {
      // Update hidden fields
      $("#book-category").val($(this).val());
    })
    .on("select2:clear", function (e) {
      $(this).empty();
      $("#book-category").val("");
      $("#book-categories").empty();

      console.log("Cleared all fields");
    });

  // Book title search
  $("#title-search")
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
          return {
            results: data,
            pagination: {
              more: false, // Disable pagination
            },
          };
        },
        cache: false,
      },
      placeholder: "Search books...",
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
      $("#selected-post-id").val(data.id);

      // Update select element attributes
      $(this).attr("data-post-title", data.id);
    })
    .on("select2:clear", function (e) {
      $(this).empty();
      $("#selected-post-id").val("");
      $("#title-search").empty();

      // Clear ALL hidden fields and data attributes
      $(this).removeAttr("data-post-title");

      $(this).append('<option value="">Start typing to search...</option>');

      console.log("Cleared all fields");
    })
    .on("change", function (e) {
      console.log("CHANGE event:", $(this).val());

      // If empty value, clear everything
      if (!$(this).val()) {
        $("#title-search").empty();
        $("#selected-post-id").val("");
        $(this).removeAttr("data-post-title");
      }
    });

    // Force radio button behaviour on checkbox
     $(document).on("change", ".format-checkbox", function () {
       // Uncheck all except the one clicked
       $(".format-checkbox").not(this).prop("checked", false);
     });
});
