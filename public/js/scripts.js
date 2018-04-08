var map;
var infowindow;
var arrCorredores   = [];

var arrStopsMarkers     = [];
var arrRoutesPolylines  = [];

var arrMarker                   = [];

var arrAutocomplete             = [];
var arrInput                    = [];

var marker;

var componentForm = {
    street_number: 'short_name',
    route: 'short_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name'
};

function initializeApplication()
{
    initializeForms();
    initializeMap();
//    initializeBusesStops();
    initializeAlerts();
    initializeAutocompleteMenu();
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

function initializeAutocompleteMenu()
{
    //Cuadricula en la que debe priorizar las busquedas google del autocompletado
    var defaultBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(-24.675531, -65.593153),
        new google.maps.LatLng(-24.948735, -65.329512)
    );

    //Seteamos la cuadricula, limitamos resultados a Argentina e indicamos que se buscaran direcciones
    var options = {
        types: ['address'],
        bounds: defaultBounds,
        componentRestrictions: {
            country: 'ar'
        }
    };

    arrInput['origen'] = document.getElementById('origen-input');
    arrInput['destino'] = document.getElementById('destino-input');


    //event fired when user selects direction from list direction.
    //Here we have to make a marker into maps showing the point selected by user.
    arrAutocomplete['origen'] = new google.maps.places.Autocomplete(arrInput['origen'], options);
    //arrAutocomplete['origen'].bindTo('bounds', map);

    google.maps.event.addListener(arrAutocomplete['origen'], 'place_changed', function ()
    {
        onPlaceListItemSelected(arrAutocomplete['origen'].getPlace(), "origen");
    });


    arrAutocomplete['destino'] = new google.maps.places.Autocomplete(arrInput['destino'], options);
    google.maps.event.addListener(arrAutocomplete['destino'], 'place_changed', function ()
    {
        onPlaceListItemSelected(arrAutocomplete['destino'].getPlace(), "destino");
    });
}
    
    
function initializeForms()    
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
    marker = new google.maps.Marker(
    {
        map: map
    });
    
    
    if(  isMobile.any() !== null ) 
    {
        google.maps.event.addListener(map, 'dblclick', function(mouseEvent) 
        {
            displayMenuOptionOnMap(mouseEvent.latLng);
            mouseEvent.preventDefault();
            return;
        });
    } 
    else 
    {
        map.addListener('rightclick', function(mouseEvent) 
        {
            displayMenuOptionOnMap(mouseEvent.latLng);
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


function onPlaceListItemSelected(place, tipoDireccion)
{
//        var place = inputAddress.getPlace();
    //Verificamos que la varibale tipoDireccion se encuentre seteada
    //y que su valor pertenezca a los valores aceptados.
    if ((tipoDireccion !== 'origen' && tipoDireccion !== 'destino') || (!place.geometry))
    {
        return null;
    }
    //Limpiamos los valores que puedan tener los campos de direccion, pues se deben rellenar 
    //con la nueva direccion seleccionada.
    unsetVaribales(tipoDireccion);

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport)
    {
        map.fitBounds(place.geometry.viewport);
    } else
    {
    map.setCenter(place.geometry.location);
        map.setZoom(16);
    }

    marker = new google.maps.Marker(
    {
        map: map,
        visible: true,
        position: place.geometry.location,
    });
    arrMarker[tipoDireccion] = marker;
    

    // Get each component of the address from the place details
    // and fill the corresponding field on the form.
    for (var i = 0; i < place.address_components.length; i++)
    {
        var addressType = place.address_components[i].types[0];
        if (componentForm[addressType])
        {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById(tipoDireccion + "-" + addressType).value = val;
        }
    }
    document.getElementById(tipoDireccion + "-input").value = place.formatted_address;
    document.getElementById(tipoDireccion + "-latitud").value = place.geometry.location.lat();
    document.getElementById(tipoDireccion + "-longitud").value = place.geometry.location.lng();

    document.getElementById(tipoDireccion + "-h4").innerHTML = 
    document.getElementById(tipoDireccion + "-route").value +" "+
    document.getElementById(tipoDireccion + "-street_number").value;
    

    
    console.log(JSON.stringify(place));
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


function displayMenuOptionOnMap(latlng)
{
    marker.setPosition(latlng);
    marker.setVisible(true);

    var geocoder = new google.maps.Geocoder;
    geocoder.geocode({'location': latlng}, function(results, status) 
    {
        if (status === 'OK') 
        {
            if (results[0]) 
            {
                console.log(results[0]);
                var street = getAddressComponenet(results[0], "route");
                var door_number = getAddressComponenet(results[0], "street_number");
                var locality = getAddressComponenet(results[0], "administrative_area_level_2");
                var province = getAddressComponenet(results[0], "administrative_area_level_1");

                var placeId = results[0].place_id;

                if (street === "Unnamed Road") {
                    street = "Calle S/N";
                }
                if (door_number === null) {
                    door_number = "S/N";
                }
                if (locality === null) {
                    locality = "";
                }
                if (province === null) {
                    province = "";
                }

                infowindow.setContent('\
                    <div class="" style="min-width:200px">'+
                        '<strong>'+
                            street +' '+ door_number +' - ' +
                            locality +
                        '</strong><br>' +
                        results[0].geometry.location + '<br><br>' +
                        '<div class="col-xs-6 text-left"><a href="#" role="button" onclick="setOrigenDestino(\''+placeId+'\', \'origen\')">Desde aqui</a></div>'+
                        '<div class="text-right"><a href="#" role="button" onclick="setOrigenDestino(\''+placeId+'\', \'destino\')">Hasta aqui</a></div>'+
                    '</div>'
                );
                infowindow.open(map, marker);
            } else {
                window.alert('No results found');
            }
        } else {
            window.alert('Geocoder failed due to: ' + status);
        }
    });
}

function bindMarkerInfowindow(marker, message) 
{
    marker.addListener('click', function() {
        infowindow.setContent(message);
        infowindow.open(map, marker);
    });
}


function unsetVaribales(tipoDireccion)
{
    if (typeof arrMarker[tipoDireccion] !== 'undefined' && arrMarker[tipoDireccion] !== null)
        arrMarker[tipoDireccion].setVisible(false);
    arrMarker[tipoDireccion] = null;

    for (var component in componentForm)
    {
        document.getElementById(tipoDireccion + "-" + component).value = '';
    }
    document.getElementById(tipoDireccion + "-latitud").value = '';
    document.getElementById(tipoDireccion + "-longitud").value = '';

    return;
}


function setOrigenDestino(placeId, origenDestino)
{
    var request = {
        placeId: placeId
    };
    var service = new google.maps.places.PlacesService(map);
    service.getDetails(request, function(place, status){
        infowindow.close();
        onPlaceListItemSelected(place, origenDestino);
    });

}
    

function getAddressComponenet(address, component)
{
    for (var i=0; i<address.address_components.length; i++) {
        for (var j=0; j<address.address_components[i].types.length; j++) {
            console.log(address.address_components[i].types[j]);
            if (address.address_components[i].types[j] === component) {
                return address.address_components[i].long_name;
            }
        }
    }
    return null;
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

