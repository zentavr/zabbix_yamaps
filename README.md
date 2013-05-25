Yandex Maps Plugin for Zabbix
==============

Code is based on the code of oscar, original sources was downloaded from here:
https://www.zabbix.com/forum/showthread.php?t=37480

1. Put the files to the root folder of Zabbix PHP Frontend
2. Edit include/menu.inc.php:
After:
	array(
		'url' => 'maps.php',
		'label' => _('Maps'),
		'sub_pages' => array('map.php')
	),

Add:
	array(
		'url' => 'map_ya_ro.php',
		'label' => _('Yandex-RO'),
		'sub_pages' => array('map_ya_ro.php')
	),

After: 
	array(
		'url' => 'sysmaps.php',
		'label' => _('Maps'),
		'sub_pages' => array('image.php', 'sysmap.php')
	),
	
Add:
	array(
		'url' => 'map_ya_rw.php',
		'label' => _('Yandex-RW'),
		'sub_pages' => array('map_ya_rw.php')
	),

3. Add to jsLoader.php after:
	// templates
	'sysmap.tpl.js' => 'templates/',
Add this (probably you need to add the coma above too):
	// YaMaps
	'yamaps_functions.js' => 'yamaps',
	'yamaps_functions_rw.js' => 'yamaps/'
