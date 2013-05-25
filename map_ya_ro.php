<?php

require_once('include/config.inc.php');
require_once('include/js.inc.php');
require_once('include/hosts.inc.php');
require_once('include/items.inc.php');
require_once('yandexapi.conf.php');

$page["title"]    = $MYGOROD;
$page['file']     = basename(__FILE__);
$page['hist_arg'] = array();
$page['scripts']  = array('yamaps_functions_shared.js', 'yamaps_functions_ro.js');
$page['type']     = detect_page_type(PAGE_TYPE_HTML);

include_once('include/page_header.php');

insert_js("
	document.write('<select id=\"selectgroup\" onChange=\"ChangeGroup();\"></select>');
	var h = jQuery(window).height() - 180;
	document.write('<div id=\"map\" style=\"width:100%; height:' + h + 'px\"></div>');

	ZabbixYaMap.def_lat     = ".$MYLATLON['lat'].";
	ZabbixYaMap.def_lon     = ".$MYLATLON['lon'].";
	ZabbixYaMap.def_zoom    = ".$MYZOOM."; 
	ZabbixYaMap.MapType     = '".$MAPTYPE."';
	ZabbixYaMap.PrioProblem = ".$PRIOPROBLEM.";
");
?>
<!-- Load YandexMaps JS Classes -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
<?php
insert_js("
    ymaps.ready(function() {
		init(def_lat, def_lon, def_zoom, MapType, PrioProblem);
    });
");

require_once('include/page_footer.php');
?>



