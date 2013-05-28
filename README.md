Yandex Maps Plugin for Zabbix
==============

Code is based on the code of oscar, original sources was downloaded from here:
https://www.zabbix.com/forum/showthread.php?t=37480

-  Put the files to the root folder of Zabbix PHP Frontend
-  Edit include/menu.inc.php:
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

-  Add to jsLoader.php after:
		
		// templates
		'sysmap.tpl.js' => 'templates/',
		
	Add this (probably you need to add the coma above too):
		
		// YaMaps
		'yamaps_functions_shared.js' => 'yamaps/',

- Probably you need to regenerate your Zabbix's locale (i18n) files, if you want to see the text on the buttons on your own language. In order to do so, follow Zabbix instructuions of how to update the locale: https://www.zabbix.com/documentation/2.0/manual/web_interface/translations
In short, you should do inside the folder with your Zabbix locales
```bash
./update_po.sh
./make_mo.s
```
You can try to use my locale/ru/LC_MESSAGES/frontend.ru_RU.diff.po with my Russian trunslate :)
