<?php

// This is a PLUGIN TEMPLATE for Textpattern CMS.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'msd_piwik_dashboard';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.2';
$plugin['author'] = 'Matt Davis';
$plugin['author_uri'] = 'http://mattsdavis.com';
$plugin['description'] = 'Replace Textpattern\'s Visitor Log with your Piwik Dashboard.';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public              : only on the public side of the website (default)
// 1 = public+admin        : on both the public and admin side
// 2 = library             : only when include_plugin() or require_plugin() is called
// 3 = admin               : only on the admin side (no AJAX)
// 4 = admin+ajax          : only on the admin side (AJAX supported)
// 5 = public+admin+ajax   : on both the public and admin side (AJAX supported)
$plugin['type'] = '3';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '3';

// Plugin 'textpack' is optional. It provides i18n strings to be used in conjunction with gTxt().
// Syntax:
// ## arbitrary comment
// #@event
// #@language ISO-LANGUAGE-CODE
// abc_string_name => Localized String

/** Uncomment me, if you need a textpack
$plugin['textpack'] = <<< EOT
#@admin
#@language en-gb
abc_sample_string => Sample String
abc_one_more => One more
#@language de-de
abc_sample_string => Beispieltext
abc_one_more => Noch einer
EOT;
**/
// End of textpack

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
##################
#
#	msd_piwik_dashboard (BETA) for Textpattern
#	version 0.2
#	by Matt Davis
#	http://photographdaddy.com
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    A copy of the GNU General Public License is available at http://www.gnu.org/licenses/.
#
# v0.2 - Added ability to display alternate widgets with auth token.
#
#
###################
register_callback("msd_piwik_dashboard", "log");
	add_privs('msd_piwik_options', 1);//Exten tab
	add_privs('plugin_prefs.msd_piwik_dashboard', 1);

	register_tab("extensions", "msd_piwik_options", "Piwik Dashboard");//Exten Tab
	register_callback("msd_piwik_options", "msd_piwik_options");//Exten Tab
	register_callback('msd_piwik_options', 'plugin_prefs.msd_piwik_dashboard');
	register_callback('msd_piwik_install', 'plugin_lifecycle.msd_piwik_dashboard', 'installed');
	register_callback('msd_piwik_uninstall', 'plugin_lifecycle.msd_piwik_dashboard', 'deleted');
	register_callback('msd_piwik_install', 'plugin_lifecycle.msd_piwik_dashboard', 'enabled');


function msd_piwik_dashboard($event, $step) {
global $msd_piwik_site, $msd_piwik_url, $msd_piwik_widget, $msd_piwik_token;;
echo <<<JS
<script type="text/javascript">
var winW = 650, winH = 460;
if (document.body && document.body.offsetWidth) {
 winW = document.body.offsetWidth;
 winH = document.body.offsetHeight;
}
if (document.compatMode=='CSS1Compat' &&
    document.documentElement &&
    document.documentElement.offsetWidth ) {
 winW = document.documentElement.offsetWidth;
 winH = document.documentElement.offsetHeight;
}
if (window.innerWidth && window.innerHeight) {
 winW = window.innerWidth;
 winH = window.innerHeight;
}
$(document).ready(function() {
$("#log_container").hide();
$("#log_control").after('<div id="widgetIframe"><iframe width="100%" height="'+winH+'" src="$msd_piwik_url/index.php?module=Widgetize&action=iframe&moduleToWidgetize=$msd_piwik_widget&actionToWidgetize=index&idSite=$msd_piwik_site&period=week&date=yesterday&token_auth=$msd_piwik_token" scrolling="yes" frameborder="0" marginheight="0" marginwidth="0"></iframe></div>');
$("#widgetIframe").after('<a href="#msd_log_piwik" class="navlink-active" id="msd_log_piwik">Piwik</a><a href="#msd_log_txp" class="navlink" id="msd_log_txp">Textpattern</a>');
 $("#msd_log_txp").click(function() {
   $("#msd_log_txp").addClass("navlink-active").removeClass("navlink");
   $("#msd_log_piwik").removeClass("navlink-active").addClass("navlink");
   $("#log_container").show();
   $("#widgetIframe").hide();
  });
 $("#msd_log_piwik").click(function() {
   $("#msd_log_piwik").addClass("navlink-active").removeClass("navlink");
   $("#msd_log_txp").removeClass("navlink-active").addClass("navlink");
   $("#log_container").hide();
   $("#widgetIframe").show();
  });
});
</script>
JS;
}


