<?php
   $host = "localhost"; 	//host
   $port ="5433";			//posrt
   $user = "postgres"; 		//username
   $pass = "1234"; 			//password
   $db = "alvin"; 			//database name

   /**Creating connection to PostgresSQL */
   $con = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass")
	   or die ("Could not connect to server\n");

	//query to get the total active cases, recoveries and deaths 
   $query = "SELECT SUM(active_cases),SUM(recoveries),SUM(deaths) FROM caragaregion_municipalities;";   
   $rs = pg_query($con, $query) or die("Cannot execute query: $query\n");  
	
   //if there is return, values will be saved to the variables
   while ($row = pg_fetch_row($rs)) {  
	$active_cases = $row[0];
	$recoveries = $row[1];
    $deaths = $row[2];
   } 
   //closing the connection
   pg_close($con); 
?>

<!DOCTYPE html>
<html>
	<head>
	
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<title>CARAGA</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<link rel="stylesheet" href="leaflet/leaflet.css">
		<link rel="stylesheet" href="bootstrap/bootstrap.min.css">
		<script type="text/javascript" src="leaflet/leaflet.js"></script>
        <script type="text/javascript" src="jquery.min.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
	</head>
	<body>
	<style>
		html, body {
  		padding: 10px;
  		height: 100%;
		}
		#map {
		width: 60%;
		height: 500px;
		}
		#seachWrapper{
		margin-bottom: 10px
		}
		#loader{
		position:absolute;
		top: 50%;
		left: 45%;
		z-index: 99999;
		display: none;
		}


		body{
		margin: 0;
		padding: 0;
		background: #34495e;
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
		font-family: "montserrat",sans-serif;
		}

		.loading{
		width: 50px;
		height: 50px;
		box-sizing: border-box;
		border-radius: 50%;
		border-top: 10px solid #e74c3c;
		position: relative;
		animation: a1 2s linear infinite;
		}

		.loading::before,.loading::after{
		content: '';
		width: 50px;
		height: 50px;
		position: absolute;
		left: 0;
		top: -10px;
		box-sizing: border-box;
		border-radius: 50%;
		}

		.loading::before{
		border-top: 10px solid #e67e22;
		transform: rotate(120deg);
		}

		.loading::after{
		border-top: 10px solid #3498db;
		transform: rotate(240deg);
		}

		#loader span{
		
		
		font-size: 20px;
		color: rgb(4, 116, 243);
		
		}
		
		@keyframes a1 {
		to{
			transform: rotate(360deg);
		}
		}

		@keyframes a2 {
		to{
			transform: rotate(-360deg);
		}
		}


		h5{
  		text-align: center;
  		color: white;
  		display: inline-block;
  		width: 250px;
		}

        .modal-confirm {		
		color: #434e65;
		width: 525px;
		margin: 30px auto;
	}
	.modal-confirm .modal-content {
		padding: 20px;
		font-size: 16px;
		border-radius: 5px;
		border: none;
	}
	.modal-confirm .modal-header {
		background: #e85e6c;
		border-bottom: none;   
        position: relative;
		text-align: center;
		margin: -20px -20px 0;
		border-radius: 5px 5px 0 0;
		padding: 35px;
	}
	.modal-confirm h4 {
		text-align: center;
		font-size: 36px;
		margin: 10px 0;
	}
	.modal-confirm .form-control, .modal-confirm .btn {
		min-height: 40px;
		border-radius: 3px; 
	}
	.modal-confirm .close {
        position: absolute;
		top: 15px;
		right: 15px;
		color: #fff;
		text-shadow: none;
		opacity: 0.5;
	}
	.modal-confirm .close:hover {
		opacity: 0.8;
	}
	.modal-confirm .icon-box {
		color: #fff;		
		width: 95px;
		height: 95px;
		display: inline-block;
		border-radius: 50%;
		z-index: 9;
		border: 5px solid #fff;
		padding: 15px;
		text-align: center;
	}
	.modal-confirm .icon-box i {
		font-size: 58px;
		margin: -2px 0 0 -2px;
	}
	.modal-confirm.modal-dialog {
		margin-top: 80px;
	}
    .modal-confirm .btn {
        color: #fff;
        border-radius: 4px;
		background: #eeb711;
		text-decoration: none;
		transition: all 0.4s;
        line-height: normal;
		border-radius: 30px;
		margin-top: 10px;
		padding: 6px 20px;
		min-width: 150px;
        border: none;
    }
	.modal-confirm .btn:hover, .modal-confirm .btn:focus {
		background: #eda645;
		outline: none;
    }
    
    .municipality{
    text-align: center;
    background-color: #3498DB;
}
	</style>
		<div class="container">		
        
			<div class="row" id="seachWrapper">
				<form class="form-inline">
					<div class="form-group">
                    <input class="form-control" type="text" placeholder="type municipality" name="textInput" id="textInput" onkeypress="return /[a-zA-Z]/i.test(event.key)"></input>
                  
						<select class="form-control" id="typeOfQuery" onchange="mytypeOfQuery()">
						<option value="active_cases">Active cases</option>
						<option value="recoveries">Recoveries</option>
						<option value="deaths">Deaths</option>
						</select>
						
						<select class="form-control" id="greaterLess">
						<option value="<">Less than</option>
						<option value="=">Equal</option>
						<option value=">">Greater than</option>
						</select>

						<input class="form-control" type="number" placeholder="input number" id="numberInput"></input>
						<button type="button" class="btn btn-primary" id="btnGo">Go</button>
						<button type="button" class="btn btn-danger" id="btnClear">Clear</button>
					</div>
				</form>
			</div>
			<div class="row">
				<div class="text-center" id="loader">
					<div class="loading">
					</div>
					<span>Loading...</span>
				</div>
				<div id='map'></div>
			</div>
			<div id="foot_total">
			<h5 style="background-color:#e67e22">ACTIVE CASES: <p id="active_value">0</p></h5>
			<h5 style="background-color:#3498db">RECOVERIES: <p id="rec_value">0</p></h5>
			<h5 style="background-color:#e74c3c">DEATHS: <p id="deaths_value">0</p></h5>
			</div>
			
        </div>
        <!-- Modal HTML -->
