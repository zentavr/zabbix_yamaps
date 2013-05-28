<script type="text/javascript">
var ZabbixYaMapRW = Class.create(ZabbixYaMap, {
	/* Add variables */
	Hosts      : [],        // Placemarks
	ChangeHost : [],        // Contains the changed hosts (e.g. dragged hosts)
	HostArray  : undefined, // Cluster of geo objects
	saved      : false,     // were the hosts saved?
	//
	SaveButton : undefined,

	/* Add new methods */
	/**
	 * Initialization of additional controls
	 */
	init: function() {
		//console.info('ZabbixYaMapRW.init() was called');
		var me = this;
		me.HostArray = new ymaps.Clusterer({ maxZoom : 9});
		me.SetSelect(document.getElementById("selectgroup"), "<?php echo _('All'); ?>", "<?php echo _('All'); ?>");
		
		me.SaveButton = new ymaps.control.Button({
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
		me.SaveButton.disable();
		me.Map.controls.add(me.SaveButton);
		me.saved = false;

		me.ChangeGroup();
		// Set up onChange reaction
		jQuery('#selectgroup').change(function() {
			me.ChangeGroup();
		});
	},

	/**
	 * Saves the changed hosts
	 */
	save_change: function() {
		var me = this;
		for (var i = 0; i < this.ChangeHost.length; i++) {
			
			var query = {
					jsonrpc:"2.0",
					method:"host.update",
					params: {
						hostid: me.ChangeHost[i].hid,
						inventory: {
							location_lat: me.ChangeHost[i].point[0].toFixed(12),
							location_lon: me.ChangeHost[i].point[1].toFixed(12)
						}
					},
					id: i
				};
			me.apiQuery(query, true, function(){
				me.ChangeHost.length = 0;

				me.SaveButton.disable();
				me.saved = false;
				me.SaveButton.events.remove('click', function() {
			    	me.save_change();
			    });
			}, 'Cannot save the objects');
	     }
	},

	/**
	 * Drags the hosts
	 */
	draghost: function(id, newpoint) {
		// TODO: FIX, how many hosts there will be if we'll drag it all the time?? We need only the last value!
		var me = this;
		me.ChangeHost.push(new Object({
			hid: id,
			point: newpoint
		}));
		
		if (me.saved == false) {
			me.saved = true;
			me.SaveButton.enable();
			me.SaveButton.events.add('click', function() {
				me.save_change();
	        });
	    }
	},
	/**
	 * Redisplays the hosts, which are belonged to the certain group
	 */
	ChangeGroup: function(){
		//console.info('ZabbixYaMapRW.ChangeGroup() was called');
		var me = this;

		var sel = document.getElementById("selectgroup");
		var groupid = sel.options[sel.selectedIndex].value;

		me.HostArray.removeAll();
		me.Map.geoObjects.remove(me.HostArray);

		var query = {
				jsonrpc: "2.0",
				method: "host.get",
				params: {
					output:["host","name"],
					selectInventory:["location_lat","location_lon"]
				},
				id: 1
			};
		if(groupid == 0){
			var groups = {};
		} else {
			var groups = { groupids: [ groupid ]};
		}
		query.params = me.objMerge(query.params, groups);

		//console.info('Preparing to do the query');
		//console.log(query);
		
		me.apiQuery(query, true, function(out) {
			var x_max = 0;
			var y_max = 0;
			var x_min = 180;
			var y_min = 180;
            //console.info('Got the result');
            //console.log(out);
            //console.log(me);
			for ( var i = 0; i < out.result.length; i++) {
				//console.info("'this' in processing results");
				//console.log(me);
				/* If there is no Lattitude and Longtitude came from Zabbix */
				if (out.result[i].inventory.location_lat == 0 || out.result[i].inventory.location_lon == 0) {
					x = me.def_lat;
					y = me.def_lon;
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
				//console.info('Defining new host');
				me.Hosts[i] = new ymaps.Placemark(
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
				//console.log(me.Hosts[i]);
				(function(i) {
					me.Hosts[i].events.add('dragend', function() {
							me.draghost(
								me.Hosts[i].properties.get('hostid'),	
								me.Hosts[i].geometry.getCoordinates()
							);
						});
				})(i);
				me.HostArray.add(me.Hosts[i]);
			}

			//console.info('ALl the hosts');
			//console.log(me.HostArray);
			me.Map.geoObjects.add(me.HostArray);
						
			// Zoom the map
			me.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
				duration : 1000,
				checkZoomRange : true
			});
		}, 'Cannot load hosts');
	}
// The methods are over :(		
});

</script>