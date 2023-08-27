<?php
require_once "loxberry_system.php";
require_once "loxberry_web.php";
require_once "loxberry_json.php";

$plugin = LBSystem::plugindata();

$template_title = $plugin['PLUGINDB_TITLE'] . "V" . $plugin['PLUGINDB_VERSION'];
$helplink = "https://www.loxwiki.eu:80";
$helptemplate = "help.html";

LBWeb::lbheader($template_title, $helplink, $helptemplate);

$cJson = new LBJSON("$lbpconfigdir/configuration.json");

if (isset($_POST['updater'])) {
    if ($_POST['updater'] == 0) {
        //$cJson->WuCloudUploadEnabled = false;
        file_put_contents(          $cJson->filename(), "{ \"WuCloudUploadEnabled\": false }" );
        $cJson = new LBJSON("$lbpconfigdir/configuration.json");
    } else {
        //$cJson->WuCloudUploadEnabled = true;
        file_put_contents(          $cJson->filename(), "{ \"WuCloudUploadEnabled\": true }" );
        $cJson = new LBJSON("$lbpconfigdir/configuration.json");
    }
    //$cJson->write(); //is not working, LB 3.0.6 with LBJSON Object ( [VERSION] => 2.0.2.2      -- I meant this was fixed with LB 2.2 and later   :-(
}

?>

<h1>Configuration</h1>

<form method=POST >
    <label><input disabled="" type="checkbox" name="enableMQTT" id="enableMQTT" checked>Publish with MQTT</label>
    <label><input type="checkbox" name="enableWu" id="enableWu" <?php if ($cJson->WuCloudUploadEnabled == true) {echo "checked onclick=\"document.getElementById('updater').value = 0;submit();\"";} else {echo "onclick=\"document.getElementById('updater').value = 1;submit();\"";} ; ?> >Enable data upload to wunderground cloud servers</label>
    <input type=hidden name=updater id=updater value="<?php echo (($cJson->WuCloudUploadEnabled == true) ? 1: 0) ; ?>">
</form>

<div class="ui-body ui-body-a ui-corner-all">
    <h4>DNS check</h4>
    At least one of the following target servers needs to resolve to a public IPv4 address.<br>
    You need to have at least one <font color=green>green</font> line to get this working:<br><br>
    <?php
    $serversJson = new LBJSON("$lbpconfigdir/wuuploadserers.json");
    $generalJson = new LBJSON("$lbsconfigdir/general.json");
    $countOfExternal = 0;
    foreach ($serversJson->wuUploadServers as $s) {
        $thisStateStringTemplate = "";
        if (resolvesToPublicIPv4($s,$serversJson->dnsResolvedHostMustContain,$generalJson->Network->Ipv4->Dns)) { $thisStateStringTemplate = "<font color=green>%s</font>" ; $countOfExternal += 1;} else { $thisStateStringTemplate = "<font color=lightgray>%s</font>"; }
        echo "&nbsp;&nbsp; - " . sprintf($thisStateStringTemplate,$s) ."<br>";
    }

    if ($countOfExternal > 0) {
        echo "<br>I looks you have " . $countOfExternal . " internet resolvable servers left.<br><font color=green><b>Data upload to cloud servers should be possible.</b></font>";
    } else {
        echo "<br><font color=darkorange>It looks like your DNS configuration cannot resolve any of the names above to a public internet address.<br>Data upload to cloud servers is probably not possible.</b></font>" ;
    }

    ?>
</div>

<div class="ui-body ui-body-a ui-corner-all">
    <h4>Logs</h4>
    Every request of a weatherstation is logged (press the button ;-) ).<br>Logfiles are deleted automatically on a regular basis.<br>
    <?php echo LBWeb::loglist_button_html ( [] ); ?>
</div>
<?php

function resolvesToPublicIPv4($thisFqdn,$mustContain,$nsIPv4) {
    $pDnsA = shell_exec("host -t A -W 1 $thisFqdn. $nsIPv4 | grep -c $mustContain'.'");
    return ($pDnsA > 0) ? true : false;
}

LBWeb::lbfooter();
?>