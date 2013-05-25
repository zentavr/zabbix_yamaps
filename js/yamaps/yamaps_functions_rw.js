function initRW() {
	Hosts = [];
	ChangeHost = [];
	HostArray = new ymaps.Clusterer({
		maxZoom : 9
	});
	
	SetSelect(document.getElementById("selectgroup"), "Все");
	
	SaveButton = new ymaps.control.Button({
		data : {
			content : 'Сохранить',
			title : 'Нажмите для сохранения'
		}
	}, {
		position : {
			top : 5,
			right : 200
		},
		selectOnClick : false
	});
	SaveButton.disable();
	ZabbixYaMap.Map.controls.add(SaveButton);
	saved = false;
	ChangeGroup();
}

function save_change() {
	for (var i = 0; i < ChangeHost.length; i++) {
    	var jsonReq;
        if (window.XMLHttpRequest) {
        	jsonReq = new XMLHttpRequest();
            jsonReq.overrideMimeType('text/xml');
        } else if (window.ActiveXObject) {
        	jsonReq = new ActiveXObject("Microsoft.XMLHTTP");
        }

        jsonReq.overrideMimeType('application/json');
        var url = "api_jsonrpc.php";
        jsonReq.open('POST', url, true);
        jsonReq.setRequestHeader("Content-Type", "application/json");
        var query = '{"jsonrpc":"2.0","method":"host.update","params":{"hostid":"' + ChangeHost[i].hid + '","inventory":{"location_lat":"' + ChangeHost[i].point[0].toFixed(12) + '","location_lon":"' + ChangeHost[i].point[1].toFixed(12) + '"}},"auth":"' + ZabbixYaMap.auth() + '","id":' + i + '}';
        jsonReq.send(query);
     }

	ChangeHost.length = 0;

	SaveButton.disable();
    saved = false;
    SaveButton.events
    	.remove('click', function() {
    		save_change();
         });
}

function draghost(id, newpoint) {
	ChangeHost.push(new Object({
		hid: id,
		point: newpoint
	}));
	
	if (saved == false) {
		saved = true;
        SaveButton.enable();
        SaveButton.events
        		.add('click', function() {
                	save_change();
                });
    }
}

/* Fetch the Host Groups */
function SetSelect(htmlSelect, selected) {
	$.ajax({
		url: "api_jsonrpc.php",
		type: "POST",
		contentType: "application/json",
		processData : false,
		async: false,
		data: '{"jsonrpc":"2.0","method":"hostgroup.getobjects","params":{},"auth":"' + ZabbixYaMap.auth() + '","id":1}',
		success : function(data, textStatus, jqXHR) {
				console.log(arguments);
				/*
				var out = JSON.parse(jsonReq.responseText);
                opt = new Option("Все", 0);
                opt.selected = "selected";
                htmlSelect.options.add(opt, 0);
                for (i = 0; i < out.result.length; i++) {
                	opt = new Option(out.result[i].name, out.result[i].groupid);
                    if (out.result[i].name === selected) {
                    	opt.selected = "selected";
                    }
                    htmlSelect.options.add(opt, i + 1);
                }
                return true;
                */
		},
		error : function( jqXHR, textStatus, errorThrown ) {
			alert('Cannot load host groups: ' + textStatus);
		}
	});

}

function ChangeGroup() {
	var sel = document.getElementById("selectgroup");
	var groupid = sel.options[sel.selectedIndex].value;
	HostArray.removeAll();
	ZabbixYaMap.Map.geoObjects.remove(HostArray);
	var jsonReq;
	if (window.XMLHttpRequest) {
		jsonReq = new XMLHttpRequest();
		jsonReq.overrideMimeType('text/xml');
	} else if (window.ActiveXObject) {
		jsonReq = new ActiveXObject("Microsoft.XMLHTTP");
	}
	jsonReq.overrideMimeType('application/json');
	var url = "api_jsonrpc.php";
	jsonReq.open('POST', url, true);
	jsonReq.setRequestHeader("Content-Type", "application/json");
	if (groupid == 0) {
		var query = '{"jsonrpc":"2.0","method":"host.get","params":{"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + ZabbixYaMap.auth() + '","id":1}';
	} else {
		var query = '{"jsonrpc":"2.0","method":"host.get","params":{"groupids":' + groupid + ',"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + ZabbixYaMap.auth() + '","id":1}';
	}
	jsonReq.send(query);
	
	jsonReq.onreadystatechange = function alertContents() {
		if (jsonReq.readyState === 4) {
				if (jsonReq.status === 200) {
					var out = JSON.parse(jsonReq.responseText);
					var x_max = 0;
					var y_max = 0;
					var x_min = 180;
					var y_min = 180;
					for ( var i = 0; i < out.result.length; i++) {
						if (out.result[i].inventory.location_lat == 0
								|| out.result[i].inventory.location_lon == 0) {
							x = def_lat;
							y = def_lon;
						} else {
							x = out.result[i].inventory.location_lat;
							y = out.result[i].inventory.location_lon;
						}
						if (x > x_max)
							x_max = x;
						if (x < x_min)
							x_min = x;
						if (y > y_max)
							y_max = y;
						if (y < y_min)
							y_min = y;
						Hosts[i] = new ymaps.Placemark([ x, y ], {
							hintContent : out.result[i].name,
							hostid : out.result[i].hostid
						}, {
							draggable : true
						});
						(function(i) {
							Hosts[i].events.add('dragend', function() {
								draghost(Hosts[i].properties.get('hostid'),
										Hosts[i].geometry.getCoordinates());
							});
						})(i);
						HostArray.add(Hosts[i]);
	
					}
					ZabbixYaMap.Map.geoObjects.add(HostArray);
					ZabbixYaMap.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
						duration : 1000,
						checkZoomRange : true
					});
					return true;
				}
				return (100);
			}
			return (99);
	
		};
}