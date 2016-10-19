var myMapsApiKey = charty.charty_maps_api_key;
var chart_div = document.getElementById('charty_'+ charty.charty_id);
google.charts.load('upcoming', {mapsApiKey: myMapsApiKey, 'packages':['geochart', 'map']});

charty.charty_data.unshift(charty.charty_labels);
console.log("coucou");
console.log(charty.charty_data);
console.log(charty.charty_data.toString());
console.log("coucou");

switch(charty.charty_type){
    case "geo_chart":
        google.charts.setOnLoadCallback(drawGeoChart);
        break;
    case "map":
        google.charts.setOnLoadCallback(drawMap);
        break;
    default:
        break;
}

function drawGeoChart() {
    var data = google.visualization.arrayToDataTable(charty.charty_data);

    var options = {
        region: charty.charty_region,
        displayMode: charty.charty_display_mode,
        colorAxis: {colors: charty.charty_color_axis},
        backgroundColor: charty.charty_bg_color,
        datalessRegionColor: charty.charty_dataless_region_color,
        defaultColor: charty.charty_default_color,
        tooltip: {trigger: charty.charty_tooltip_trigger}
    };

    var chart = chart = new google.visualization.GeoChart(chart_div);
    chart.draw(data, options);
}

function drawMap() {
    var data = google.visualization.arrayToDataTable(charty.charty_data);
    var style;

    switch(charty.charty_map_style){
        case "none":
            style= "hybrid";
            break;
        case "green":
            style = "greenAll";
            break;
        case "red":
            style = "redAll";
            break;
        case "blue":
            style = "blueAll";
            break;
    }

    var options = {
        mapType: style,
        zoomLevel: charty.charty_map_zoom_level,
        showTooltip: true,
        showInfoWindow: true,
        useMapTypeControl: charty.charty_map_type_control,
        maps: {
            greenAll: {
                name: 'Styled Map',
                styles: [
                    {featureType: 'poi.attraction',
                        stylers: [{color: '#fce8b2'}]},
                    {featureType: 'road.highway',
                        stylers: [{hue: '#0277bd'}, {saturation: -50}]},
                    {featureType: 'road.highway', elementType: 'labels.icon',
                        stylers: [{hue: '#000'}, {saturation: 100}, {lightness: 50}]},
                    {featureType: 'landscape',
                        stylers: [{hue: '#259b24'}, {saturation: 10},{lightness: -22}]}
                ]},
            redAll: {
                name: 'Redden All The Things',
                styles: [
                    {featureType: 'landscape',
                        stylers: [{color: '#fde0dd'}]},
                    {featureType: 'road.highway',
                        stylers: [{color: '#67000d'}]},
                    {featureType: 'road.highway', elementType: 'labels',
                        stylers: [{visibility: 'off'}]},
                    {featureType: 'poi',
                        stylers: [{hue: '#ff0000'}, {saturation: 50}, {lightness: 0}]},
                    {featureType: 'water',
                        stylers: [{color: '#67000d'}]},
                    {featureType: 'transit.station.airport',
                        stylers: [{color: '#ff0000'}, {saturation: 50}, {lightness: -50}]}
                ]},
            blueAll: {
                name: 'All Your Blues are Belong to Us',
                styles: [
                    {featureType: 'landscape',
                        stylers: [{color: '#c5cae9'}]},
                    {featureType: 'road.highway',
                        stylers: [{color: '#023858'}]},
                    {featureType: 'road.highway', elementType: 'labels',
                        stylers: [{visibility: 'off'}]},
                    {featureType: 'poi',
                        stylers: [{hue: '#0000ff'}, {saturation: 50}, {lightness: 0}]},
                    {featureType: 'water',
                        stylers: [{color: '#0288d1'}]},
                    {featureType: 'transit.station.airport',
                        stylers: [{color: '#0000ff'}, {saturation: 50}, {lightness: -50}]}
                ]}
        }
    };
    var chart = new google.visualization.Map(chart_div);
    chart.draw(data, options);
}