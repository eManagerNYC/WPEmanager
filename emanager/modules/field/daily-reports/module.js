/**
 * eManager module: Daily Reports — adds a "Fetch site weather" button to the
 * form that auto-fills weather fields from Open-Meteo via the WP proxy,
 * using the project coordinates configured in Settings.
 */
( function ( EM ) {
	'use strict';

	const WEATHER_CODES = {
		0: 'Clear', 1: 'Mostly clear', 2: 'Partly cloudy', 3: 'Overcast',
		45: 'Fog', 48: 'Rime fog', 51: 'Light drizzle', 53: 'Drizzle', 55: 'Heavy drizzle',
		61: 'Light rain', 63: 'Rain', 65: 'Heavy rain', 66: 'Freezing rain', 67: 'Heavy freezing rain',
		71: 'Light snow', 73: 'Snow', 75: 'Heavy snow', 77: 'Snow grains',
		80: 'Rain showers', 81: 'Heavy rain showers', 82: 'Violent rain showers',
		85: 'Snow showers', 86: 'Heavy snow showers', 95: 'Thunderstorm', 96: 'Thunderstorm w/ hail', 99: 'Severe thunderstorm',
	};

	async function form( container, module, id ) {
		await EM.form.render( container, module, id ); // Default form first.

		const weatherInput = container.querySelector( '[name="weather"]' );
		if ( ! weatherInput ) return;

		const button = document.createElement( 'button' );
		button.type = 'button';
		button.className = 'btn btn-sm btn-outline-primary mt-2';
		button.innerHTML = '<i class="bi bi-cloud-download" aria-hidden="true"></i> Fetch site weather';
		weatherInput.insertAdjacentElement( 'afterend', button );

		button.addEventListener( 'click', async () => {
			const project = EM.app.boot.project || {};
			if ( ! project.lat || ! project.lon ) {
				EM.tpl.toast( 'Set the project coordinates in eManager Settings first.', 'warning' );
				return;
			}
			button.disabled = true;
			try {
				const data = await EM.api.weather( project.lat, project.lon );
				const current = data.current || {};
				const daily = data.daily || {};
				const summary = WEATHER_CODES[ current.weather_code ] || 'See details';

				weatherInput.value = `${ summary }, ${ Math.round( current.temperature_2m ) }°F, wind ${ Math.round( current.wind_speed_10m ) } mph, RH ${ current.relative_humidity_2m }%`;
				const set = ( name, value ) => {
					const input = container.querySelector( `[name="${ name }"]` );
					if ( input && value !== undefined && value !== null ) input.value = value;
				};
				set( 'temp_high', Math.round( ( daily.temperature_2m_max || [] )[ 0 ] ) );
				set( 'temp_low', Math.round( ( daily.temperature_2m_min || [] )[ 0 ] ) );
				set( 'precipitation', ( daily.precipitation_sum || [] )[ 0 ] );
				EM.tpl.toast( 'Weather filled from site forecast.' );
			} catch ( error ) {
				EM.tpl.toast( error.message, 'danger' );
			} finally {
				button.disabled = false;
			}
		} );
	}

	EM.registerModule( 'daily-reports', { form } );
} )( window.EM );
