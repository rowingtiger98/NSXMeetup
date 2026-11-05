// -------------------------------------------------------------------------------------
// Function: 	RenderActivePicturesSection
// Description: This function renders the large active picture.
// -------------------------------------------------------------------------------------
function RenderActivePicture(filename) {
    var selected_image = $(this).attr('filename');
    var selected_image_index = $(this).attr('index');
    var active_directory = $("#active_image_viewer_container").attr('active_directory');
	var file_path = active_directory+"/resized/"+selected_image;

    $(".image_loading_screen").show();

    console.log("Display ",selected_image,"( index = ",selected_image_index,") in ", active_directory);
    console.log(file_path);
    $("#active_selected_resized_image").attr("src", file_path);
    $(".image_loading_screen").fadeOut(slow);
// 	$.ajax({
// 	  url: 'PICTUREFunctions.php',
// 	  type: 'POST',
// 	  data: {method: 'RenderActivePicture', section_name:'edit_news_container_textarea'},
// 	  success: function(data) {
// 		results = eval(data);
// 		if (results[0] == '1') {
// 			$("#news").removeClass('active');
// 		} else {
// 			$("#news").addClass('active');
// 			$("#news_text_container").html(results[1]);
// 		}
// 	  }
// 	});	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderThumbnailImages
// Description: This function renders the thumbnails for a directory.
// -------------------------------------------------------------------------------------
function RenderThumbnailImages(dirname) {
	
    console.log("DIRNAME = ", dirname);
	$.ajax({
	  url: 'PICTUREFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderThumbnailsForDirectory', dirname:dirname},
	  success: function(data) {
		results = eval(data);
        $("active_image_viewer_container").attr('active_image', results[0]);
        $("#active_selected_resized_image").attr("src",results[0]);
	    $("#thumbnail_container").html(results[2]);

		var elms = document.querySelectorAll('[id^="thumbnail_image_container_"]');
		for (i = 0; i < elms.length; i++) {
			elms[i].onclick = RenderActivePicture;
		}

	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderNewDirectory
// Description: This function renders the thumbnails for a directory.
// -------------------------------------------------------------------------------------
function RenderNewDirectory() {
    var path = $(this).attr('dir_path');
    var index_number = $(this).attr('index');
    var thumbnailsdirname = $(this).attr('dir_path')+'/thumbnails/';
    $('[id^="navigation_button_"]').each(function () {
        $(this).removeClass('active');
    });

    $("#navigation_button_"+index_number+"").addClass('active');

    $("#active_image_viewer_container").attr('active_directory', path);
    console.log("Render a new directory for ", thumbnailsdirname );
    RenderThumbnailImages(thumbnailsdirname);
    $(".image_loading_screen").fadeOut("slow");

}