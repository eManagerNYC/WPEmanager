
/**
 * acf_locations.open_infowindow
 *
 * Tracks which info window is currently open, so we can close it when needed
 *
 * @type	var
 * @since	0.1
 */
acf_locations.open_infowindow = null;

/**
 * acf_locations.map
 *
 * Holds our map
 *
 * @type	var
 * @since	0.1
 */
acf_locations.map = null;

/**
 * acf_locations.user
 *
 * Set up to hold user variables like new center lat/lng after searches
 *
 * @type	var
 * @since	0.1
 */
acf_locations.user = {
	'center_lat': null,
	'center_lng': null,
};

/**
 * acf_locations.$spinner
 *
 * Will hold a spinner object
 *
 * @type	var
 * @since	0.1
 */
acf_locations.$spinner = null;

/**
 * render_map
 *
 * This function will render a Google Map onto the selected jQuery element
 *
 * @type	function
 * @since	0.1
 *
 * @param	$el (jQuery element)
 * @return	void
 */
acf_locations.render_map = function( $el )
{
	// var
	var $markers = $el.find('.marker'),
		args = {
			zoom		: ( acf_locations.defaults.zoom ) ? acf_locations.defaults.zoom : 11,
			center		: new google.maps.LatLng(0, 0),
			mapTypeId	: google.maps.MapTypeId.ROADMAP,
			scrollwheel : false,
		},
		center = $el.data('center');

	
 
	// create map	        	
	acf_locations.map = new google.maps.Map( $el[0], args );
 
	// add a markers reference
	acf_locations.map.markers = [];
 
	// add markers
	$markers.each(function(){
    	acf_locations.add_marker( $(this), acf_locations.map );
	});
 
	// center map
	acf_locations.center_map( acf_locations.map, center );

	google.maps.event.addListener(acf_locations.map, 'click', function() {
		if ( acf_locations.open_infowindow != null ) { acf_locations.open_infowindow.close(); }
	});
}

/**
 * add_marker
 *
 * This function will add a marker to the selected Google Map
 *
 * @type	function
 * @since	0.1
 *
 * @param	$marker (jQuery element)
 * @param	map (Google Map object)
 * @return	void
 */
acf_locations.add_marker = function( $marker, map )
{
	// var
	var latlng       = new google.maps.LatLng( $marker.data('lat'), $marker.data('lng') ),
		default_icon = 'http://maps.google.com/mapfiles/ms/micons/red.png',
		icon_url     = $marker.data('marker'),
		icon         = ( icon_url ? icon_url : default_icon );

	// create marker
	var marker = new google.maps.Marker({
		position : latlng,
		map      : map,
		icon     : icon,
	});

	// add to array
	map.markers.push( marker );

	// if marker contains HTML, add it to an infoWindow
	if( $marker.html() )
	{
		// create info window
		marker.infowindow = new google.maps.InfoWindow({
			content : $marker.html()
		});
 
		// show info window when marker is clicked
		google.maps.event.addListener(marker, 'click', function() {
			if ( acf_locations.open_infowindow != null ) { acf_locations.open_infowindow.close(); }
			marker.infowindow.open( map, marker );
			acf_locations.open_infowindow = marker.infowindow;
		});
	}
}
 
/**
 * center_map
 *
 * Center the map, showing all markers attached to this map
 *
 * @type	function
 * @since	0.1
 *
 * @param	map (Google Map object)
 * @return	void
 */
acf_locations.center_map = function( map, center )
{
	// vars
	var bounds = new google.maps.LatLngBounds();

	// loop through all markers and create bounds
	$.each( map.markers, function( i, marker ){
		var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );
		bounds.extend( latlng );
	});

	// only 1 marker?
	if( $.isPlainObject(center) || 'fit' == center )
	{
		if ( 'fit' == center )
		{
			map.fitBounds( bounds );
		}
		else
		{
			center_latlng = new google.maps.LatLng(center.lat, center.lng);

			// set center of map
			map.setCenter( center_latlng );
			map.setZoom( center.zoom );
		}
	}
	else if( map.markers.length == 1 )
	{
		// set center of map
		map.setCenter( bounds.getCenter() );
		map.setZoom( acf_locations.defaults.single_zoom );

		map.markers[0].infowindow.open( map, map.markers[0] );
		acf_locations.open_infowindow = map.markers[0].infowindow;
	}
	else
	{
		var center_latlng = false;

		// See if there is a lat lng to center on
		if ( acf_locations.user.center_lat && acf_locations.user.center_lng )
		{
			center_latlng = new google.maps.LatLng(acf_locations.user.center_lat, acf_locations.user.center_lng);
			// create marker
			var marker = new google.maps.Marker({
				position : center_latlng,
				map      : map,
				icon     : acf_locations.defaults.marker_user,
			});
		}
		else if ( acf_locations.defaults.center_lat && acf_locations.defaults.center_lng )
		{
			center_latlng = new google.maps.LatLng(acf_locations.defaults.center_lat, acf_locations.defaults.center_lng);
		}

		// use default lat/lng or fit to bounds
		if ( center_latlng )
		{
			map.setCenter( center_latlng );
			map.setZoom( acf_locations.defaults.zoom );
		}
		else
		{
			map.fitBounds( bounds );
		}
	}
}
 
