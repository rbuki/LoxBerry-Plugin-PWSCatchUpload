<?php
require_once "loxberry_system.php";
require_once "loxberry_XL.php";

file_put_contents( "/tmp/wunderground_data.log", print_r( $_GET ) );

$remove_imperial_units = true;


$topic="wstation/";
if( isset($_GET['ID']) ) {
    $ws = $_GET['ID'];
} else {
    $ws = "Generic";
}

// PWS Upload protocol documentation
// https://support.weather.com/s/article/PWS-Upload-Protocol?language=en_US

// Fahrenheit to Celsius
if( isset( $_GET['tempf'] ) ) {
	$_GET['tempc'] = to_celsius( $_GET['tempf'] );
}
if( isset( $_GET['dewptf'] ) ) {
	$_GET['dewptc'] = to_celsius( $_GET['dewptf'] );
}
if( isset( $_GET['windchillf'] ) ) {
	$_GET['windchillc'] = to_celsius( $_GET['windchillf'] );
}
if( isset( $_GET['indoortempf'] ) ) {
	$_GET['indoortempc'] = to_celsius( $_GET['indoortempf'] );
}
if( isset( $_GET['soiltempf'] ) ) {
	$_GET['soiltempc'] = to_celsius( $_GET['soiltempf'] );
}
if( isset( $_GET['soiltemp2f'] ) ) {
	$_GET['soiltemp2c'] = to_celsius( $_GET['soiltemp2f'] );
}
if( isset( $_GET['soiltemp3f'] ) ) {
	$_GET['soiltemp3c'] = to_celsius( $_GET['soiltemp3f'] );
}
if( isset( $_GET['soiltemp4f'] ) ) {
	$_GET['soiltemp4c'] = to_celsius( $_GET['soiltemp4f'] );
}

// Miles to km/h
if( isset( $_GET['windspeedmph'] ) ) {
	$_GET['windspeedkmh'] = to_kmh( $_GET['windspeedmph'] );
}
if( isset( $_GET['windgustmph'] ) ) {
	$_GET['windgustkmh'] = to_kmh( $_GET['windgustmph'] );
}
if( isset( $_GET['windspdmph_avg2m'] ) ) {
	$_GET['windspdkmh_avg2m'] = to_kmh( $_GET['windspdmph_avg2m'] );
}
if( isset( $_GET['windgustmph_10m'] ) ) {
	$_GET['windgustkmh_10m'] = to_kmh( $_GET['windgustmph_10m'] );
}


// Inch to mm
if( isset( $_GET['baromin'] ) ) {
	$_GET['barommm'] = to_mm( $_GET['baromin'] );
	$_GET['baromhpa'] = round( $_GET['barommm'] * 1.333224, 2 );
}
if( isset( $_GET['rainin'] ) ) {
	$_GET['rainmm'] = to_mm( $_GET['rainin'] );
}
if( isset( $_GET['dailyrainin'] ) ) {
	$_GET['dailyrainmm'] = to_mm( $_GET['dailyrainin'] );
}
if( isset( $_GET['weeklyrainin'] ) ) {
	$_GET['weeklyrainmm'] = to_mm( $_GET['weeklyrainin'] );
}
if( isset( $_GET['monthlyrainin'] ) ) {
	$_GET['monthlyrainmm'] = to_mm( $_GET['monthlyrainin'] );
}
if( isset( $_GET['yearlyrainin'] ) ) {
	$_GET['yearlyrainmm'] = to_mm( $_GET['yearlyrainin'] );
}


if( $remove_imperial_units) {
	
	unset($_GET['tempf']);
	unset($_GET['dewptf']);
	unset($_GET['windchillf']);
	unset($_GET['indoortempf']);
	unset($_GET['soiltempf']);
	unset($_GET['soiltemp2f']);
	unset($_GET['soiltemp3f']);
	unset($_GET['soiltemp4f']);
	
	unset($_GET['windspeedmph']);
	unset($_GET['windgustmph']);
	unset($_GET['windspdmph_avg2m']);
	unset($_GET['windgustmph_10m']);

	unset($_GET['baromin']);
	unset($_GET['rainin']);
	unset($_GET['dailyrainin']);
	unset($_GET['weeklyrainin']);
	unset($_GET['monthlyrainin']);
	unset($_GET['yearlyrainin']);
	
}

// Set update timestamp
$_GET['lastUpdateEpoch'] = time();
$_GET['lastUpdateHr'] = currtime('hr');

// Iterate and send all values
foreach( $_GET as $key => $value ) {
	$mqtt->set($topic.$ws."/"."$key", $value);
}



// Functions for unit conversions

function to_celsius( $fahrenheit ) {
	return round( ( $fahrenheit-32 ) / 1.8, 2 ); 	
}

function to_kmh( $mph ) {
	return round( $mph * 1.609344, 2 );
}

function to_mm( $inch ) {
	return round( $inch * 25.4 , 2 );	
}