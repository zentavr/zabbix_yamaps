<script type="text/javascript">
function initRW() {
	Hosts = [];
	ChangeHost = [];
	HostArray = new ymaps.Clusterer({
		maxZoom : 9
	});
	
	ZabbixYaMap.SetSelect(document.getElementById("selectgroup"), "<?php echo _('All'); ?>", "<?php echo _('All'); ?>");
	
	SaveButton = new ymaps.control.Button({
		data : {
			content : '<?php echo _('Save'); ?>',
			title : '<?php echo _('Press to save the positions'); ?>'
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
		
		var query = '{"jsonrpc":"2.0","method":"host.update","params":{"hostid":"' + ChangeHost[i].hid + '","inventory":{"location_lat":"' + ChangeHost[i].point[0].toFixed(12) + '","location_lon":"' + ChangeHost[i].point[1].toFixed(12) + '"}},"auth":"' + ZabbixYaMap.auth() + '","id":' + i + '}';
		jQuery.ajax({
			url: "api_jsonrpc.php",
			type: "POST",
			contentType: "application/json",
			processData : false,
			async: false,
			dataType: "json",
			data: query
		});
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

function ChangeGroup() {
	var sel = document.getElementById("selectgroup");
	var groupid = sel.options[sel.selectedIndex].value;
	HostArray.removeAll();
	ZabbixYaMap.Map.geoObjects.remove(HostArray);

	ZabbixYaMap.displayHosts(groupid, function(out) {
		var x_max = 0;
		var y_max = 0;
		var x_min = 180;
		var y_min = 180;
		for ( var i = 0; i < out.result.length; i++) {
			/* If there is no Lattitude and Longtitude came from Zabbix */
			if (out.result[i].inventory.location_lat == 0 || out.result[i].inventory.location_lon == 0) {
				x = ZabbixYaMap.def_lat;
				y = ZabbixYaMap.def_lon;
				iconPreset = 'twirl#darkorangeDotIcon';
			} else {
				x = out.result[i].inventory.location_lat;
				y = out.result[i].inventory.location_lon;
				iconPreset = 'twirl#blueIcon';
			}
			if (x > x_max) x_max = x;
			if (x < x_min) x_min = x;
			if (y > y_max) y_max = y;
			if (y < y_min) y_min = y;
			Hosts[i] = new ymaps.Placemark(
					[ x, y ], 
					{
						hintContent : out.result[i].name,
						hostid : out.result[i].hostid
					},
					{
						draggable : true,
						preset : iconPreset
					}
			);
			(function(i) {
				Hosts[i].events.add('dragend', function() {
						draghost(Hosts[i].properties.get('hostid'),	Hosts[i].geometry.getCoordinates());
					});
			})(i);
			HostArray.add(Hosts[i]);
		}
		
		ZabbixYaMap.Map.geoObjects.add(HostArray);
					
		// Zoom the map
		ZabbixYaMap.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
			duration : 1000,
			checkZoomRange : true
		});
		return true;
	});
}
</script>