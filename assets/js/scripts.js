var $ = jQuery;
jQuery(document).ready(function (e) {
  // Book title search

  // // Force radio button behaviour on checkbox
  // $(document).on("change", ".format-checkbox", function () {
  //   // Uncheck all except the one clicked
  //   $(".format-checkbox").not(this).prop("checked", false);
  // });

  // PREFILL SELECTED BOOK TITLE ON PAGE LOAD
  (function preselectBookTitle() {
    const selectedBookId = $("#selected-post-id").val();

    if (selectedBookId) {
      $.ajax({
        url: "/wp-admin/admin-ajax.php",
        type: "GET",
        dataType: "json",
        data: {
          action: "search_posts",
          id: selectedBookId,
        },
        success: function (data) {
          if (data && data.length) {
            const book = data[0];
            const option = new Option(book.text, book.id, true, true);
            $("#title-search").append(option).trigger("change");
            console.log("Preselected title:", book.text);
          }
        },
        error: function (err) {
          console.error("Error preloading selected title:", err);
        },
      });
    }
  })();
});

(function () {
  const container = document.getElementById("author-content");
  if (!container) return;

  const searchInput = document.getElementById("author-search");
  const loadMoreBtn = container.querySelector(".load-more-author-taxonomy");
  const LIMIT = 10;

  function reorderAuthors() {
    const items = Array.from(
      container.querySelectorAll(".taxonomy-item-author")
    );
    const checked = items.filter((i) => i.querySelector("input").checked);
    const unchecked = items.filter((i) => !i.querySelector("input").checked);

    // Move checked items to top
    checked.forEach((item) =>
      container.insertBefore(item, container.firstChild)
    );
  }

  function applyLoadMoreState() {
    let visible = 0;
    const items = container.querySelectorAll(".taxonomy-item-author");

    items.forEach((item) => {
      if (item.querySelector("input").checked) {
        item.classList.remove("is-hidden");
        item.style.display = "";
        visible++;
        return;
      }

      if (visible < LIMIT) {
        item.classList.remove("is-hidden");
        item.style.display = "";
        visible++;
      } else {
        item.classList.add("is-hidden");
        item.style.display = "";
      }
    });

    if (loadMoreBtn) {
      loadMoreBtn.style.display = container.querySelectorAll(
        ".taxonomy-item-author.is-hidden"
      ).length
        ? ""
        : "none";
    }
  }

  // Initial reorder + load-more
  reorderAuthors();
  applyLoadMoreState();

  // Search logic
  searchInput.addEventListener("input", function () {
    const query = this.value.toLowerCase().trim();
    const items = container.querySelectorAll(".taxonomy-item-author");

    reorderAuthors();

    items.forEach((item) => {
      const text = item.textContent.toLowerCase();
      const checked = item.querySelector("input").checked;

      item.classList.remove("is-hidden");

      if (!query) {
        item.style.display = "";
      } else {
        item.style.display = text.includes(query) || checked ? "" : "none";
      }
    });

    if (loadMoreBtn) {
      loadMoreBtn.style.display = query ? "none" : "";
    }

    if (!query) {
      applyLoadMoreState();
    }
  });

  // When user checks/unchecks an author
  container.addEventListener("change", function (e) {
    if (e.target.type !== "checkbox") return;

    reorderAuthors();
    applyLoadMoreState();
  });
})();
