<?php
//require_once dirname(__FILE__) . '/include/config.inc.php';
//require_once dirname(__FILE__) . '/include/hosts.inc.php';
//require_once dirname(__FILE__) . '/include/items.inc.php';
//require_once dirname(__FILE__) . '/yandexapi.conf.php';

require_once('include/config.inc.php');
require_once('include/js.inc.php');
require_once('include/hosts.inc.php');
require_once('include/items.inc.php');
require_once('yandexapi.conf.php');

$page["title"] = $MYGOROD;
$page['file'] = 'map_ya_ro.php';
$page['hist_arg'] = array();
$page['scripts'] = array('yamaps_functions.js');


//$page['type'] = detect_page_type();

$page['type'] = detect_page_type(PAGE_TYPE_HTML);

//define('ZBX_PAGE_MAIN_HAT','hat_latest');
//if (PAGE_TYPE_HTML == $page['type']) {
//    define('ZBX_PAGE_DO_REFRESH', 1);
//}

include_once('include/page_header.php');


insert_js("
	document.write('<select id=\"selectgroup\" onChange=\"ChangeGroup();\"></select>');
	var h = jQuery(window).height() - 180;
	document.write('<div id=\"map\" style=\"width:100%; height:' + h + 'px\"></div>');

	var def_lat     = ".$MYLATLON['lat'].";
	var def_lon     = ".$MYLATLON['lon'].";
	var def_zoom    = ".$MYZOOM."; 
	var MapType     = '".$MAPTYPE."';
	var PrioProblem = ".$PRIOPROBLEM.";
");
?>
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
<?php
insert_js("
    ymaps.ready(function() {
	init(def_lat, def_lon, def_zoom, MapType, PrioProblem);
    });
");

require_once('include/page_footer.php');
?>



