function formatfilelink(){
	var preid = $("#preid", window.parent.document).val();
	var obj = $("#obj", window.parent.document).val();
	var fromeditor = $("#fromeditor", window.parent.document).val();
	var ispre = $("#ispre", window.parent.document).val();

	if (obj.length>0)
	{
		$("a.url").bind("click", function(){
			var url = $(this).attr("href");
			var thumb = $(this).attr("rel");
			var src = ''; //

			if('1' == ispre){
				src = thumb;
			}else{
				src = url;
			}

			window.parent.document.getElementById(obj).value=src;
			if (preid.length>0)
			{
				window.parent.document.getElementById(preid).src=src;
			}
			//解决IE iframe后不能聚焦问题
			//$(window.parent.document.getElementById("focus")).focus();
			
			
			//window.parent.document.getElementById(obj).focus();

			//$(window.parent.document).find('body').append("<input id='debugfocus' value='' style='position:absolute;top:-9999px;' />");
			//window.parent.document.getElementById('debugfocus').focus();

			$(window.parent.document.getElementById("bg")).remove();
			$(window.parent.document.getElementById("edit")).remove();

			return false;	
		})
	}
	else if(fromeditor.length>0){
		$("a.url").bind("click", function(){
			var url=$(this).attr("href");

			//var dialog = window.parent.CKEDITOR.dialog.getCurrent();
			//dialog.setValueOf('info','txtUrl',url);  // Populates the URL field in the Links dialogue.

			//$(window.parent.document.getElementById("bg")).remove();
			//$(window.parent.document.getElementById("edit")).remove();

			returnFileUrl(url);

			return false;			
		});			
	}
}



// Helper function to get parameters from the query string.
function getUrlParam( paramName ) {
	var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' );
	var match = window.parent.location.search.match( reParam );

	return ( match && match.length > 1 ) ? match[1] : null;
}

// Simulate user action of selecting a file to be returned to CKEditor.
function returnFileUrl(fileUrl) {

	var funcNum = getUrlParam( 'CKEditorFuncNum' );
	//var fileUrl = 'http://c.cksource.com/a/1/img/sample.jpg';

	window.parent.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl, function() {

		 // Get the reference to a dialog window.
		 var dialog = this.getDialog();
		 // Check if this is the Image Properties dialog window.
		 if ( dialog.getName() == 'image' ) {
			  // Get the reference to a text field that stores the "alt" attribute.
			  var element = dialog.getContentElement( 'info', 'txtAlt' );
			  // Assign the new value.
			  if ( element )
					element.setValue( 'alt text' );
		 }
		 // Return "false" to stop further execution. In such case CKEditor will ignore the second argument ("fileUrl")
		 // and the "onSelect" function assigned to the button that called the file manager (if defined).
		 // return false;
	} );
	window.parent.close();
}
