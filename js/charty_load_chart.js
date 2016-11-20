/*
 * charty_load_chart.js
 * @author  pa-bru
 * @version 1.2
 * @url http://pa-bru.fr
 */
(function() {
    /***************************************
     HELPERS :
     ***************************************/
    function extendDefaults(source, properties) {
        var property;
        for (property in properties) {
            if (properties.hasOwnProperty(property)) {
                source[property] = properties[property];
            }
        }
        return source;
    }

    function getElems(elems, ctx){
        return (typeof elems === 'string') ? (ctx || document).querySelectorAll(elems) : [elems];
    }

    function getElem(elem, ctx){
        return (typeof elem === 'string') ? (ctx || document).querySelector(elem) : elem;
    }

    /***************************************
     CHARTY OBJECT CONSTRUCTOR :
     ***************************************/
    this.Charty = function(options) {
        //save popup instance to get the scope :
        var chart = {};
        if(chart.instance) return;
        chart.instance = this;

        function mergeParams(options){
            var defaults = {
                region: null,
                displayMode: null,
                colorAxis: null,
                backgroundColor: null,
                datalessRegionColor: null,
                defaultColor: null,
                tooltip: null,
                charty_data: null,
                charty_type: null,
                charty_maps_api_key: null,
                charty_id: null,
                charty_labels: null,
                charty_map_style: null,
                charty_map_zoom_level: null,
                charty_map_type_control: null
            };

            // Create options by extending defaults with the passed in arguments
            if (options && typeof options === "object") {
                chart.instance.options = extendDefaults(defaults, options);
            }
        }
        function init(){
            console.log(chart);
            //load google charts API :
            google.charts.load('upcoming', {mapsApiKey: chart.instance.options.charty_maps_api_key, 'packages':['geochart', 'map']});

            // Get the div element to add the chart :
            chart.elem = getElem('#charty_' + chart.instance.options.charty_id);

            //add labels at the beginning of the array of data :
            chart.instance.options.charty_data.unshift(chart.instance.options.charty_labels);

            //Write the chart after the document is loaded according to the chart type :
            switch(chart.instance.options.charty_type){
                case "geo_chart":
                    google.charts.setOnLoadCallback(drawGeoChart);
                    break;
                case "map":
                    google.charts.setOnLoadCallback(drawMap);
                    break;
                default:
                    break;
            }
        }

        function drawGeoChart() {
            var data = google.visualization.arrayToDataTable(chart.instance.options.charty_data);

            var options = {
                region: chart.instance.options.charty_region,
                displayMode: chart.instance.options.charty_display_mode,
                colorAxis: {colors: chart.instance.options.charty_color_axis},
                backgroundColor: chart.instance.options.charty_bg_color,
                datalessRegionColor: chart.instance.options.charty_dataless_region_color,
                defaultColor: chart.instance.options.charty_default_color,
                tooltip: {trigger: chart.instance.options.charty_tooltip_trigger}
            };

            //remove property colorAxis if the user has not specified this property : (can't be null for google)
            if(chart.instance.options.charty_color_axis == (null || "")){
                options.colorAxis = undefined;
            }
            var geoChart = new google.visualization.GeoChart(chart.elem);
            geoChart.draw(data, options);
        }

        function drawMap() {
            var data = google.visualization.arrayToDataTable(chart.instance.options.charty_data);
            var style;

            switch(chart.instance.options.charty_map_style){
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
                zoomLevel: chart.instance.options.charty_map_zoom_level,
                showTooltip: true,
                showInfoWindow: true,
                useMapTypeControl: chart.instance.options.charty_map_type_control,
                maps: {
                    greenAll: {
                        name: 'color green',
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
                        name: 'color red',
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
                        name: 'color blue',
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
            var mapChart = new google.visualization.Map(chart.elem);
            mapChart.draw(data, options);
        }
        mergeParams(options);
        init();
    };
}());