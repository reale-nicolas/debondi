var map;
var infowindow;
var arrCorredores   = [];

var arrStopsMarkers     = [];
var arrRoutesPolylines  = [];


function initializeApplication()
{
    initializeFroms();
    initializeMap();
    initializeBusesStops();
    initializeAlerts();
}
    
function initializeAlerts()
{
    $.notify("Recuerde usar la aplicacion con precaución," +
            " la misma fue desarrollada tomando información de la web de SAETA,"+ 
            " la cuál puede ser incorrecta o estar desactualizada.", 
        { 
            globalPosition:"top right", 
            className: "warn",
            autoHide: false
        }
    );
}


function initializeFroms()    
{
    var form = document.getElementById('formContact');
    form.addEventListener('submit', function(event) 
    {
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
}


function initializeMap()
{
    var arrScreenDimension = getWidthAndHeightScreen();
    document.getElementById("map").style.height = arrScreenDimension.height + "px";
        
    //Iniciamos el mapa
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 12,
        center: new google.maps.LatLng(-24.7931342, -65.4303173),
        streetViewControl: false,
        mapTypeControl: false,
        mapTypeControlOptions: {
        }
    });
    
    infowindow = new google.maps.InfoWindow();
    
    if(  isMobile.any() !== null ) 
    {
        google.maps.event.addListener(map, 'dblclick', function(mouseEvent) 
        {
//            displayMenuOptionOnMap(mouseEvent.latLng);
            mouseEvent.preventDefault();
            return;
        });
    } 
    else 
    {
        map.addListener('rightclick', function(mouseEvent) 
        {
//            displayMenuOptionOnMap(mouseEvent.latLng);
        });
    }
}

function initializeBusesStops()
{
    $.ajax(
    {
        url: "http://debondi.test/api/stops",
        type: 'GET',
        async: true,
        success: function (response) 
        {
            var arrBusesStop    = response.data;
            
            for(var i = 0; i < arrBusesStop.length; i++) 
            {
                var markerAux = [];
                var marker = new google.maps.Marker(
                {
                    map: map,
                    visible: false,
                    position: {lat: parseFloat(arrBusesStop[i].latitude), lng: parseFloat(arrBusesStop[i].longitude)},
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 0.7,
                        strokeOpacity: 0.8,
                        strokeWeight: 2
                    }
                });
         
                markerAux['id']         = arrBusesStop[i].id;
                markerAux['name']       = arrBusesStop[i].name;
                markerAux['latitude']   = arrBusesStop[i].latitude;
                markerAux['longitude']  = arrBusesStop[i].longitude;
                markerAux['marker']     = marker;
                markerAux['instances']   = 0;
                
                arrStopsMarkers.push(markerAux);
                
                bindMarkerInfowindow(marker, "<b>Id:</b> "+arrBusesStop[i].id+" - <b>Buses:</b> "+arrBusesStop[i].name+"</br> \n\
                    <b>lat:</b> "+arrBusesStop[i].latitude+", <b>lng:</b>: "+arrBusesStop[i].longitude);
            }
            
            /* Change markers on zoom */
            google.maps.event.addListener(map, 'zoom_changed', function() 
            {
                var zoom = map.getZoom();
                // iterate over markers and call setVisible
                for (i = 0; i < arrStopsMarkers.length; i++) 
                {
                    arrStopsMarkers[i]['marker'].setVisible(zoom > 12);
                    
                    if (zoom > 15 && arrStopsMarkers[i]['instances'] < 1) {
                        arrStopsMarkers[i]['marker'].setIcon({
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 1,
                            strokeOpacity: 0.8,
                            strokeWeight: 4
                        });
                    } else if (zoom <= 15 && arrStopsMarkers[i]['instances'] < 1){
                        arrStopsMarkers[i]['marker'].setIcon({
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 0.7,
                            strokeOpacity: 0.8,
                            strokeWeight: 2
                        });
                    }
                }
            });
        }
    });
}


