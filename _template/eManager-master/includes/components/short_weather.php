<?php
/**
 * Weather support
 */

class eman_weather
{
	public function __construct() {
		add_shortcode( 'weather', array($this, 'forecast') );
	}

	public function forecast( $atts ) {
		extract( shortcode_atts( array(
			'city'  => 'New_York',
			'state' => 'NY',
			'days'  => '3',
			'm'     => 'F'
		), $atts, 'weather' ) );

		// Cache the call to api for 12 hours
		$transient_key = 'weather/forecasts';
		$weatherunit = '';
		if ( false === ( $forecasts = get_transient($transient_key) ) ) {
			$apikey = eman_get_field('wunderground_api_key', 'option');
			$apiurl = 'http://api.wunderground.com/api/' . $apikey . '/forecast10day/q/' . ($state ? $state : 'NY') . '/' . $city . '.json';
			if ( ! ($json_string = @file_get_contents($apiurl)) ) {
				return false;
			}
			$parsed_json = json_decode($json_string);
			$forecasts   = ( ! empty($parsed_json->forecast->simpleforecast->forecastday) ) ? $parsed_json->forecast->simpleforecast->forecastday : false;

			set_transient( $transient_key, $forecasts, 12 * HOUR_IN_SECONDS );
		}

		if ( $forecasts ) {
			for ( $i = 0; $i < $days; $i++ ) {
				if ( empty($forecasts[$i]) ) {
					continue;
				}

				$forecast = $forecasts[$i];

				$cols = floor(100 / $days);
				if ( 'F' === $m ) {
					$temp = 'High: ' . ( ! empty($forecast->high->fahrenheit) ? $forecast->high->fahrenheit : '') . '&deg; Low: ' . ( ! empty($forecast->low->fahrenheit) ? $forecast->low->fahrenheit : '') . '&deg;F';
				}

				if ( 'C' === $m ) {
					$temp = 'High: ' . $forecast->high->celsius . '&deg;C Low: ' . $forecast->low->celsius . '&deg;C';
				}

				$weatherunit .= '<div class="weatherunit" style="float: left; width: '.$cols.'%"><small><center>';
				if ( ! empty($forecast->date->weekday) && ! empty($forecast->date->pretty) ) {
					$weatherunit .= $forecast->date->weekday . ', ' . substr(strstr($forecast->date->pretty, ' on '), 4) . '<br />';
				}
				if ( ! empty($forecast->conditions) ) {
					$weatherunit .= $this->forecast_icon($forecast->conditions, 42) . '<br>';
					$weatherunit .= $forecast->conditions.'<br>'.$temp;
				}
				$weatherunit .= '</center></small></div>';
			}
			return $weatherunit;
		}
	}

	private function forecast_icon( $status, $size=42 ) {
		$icons = array(
			'Chance of Flurries'       => 'wi-day-snow',
			'Chance of Rain'           => 'wi-day-rain',
			'Chance Rain'              => 'wi-day-rain',
			'Chance of Freezing Rain'  => 'wi-day-rain-mix',
			'Chance of Sleet'          => 'wi-day-rain-mix',
			'Chance of Snow'           => 'wi-day-snow',
			'Chance of Thunderstorms'  => 'wi-day-thunderstorm',
			'Chance of a Thunderstorm' => 'wi-day-thunderstorm',
			'Clear'                    => 'wi-day-sunny',
			'Cloudy'                   => 'wi-day-cloudy',
			'Fog'                      => 'wi-smoke',
			'Haze'                     => 'wi-smog',
			'Mostly Cloudy'            => 'wi-day-cloudy',
			'Mostly Sunny'             => 'wi-day-sunny',
			'Partly Cloudy'            => 'wi-day-cloudy',
			'Partly Sunny'             => 'wi-day-sunny',
			'Freezing Rain'            => 'wi-day-rain-mix',
			'Rain'                     => 'wi-rain',
			'Sleet'                    => 'wi-rain-mix',
			'Snow'                     => 'wi-snow',
			'Sunny'                    => 'wi-day-sunny',
			'Thunderstorms'            => 'wi-thunderstorm',
			'Thunderstorm'             => 'wi-thunderstorm',
			'Unknown'                  => 'wi-sunny',
			'Overcast'                 => 'wi-day-sunny-overcast',
			'Scattered Clouds'         => 'wi-day-cloudy',
		);
		if ( ! empty($icons[$status]) ) {
			return '<span style="font-size: ' . $size . 'px;" class="' . $icons[$status] . '" aria-hidden="true"></span>';
		}
	}
}

