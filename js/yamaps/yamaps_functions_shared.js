// Shared Functions for Zabbix YaMaps
var ZabbixYaMap = {
		def_lat     : undefined,
		def_lon     : undefined,
		def_zoom    : undefined,
		MapType     : undefined,
		PrioProblem : undefined,
		isEditable  : false,
		
		Map         : undefined,
		
		/* Functions */
		init : function() {
			/* Initialize the map */
			ZabbixYaMap.Map = new ymaps.Map('map', {
				center : [ ZabbixYaMap.def_lat, ZabbixYaMap.def_lon ],
				zoom : ZabbixYaMap.def_zoom,
				type : 'yandex#' + ZabbixYaMap.MapType,
				behaviors : [ 'default', 'scrollZoom' ]
			});
			
			/* Add default controls */
			ZabbixYaMap.Map.controls
			        .add('zoomControl')
			        .add('typeSelector')
			        .add('mapTools')
					.add(new ymaps.control.ScaleLine())
					.add(new ymaps.control.SearchControl({
								provider : 'yandex#' + ZabbixYaMap.MapType,
								left : '40px',
								top : '10px',
								useMapBounds : true
							}))
					.add(new ymaps.control.MiniMap({
								type : 'yandex#' + ZabbixYaMap.MapType
					}));
		},
		auth : function() {
			var cookie = " " + document.cookie;
			var search = " zbx_sessionid=";
			var setStr = null;
			var offset = 0;
			var end = 0;
			if (cookie.length > 0) {
				offset = cookie.indexOf(search);
				if (offset !== -1) {
					offset += search.length;
					end = cookie.indexOf(";", offset);
					if (end === -1) {
						end = cookie.length;
					}
					setStr = unescape(cookie.substring(offset, end));
				}
			}
			return(setStr);
		}
};

