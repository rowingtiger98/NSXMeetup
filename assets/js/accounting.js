/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckFacebookLinkFormatting 																*/
/* Description:	This function checks to see if the user entered a valid facebook link.              		*/
/* -------------------------------------------------------------------------------------------------------- */
function CheckFacebookLinkFormatting() {
	var content = $(this).val();
	if(content == '') {
		console.log("Nothing here, nothing to worry about");
	} else {
		var result = content.includes('facebook.com/');
		if(!(result)) {
			alert("'"+content +"' does not appear to be the full link to your facebook profile.Please enter the full link. \n\nExample: http://www.facebook.com/<profile_name> \n\nYou can also cut and paste the link from your browser that's open to your facebook profile.")
		}
	}
}