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
	document.write('<select id=\"selectgroup\"></select>');
	var h = jQuery(window).height() - 180;
	document.write('<div id=\"map\" style=\"width:100%; height:' + h + 'px\"></div>');
");

?>
<!-- Load YandexMaps JS Classes -->
<script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=<?php echo $page['yaLang']; ?>" type="text/javascript"></script>
<?php
insert_js("
    ymaps.ready(function() {
		//console.log('YandexMaps is starting');
		YaMap = new ZabbixYaMapRO(".$ZabbixYaMap['latitude'].",
			                ".$ZabbixYaMap['longitude'].",
			                ".$ZabbixYaMap['zoom'].",
			                '".$ZabbixYaMap['maptype']."',
			                '".$ZabbixYaMap['prioproblem']."');
		//console.log(YaMap);
		YaMap.init();
    });
");

require_once('include/page_footer.php');
?>