function msd_piwik_options($event, $step){
global $msd_piwik_widget, $msd_piwik_token, $msd_piwik_site, $msd_piwik_url;
	include_once txpath . '/include/txp_prefs.php';

  if (ps("save")) {
	prefs_save();
	header("Location: index.php?event=msd_piwik_options");
  }
  pagetop("Piwik Dashboard Preferences");
  echo form(startTable("list").
  tr(tdcs(hed("Piwik Dashboard Preferences",1),2)).
  tr(tda("Piwik Site Number", ' style="text-align:right;vertical-align:middle"').td(text_input("msd_piwik_site",$msd_piwik_site,'20'))).
  tr(tda("Piwik Site URL", ' style="text-align:right;vertical-align:middle"').td(text_input("msd_piwik_url",$msd_piwik_url,'20'))).
  tr(tda("Advanced Settings", ' style="text-align:center;vertical-align:middle"').td('')).
  tr(tda("Module", ' style="text-align:right;vertical-align:middle"').td(text_input("msd_piwik_widget",$msd_piwik_widget,'20'))).
  tr(tda("Auth Token", ' style="text-align:right;vertical-align:middle"').td(text_input("msd_piwik_token",$msd_piwik_token,'20'))).
  tr(tda(fInput("submit","save",gTxt("save_button"),"publish").eInput("msd_piwik_options").sInput('saveprefs'), " colspan='2' class='noline'")).
  endTable());
}
#
#Install Preferences
#
function msd_piwik_install(){
global $prefs;
if (!isset($prefs['msd_piwik_site'])){
	safe_insert('txp_prefs', "
		name = 'msd_piwik_site',
		val = '1',
		prefs_id = 1
		");
}
if (!isset($prefs['msd_piwik_url'])){
	safe_insert('txp_prefs', "
		name = 'msd_piwik_url',
		val = '',
		prefs_id = 1
		");
}
if (!isset($prefs['msd_piwik_token'])){
	safe_insert('txp_prefs', "
		name = 'msd_piwik_token',
		val = '',
		prefs_id = 1
		");
}
if (!isset($prefs['msd_piwik_widget'])){
	safe_insert('txp_prefs', "
		name = 'msd_piwik_widget',
		val = 'Dashboard',
		prefs_id = 1
		");
}
}
#
#Uninstall Preferences
#
function msd_piwik_uninstall(){
	global $prefs;
	if (isset($prefs['msd_piwik_site'])){
		safe_delete('txp_prefs', "
			name = 'msd_piwik_site'");
	}
	if (isset($prefs['msd_piwik_url'])){
		safe_delete('txp_prefs', "
			name = 'msd_piwik_url'");
	}
	if (isset($prefs['msd_piwik_widget'])){
		safe_delete('txp_prefs', "
			name = 'msd_piwik_widget'");
	}
	if (isset($prefs['msd_piwik_token'])){
		safe_delete('txp_prefs', "
			name = 'msd_piwik_token'");
	}
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---
<h1 class="title">msd_piwik_dashboard</h1>

	<h2 class="section">Features</h2>

	<p>Replaces the standard Textpattern Visitor Logs with your <a href="http://piwik.org/">Piwik</a> Dashboard</p>

	<h2 class="section">Setup</h2>

	<p>Set the <a href="/textpattern/index.php?event=plugin_prefs.msd_piwik_dashboard">plugin preferences</a> to configure your piwik site id and the url to your piwik installation.</p>

	<p>Currently the plugin requires you either be logged into piwik or allow anonymous view of your piwik data. Information on configuring piwik can be found on the <a href="http://piwik.org/docs/manage-users/">piwik documentation site</a>.</p>

	<h2 class="section">Advanced</h2>

	<p>If you do not want to allow anonymous users to view Piwik data you may set the Auth Token to the value found under <a href="http://demo.piwik.org/index.php?module=API&amp;action=listAllAPI&amp;idSite=7&amp;period=day&amp;date=yesterday"><span class="caps">API</span></a>. However, this does not work for the Dashboard which is the default widget displayed. You must use a <a href="http://demo.piwik.org/index.php?module=Widgetize&amp;action=index&amp;idSite=7&amp;period=day&amp;date=yesterday">different module</a> if you want authentication.</p>
# --- END PLUGIN HELP ---
-->
<?php
}
?>