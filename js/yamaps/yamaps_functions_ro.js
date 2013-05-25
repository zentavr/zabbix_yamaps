
function initRO() {
	minseverity = 0;
	
	HostArray = new ymaps.Clusterer({
		maxZoom : 17
	});

	ProblemArray = new ymaps.GeoObjectCollection();

	ZabbixYaMap.SetSelect(document.getElementById("selectgroup"), "Все");

	problems();

	interval = setInterval(function() {
		problems();
	}, 60000);

	var UpdateListBox = new ymaps.control.ListBox({
		data : {
			title : 'Обновлять каждые 60сек'
		},
		items : [ 
		new ymaps.control.ListBoxItem({
			data : {
				time : 10,
				content : '10 секунд'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 30,
				content : '30 секунд'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 60,
				content : '60 секунд'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 120,
				content : '120 секунд'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 600,
				content : '600 секунд'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				time : 900,
				content : '900 секунд'
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
				UpdateListBox.setTitle('Обновлять каждые '
						+ UpdateListBox.get(i).data.get('time') + 'сек');
			});
		})(i);
	}

	ZabbixYaMap.Map.controls.add(UpdateListBox);
	
	
	var MinseverityListBox = new ymaps.control.ListBox({
		data : {
			title : 'Показать все проблемы'
		},
		items : [ new ymaps.control.ListBoxItem({
			data : {
				severity : 0,
				content : 'Не классифицированно'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 1,
				content : 'Информация'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 2,
				content : 'Предупреждение'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 3,
				content : 'Средняя'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 4,
				content : 'Высокая'
			}
		}), new ymaps.control.ListBoxItem({
			data : {
				severity : 5,
				content : 'Чрезвычайная'
			}
		}) ]
	}, {
		position : {
			top : 5,
			right : 650
		}
	});
	for ( var i = 0; i < MinseverityListBox.length(); i++) {
		(function(i) {
			MinseverityListBox.get(i).events.add('click', function() {
				minseverity = MinseverityListBox.get(i).data.get('severity');
				MinseverityListBox.collapse();
				MinseverityListBox.setTitle('Показать '
						+ MinseverityListBox.get(i).data.get('content')
						+ ' и выше');
			});
		})(i);
	}
	ZabbixYaMap.Map.controls.add(MinseverityListBox);
	
	var FollowProblem = new ymaps.control.RadioGroup({
		items : [ new ymaps.control.Button('Проблемами'),
				new ymaps.control.Button('Выбором групп') ]
	}, {
		position : {
			top : 5,
			right : 430
		}
	});
	FollowProblem.get(0).select();
	FollowProblem.get(0).events.add('click', function() {
		PrioProblem = 'true';
	});
	FollowProblem.get(1).events.add('click', function() {
		PrioProblem = 'false';
	});
	ZabbixYaMap.Map.controls.add(FollowProblem);
	
	//ChangeGroup();
}

function problems() {
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
	var query = '{"jsonrpc": "2.0","method": "trigger.get","params":{"monitored":"true","expandDescription":"true","min_severity":"'
			+ minseverity
			+ '","expandData":"true","output":["description"],"filter":{"value":"1","value_flags":0}},"auth":"'
			+ ZabbixYaMap.auth() + '","id":1}';
	jsonReq.send(query);
	ProblemArray.removeAll();
	jsonReq.onreadystatechange = function alertContents() {
		if (jsonReq.readyState === 4) {
			if (jsonReq.status === 200) {
				var out = JSON.parse(jsonReq.responseText);
				var x_max = 0;
				var y_max = 0;
				var x_min = 180;
				var y_min = 180;
				for (i = 0; i < out.result.length; i++) {
					(function(i) {
						var jsonReqTr;
						if (window.XMLHttpRequest) {
							jsonReqTr = new XMLHttpRequest();
							jsonReqTr.overrideMimeType('text/xml');
						} else if (window.ActiveXObject) {
							jsonReqTr = new ActiveXObject("Microsoft.XMLHTTP");
						}
						jsonReqTr.overrideMimeType('application/json');
						jsonReqTr.open('POST', url, true);
						jsonReqTr.setRequestHeader("Content-Type",
								"application/json");
						var query2 = '{"jsonrpc":"2.0","method":"host.get","params":{"hostids":"'
								+ out.result[i].hostid
								+ '","selectInventory":["location_lat","location_lon"]},"auth":"'
								+ ZabbixYaMap.auth() + '","id":' + i + '}';
						jsonReqTr.send(query2);
						jsonReqTr.onreadystatechange = function alertContents() {
							if (jsonReqTr.readyState === 4) {
								if (jsonReqTr.status === 200) {
									var out2 = JSON
											.parse(jsonReqTr.responseText);
									if (out2.result[0].inventory.location_lat == 0
											|| out2.result[0].inventory.location_lon == 0) {
										var x = def_lat;
										var y = def_lon;
									} else {
										var x = out2.result[0].inventory.location_lat;
										var y = out2.result[0].inventory.location_lon;
									}
									if (x > x_max) x_max = x;
									if (x < x_min) x_min = x;
									if (y > y_max) y_max = y;
									if (y < y_min) y_min = y;
									ProblemArray.add(
											new ymaps.Placemark([ x, y ],
															{
																balloonContent : out.result[i].hostname
																		+ '<br>'
																		+ out.result[i].description,
																iconContent : out.result[i].description
															// hintContent:
															// out.result[i].hostname
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
								}
							}
						};
					})(i);
				}
				ZabbixYaMap.Map.geoObjects.add(ProblemArray);
			}
		}
	};
}


function ChangeGroup() {
	//console.info('Doing ChangeGroup()');
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
			if (out.result[i].inventory.location_lat == 0
					|| out.result[i].inventory.location_lon == 0) {
				x = def_lat;
				y = def_lon;
			} else {
				x = out.result[i].inventory.location_lat;
				y = out.result[i].inventory.location_lon;
			}
			if (x > x_max) x_max = x;
			if (x < x_min) x_min = x;
			if (y > y_max) y_max = y;
			if (y < y_min) y_min = y;
			HostArray.add(new ymaps.Placemark([ x, y ], {
				balloonContent : out.result[i].name,
				iconContent : out.result[i].host,
				hintContent : out.result[i].name
			}, {
				preset : 'twirl#greenStretchyIcon'
			}), i);
		}
		
		ZabbixYaMap.Map.geoObjects.add(HostArray);
		if (PrioProblem === 'false' && x_max != 0) {
			ZabbixYaMap.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
				duration : 1000,
				checkZoomRange : true
			});
		}
		return true;
	});

}