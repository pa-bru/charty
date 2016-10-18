var myMapsApiKey = charty.charty_maps_api_key;
google.charts.load('upcoming', {mapsApiKey: myMapsApiKey, 'packages':['geochart', 'map']});
google.charts.setOnLoadCallback(drawRegionsMap);

charty.charty_data.unshift(charty.charty_labels);
console.log("coucou");

console.log(charty.charty_data);
console.log(charty.charty_data.toString());
console.log("coucou");

function drawRegionsMap() {
    var data = google.visualization.arrayToDataTable(charty.charty_data);

    var options = {
        region: charty.charty_region,
        displayMode: charty.charty_display_mode,
        colorAxis: {colors: charty.charty_color_axis},
        backgroundColor: charty.charty_bg_color,
        datalessRegionColor: charty.charty_dataless_region_color,
        defaultColor: charty.charty_default_color,
        tooltip: {trigger: charty.charty_tooltip_trigger},
        
//for map : 
        showTooltip: true,
        showInfoWindow: true
    };

    var chart;
    var chart_div = document.getElementById('charty_'+ charty.charty_id);
    switch(charty.charty_type){
        case "geo_chart":
            chart = new google.visualization.GeoChart(chart_div);
            break;
        case "map":
            chart = new google.visualization.Map(chart_div);
            break;
    }

        
    chart.draw(data, options);
}