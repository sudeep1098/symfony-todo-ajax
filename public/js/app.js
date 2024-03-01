document.addEventListener("DOMContentLoaded", function () {
  $(".checkbox").on("change", function () {
    var checkbox = $(this);
    var itemId = checkbox.attr("id");
    $.ajax({
      url: `/update-note/${itemId}`,
      type: "PUT",
      contentType: "application/json",
      data: JSON.stringify({
        done: true,
        id: itemId,
      }),
      success: function (response) {
        checkbox.parent().remove();
        updateNotes();
      },
      error: function (xhr, status, error) {
        console.error("Error updating note:", error);
      },
    });
  });

  $(".edit").on("click", function () {
    localStorage.clear();
    var id = $(this).data("id");
    localStorage.setItem("id", id);
    $.ajax({
      url: `/get-note-for-modal/${id}`,
      type: "GET",
      contentType: "application/json",
      success: function (response) {
        response.notes.forEach(function (note) {
          $("#edit_note_title").val(note.title);
          $("#edit_note_description").val(note.description);
        });
      },
    });
  });

  $("#editnotemodal form").submit(function (event) {
    var id = localStorage.getItem("id");
    id = parseInt(id);
    var title = $("#edit_note_title").val();
    var desc = $("#edit_note_description").val();
    $.ajax({
      url: `/edit-note/${id}`,
      type: "PUT",
      contentType: "application/json",
      data: JSON.stringify({
        id: id,
        title: title,
        desc: desc,
      }),
      success: function (response) {
        $("#editnotemodal").modal("hide");
      },
    });
  });
  $(".delete").on("click", function () {
    var checkbox = $(this);
    var id = $(this).attr("id");
    var container = checkbox.closest(`.col-${id}`);
    $.ajax({
      url: `/delete-note/${id}`,
      type: "GET",
      contentType: "application/json",
      success: function (response) {
        container.remove();
        updateNotes();
      },
    });
  });

  $("#donenotemodal form").on("submit", function (e) {
    var title = $("#done_note_title").val();
    var desc = $("#done_note_description").val();
    $.ajax({
      url: `/done-note`,
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        title: title,
        desc:desc
      }),
      success: function (response) {
        console.log(response);
        // $("#donenotemodal").modal("hide");

      }
    })
  });
  function updateNotes() {
    $.ajax({
      url: `/update-page`,
      type: "GET",
      contentType: "application/json",
      success: function (response) {
        const container = document.getElementById("done");
        const ul = container.querySelector("ul");

        response.notes.forEach((element) => {
          ul.innerHTML += "<li>" + element.title + "</li>";
        });
      },
      error: function (xhr, status, error) {
        console.error("Error updating note:", error);
      },
    });
  }

  updateNotes();
});
