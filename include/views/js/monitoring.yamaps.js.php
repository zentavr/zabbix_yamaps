<script type="text/javascript">
function initRO() {
	minseverity = 0;
	
	HostArray = new ymaps.Clusterer({
		maxZoom : 17
	});

	ProblemArray = new ymaps.GeoObjectCollection();

	ZabbixYaMap.SetSelect(document.getElementById("selectgroup"), "<?php echo _('All'); ?>", "<?php echo _('All'); ?>");

	problems();

	interval = setInterval(function() {
		problems();
	}, 60000);

	var UpdateListBox = new ymaps.control.ListBox({
		data : {
			title : '<?php echo _('refreshed every'); ?> 60 <?php echo _('sec'); ?>'
		},
		items : [ 
		new ymaps.control.ListBoxItem({
			data : {
				time : 10,
				content : '10 <?php echo _('sec'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 30,
				content : '30 <?php echo _('sec'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 60,
				content : '60 <?php echo _('sec'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 120,
				content : '120 <?php echo _('sec'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 600,
				content : '600 <?php echo _('sec'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 900,
				content : '900 <?php echo _('sec'); ?>'
			}
		}), 
		]
	}, {
		position : {
			top : 5,
			right : 200
		}
	});
	for ( var i = 0; i < UpdateListBox.length(); i++) {
		(function(i) {
			UpdateListBox.get(i).events.add('click', function() {
				clearInterval(interval);
				interval = setInterval(function() {
					problems();
				}, UpdateListBox.get(i).data.get('time') * 1000);
				UpdateListBox.collapse();
				UpdateListBox.setTitle('<?php echo _('refreshed every'); ?> '
						+ UpdateListBox.get(i).data.get('time') + ' <?php echo _('sec'); ?>');
			});
		})(i);
	}

	ZabbixYaMap.Map.controls.add(UpdateListBox);
	
	
	var MinseverityListBox = new ymaps.control.ListBox({
		data : {
			title : '<?php echo _('Show all events'); ?>'
		},
		items : [ new ymaps.control.ListBoxItem({
			data : {
				severity : 0,
				content : '<?php echo _('Not classified'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 1,
				content : '<?php echo _('Information'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 2,
				content : '<?php echo _('Warning'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 3,
				content : '<?php echo _('Average'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 4,
				content : '<?php echo _('High'); ?>'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 5,
				content : '<?php echo _('Disaster'); ?>'
			}
		}) ]
	}, {
		position : {
			top : 5,
			right : 820
		}
	});
	for ( var i = 0; i < MinseverityListBox.length(); i++) {
		(function(i) {
			MinseverityListBox.get(i).events.add('click', function() {
				/* Setting up the minimum severity */
				minseverity = MinseverityListBox.get(i).data.get('severity');
				//console.log('The min severity is: '+ minseverity);
				MinseverityListBox.collapse();
				MinseverityListBox.setTitle('<?php echo _('Show'); ?> '
						+ MinseverityListBox.get(i).data.get('content')
						+ ' <?php echo _('and more'); ?>');
			});
		})(i);
	}
	ZabbixYaMap.Map.controls.add(MinseverityListBox);
	
	var FollowProblem = new ymaps.control.RadioGroup({
		items : [ new ymaps.control.Button('<?php echo _('Follow the events'); ?>'),
				new ymaps.control.Button('<?php echo _('Follow the chosen group'); ?>') ]
	}, {
		position : {
			top : 5,
			right : 430
		}
	});
	FollowProblem.get(0).select();
	FollowProblem.get(0).events.add('click', function() {
		//console.log('Setting ZabbixYaMap.PrioProblem to true');
		ZabbixYaMap.PrioProblem = 'true';
	});
	FollowProblem.get(1).events.add('click', function() {
		//console.log('Setting ZabbixYaMap.PrioProblem to false');
		ZabbixYaMap.PrioProblem = 'false';
	});
	ZabbixYaMap.Map.controls.add(FollowProblem);
	
	//ChangeGroup();
}