class eman_weather_history
{
	public function __construct() {
		add_shortcode( 'weather_history', array($this, 'history') );
	}

	public function history( $atts ) {
		extract( shortcode_atts( array(
			'city'  => 'New_York',
			'state' => 'NY',
			'y'     => '1986',
			'm'     => '11',
			'd'     => '27',
			'icon'  => 72,
			'deg'   => 'F'
		), $atts, 'hw' ) );

		// Cache the api call for 1 week
		$transient_key = "weather/$state/$city/$y/$m/$d";
		if ( false === ( $history = get_transient($transient_key) ) ) {
			$apikey       = eman_get_field('wunderground_api_key', 'options');
			$url          = 'http://api.wunderground.com/api/'.$apikey.'/history_' . $y . $m . $d . '/q/' . $state . '/' . $city . '.json';
			$json_string  = file_get_contents($url);
			$parsed_json  = json_decode($json_string);
			$history      = $parsed_json->history;

			set_transient( $transient_key, $history, WEEK_IN_SECONDS );
		}

		$dailysummary = $history->dailysummary[0];
		$observations = $history->observations;
		$obsarray     = array();
		$hourSearch   = 6;
		$tempext      = ('C' === $deg ? 'm' : 'i');

		if ( $observations ) {
			foreach ( $observations as $observation ) {
				$hour = $observation->date->hour;
				if ( $hour == $hourSearch ) {
					array_push($obsarray, $observation);
					$hourSearch += 6;
				}
			}
		}
?>
		<div class="historicalweather"><div class="wgroup">
			<table style="margin-bottom:0.5rem;width:auto;">
				<tr>
					<th style="padding-right:10px">Temperature:</th>
					<td>Low: <?php echo $dailysummary->{"mintemp$tempext"}; ?>&deg;<?php echo $deg; ?> / High: <?php echo $dailysummary->{"maxtemp$tempext"}; ?>&deg;<?php echo $deg; ?></td>
				</tr>
				<tr>
					<th style="padding-right:10px">Avg. Wind Speed:</th>
					<td><?php echo $dailysummary->meanwindspdi; ?> mph</td>
				</tr>
				<tr>
					<th style="padding-right:10px">Avg. Visibility:</th>
					<td><?php echo $dailysummary->meanvisi; ?> miles</td>
				</tr>
				<tr>
					<th style="padding-right:10px">Precipitation:</th>
					<td>
<?php
						$output  = '';
						$output .= ($dailysummary->rain ? "Rainfall" : '');
						$output .= ($dailysummary->snow ? ($output ? ', ' : '') . "Snowfall" : '');
						$output .= ($dailysummary->fog ? ($output ? ', ' : '') . "Fog" : '');
						echo ( $output ? $output : 'None' );
?>
					</td>
				</tr>
			</table>
<?php
			foreach ( $obsarray as $obs ) :
				$cols = floor( 100 / count($obsarray) );
?>
				<div class="time" style="float:left; text-align:center; width:<?php echo $cols; ?>%">
<?php
					echo str_replace(' on ', '<br>', $obs->date->pretty);
					echo $this->history_icon($obs->conds, 50);
					
?>
					<?php echo $obs->conds; ?><br>
					Temperature: <?php echo $obs->{"temp$tempext"}; ?>&deg;<?php echo $deg; ?>
				</div>
			<?php endforeach; ?>
		</div></div>
<?php
	}

