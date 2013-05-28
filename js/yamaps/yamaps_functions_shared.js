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
		},
		SetSelect : function(htmlSelect, selected) {
			jQuery.ajax({
				url: "api_jsonrpc.php",
				type: "POST",
				contentType: "application/json",
				processData : false,
				async: false,
				dataType: "json",
				data: '{"jsonrpc":"2.0","method":"hostgroup.getobjects","params":{},"auth":"' + ZabbixYaMap.auth() + '","id":1}',
				success : function(data, textStatus, jqXHR) {
						/* Populate the select box */
						opt = new Option("Все", 0);
		                opt.selected = "selected";
		                htmlSelect.options.add(opt, 0);
		                for (i = 0; i < data.result.length; i++) {
		                	opt = new Option(data.result[i].name, data.result[i].groupid);
		                    if (data.result[i].name === selected) {
		                    	opt.selected = "selected";
		                    }
		                    htmlSelect.options.add(opt, i + 1);
		                }
		                return true;                
				},
				error : function( jqXHR, textStatus, errorThrown ) {
					alert("Cannot load host groups\n\n" + 
							"Code: " + jqXHR.status + "\n" +
							"Status: " + jqXHR.statusText + "\n" +
							"Response: " + jqXHR.responseText);
				}
			});
		},
		displayHosts : function(groupid, callback){
			//console.info(arguments);
			if (groupid == 0) {
				var query = '{"jsonrpc":"2.0","method":"host.get","params":{"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + ZabbixYaMap.auth() + '","id":1}';
			} else {
				var query = '{"jsonrpc":"2.0","method":"host.get","params":{"groupids":' + groupid + ',"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + ZabbixYaMap.auth() + '","id":1}';
			}
			//console.info(query);
			jQuery.ajax({
				url: "api_jsonrpc.php",
				type: "POST",
				contentType: "application/json",
				processData : false,
				async: true,
				dataType: "json",
				data: query,
				success : function(data, textStatus, jqXHR) {
					callback(data);
				},
				error : function( jqXHR, textStatus, errorThrown ) {
					alert("Cannot load hosts\n\n" + 
							"Code: " + jqXHR.status + "\n" +
							"Status: " + jqXHR.statusText + "\n" +
							"Response: " + jqXHR.responseText);
				}
			});
		}
};