function problems() {
	//console.info("Running problems()");
	ProblemArray.removeAll();
	
	var sel = document.getElementById("selectgroup");
	var groupid = sel.options[sel.selectedIndex].value;
	// if grouid=0, do not add it to the query
	if(groupid == 0){
		var groups = '';
	} else {
		var groups = '"groupids":' + groupid + ',';
	}
	var query = '{"jsonrpc": "2.0","method": "trigger.get","params":{"monitored":"true", ' + groups + '"expandDescription":"true","min_severity":"'
		+ minseverity
		+ '","expandData":"true","output":["description"],"filter":{"value":"1","value_flags":0}},"auth":"'
		+ ZabbixYaMap.auth() + '","id":1}';
	//console.info("The query will be:");
	//console.log(query);
	
	jQuery.ajax({
		url: "api_jsonrpc.php",
		type: "POST",
		contentType: "application/json",
		processData : false,
		async: true,
		dataType: "json",
		data: query,
		success : function(out, textStatus, jqXHR) {
			//console.info("problems():trigger.get response:");
			//console.log(out);
			
			var x_max = 0;
			var y_max = 0;
			var x_min = 180;
			var y_min = 180;
			for (i = 0; i < out.result.length; i++) {
				(function(i) {
					/* Selecting the coordinates */
					var hostQuery = '{"jsonrpc":"2.0","method":"host.get","params":{"hostids":"'
						+ out.result[i].hostid
						+ '","selectInventory":["location_lat","location_lon"]},"auth":"'
						+ ZabbixYaMap.auth() + '","id":' + i + '}';
					//console.info("Doing problems():host.get");
					//console.log(hostQuery);
					
					jQuery.ajax({
						url: "api_jsonrpc.php",
						type: "POST",
						contentType: "application/json",
						processData : false,
						async: true,
						dataType: "json",
						data: hostQuery,
						success : function(data, textStatus, jqXHR) {
							//console.info("problems():host.get response:");
							//console.log(data);
							
							if (data.result[0].inventory.location_lat == 0 || data.result[0].inventory.location_lon == 0) {
								var x = def_lat;
								var y = def_lon;
							} else {
								var x = data.result[0].inventory.location_lat;
								var y = data.result[0].inventory.location_lon;
							}
							if (x > x_max) x_max = x;
							if (x < x_min) x_min = x;
							if (y > y_max) y_max = y;
							if (y < y_min) y_min = y;
							ProblemArray.add(
									new ymaps.Placemark([ x, y ],{
											balloonContent : out.result[i].hostname
															+ '<br>'
															+ out.result[i].description,
											iconContent : out.result[i].description
											// hintContent: out.result[i].hostname
											// + '<br>' +
											// out.result[i].description
									},
									{
										preset : 'twirl#redStretchyIcon'
									}), i);
							if (ZabbixYaMap.PrioProblem === 'true' && x_max != 0) {
								ZabbixYaMap.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
									duration : 1000,
									checkZoomRange : true
								});
							}
						},
						error : function( jqXHR, textStatus, errorThrown ) {
							alert("Cannot load hosts\n\n" + 
									"Code: " + jqXHR.status + "\n" +
									"Status: " + jqXHR.statusText + "\n" +
									"Response: " + jqXHR.responseText);
						}
					});	/* Ajax is done */
				})(i);
			}
			ZabbixYaMap.Map.geoObjects.add(ProblemArray);
		},
		error : function( jqXHR, textStatus, errorThrown ) {
			alert("Cannot load hosts\n\n" + 
					"Code: " + jqXHR.status + "\n" +
					"Status: " + jqXHR.statusText + "\n" +
					"Response: " + jqXHR.responseText);
		}
	});

}


function ChangeGroup() {
	//console.info('Doing ChangeGroup()');
	problems();
	/*
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
			if (out.result[i].inventory.location_lat == 0 || out.result[i].inventory.location_lon == 0) {
				x = ZabbixYaMap.def_lat;
				y = ZabbixYaMap.def_lon;
				iconPreset = 'twirl#whiteStretchyIcon';
			} else {
				x = out.result[i].inventory.location_lat;
				y = out.result[i].inventory.location_lon;
				iconPreset = 'twirl#greenStretchyIcon';
			}
			if (x > x_max) x_max = x;
			if (x < x_min) x_min = x;
			if (y > y_max) y_max = y;
			if (y < y_min) y_min = y;
			HostArray.add(new ymaps.Placemark(
					[ x, y ], 
					{
						balloonContent : out.result[i].name,
						iconContent : out.result[i].host,
						hintContent : out.result[i].name
					},
					{
						preset : iconPreset
					}
				), i);
		}
		
		ZabbixYaMap.Map.geoObjects.add(HostArray);
		if (ZabbixYaMap.PrioProblem === 'false' && x_max != 0) {
			ZabbixYaMap.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
				duration : 1000,
				checkZoomRange : true
			});
		}
		return true;
	});
	*/
}
</script>