<?php
//require_once dirname(__FILE__) . '/include/config.inc.php';
//require_once dirname(__FILE__) . '/include/hosts.inc.php';
//require_once dirname(__FILE__) . '/include/items.inc.php';
//require_once dirname(__FILE__) . '/yandexapi.conf.php';

require_once dirname($_SERVER["SCRIPT_FILENAME"]) . '/include/config.inc.php';
require_once dirname($_SERVER["SCRIPT_FILENAME"]) . '/include/hosts.inc.php';
require_once dirname($_SERVER["SCRIPT_FILENAME"]) . '/include/items.inc.php';
require_once dirname($_SERVER["SCRIPT_FILENAME"]) . '/yandexapi.conf.php';


$page["title"] = $MYGOROD;
$page['file'] = 'map_ya_rw.php';
$page['hist_arg'] = array();
$page['scripts'] = array();


//$page['type'] = detect_page_type();

$page['type'] = detect_page_type(PAGE_TYPE_HTML);

//define('ZBX_PAGE_MAIN_HAT','hat_latest');
//if (PAGE_TYPE_HTML == $page['type']) {
//    define('ZBX_PAGE_DO_REFRESH', 1);
//}

include_once('include/page_header.php');
//<meta http-equiv="refresh" content="10"> 
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
        <!-- jQuery is already loaded by Zabbix 2.0.6
        	<script src="http://yandex.st/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
        -->
        <script type="text/javascript">
            document.write('<select id="selectgroup" onChange="ChangeGroup();"></select>');
            var h = jQuery(window).height() - 180;
            document.write('<div id="map" style="width:100%; height:' + h + 'px"></div>');
        </script>
        <script>
            ymaps.ready(init);
            function init() {
                def_lat = <?php echo $MYLATLON['lat']; ?>;
                def_lon = <?php echo $MYLATLON['lon']; ?>;
                ZabbixMap = new ymaps.Map('map', {
                    center: [<?php echo $MYLATLON['lat']; ?>, <?php echo $MYLATLON['lon']; ?>],
                    zoom: <?php echo $MYZOOM; ?>,
                    type: 'yandex#<?php echo $MAPTYPE ?>',
                    behaviors: ['default', 'scrollZoom']
                });
                ZabbixMap.controls
                        .add('zoomControl')
                        .add('typeSelector')
                        .add('mapTools')
                        .add(new ymaps.control.ScaleLine())
                        .add(new ymaps.control.SearchControl({
                    provider: 'yandex#<?php echo $MAPTYPE ?>',
                    left: '40px',
                    top: '10px',
                    useMapBounds: true
                }))
                        .add(new ymaps.control.MiniMap({
                    type: 'yandex#<?php echo $MAPTYPE ?>'
                }));
                Hosts = [];
                ChangeHost = [];
                HostArray = new ymaps.Clusterer({
                    maxZoom: 9
                });
                SetSelect(document.getElementById("selectgroup"), "Все");
                SaveButton = new ymaps.control.Button({
                    data: {
                        content: 'Сохранить',
                        title: 'Нажмите для сохранения'
                    }
                },
                {
                    position: {
                        top: 5,
                        right: 200},
                    selectOnClick: false
                });
                SaveButton.disable();
                ZabbixMap.controls.add(SaveButton);
                saved = false;
                ChangeGroup();
            }
            function save_change() {
                for (var i = 0; i < ChangeHost.length; i++) {
                    var jsonReq;
                    if (window.XMLHttpRequest) {
                        jsonReq = new XMLHttpRequest();
                        jsonReq.overrideMimeType('text/xml');
                    }
                    else if (window.ActiveXObject) {
                        jsonReq = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    jsonReq.overrideMimeType('application/json');
                    var url = "api_jsonrpc.php";
                    jsonReq.open('POST', url, true);
                    jsonReq.setRequestHeader("Content-Type", "application/json");
                    var query = '{"jsonrpc":"2.0","method":"host.update","params":{"hostid":"' + ChangeHost[i].hid + '","inventory":{"location_lat":"' + ChangeHost[i].point[0].toFixed(12) + '","location_lon":"' + ChangeHost[i].point[1].toFixed(12) + '"}},"auth":"' + auth() + '","id":' + i + '}';
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
            function auth() {
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
            function SetSelect(htmlSelect, selected) {
                var jsonReq;
                if (window.XMLHttpRequest) {
                    jsonReq = new XMLHttpRequest();
                    jsonReq.overrideMimeType('text/xml');
                }
                else if (window.ActiveXObject) {
                    jsonReq = new ActiveXObject("Microsoft.XMLHTTP");
                }
                jsonReq.overrideMimeType('application/json');
                var url = "api_jsonrpc.php";
                jsonReq.open('POST', url, true);
                jsonReq.setRequestHeader("Content-Type", "application/json");
                var query = '{"jsonrpc":"2.0","method":"hostgroup.getobjects","params":{},"auth":"' + auth() + '","id":1}';
                jsonReq.send(query);
                jsonReq.onreadystatechange = function alertContents() {
                    if (jsonReq.readyState === 4) {
                        if (jsonReq.status === 200) {
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
                        }
                        return (100);
                    }
                    return (99);
                };
            }
            function ChangeGroup() {
                var sel = document.getElementById("selectgroup");
                var groupid = sel.options[sel.selectedIndex].value;
                HostArray.removeAll();
                ZabbixMap.geoObjects.remove(HostArray);
                var jsonReq;
                if (window.XMLHttpRequest) {
                    jsonReq = new XMLHttpRequest();
                    jsonReq.overrideMimeType('text/xml');
                }
                else if (window.ActiveXObject) {
                    jsonReq = new ActiveXObject("Microsoft.XMLHTTP");
                }
                jsonReq.overrideMimeType('application/json');
                var url = "api_jsonrpc.php";
                jsonReq.open('POST', url, true);
                jsonReq.setRequestHeader("Content-Type", "application/json");
                if (groupid == 0) {
                    var query = '{"jsonrpc":"2.0","method":"host.get","params":{"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + auth() + '","id":1}';
                } else {
                    var query = '{"jsonrpc":"2.0","method":"host.get","params":{"groupids":' + groupid + ',"output":["host","name"],"selectInventory":["location_lat","location_lon"]},"auth":"' + auth() + '","id":1}';
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
                            for (var i = 0; i < out.result.length; i++) {
                                if (out.result[i].inventory.location_lat == 0 || out.result[i].inventory.location_lon == 0) {
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
                                Hosts[i] = new ymaps.Placemark([x, y],
                                        {hintContent: out.result[i].name,
                                            hostid: out.result[i].hostid},
                                {draggable: true}
                                );
                                (function(i) {
                                    Hosts[i].events.add('dragend', function() {
                                        draghost(Hosts[i].properties.get('hostid'), Hosts[i].geometry.getCoordinates());
                                    });
                                })(i);
                                HostArray.add(Hosts[i]);

                            }
                            ZabbixMap.geoObjects.add(HostArray);
                            ZabbixMap.setBounds(
                                    [
                                        [x_min, y_min],
                                        [x_max, y_max]
                                    ], {
                                duration: 1000,
                                checkZoomRange: true
                            });
                            return true;
                        }
                        return (100);
                    }
                    return (99);

                }
                ;
            }
        </script>
    </head>
</html>

<?php
require_once dirname(__FILE__) . '/include/page_footer.php';
?>



