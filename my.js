// default layer on map
var Selected_layer = 'covid19:active_cases'; 

//generating map using leaflet api
var WFSLayer = null;
var map = L.map('map').setView([9.1204, 125.59], 8);
var basemap = L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);


//adding kilometer control scale
L.control.scale().addTo(map); 
var legend = L.control({position: 'topright'});

//adding legend to the map
legend.onAdd = function (map) {
    var div = L.DomUtil.create('div', 'info legend');
    div.innerHTML ='<img src="http://localhost:8080/geoserver/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER='+Selected_layer+'&legend_options=fontName:Arial;fontAntiAliasing:true;fontColor:0x000033;fontSize:10;bgColor:0xFFFFEE;dpi:120;labelMargin:5"/>';
    return div;
};
legend.addTo(map);