<div id="myModal" class="modal fade">
	<div class="modal-dialog modal-confirm">
		<div class="modal-content">
			<div class="modal-header">
				<div class="icon-box">
					<i class="material-icons">&#xE5CD;</i>
				</div>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body text-center">
				<h4>Ooops!</h4>	
				<p id="mun_message"> </p>
				<button class="btn btn-success" data-dismiss="modal">Try Again</button>
			</div>
		</div>
	</div>
</div>  
		
        <script type="text/javascript">


    
/**Getting variable value from php to js and display on html 
    Total active cases, recoveries, and deaths */

    var active = "<?php echo $active_cases ?>";
    var rec = '<?php echo $recoveries; ?>';
    var death = '<?php echo $deaths; ?>';
    
    
    
    function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
    }
    
    const obj = document.getElementById("active_value");
    animateValue(obj, 0, active, 3000);
    
    const obj2 = document.getElementById("rec_value");
    animateValue(obj2, 0, rec, 3000);
    
    const obj3 = document.getElementById("deaths_value");
    animateValue(obj3, 0, death, 3000);

    


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

//adding the caragaregion layer from the geoserver to the map
var owsURI = 'http://127.0.0.1:8080/geoserver/covid19/ows';
var municipalities;
function load_municipalities(){
    municipalities = L.tileLayer.wms(owsURI, {
        layers: Selected_layer,
        format: 'image/png',
        transparent: true,
        attribution: "",
        zIndex: 100,
        opacity: .8,
        version: '1.1.0',
    }).addTo(map);
}

load_municipalities();

//changing selectedquery value
function mytypeOfQuery(){
    var selectquery = document.getElementById("typeOfQuery").value;
    
    if(selectquery == 'active_cases'){
        Selected_layer = 'covid19:active_cases';
        municipalities.remove();
    load_municipalities();
        legend.addTo(map);
    }
    
    else if(selectquery == 'recoveries'){
        Selected_layer = 'covid19:recoveries';
        municipalities.remove();
    load_municipalities();
        legend.addTo(map);
    }

    else if(selectquery == 'deaths'){
        Selected_layer = 'covid19:deaths';
        municipalities.remove();
    load_municipalities();
        legend.addTo(map);
    }

}


/** This function is used to create wfs request from the geoserver and the
geoserver it will return bounds that will be used to apply new map display */
function wfsRequest(filter){
    function getJsonCurrUtil(data){}
    var defaultParameters = {
        service: 'WFS',
        version: '2.0.0',
        request: 'GetFeature',
        typeName: Selected_layer,
        maxFeatures: 500,
        outputFormat: 'application/json',
        cql_filter: filter,
        format_options: 'callback: getJson',
        srsName: 'EPSG:4326'
    }

    var parameters = L.Util.extend(defaultParameters);
    var URL = owsURI + L.Util.getParamString(parameters);
    $.ajax({
        url : URL,
        dataType : 'json',
        jsonpCallback : 'getJson',
        beforeSend : function (){
            $('#loader').show();
        },
        success : function (response) {
            $('#loader').hide();
            if(response.features.length > 0){
                WFSLayer = L.geoJson(response, {
                    style: function (feature) {
                        return {
                            stroke: false,
                            fillColor: null,
                            fillOpacity: 0
                        };
                    }
                }).addTo(map);
                map.fitBounds(WFSLayer.getBounds());	
            }else{

                $("#mun_message").html($('#textInput').val() +" is not found in CARAGA REGION");
                $('#myModal').modal('show'); 

                load_municipalities();
                map.setView([9.1204, 125.59], 8);
            }
        }
    })
}



