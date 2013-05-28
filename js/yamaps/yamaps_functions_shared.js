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
		
		SetSelect : function(htmlSelect, selected, allGroupName) {
			var query = '{"jsonrpc":"2.0","method":"hostgroup.getobjects","params":{},"auth":"' + this.auth() + '","id":1}';
			this.apiQuery(query, false, function(data){
				/* Populate the select box */
				opt = new Option(allGroupName, 0);
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
			}, 'Cannot load host groups');
			
		},

		objMerge: function(obj1,obj2){
		    var obj3 = {};
		    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
		    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
		    return obj3;
		},

		apiQuery : function(query, async, callback, errMsg){
			jQuery.ajax({
				url: "api_jsonrpc.php",
				type: "POST",
				contentType: "application/json",
				processData : false,
				async: async,
				dataType: "json",
				data: query,
				success : function(data, textStatus, jqXHR) {
					callback(data);
				},
				error : function( jqXHR, textStatus, errorThrown ) {
					alert(errMsg + "\n\n" + 
							"Code: " + jqXHR.status + "\n" +
							"Status: " + jqXHR.statusText + "\n" +
							"Response: " + jqXHR.responseText);
				}
			});
		}
};

