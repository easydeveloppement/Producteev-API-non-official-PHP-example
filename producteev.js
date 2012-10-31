

// to connect a user
function producteev_connexion()
	{
	if($('#producteev_login').val() == '')
		{
		alert("The field login is required.");
		return false;
		}
		
	if($('#producteev_password').val() == '')
		{
		alert("The field password is required.");
		return false;
		}
		
	var request = $.ajax({
		  url: "producteev_ajax_connection.php",
		  data: {
			login: $('#producteev_login').val(),
			password: $('#producteev_password').val()
			},
		  type: "POST",
		  dataType: "html"
		});
	
	request.done(function(msg) {
		
		if(msg != '')
			alert(msg);
			
		else		
			document.location.reload(true);
		});
	}

$(document).ready(function(){

	// boite de dialogue pour la connexion producteev
	$( '#dialog-connexion-producteev' ).dialog({
		modal: true,
		height: 300,
		width: 500,
		position: 'center',
		autoOpen: true,
		buttons: {
			'Cancel': function() {
				$( this ).dialog( 'close' );
				},
			'Connection': function() {
				producteev_connexion();
				}
			}
		});
		
	
	
		
	});

