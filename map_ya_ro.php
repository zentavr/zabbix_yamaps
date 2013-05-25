<?php
//require_once dirname(__FILE__) . '/include/config.inc.php';
//require_once dirname(__FILE__) . '/include/hosts.inc.php';
//require_once dirname(__FILE__) . '/include/items.inc.php';
//require_once dirname(__FILE__) . '/yandexapi.conf.php';

require_once('/include/config.inc.php');
require_once('/include/js.inc.php');
require_once('/include/hosts.inc.php');
require_once('/include/items.inc.php');
require_once('/yandexapi.conf.php');

$page["title"] = $MYGOROD;
$page['file'] = 'map_ya_ro.php';
$page['hist_arg'] = array();
$page['scripts'] = array();


//$page['type'] = detect_page_type();

$page['type'] = detect_page_type(PAGE_TYPE_HTML);

//define('ZBX_PAGE_MAIN_HAT','hat_latest');
//if (PAGE_TYPE_HTML == $page['type']) {
//    define('ZBX_PAGE_DO_REFRESH', 1);
//}

include_once('include/page_header.php');


insert_js("
			document.write('<select id=\"selectgroup\" onChange=\"ChangeGroup();\"></select>');
            var h = jQuery(window).height() - 180;
            document.write('<div id=\"map\" style=\"width:100%; height:' + h + 'px\"></div>');
			")
?>
        <script src="http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU" type="text/javascript"></script>
        <script>
            ymaps.ready(init);
            function init() {
                def_lat = <?php echo $MYLATLON['lat']; ?>;
                def_lon = <?php echo $MYLATLON['lon']; ?>;
                PrioProblem = '<?php echo $PRIOPROBLEM; ?>';
                minseverity = 0;
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
                    //type: 'yandex#publicMap'
                    type: 'yandex#<?php echo $MAPTYPE ?>'
                }));
                HostArray = new ymaps.Clusterer({
                    maxZoom: 17
                });
                ProblemArray = new ymaps.GeoObjectCollection();
                SetSelect(document.getElementById("selectgroup"), "Все");
                problems();
                interval = setInterval(function() {
                    problems();
                }, 60000);
                var UpdateListBox = new ymaps.control.ListBox({
                    data: {
                        title: 'Обновлять каждые 60сек'
                    },
                    items: [
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 10,
                                content: '10 секунд'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 30,
                                content: '30 секунд'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 60,
                                content: '60 секунд'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 120,
                                content: '120 секунд'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 600,
                                content: '600 секунд'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                time: 900,
                                content: '900 секунд'}}),
                    ]
                },
                {
                    position: {
                        top: 5,
                        right: 200}
                });
                for (var i = 0; i < UpdateListBox.length(); i++) {
                    (function(i) {
                        UpdateListBox.get(i).events.add('click', function() {
                            clearInterval(interval);
                            interval = setInterval(function() {
                                problems();
                            }, UpdateListBox.get(i).data.get('time') * 1000);
                            UpdateListBox.collapse();
                            UpdateListBox.setTitle('Обновлять каждые ' + UpdateListBox.get(i).data.get('time') + 'сек');
                        });
                    })(i);
                }
                ZabbixMap.controls.add(UpdateListBox);
                var MinseverityListBox = new ymaps.control.ListBox({
                    data: {
                        title: 'Показать все проблемы'
                    },
                    items: [
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 0,
                                content: 'Не классифицированно'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 1,
                                content: 'Информация'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 2,
                                content: 'Предупреждение'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 3,
                                content: 'Средняя'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 4,
                                content: 'Высокая'}}),
                        new ymaps.control.ListBoxItem({
                            data: {
                                severity: 5,
                                content: 'Чрезвычайная'}})
                    ]
                },
                {
                    position: {
                        top: 5,
                        right: 650}
                });
                for (var i = 0; i < MinseverityListBox.length(); i++) {
                    (function(i) {
                        MinseverityListBox.get(i).events.add('click', function() {
                            minseverity = MinseverityListBox.get(i).data.get('severity');
                            MinseverityListBox.collapse();
                            MinseverityListBox.setTitle('Показать ' + MinseverityListBox.get(i).data.get('content') + ' и выше');
                        });
                    })(i);
                }
                ZabbixMap.controls.add(MinseverityListBox);
                var FollowProblem = new ymaps.control.RadioGroup({
                    items: [
                        new ymaps.control.Button('Проблемами'),
                        new ymaps.control.Button('Выбором групп')
                    ]
                },
                {
                    position: {
                        top: 5,
                        right: 430}
                });
                FollowProblem.get(0).select();
                FollowProblem.get(0).events.add('click', function() {
                    PrioProblem = 'true';
                });
                FollowProblem.get(1).events.add('click', function() {
                    PrioProblem = 'false';
                });
                ZabbixMap.controls.add(FollowProblem);
                ChangeGroup();
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
            function problems() {
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
                var query = '{"jsonrpc": "2.0","method": "trigger.get","params":{"monitored":"true","expandDescription":"true","min_severity":"' + minseverity + '","expandData":"true","output":["description"],"filter":{"value":"1","value_flags":0}},"auth":"' + auth() + '","id":1}';
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
                                    }
                                    else if (window.ActiveXObject) {
                                        jsonReqTr = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    jsonReqTr.overrideMimeType('application/json');
                                    jsonReqTr.open('POST', url, true);
                                    jsonReqTr.setRequestHeader("Content-Type", "application/json");
                                    var query2 = '{"jsonrpc":"2.0","method":"host.get","params":{"hostids":"' + out.result[i].hostid + '","selectInventory":["location_lat","location_lon"]},"auth":"' + auth() + '","id":' + i + '}';
                                    jsonReqTr.send(query2);
                                    jsonReqTr.onreadystatechange = function alertContents() {
                                        if (jsonReqTr.readyState === 4) {
                                            if (jsonReqTr.status === 200) {
                                                var out2 = JSON.parse(jsonReqTr.responseText);
                                                if (out2.result[0].inventory.location_lat == 0 || out2.result[0].inventory.location_lon == 0) {
                                                    var x = def_lat;
                                                    var y = def_lon;
                                                } else {
                                                    var x = out2.result[0].inventory.location_lat;
                                                    var y = out2.result[0].inventory.location_lon;
                                                }
                                                if (x > x_max)
                                                    x_max = x;
                                                if (x < x_min)
                                                    x_min = x;
                                                if (y > y_max)
                                                    y_max = y;
                                                if (y < y_min)
                                                    y_min = y;
                                                ProblemArray.add(new ymaps.Placemark([x, y],
                                                        {balloonContent: out.result[i].hostname + '<br>' + out.result[i].description,
                                                            iconContent: out.result[i].description
                                                            //hintContent: out.result[i].hostname + '<br>' + out.result[i].description
                                                        },
                                                {preset: 'twirl#redStretchyIcon'}),i);
                                                if (PrioProblem === 'true' && x_max != 0) {
                                                    ZabbixMap.setBounds(
                                                            [
                                                                [x_min, y_min],
                                                                [x_max, y_max]
                                                            ], {
                                                        duration: 1000,
                                                        checkZoomRange: true
                                                    });
                                                }
                                            }
                                        }
                                    };
                                })(i);
                            }
                            ZabbixMap.geoObjects.add(ProblemArray);
                        }
                    }
                };
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
                                HostArray.add(new ymaps.Placemark([x, y],
                                        {balloonContent: out.result[i].name,
                                            iconContent: out.result[i].host,
                                            hintContent: out.result[i].name
                                        },
                                {preset: 'twirl#greenStretchyIcon'}),i);
                            }
                            ZabbixMap.geoObjects.add(HostArray);
                            if (PrioProblem === 'false' && x_max != 0) {
                                ZabbixMap.setBounds(
                                        [
                                            [x_min, y_min],
                                            [x_max, y_max]
                                        ], {
                                    duration: 1000,
                                    checkZoomRange: true
                                });
                            }
                            return true;
                        }
                        return (100);
                    }
                    return (99);

                };
            }
        </script>

<?php
require_once dirname(__FILE__) . '/include/page_footer.php';
?>