/** When map is clicked, a table popup will appear with the values of
    city/munipalities, active cases, recoveries, and deaths */
map.on("click", function(e) {
    var _layers = this._layers,
    layers = [],
    versions = [],
    styles = [];

    for (var x in _layers) {
        var _layer = _layers[x];
        if (_layer.wmsParams) {
            layers.push(_layer.wmsParams.layers);
            versions.push(_layer.wmsParams.version);
            styles.push(_layer.wmsParams.styles);
        }
    }
    
    var loc = e.latlng,
    xy = e.containerPoint,
    size = this.getSize(),
    bounds = this.getBounds(),
    crs = this.options.crs,
    sw = crs.project(bounds.getSouthWest()),
    ne = crs.project(bounds.getNorthEast()),
    obj = {
        service: "WMS",
        version: versions[0],
        request: "GetFeatureInfo",
        layers: layers,
        bbox: sw.x + "," + sw.y + "," + ne.x + "," + ne.y,
        width: size.x,
        height: size.y,
        query_layers: layers,
        info_format: "application/json", 
        feature_count: 1
    };
    if (parseFloat(obj.version) >= 1.3) {
        obj.crs = crs.code;
        obj.i = Math.round(xy.x);
        obj.j = Math.round(xy.y);
    } else {
        obj.srs = crs.code;
        obj.x = Math.round(xy.x);
        obj.y = Math.round(xy.y);
    }

    $.ajax({
        url: owsURI + L.Util.getParamString(obj, owsURI, true),
        beforeSend:function(){
        $('#loader').show();
    },
    success: function(data, status, xhr) {
        $('#loader').hide();
        var html = "<table  class='table table-striped'>";
        var found = true;
        if (data.features) {
            var features = data.features;
            if (features.length) {
                for (var i in features) {
                    var feature = features[i];                     
                    var properties=feature.properties;
                    
                    html+='<thead><tr><th colspan="2" class="municipality">'+properties['mun_name']+'</th></tr></thead><tbody>';
                    html+='<tr><th class = "active_cases">Active Cases:</th><td>'+properties['active_cases']+'</td></tr>';
                    html+='<tr><th class = "recoveries">Recoveries:</th><td>'+properties['recoveries']+'</td></tr>';
                    html+='<tr><th class = "deaths">Deaths:</th><td>'+properties['deaths']+'</td></tr>';
                    html+='</tbody></table>';
                }
            } else {
               found = false;
                $("#mun_message").html("Location not belong in CARAGA REGION");
                $('#myModal').modal('show');
            }
        } else {
            html += "Failed to Read the Feature(s).";
        }
        if(found){
            
            map.openPopup(html, loc,{maxHeight:500});
        }
        
    },
    error: function(xhr, status, err) {
        html += "Unable to Complete the Request.: " + err;
        map.openPopup(html, loc);
    }
    });
});

//Setting CQL filter from the input values
function set_cql(textInput,greaterLess, numberInput, typeOfQuery) {
    var cql_filter = [];
    if (textInput){
        cql_filter.push("mun_name LIKE '%"+textInput+"%'")
    }
    if (numberInput){
        cql_filter.push(typeOfQuery+" "+greaterLess+" "+numberInput)
    }
    
    if (cql_filter.length == 2){
        cql_filter.splice(1, 0, "AND");
    }
    if (cql_filter.length==0){
        cql_filter.push("1=1")
    }

    municipalities.setParams({
        CQL_FILTER: cql_filter.join(' ')
    })
    wfsRequest(cql_filter.join(' '));
}


$('#btnGo').click(function(){
    municipalities.remove();
    load_municipalities();
    var textInput = $('#textInput').val();
    var greaterLess = $('#greaterLess').val();
    var numberInput = $('#numberInput').val();
    var typeOfQuery = $('#typeOfQuery').val();
    WFSLayer = null;
    set_cql(textInput.toUpperCase(),greaterLess, numberInput, typeOfQuery);
    map.closePopup();
})
$('#btnClear').click(function(){
    $('#loader').hide();
    $('#textInput').val('');
    $('#numberInput').val('');
    municipalities.setParams({
        CQL_FILTER: '1=1',
    })
    map.closePopup();
    WFSLayer = null;
    map.setView([9.1204, 125.59], 8);
})
</script>






		
		
	</body>
	
	
</html>