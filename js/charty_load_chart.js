var myMapsApiKey = charty.charty_maps_api_key;

google.charts.load('upcoming', {mapsApiKey: myMapsApiKey, 'packages':['geochart']});
google.charts.setOnLoadCallback(drawRegionsMap);


charty.charty_data.unshift(charty.charty_labels);
console.log("coucou");
console.log(charty.charty_data);
console.log("coucou");

function drawRegionsMap() {
    var data = google.visualization.arrayToDataTable(charty.charty_data);

    var options = {
        displayMode: charty.charty_display_mode,
        region: charty.charty_region, // Africa
        colorAxis: {colors: charty.charty_color_axis},
        backgroundColor: charty.charty_bg_color,
        datalessRegionColor: charty.charty_dataless_region_color,
        defaultColor: charty.charty_default_color,
        tooltip: {trigger: charty.charty_tooltip_trigger}
    };

    var chart_div = document.getElementById('charty_'+ charty.charty_id);
    var chart = new google.visualization.GeoChart(chart_div);
    chart.draw(data, options);
}