function hideBusRouteOnMap(line, ramal)
{
    var result = false;
    for (var i = 0; i < arrRoutesPolylines.length; i++)
    {
        if (arrRoutesPolylines[i]['line'] === line && arrRoutesPolylines[i]['ramal'] === ramal)
        {
            //Ocultamos la ruta trazada para el corredor y ramal especificado.
            arrRoutesPolylines[i]['polyLine'].setMap(null);
            
            var polylinePath = arrRoutesPolylines[i]['lineRoute'];
            
            for (var j = 0; j < polylinePath.length; j++)
            {
                for (var k = 0; k < arrStopsMarkers.length; k++) 
                {
                    if (polylinePath[j].id === arrStopsMarkers[k]['id'])
                    {
                        if (arrStopsMarkers[k]['instances'] <= 1) 
                        {
                            arrStopsMarkers[k]['marker'].setAnimation(null);
                            arrStopsMarkers[k]['marker'].setIcon({
                                path: google.maps.SymbolPath.CIRCLE,
                                scale: 0.7,
                                strokeOpacity: 0.8,
                                strokeWeight: 2
                            });
                        }
                        arrStopsMarkers[k]['instances'] = arrStopsMarkers[k]['instances'] - 1;
                    }
                }
                
            }
            
            result = true;
        }
    }
    
    return result;
}


function showBusRouteOnMap(line, ramal)
{
    var result = false;
    for (var i = 0; i < arrRoutesPolylines.length; i++)
    {
        if (arrRoutesPolylines[i]['line'] === line && arrRoutesPolylines[i]['ramal'] === ramal)
        {
            //Mostramos la ruta trazada para el corredor y ramal especificado.
            arrRoutesPolylines[i]['polyLine'].setMap(map);
            
            var polylinePath = arrRoutesPolylines[i]['lineRoute'];
            
            for (var j = 0; j < polylinePath.length; j++)
            {
                for (var k = 0; k < arrStopsMarkers.length; k++) 
                {
                    if (polylinePath[j].id === arrStopsMarkers[k]['id'])
                    {
                        arrStopsMarkers[k]['instances'] = arrStopsMarkers[k]['instances'] + 1;
                        arrStopsMarkers[k]['marker'].setAnimation(google.maps.Animation.DROP);
                        arrStopsMarkers[k]['marker'].setIcon({
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 3,
                            fillColor: "#00F",
                            fillOpacity: 0.8,
                            strokeWeight: 1
                        });
                    }
                }
                
            }


            
            result =  true;
        }
    }
    
    return result;
}




function bindMarkerInfowindow(marker, message) 
{
    marker.addListener('click', function() {
        infowindow.setContent(message);
        infowindow.open(map, marker);
    });
}





var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};



function getWidthAndHeightScreen()
{
    var myWidth = 0, myHeight = 0;
    if (typeof (window.innerWidth) === 'number')
    {
        //No-IE 
        myWidth = window.innerWidth;
        myHeight = window.innerHeight;
    } else if (document.documentElement &&
            (document.documentElement.clientWidth || document.documentElement.clientHeight))
    {
        //IE 6+ 
        myWidth = document.documentElement.clientWidth;
        myHeight = document.documentElement.clientHeight;
    } else if (document.body && (document.body.clientWidth || document.body.clientHeight))
    {
        //IE 4 compatible 
        myWidth = document.body.clientWidth;
        myHeight = document.body.clientHeight;
    } else
    {
        myWidth = 600;
        myHeight = 800;
        alert("Imposible determinar alto y ancho de la pantalla");
    }

    var windowProperties = {
        "width": myWidth,
        "height": myHeight
    };

    return windowProperties;
}







function getRandomColor()
{
    var lum = 0;
    var hex = Math.floor(Math.random()*16777215).toString(16);
    // validate hex string
    hex = String(hex).replace(/[^0-9a-f]/gi, '');
    if (hex.length < 6) {
        hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    }
    lum = lum || 0;

    // convert to decimal and change luminosity
    var rgb = "#", c, z;
    for (z = 0; z < 3; z++) {
        c = parseInt(hex.substr(z*2,2), 16);
        c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
        rgb += ("00"+c).substr(c.length);
    }
    
    return rgb;
}

            
function w3_open() 
{
    document.getElementById("btnDisplayMenu").style.display = "none";
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}

function w3_close() 
{
    document.getElementById("btnDisplayMenu").style.display = "block";
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}

function showHideMenuOption(idElementToShow, arrIdElementToHide) 
{
    if (idElementToShow !== null) 
    {
        var x = document.getElementById(idElementToShow);

        if (x.className.indexOf("w3-show") === -1) 
        {
            x.className += " w3-show";
        } 
        if (x.className.indexOf("w3-hide") > -1) 
        {
            x.className = x.className.replace("w3-hide", "");
        }
    }
    if (arrIdElementToHide !== null) 
    {
        arrIdElementToHide.forEach(function(element ) {
            var x = document.getElementById(element);
            if (x.className.indexOf("w3-show") > -1) 
            {
                x.className = x.className.replace("w3-show", "");
            } 
            if (x.className.indexOf("w3-hide") === -1) 
            {
                 x.className += " w3-hide";
            }
        });
    }
}

