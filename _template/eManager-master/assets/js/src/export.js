document.addEventListener( 'DOMContentLoaded', function() {

	/**
	 * Set up export buttons and defaults
	 */
	var exportAllButton   = document.getElementsByClassName('table-export-all'),
		exportMonthButton = document.getElementsByClassName('table-export-month'),
		exportRangeButton = document.getElementsByClassName('table-export-range'),
		$startPicker      = jQuery('#export_date_start'),
		$endPicker        = jQuery('#export_date_end'),
		note_text         = "This may take a while&hellip;",
		failed_text       = "No items were found to export.",
		dateFormatShow    = 'MM d, yy',
		dateFormatServer  = 'yy-mm-dd',
		dateArgs          = {
			altField:       '',
			altFormat:       dateFormatServer,
			changeMonth:     true,
			dateFormat:      dateFormatShow,
			defaultDate:    '-1w',
//			numberOfMonths:  3,
			onClose:         function() {},
		},
		dateCloseStart    = function( selectedDate ) {
			$endPicker.datepicker( 'option', 'minDate', selectedDate );
		},
		dateCloseEnd      = function( selectedDate ) {
			$startPicker.datepicker( 'option', 'maxDate', selectedDate );
		};


	/**
	 * Set up the date range pickers
	 */
	// Range start
	dateArgs['altField'] = '#export_date_start_value';
	dateArgs['onClose']  = dateCloseStart;
	$startPicker.datepicker( dateArgs );
	// Range end
	dateArgs['altField'] = '#export_date_end_value';
	dateArgs['onClose']  = dateCloseEnd;
	$endPicker.datepicker( dateArgs );


	/**
	 * Handles each step of export
	 */
	function export_items( progressBar, btn, args )
	{
		// Set default arguments to be sent to the server
		var defaults = {
				action:      'eman_csv_export_all',
				post_type:    eman.post_type,
				query_string: eman.query_string,
			};
			if ( ! args ) { args = {}; }
			for ( var key in defaults ) {
				if ( ! args.hasOwnProperty(key) ) {
					args[key] = defaults[key];
				}
			}

		// AJAX success, loops until done, updates the progress bar, adds the file link when done
		var callback = function( response ) {
//				console.log(response);
				response = JSON.parse( response );

				if ( response )
				{
					// Not finished yet
					if ( 1 == response.status )
					{
						// Update the progress bar
						var progressValue        = (response.page / response.pages) * 100;
						progressBar.classList.add('active');
						progressBar.setAttribute('aria-valuenow', progressValue);
						progressBar.innerHTML    = Math.round(progressValue) + '%';
						progressBar.style.width  = Math.round(progressValue) + '%';

						// Keep going until done
						export_items( progressBar, btn, args );
					}
					// Finished
					else
					{
						if ( response.url )
						{
							// Create the link
							var new_content                  = document.createElement('a'),
								filename_array               = response.url.split('/'),
								filename                     = filename_array[filename_array.length-1];
	
								// Set up the file link
								new_content.setAttribute('class', 'export_link');
								new_content.setAttribute('href', response.url);
								new_content.setAttribute('title', 'Download export');
								new_content.setAttribute('target', '_blank');
								new_content.setAttribute('download', filename);
								new_content.innerHTML        = 'Download';
								new_content.style.fontWeight = 'bold';
								new_content.style.color      = 'white';
						}
						else
						{
							var new_content                  = document.createElement('span');
								new_content.innerHTML        = failed_text;
						}

						// Deactivate the progress bar and add the link
						progressBar.classList.remove('active');
						progressBar.setAttribute('aria-valuenow', '100');
						progressBar.style.width  = '100%';
						progressBar.innerHTML    = '';
						progressBar.appendChild( new_content );

						// Remove note
						var note = progressBar.parentNode.parentNode.querySelector('.export-note');
						if ( note ) {
							note.style.display = 'none';
						}

						// Reactivate the button
						if ( btn ) {
							btn.style.display = 'block';
						}
					}
				}
			};

		// Start talking to the server
		ajax.post( args, callback );
	};

	/**
	 * Creates a progress bar for visual feedback
	 */
	function setup_progress()
	{
		// Add the bootstrap progress bar
		var container = document.createElement('div'),
			progress  = document.createElement('div'),
			note      = document.createElement('small'),
			bar       = document.createElement("div");

			// Set up the progress wrapper
			progress.setAttribute('class', 'progress export_progress');

			// Initial notes on progress
			note.setAttribute('class', 'small export-note');
			note.innerHTML = note_text;

			// Set up the progress bar
			bar.setAttribute('class', 'progress-bar progress-bar-warning progress-bar-striped');
			bar.setAttribute('role', 'progressbar');
			bar.setAttribute('aria-valuenow', 0);
			bar.setAttribute('aria-valuemin', 0);
			bar.setAttribute('aria-valuemax', 100);

		progress.appendChild( bar );
		container.appendChild( note );
		container.appendChild( progress );

		return {
			container: container,
			progress:  progress,
			bar:       bar,
		};
	}

	/**
	 * Creates a progress bar for visual feedback
	 */
	function replace_button( btn, args )
	{
		// Add the bootstrap progress bar
		var title           = btn.innerHTML,
			title_container = document.createElement('h6'),
			progress_items  = setup_progress(),
			date            = '';

			if ( args )
			{
				if ( args.month && args.year ) {
					date = args.month + '/' + args.year;
				} else if ( args.start && args.end ) {
					date = args.start + ' - ' + args.end;
				}
			}

			title_container.innerHTML = title + (date ? ': ' + date : '');
			title_container.style.marginBottom = 0;
			btn.parentNode.appendChild( title_container );
			btn.parentNode.appendChild( progress_items.container );
			btn.style.display = 'none';

		return progress_items;
	}

	/**
	 * Export all items
	 */
	if ( exportAllButton )
	{
		for ( var i=0; i<exportAllButton.length; i++ )
		{
			exportAllButton[i].addEventListener( 'click', function(e) {
				e.preventDefault();
				var progress_items = replace_button( this );
				export_items( progress_items.bar );
			} );
		}
	}

	/**
	 * Export by month
	 */
	if ( exportMonthButton )
	{
		// Set up the dates
		var today      = new Date(),
			month_args = {
				month:     today.getMonth()+1,
				year:      today.getFullYear(),
			},
			m_select   = document.getElementById('export_month'),
			y_select   = document.getElementById('export_year');

		for ( var i=0; i<exportMonthButton.length; i++ )
		{
			exportMonthButton[i].addEventListener( 'click', function(e) {
				e.preventDefault();

				// Get month and year from form
				if ( m_select ) {
					month_args.month = m_select.value;
				}
				if ( y_select ) {
					month_args.year = y_select.value;
				}

				var progress_items = replace_button( this, month_args );
				export_items( progress_items.bar, this, month_args );
			} );
		}
	}

	/**
	 * Export a date range
	 */
	if ( exportRangeButton )
	{
		// Set up the dates
		var range_args = {
				start:     0,
				end:       0,
			},
			s_select   = document.getElementById('export_date_start_value'),
			e_select   = document.getElementById('export_date_end_value');

		for ( var i=0; i<exportRangeButton.length; i++ )
		{
			exportRangeButton[i].addEventListener( 'click', function(e) {
				e.preventDefault();

				// Get start and end dates from form
				if ( s_select ) {
					range_args.start = s_select.value;
				}
				if ( e_select ) {
					range_args.end = e_select.value;
				}

				// Don't bother if dates have not been picked
				if ( range_args.start && range_args.end )
				{
					var progress_items = replace_button( this, range_args );
					export_items( progress_items.bar, this, range_args );
				}
			} );
		}
	}

});