	private function history_icon( $status, $size ) {
		if ( 0 == strncmp($status, 'Light', 5) || 0 == strncmp($status, 'Heavy', 5) ) {
			$status = substr($status, 6);
		}
		$icons = array(
			'Drizzle'                       => 'wi-day-sprinkle',
			'Rain'                          => 'wi-day-rain',
			'Snow'                          => 'wi-day-snow',
			'Snow Grains'                   => 'wi-day-snow',
			'Ice Crystals'                  => 'wi-day-snow',
			'Ice Pellets'                   => 'wi-day-snow',
			'Hail'                          => 'wi-day-hail',
			'Mist'                          => 'wi-day-fog',
			'Fog'                           => 'wi-day-fog',
			'Fog Patches'                   => 'wi-day-fog',
			'Smoke'                         => 'wi-smoke',
			'Volcanic Ash'                  => 'wi-smog',
			'Widespread Dust'               => 'wi-dust',
			'Sand'                          => 'wi-dust',
			'Haze'                          => 'wi-smog',
			'Spray'                         => 'wi-day-sprinkle',
			'Dust Whirls'                   => 'wi-dust',
			'Sandstorm'                     => 'wi-tornado',
			'Low Drifting Snow'             => 'wi-day-snow',
			'Low Drifting Widespread Dust'  => 'wi-dust',
			'Low Drifting Sand'             => 'wi-dust',
			'Blowing Snow'                  => 'wi-day-snow-wind',
			'Blowing Widespread Dust'       => 'wi-dust',
			'Blowing Sand'                  => 'wi-dust',
			'Rain Mist'                     => 'wi-day-sprinkle',
			'Rain Showers'                  => 'wi-day-showers',
			'Snow Showers'                  => 'wi-day-snow',
			'Snow Blowing Snow Mist'        => 'wi-day-snow-wind',
			'Ice Pellet Showers'            => 'wi-day-hail',
			'Hail Showers'                  => 'wi-day-hail',
			'Small Hail Showers'            => 'wi-day-hail',
			'Thunderstorm'                  => 'wi-day-thunderstorm',
			'Thunderstorms and Rain'        => 'wi-day-storm-showers',
			'Thunderstorms and Snow'        => 'wi-day-snow-thunderstorm',
			'Thunderstorms and Ice Pellets' => 'wi-day-snow-thunderstorm',
			'Thunderstorms with Hail'       => 'wi-day-snow-thunderstorm',
			'Thunderstorms with Small Hail' => 'wi-day-snow-thunderstorm',
			'Freezing Drizzle'              => 'wi-day-rain-mix',
			'Freezing Rain'                 => 'wi-day-rain-mix',
			'Freezing Fog'                  => 'wi-day-fog',
			'Patches of Fog'                => 'wi-day-fog',
			'Shallow Fog'                   => 'wi-day-fog',
			'Partial Fog'                   => 'wi-day-fog',
			'Overcast'                      => 'wi-day-sunny-overcast',
			'Clear'                         => 'wi-day-sunny',
			'Partly Cloudy'                 => 'wi-day-cloudy',
			'Mostly Cloudy'                 => 'wi-day-cloudy',
			'Scattered Clouds'              => 'wi-day-cloudy',
			'Small Hail'                    => 'wi-day-snow',
			'Squalls'                       => 'wi-day-cloudy-gusts',
			'Funnel Cloud'                  => 'wi-tornado',
			'Unknown Precipitation'         => 'wi-day-rain',
			'Unknown'                       => 'wi-day-sunny'
		);
		return '<span style="display:block;font-size: ' . $size . 'px;margin:10px auto 5px;" class="wi ' . $icons[$status] . '" aria-hidden="true"></span>';
	}
}

$eman_weather = new eman_weather();

add_action( 'wp_loaded', 'init_eman_weather_history' );
function init_eman_weather_history()
{
	if ( method_exists('eman_weather_history', 'history') ) {
		$eman_weather_history = new eman_weather_history();
	}
}