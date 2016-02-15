$(document).ready(function(){
	var $user_options = $('.user-access-option').not('.logged-in');
	var $loggedincheckbox = $('input[data-loggedin="true"]');

	//if there's already an item checked, then enable other checkboxes
	if ($('.user-access-option:checked').length === 1) {
		$user_options.not(":checked").removeProp('disabled');
	} else if ($('.user-access-option:checked').length > 1) {
		$user_options.removeProp('disabled');
	} else if (!$('input[data-role="administrator"]:checked').length) {
		$('input[data-role="administrator"]').removeProp('disabled').prop('checked', true);
	}

	//Update counter
	function updateCounter() {
		var len = $(".user-access-option:checked").length;
		if ( len > 0 ) {
			$('#user-access-notice').addClass('hide');
			$('.submit-alert').addClass('hide');
			$('input[name="submit"]').removeClass('disabled');
			$('div.submit-alert').hide();
		} else {
			$('#user-access-notice').removeClass('hide');
			$('.submit-alert').removeClass('hide');
			$('input[name="submit"]').addClass('disabled');
		}
	}

	//On change of logged in checkbox, notify user that they can't block themselves
	$loggedincheckbox.on('change', function(e) {
		e.preventDefault();
		var $this = $(this);
		$this.prop('checked', true);
		if ( !$('.access-alert').length ) { 
			$('.logged-in-notice').append('<span class="access-alert alert alert-danger">You cannot remove access for the user you are logged in as!</span>');
			setTimeout(function(){
				$('.access-alert').fadeOut(500);
			},4000);
		} else if ( $('.access-alert').is(':hidden') ) {
			$('.access-alert').show();
			setTimeout(function(){
				$('.access-alert').fadeOut(500);
			},4000);
		}
	});

	//On change of user checkboxes set/unset user checkboxes
	$user_options.on('change', function(){
		updateCounter();

		if ($('.user-access-option:checked').length === 1) {
			$user_options.not(":checked").removeProp('disabled');
		} else {
			$user_options.removeProp('disabled');
		}
	});

	//Throw notice if submit is clicked and no users are set
	$('input[name="submit"]').on('click', function(e) {
		var $this = $(this);
		if ( $this.hasClass('disabled') ) {
			e.preventDefault();
			if ( !$('p.submit div.submit-alert').length ) {
				$('p.submit').prepend("<div><div class='alert danger submit-alert'>You must provide access to at least one user.</div></div>");
			} else {
				$('div.submit-alert').fadeOut(200).fadeIn(200);
			}
		} else {
			return;
		}
	});
});