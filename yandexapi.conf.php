<?php

$ZabbixYaMap = array(
		'city'        =>  'Запорожье',  //Название вашего города
		'latitude'    => 47.781252,     //Координаты по умолчанию
		'longitude'   => 35.187377,     //Масштаб карты по умолчанию
		'zoom'        => 16,
		'prioproblem' => 'true',        //Двигать карту вслед за проблемами по умолчанию
		'maptype'     => 'map',         // Тип карты (publicMap - народная, map - схема)
		
);

/**
 * Sets up the language for Yandex Maps.
 * @url: http://api.yandex.com/maps/doc/jsapi/2.x/quick-start/tasks/quick-start.xml
 */
function YaMapLanguage($lang) {
	switch($lang) {
		case 'en_US': return 'en-US';
		case 'ru_RU': return 'ru-RU';
		case 'tr_TR': return 'tr-TR';
		case 'uk_UA': return 'uk-UA';
		default:      return 'en-US';
	}
}

?>
