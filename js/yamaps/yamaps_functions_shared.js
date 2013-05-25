// Shared Functions for Zabbix YaMaps
var ZabbixYaMap = {
		def_lat     : undefined,
		def_lon     : undefined,
		def_zoom    : undefined,
		MapType     : undefined,
		PrioProblem : undefined,
		
		/* Functions */
		
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

