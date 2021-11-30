<?php
require_once "loxberry_system.php";
require_once "loxberry_XL.php";

$remove_imperial_units = true;

$getdata = $_GET;

// If the incoming request was created by this script, exit (->loop)
if( isset($getdata['lbforwarded']) ) {
	error_log("updateweatherstation: Incoming request is a loop -> Canceling");
	exit;
}

file_put_contents( "/tmp/wunderground_data.log", print_r( $_GET ) );

$_GET['lbforwarded'] = 1;

$topic="wstation/";
if( isset($getdata['ID']) ) {
    $ws = $getdata['ID'];
} else {
    $ws = "Generic";
}

// PWS Upload protocol documentation
// https://support.weather.com/s/article/PWS-Upload-Protocol?language=en_US

// Fahrenheit to Celsius
if( isset( $getdata['tempf'] ) ) {
	$getdata['tempc'] = to_celsius( $getdata['tempf'] );
}
if( isset( $getdata['dewptf'] ) ) {
	$getdata['dewptc'] = to_celsius( $getdata['dewptf'] );
}
if( isset( $getdata['windchillf'] ) ) {
	$getdata['windchillc'] = to_celsius( $getdata['windchillf'] );
}
if( isset( $getdata['indoortempf'] ) ) {
	$getdata['indoortempc'] = to_celsius( $getdata['indoortempf'] );
}
if( isset( $getdata['soiltempf'] ) ) {
	$getdata['soiltempc'] = to_celsius( $getdata['soiltempf'] );
}
if( isset( $getdata['soiltemp2f'] ) ) {
	$getdata['soiltemp2c'] = to_celsius( $getdata['soiltemp2f'] );
}
if( isset( $getdata['soiltemp3f'] ) ) {
	$getdata['soiltemp3c'] = to_celsius( $getdata['soiltemp3f'] );
}
if( isset( $getdata['soiltemp4f'] ) ) {
	$getdata['soiltemp4c'] = to_celsius( $getdata['soiltemp4f'] );
}

// Miles to km/h
if( isset( $getdata['windspeedmph'] ) ) {
	$getdata['windspeedkmh'] = to_kmh( $getdata['windspeedmph'] );
}
if( isset( $getdata['windgustmph'] ) ) {
	$getdata['windgustkmh'] = to_kmh( $getdata['windgustmph'] );
}
if( isset( $getdata['windspdmph_avg2m'] ) ) {
	$getdata['windspdkmh_avg2m'] = to_kmh( $getdata['windspdmph_avg2m'] );
}
if( isset( $getdata['windgustmph_10m'] ) ) {
	$getdata['windgustkmh_10m'] = to_kmh( $getdata['windgustmph_10m'] );
}


// Inch to mm
if( isset( $getdata['baromin'] ) ) {
	$getdata['barommm'] = to_mm( $getdata['baromin'] );
	$getdata['baromhpa'] = round( $getdata['barommm'] * 1.333224, 2 );
}
if( isset( $getdata['rainin'] ) ) {
	$getdata['rainmm'] = to_mm( $getdata['rainin'] );
}
if( isset( $getdata['dailyrainin'] ) ) {
	$getdata['dailyrainmm'] = to_mm( $getdata['dailyrainin'] );
}
if( isset( $getdata['weeklyrainin'] ) ) {
	$getdata['weeklyrainmm'] = to_mm( $getdata['weeklyrainin'] );
}
if( isset( $getdata['monthlyrainin'] ) ) {
	$getdata['monthlyrainmm'] = to_mm( $getdata['monthlyrainin'] );
}
if( isset( $getdata['yearlyrainin'] ) ) {
	$getdata['yearlyrainmm'] = to_mm( $getdata['yearlyrainin'] );
}


if( $remove_imperial_units) {
	
	unset($getdata['tempf']);
	unset($getdata['dewptf']);
	unset($getdata['windchillf']);
	unset($getdata['indoortempf']);
	unset($getdata['soiltempf']);
	unset($getdata['soiltemp2f']);
	unset($getdata['soiltemp3f']);
	unset($getdata['soiltemp4f']);
	
	unset($getdata['windspeedmph']);
	unset($getdata['windgustmph']);
	unset($getdata['windspdmph_avg2m']);
	unset($getdata['windgustmph_10m']);

	unset($getdata['baromin']);
	unset($getdata['rainin']);
	unset($getdata['dailyrainin']);
	unset($getdata['weeklyrainin']);
	unset($getdata['monthlyrainin']);
	unset($getdata['yearlyrainin']);
	
}

// Set update timestamp
$getdata['lastUpdateEpoch'] = time();
$getdata['lastUpdateHr'] = currtime('hr');

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