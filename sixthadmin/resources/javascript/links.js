$(document).ready(function () {
	search("");
	$("#search").click(function (e) {
		e.preventDefault();
		var searchTerm = $("#namesearch").val();
		search(searchTerm);
	});
});

function search(name) {
	var queryUrl = "/sixthadmin/links/name_search.php?name=" + name;
	$.getJSON(queryUrl, function(data) {
		process(data);
	});
}

function process(result) {
	$("#links_table > tbody").remove();

  if(result["status"]["code"] != 200) {
		$("#message").text("An error occurred");
		return;
	}

	if(result["content"]["found"] == false) {
		$("#message").text("No data found");
		return;
	}

  $("#links_table").append("<tbody>");

  $.each(result["content"]["records"], function (index, item) {
		var timeDate = item["ExpiryDate"];
		var displayDate = "";

		if(timeDate == 2147483647) {
			displayDate = "Never";
		} else {
			var date = new Date(item["ExpiryDate"] * 1000);
			displayDate = date.getDate() + "/" + (date.getMonth() + 1) + "/" + (date.getYear() + 1900);
		}

    var link = decodeURIComponent(item["Link"]);
    $("#links_table").append('<tr><td>' + item["Name"] + '</td><td>' + displayDate + '</td><td><a target="_blank" href="' + link + '">' + link + '</a></td><td id="delete_' + item["ID"] + '"><a id="delete_link_' + item["ID"] + '" href="javascript:remove(' + item["ID"] + ')">Delete</a></td></tr>');
  });

  $("#links_table").append("</tbody>");
}

function remove(id) {
  var certain = confirm("Are you sure you want to delete this?");

  if(certain == false) {
    return;
  }

	$("#delete_link_" + id).remove();
	$("#delete_" + id).text("Deleting...");
  var queryUrl = "/sixthadmin/links/delete.php";

  $.ajax({
    url: queryUrl,
    type: "post",
    dataType: "json",
    data: "id=" + id,
    success: function(data) {
      processRemoveResult(data, id);
    },
    error: function(data) {
			alert("An unexpected error occurred");
			console.log(data);
    }
  });
}

function processRemoveResult(data, id) {
	var status = data["status"];
	$("#delete_" + id).text(status["description"]);
}
