jQuery(document).ready(function($){

	/**
	 * prevent double submission with loading mask
	 */
	var validationChecker;

	function prevent_dupes( e )
	{
		var submitButton = e.target;
		submitButton.setAttribute('data-text', submitButton.textContent || submitButton.innerText);
		$(submitButton).empty();
		submitButton
			.appendChild( document.createElement('span') )
			.appendChild( document.createTextNode( submitButton.getAttribute('data-text') ) );
		submitButton.lastChild.style.visibility='hidden';
		submitButton.appendChild( document.createElement('span') ).className = 'fa fa-spinner fa-spin';
		submitButton.style.position = 'relative';
		$(submitButton.lastChild).css({
			position: 'absolute',
			left: '0px',
			top: '0px',
			height: '100%',
			width: '100%',
			textAlign: 'center',
			padding: $(submitButton).css('padding-top'),
			fontSize: $(submitButton).css('height')
		});
		// check to see if validation has failed. on false, remove mask so resubmit is possible
		validationChecker = window.setInterval(function() {
			if (('undefined' == typeof acf) || false === acf.validation.status) {
				clearButtonMask(submitButton);
			}
		}, 50);

		// disabling the button now prevents the attempt to submit the form, so we delay it...
		window.setTimeout( function () { submitButton.disabled = true; } , 10);
	}

	function clearButtonMask( btn )
	{
		if ( ('undefined' != typeof validationChecker) && validationChecker ) {
			window.clearInterval(validationChecker);
		}

		if ( btn.getAttribute('data-text') )
		{
			$(btn).empty();
			btn.appendChild(document.createTextNode(btn.getAttribute('data-text')));
			btn.removeAttribute('data-text');
			btn.disabled = false;
		}
	}

	$('.field-submit .btn-primary').on('click', prevent_dupes);

});