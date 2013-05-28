<?php

require_once('include/config.inc.php');
require_once('include/js.inc.php');
require_once('include/hosts.inc.php');
require_once('include/items.inc.php');
require_once('yandexapi.conf.php');

$page["title"]    = $ZabbixYaMap['city'];
$page['file']     = basename(__FILE__);
$page['scripts']  = array('yamaps_functions_shared.js');
$page['type']     = detect_page_type(PAGE_TYPE_HTML);
// Detect YandexMaps Language
$page['yaLang'] = YaMapLanguage(CWebUser::$data['lang']);

include_once('include/page_header.php');
include('include/views/js/monitoring.yamaps.js.php');

insert_js("
	document.write('<select id=\"selectgroup\" onChange=\"ChangeGroup();\"></select>');
	var h = jQuery(window).height() - 180;
	document.write('<div id=\"map\" style=\"width:100%; height:' + h + 'px\"></div>');

	ZabbixYaMap.def_lat     = ".$ZabbixYaMap['latitude'].";
	ZabbixYaMap.def_lon     = ".$ZabbixYaMap['longitude'].";
	ZabbixYaMap.def_zoom    = ".$ZabbixYaMap['zoom']."; 
	ZabbixYaMap.MapType     = '".$ZabbixYaMap['maptype']."';
	ZabbixYaMap.PrioProblem = '".$ZabbixYaMap['prioproblem']."';
	ZabbixYaMap.isEditable  = false;
");

?>
<!-- Load YandexMaps JS Classes -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=<?php echo $page['yaLang']; ?>" type="text/javascript"></script>
<?php
insert_js("
    ymaps.ready(function() {
		ZabbixYaMap.init();
		initRO();
    });
");

require_once('include/page_footer.php');
?>



