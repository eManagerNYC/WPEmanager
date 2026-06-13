/**
 * Get Viewport Dimensions
 * returns object with viewport dimensions to match css in width and height properties
 * ( source: http://andylangton.co.uk/blog/development/get-viewport-size-width-and-height-javascript )
 */
function updateViewportDimensions() {
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
	return { width:x,height:y }
}
// setting the viewport width
var viewport = updateViewportDimensions();

/**
 * Throttle Resize-triggered Events
 * Wrap your actions in this function to throttle the frequency of firing them off, for better performance, esp. on mobile.
 * ( source: http://stackoverflow.com/questions/2854407/javascript-jquery-window-resize-how-to-fire-after-the-resize-is-completed )
 */
var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if ( !uniqueId) { uniqueId = "Don't call this twice without a uniqueId"; }
		if (timers[uniqueId]) { clearTimeout (timers[uniqueId]); }
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();

// how long to wait before deciding the resize has stopped, in ms. Around 50-100 should work ok.
var timeToWaitForLast = 100;


/*
 * Put all your regular js in here.
 */
document.addEventListener('DOMContentLoaded', function(){

	/**
	 * Adds the screen reader text to the icon's title so it will show on hover
	 */
	var icons = document.querySelectorAll('span[aria-hidden=true]');
	if ( icons ) {
		for ( var i = 0, len = icons.length; i < len; i++ ) {
			var screen = icons[i].parentNode.querySelector('.screen-reader-text'),
				text = (screen ? screen.innerHTML : '');
			icons[i].setAttribute('title', text);
		}
	}

	/**
	 * For the share permalink field
	 */
	var share_links = document.getElementsByClassName('share-links');
	if ( share_links ) {
		for ( var i = 0, len = share_links.length; i < len; i++ ) {
			this.querySelector('.share-url').addEventListener('click', function(e) {
				this.select();
			});
		}
	}

}); /* end of as page load scripts */


/*
 * Put all your jQuery updates in here
 */
jQuery(document).ready(function($){

	/**
	 * Update pagination to bootstrap
	 */
	$('ul.page-numbers').addClass('pagination');

	/**
	 * Fullscreen button
	 */
	if (
		document.fullscreenEnabled || 
		document.webkitFullscreenEnabled || 
		document.mozFullScreenEnabled ||
		document.msFullscreenEnabled
	) {
		$('.fullscreen').on('click', function(e){
			e.preventDefault();

			var target = $(this).data('fullscreen-target');

			var i = document.getElementById(target);

			// go full-screen
			if ( i.requestFullscreen ) {
				i.requestFullscreen();
			} else if ( i.webkitRequestFullscreen ) {
				i.webkitRequestFullscreen();
			} else if ( i.mozRequestFullScreen ) {
				i.mozRequestFullScreen();
			} else if ( i.msRequestFullscreen ) {
				i.msRequestFullscreen();
			}
		});
	}


	/**
	 * Scroll to top
	 */
	$('#page-top').on('click', function(e) {
		e.preventDefault();
		$("html, body").animate({ 'scrollTop' : 0 });
	});


	/**
	 * Store the submit value.
	 */
	var submit_value = '';
	$('.acf-form input[type=submit]').on('click', function() {
		submit_value = $.trim( $(this).val() );
	});


	/**
	 * Validate on tickets and NOC
	 */
	if ( $('body').is('.single-em_tickets') || $('body').is('.single-em_noc') )
	{
		$(document).on('acf/validate_field', function(e, field) {
			var $field = $(field);

			if ( $field.is('#acf-send_to') && ('Revise' == submit_value || 'Void' == submit_value) )
			{
				$field.data('validation', true);
			}
			else if ( ($field.is('#acf-pco_number') || $field.is('#acf-noc_number')) && ('Revise' == submit_value || 'Void' == submit_value) )
			{
				$field.data('validation', true);
			}
		});
	}


	/**
	 * Update Amount Used
	 */
	function update_amount_used( $field )
	{
		var $amount_used,
			$amount_append;

		if ( $field.siblings('[data-field_name="usage"]').length ) {
			$amount_used = $field.siblings('[data-field_name="usage"]').find('.inner');
		} else {
			$amount_used = $field.siblings('[data-field_name="amount_used"]').find('.inner');
		}

		if ( ! $('.acf-input-append', $amount_used).length ) {
			$amount_append = $('<div />', {'class': "acf-input-append"});
			$amount_used.prepend( $amount_append );
		} else {
			$amount_append = $('.acf-input-append', $amount_used);
		}

		$amount_append.text( $('input', $field).val() );
	}


	/**
	 * limit labor/material/equipment by company in DCRs
	 */
	if ((typeof ajaxurl != 'undefined') && ($companyfield = $('#acf-field-company')) ) {
		// get the associations for all four subsidiary selects
		var company_assoc;
		$.post(ajaxurl, {action: 'company_assoc'}, function(response) {
			company_assoc = JSON.parse(response);
		});

		$companyfield.change(function() {
			var current_company = this.value;
			var current_post_type, options;
			function filter_options_by_assoc(i, select) {
				$select = $(select);
				// restore any stashed options
				if ($restore = $select.data('hidden_options')) {
					$select.append($restore);
				}
				// filter values by ajaxed associations, stash any removed options in $select.data
				var hidden_options = [];
				var preserved_options = [];
				if (company_assoc[current_post_type][current_company]) {
					while (select.hasChildNodes()) {
						el = select.removeChild(select.firstChild);
						innerwhile: do {
							if ('option' == el.nodeName.toLowerCase()) {
								var val = parseInt(el.value);
								for (var j=0; j<company_assoc[current_post_type][current_company].length; j++) {
									if (isNaN(val) || (company_assoc[current_post_type][current_company][j] == val)) {
										preserved_options.push(el);
										break innerwhile;
									}
								}
							}
							hidden_options.push(el);
						} while (false);
					}
				}
				$select.data('hidden_options', hidden_options);
				$select.append(preserved_options);
			}
			if (company_assoc) {
				current_post_type = 'em_employees';
				$('#acf-employee_breakdown .row select.post_object').each(filter_options_by_assoc);
				current_post_type = 'em_labortypes';
				$('#acf-classification_breakdown .row select.post_object').each(filter_options_by_assoc);
				current_post_type = 'em_materials';
				$('#acf-materials .row select.post_object').each(filter_options_by_assoc);
				current_post_type = 'em_equipment';
				$('#acf-equipment .row select.post_object').each(filter_options_by_assoc);
			}
		});
	}

});
