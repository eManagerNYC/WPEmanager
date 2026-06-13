jQuery(document).ready(function($){

	var upgrade_links    = document.querySelectorAll('a.button.' + eman_upgrades.prefix);


	/**
	 * Add the click to each button
	 */
	function eman_upgrades_add_click()
	{
		// Start the batch running
		eman_upgrades_ajax( this );
	};


	/**
	 * Run the AJAX connection
	 */
	function eman_upgrades_ajax( link )
	{
		var link_parent       = link.parentNode,
			functionName      = link.getAttribute('data-function'),
			spinner_container = link_parent.querySelector('.spinner'),
			status_log        = document.getElementById(functionName + '_log');

		if ( functionName )
		{
			// Turn off all buttons, only allow one to run at one time
			for ( var i=0; i<upgrade_links.length; i++ ) {
				eman_upgrades_deactivate_link(upgrade_links[i]);
			}

			// Add spinner
			if ( spinner_container ) {
				spinner_container.style.visibility = 'visible';
			}

			// Update the log
			if ( status_log ) {
				var status = status_log.querySelector('.status');
				status.innerHTML = eman_upgrades.strings.status_running;
			}

			// Run the AJAX function
			$.ajax({
				url:      eman_upgrades.ajaxurl,
				type:     'post',
				async:    true,
				cache:    false,
				dataType: 'json',
				data:     {
					action:    eman_upgrades.action,
					subaction: functionName,
				},
				success: function( return_data )
				{
					//console.log(return_data);

					// Update the log
					if ( return_data.output && status_log ) {
						status_log.innerHTML = return_data.output;//eman_upgrades.strings.status_complete;
					}

					// If completed, wrap things up
					if ( ! return_data || return_data.complete )
					{
						// Remove this button when complete
						link_parent.parentNode.removeChild( link_parent );

						// Turn other buttons back on
						for ( var i=0; i<upgrade_links.length; i++ ) {
							eman_upgrades_activate_link( upgrade_links[i] );
						}
					}
					// Otherwise start the next batch
					else
					{
						eman_upgrades_ajax( link );
					}
				},
				error: function( xhr )
				{
					console.log( xhr.responseText );

					if ( status_log ) {
						status_log.innerHTML = eman_upgrades.strings.status_failure;
					}
				}
			});
		}
	}


	/**
	 * Control links
	 */
	function eman_upgrades_deactivate_link( link )
	{
		link.removeEventListener( 'click', eman_upgrades_add_click );
		link.classList.add( eman_upgrades.disabled_class );
	};
	function eman_upgrades_activate_link( link )
	{
		link.classList.remove( eman_upgrades.disabled_class );
		link.addEventListener( 'click', eman_upgrades_remove_default );
	};


	/**
	 * Deactivate default click
	 */
	function eman_upgrades_remove_default(e)
	{
		e.preventDefault();
	};


	/**
	 * Set up the links initially
	 */
	for ( var i=0; i<upgrade_links.length; i++ )
	{
		eman_upgrades_activate_link( upgrade_links[i] );
		upgrade_links[i].addEventListener( 'click', eman_upgrades_add_click );
	}

});