<?php
require_once "loxberry_system.php";
require_once "loxberry_web.php";

$plugin = LBSystem::plugindata();

$template_title = $plugin['PLUGINDB_TITLE'] . "V" . $plugin['PLUGINDB_VERSION'];
$helplink = "https://www.loxwiki.eu:80";
$helptemplate = "help.html";

LBWeb::lbheader($template_title, $helplink, $helptemplate);

?>

<h1>This plugin has no settings.</h1>
<p>Every request of a weatherstation is logged (see below).</p>

<?php

echo LBWeb::loglist_html( [] );

LBWeb::lbfooter();
