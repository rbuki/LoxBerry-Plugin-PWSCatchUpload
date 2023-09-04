<?php
require_once "loxberry_system.php";
require_once "loxberry_log.php";
require_once "loxberry_XL.php";
require_once "loxberry_json.php";

$remove_imperial_units = true;
$w4l_transfer_file = '/dev/shm/pwscatchupload_w4l.json';


$log = LBLog::newLog( [ 
	"name" => "Weatherrequest" 
] );
LOGSTART("Request from " . $_SERVER['REMOTE_ADDR']);

$getdata = $_GET;
$getdataImp = $getdata;

// If the incoming request was created by this script, exit (->loop)
if( isset($getdata['lbforwarded']) ) {
	LOGWARN("Incoming request is a loop -> Canceling");
	LOGEND();
	exit;
}

$_GET['lbforwarded'] = 1;

$topic="wstation/";
if( isset($getdata['ID']) ) {
    $ws = $getdata['ID'];
} else {
    genericPage();

}
LOGINF("Weatherstation ID: $ws");
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

LOGDEB("Raw data:");
LOGDEB( print_r( $getdata, true ) );

// Prepare dataset for W4L integration
$w4l = new StdClass();
$w4l->stationid = $ws;
$w4l->cur_date = time();
$w4l->cur_date_iso = currtime('iso');
if(isset($getdata['windchillc'])) 		{ $w4l->cur_w_ch = floatval( $getdata['windchillc'] ); }
if(isset($getdata['baromhpa'])) 		{ $w4l->cur_pr = floatval( $getdata['baromhpa'] ) ; }
if(isset($getdata['dewptc'])) 			{ $w4l->cur_dp = floatval( $getdata['dewptc'] ); }
// if(isset($getdata[''])) 				{ $w4l->cur_tt_fl = $getdata['']; }
if(isset($getdata['humidity'])) 		{ $w4l->cur_hu = floatval( $getdata['humidity'] ); }
// if(isset($getdata[''])) 				{ $w4l->cur_we_code = $getdata['']; }
if(isset($getdata['tempc'])) 			{ $w4l->cur_tt = floatval( $getdata['tempc']) ; }
if(isset($getdata['winddir'])) 			{ $w4l->cur_w_dir = floatval( $getdata['winddir'] ); }
if(isset($getdata['solarradiation'])) 	{ $w4l->cur_sr = floatval( $getdata['solarradiation'] ); }
if(isset($getdata['windspeedkmh'])) 	{ $w4l->cur_w_sp = floatval( $getdata['windspeedkmh'] ); }
if(isset($getdata['windgustkmh'])) 		{ $w4l->cur_w_gu = floatval( $getdata['windgustkmh'] ); }

// Write json for W4L
file_put_contents(
	$w4l_transfer_file, 
	json_encode( $w4l, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
);


// Iterate and send all values
foreach( $getdata as $key => $value ) {
	$mqtt->set($topic.$ws."/"."$key", $value);
}


//detect the uploadserver (which is not yet in use) and upload data if configured to do so
$newQueryString = "";
$wuPushToCloud = true;
$wuPushToCloudUrlTemplate = "https://%s/weatherstation/updateweatherstation.php?%s";
$serversJson = new LBJSON("$lbpconfigdir/wuuploadservers.json");
$wuRequestedServer = explode(":",strtolower($_SERVER['HTTP_HOST']))[0];

$cJson = new LBJSON("$lbpconfigdir/configuration.json");
$wuPushToCloud = $cJson->WuCloudUploadEnabled;
LOGINF("WU Cloud Update is ". (($wuPushToCloud == true) ? "enabled" : "disabled") );

if ($wuPushToCloud == true) {

	foreach ($serversJson->wuUploadServers as $s) {
		if (strtolower($s) != $wuRequestedServer ) { $wuActivatedServer = strtolower($s); }
	}

	if (isset($getdataImp['ID']) && isset($getdataImp['PASSWORD']) && isset($getdataImp['dateutc'])) {
		foreach( $getdataImp as $key => $value ) {
			if ($key == "ID-donotrepalcetomakethisbad") {
				$newQueryString .= $key . "=" . urlencode("FAKESTATION") . "&"; //this is to foce unauthorized
			} else {
				$newQueryString .= $key . "=" . urlencode($value) . "&";
			}
		}
		$newQueryString .= "lbforwarded=1";
	}
	
	$newUrl = sprintf($wuPushToCloudUrlTemplate,$wuActivatedServer,$newQueryString);
	LOGDEB("Cloud update URL and QueryString: $newUrl");

	$curl = curl_init($newUrl);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$resp = curl_exec($curl);
	curl_close($curl);

	if ($resp != "success") {
		LOGDEB("Cloud update Return: $resp");
	} else {
		LOGERR("Cloud update Return: $resp");
		LOGDEB("Cloud update URL and QueryString: $newUrl");
	}
}


LOGEND("Finished");

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

// This is shown if no Station ID was sent (--> Test call by browser?)
function genericPage(){

?>

<div style="text-align:center">
<img src="icon_256.png">
<h1>WU Update Catcher</h1>
<p>You accessed the WU Update Catcher website that grabs Weather Undergroud Update protocol requests.</p>
<p>As the request missed the <b>ID</b> datafield, the Catcher assumed that this is not a Weather Station but it's you within your browser.</b>
<p>Nothing is forwarded to MQTT by this test request. Regular weatherstation requests will be forwarded to your MQTT broker unter the topic <b>wstation/#</b>.</p>
</div>
	
<?php
LOGWARN("WU Update Catcher site requested without station ID - assuming this was a test request from the browser.");
LOGTITLE("Test request from ". $_SERVER['REMOTE_ADDR']);
LOGEND();
exit();
	
}