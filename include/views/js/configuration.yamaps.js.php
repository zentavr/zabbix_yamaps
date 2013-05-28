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
		console.info('ZabbixYaMapRW.init() was called');
		this.HostArray = new ymaps.Clusterer({ maxZoom : 9});
		this.SetSelect(document.getElementById("selectgroup"), "<?php echo _('All'); ?>", "<?php echo _('All'); ?>");
		
		this.SaveButton = new ymaps.control.Button({
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
		this.SaveButton.disable();
		this.Map.controls.add(this.SaveButton);
		this.saved = false;

		this.ChangeGroup();
		// Set up onChange reaction
		jQuery('#selectgroup').change(function() {
			this.ChangeGroup();
		});
	},

	/**
	 * Saves the changed hosts
	 */
	save_change: function() {
		for (var i = 0; i < this.ChangeHost.length; i++) {
			
			var query = {
					jsonrpc:"2.0",
					method:"host.update",
					params: {
						hostid: this.ChangeHost[i].hid,
						inventory: {
							location_lat: this.ChangeHost[i].point[0].toFixed(12),
							location_lon: this.ChangeHost[i].point[1].toFixed(12)
						}
					},
					id: i
				};
			this.apiQuery(query, true, function(){
				this.ChangeHost.length = 0;

				this.SaveButton.disable();
				this.saved = false;
				this.SaveButton.events.remove('click', function() {
			    	this.save_change();
			    });
			}, 'Cannot save the objects');
	     }
	},

	/**
	 * Drags the hosts
	 */
	draghost: function(id, newpoint) {
		// TODO: Check, how many hosts there will be if we'll drag it all the time?? We need only the last value!
		this.ChangeHost.push(new Object({
			hid: id,
			point: newpoint
		}));
		
		if (this.saved == false) {
			this.saved = true;
			this.SaveButton.enable();
			this.SaveButton.events.add('click', function() {
				this.save_change();
	        });
	    }
	},
	/**
	 * Redisplays the hosts, which are belonged to the certain group
	 */
	ChangeGroup: function(){
		var sel = document.getElementById("selectgroup");
		var groupid = sel.options[sel.selectedIndex].value;

		this.HostArray.removeAll();
		this.Map.geoObjects.remove(this.HostArray);

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
		query.params = ZabbixYaMap.objMerge(query.params, groups);
		
		this.apiQuery(query, true, function(out) {
			var x_max = 0;
			var y_max = 0;
			var x_min = 180;
			var y_min = 180;
			for ( var i = 0; i < out.result.length; i++) {
				/* If there is no Lattitude and Longtitude came from Zabbix */
				if (out.result[i].inventory.location_lat == 0 || out.result[i].inventory.location_lon == 0) {
					x = this.def_lat;
					y = this.def_lon;
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
				this.Hosts[i] = new ymaps.Placemark(
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
					this.Hosts[i].events.add('dragend', function() {
							this.draghost(
								this.Hosts[i].properties.get('hostid'),	
								this.Hosts[i].geometry.getCoordinates()
							);
						});
				})(i);
				this.HostArray.add(this.Hosts[i]);
			}
			
			this.Map.geoObjects.add(this.HostArray);
						
			// Zoom the map
			this.Map.setBounds([ [ x_min, y_min ], [ x_max, y_max ] ], {
				duration : 1000,
				checkZoomRange : true
			});
			return true;
		}, 'Cannot load hosts');
	}
// The methods are over :(		
});

</script>