/**
 * load_user_location
 *
 * Used to load the user's current location, usually on page load
 *
 * @type	function
 * @since	0.1
 *
 * @return	void
 */
acf_locations.load_user_position = function()
{
	if ( geoPosition.init() )
	{
		geoPosition.getCurrentPosition( acf_locations.update_map_position, null, {enableHighAccuracy:false,maximumAge:120000} );
	}
}

/**
 * update_map_location
 *
 * Update the map with user's current location if provided
 *
 * @type	function
 * @since	0.1
 *
 * @param	position (geoPosition object)
 * @return	void
 */
acf_locations.update_map_position = function( position )
{
	var lat = position.coords.latitude,
		lng = position.coords.longitude;

	acf_locations.geo_search(lat, lng);
}

/**
 * geo_lookup
 *
 * Get the lat/lng from entered zip / address, and then initiate search
 *
 * @type	function
 * @since	0.1
 *
 * @param	address (string) The address entered by the user
 * @return	The lat/lng for the address
 */
acf_locations.geo_lookup = function( address, distance )
{
	var geocoder = new google.maps.Geocoder();

	geocoder.geocode({ 'address': address }, function(results, status) {
		if ( status == google.maps.GeocoderStatus.OK )
		{
			//console.log(results);
			acf_locations.map.setCenter(results[0].geometry.location);
			var lat = results[0].geometry.location.k,
				lng = results[0].geometry.location.A;

			acf_locations.geo_search(lat, lng, distance);
		}
		else
		{
			alert( acf_locations.strings.search_error + ': ' + status );
		}
	});
}

/**
 * geo_search
 *
 * Search the database for new locations around provided position
 *
 * @type	function
 * @since	0.1
 *
 * @param	address (string) The address entered by the user
 * @return	The lat/lng for the address
 */
acf_locations.geo_search = function( lat, lng, distance )
{
	$.ajax({
		url:      acf_locations.ajax_url,
		type:     'post',
		async:    true,
		cache:    false,
		dataType: 'json',

		data: {
			'action':   acf_locations.actions.search,
			'lat':      lat,
			'lng':      lng,
			'distance': distance,
			'filter' :  acf_locations.filter,
		},

		success: function( response )
		{
			//console.log(response);
			//if ( response.status )
			//{
				acf_locations.update_locations( response );
			//}
		},

		error: function( xhr ) {
			console.log(xhr.responseText);
		},
	});
}

acf_locations.update_locations = function( response )
{
	console.log(response);
	
	// Update the map
	$('.'+ acf_locations.map_class).replaceWith( response.new_map );//html( posts );
	acf_locations.user.center_lat = response.lat;
	acf_locations.user.center_lng = response.lng;
	acf_locations.render_map( $('.'+ acf_locations.map_class) );

	// Update the list
	if ( response.posts.length )
	{
		$('.'+ acf_locations.list_class).replaceWith( response.new_list );//html( posts );
	}
	else
	{
		$('.'+ acf_locations.list_class).html( '<h3>' + acf_locations.strings.search_empty + '</h3>' );
	}
}

$(document).ready(function(){

	acf_locations.$spinner = $('<img />', {'class': "acf-locations-spinner", 'src': acf_locations.spinner})

	/**
	 * Render maps
	 */
	$('.' + acf_locations.map_class).each(function(){
		acf_locations.render_map( $(this) );
	});

	/**
	 * Get user's position if possible
	 */
	if ( acf_locations.defaults.load_position_at_start )
	{
		acf_locations.load_user_position();
	}

	$('.acf_locations_locate').on('click', function(e) {
		e.preventDefault();
		acf_locations.load_user_position();
	});

	/**
	 * Zip code search
	 */
	$('#acf_locations_form').on('submit', function(e){
		e.preventDefault();

		var distance = ( $('#acf_locations_distance').length ) ? $('#acf_locations_distance').val() : false;
		acf_locations.geo_lookup( $('#acf_locations_search').val(), distance );
	});

});