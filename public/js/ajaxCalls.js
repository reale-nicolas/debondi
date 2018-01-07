function sendContactsFormAjax(subject, message, email)
{
    $.ajax(
    {
        url: "http://debondi.test/api/contacts/",
        type: 'POST',
        data: {subject: subject, message: message, email:email},
        beforeSend: function() {
            $("#btnSubmitContactsForm").toggleClass("w3-hide");
            $("#btnLoadingContactsForm").toggleClass("w3-hide");
        },
        success: function (response) 
        {
            $("#formContact").trigger("reset");
            $.notify("Mensaje enviado satisfactoriamente", 
            { 
                globalPosition:"top right", 
                className: "success"
            });
            $('#divContactenosForm').hide();
        },
        complete: function() {
            $("#btnSubmitContactsForm").toggleClass("w3-hide");
            $("#btnLoadingContactsForm").toggleClass("w3-hide");
        },
        error: function() {
            $.notify("Ocurrió un error al intentar enviar el mensaje.", 
            { 
                globalPosition:"top right", 
                className: "error"
            });
        }
    });
}


function getLineRouteAjax(corredorName, ramalName)
{
    $.ajax(
    {
        url: "http://debondi.test/api/lines/?line="+corredorName+"&ramal="+ramalName+"&detailed=1",
        type: 'GET',
        async: true,
        beforeSend: function() {
            $("#div-loading-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
            $("#input-checkbox-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
        },
        success: function (response) 
        {
            var arrBuses    = response.data;
            
            for(var i = 0; i < arrBuses.length; i++) 
            {
                var arrPolyLine = [];
                arrPolyLine['line'] = corredorName;
                arrPolyLine['ramal'] = ramalName;
                arrPolyLine['zone'] = arrBuses[i].zone;
            
                var rutaBusCoordenadas = []; 

                for (var j = 0; j < arrBuses[i].stops.length; j++) 
                {
                    rutaBusCoordenadas[j] = {id:arrBuses[i].stops[j].id, lat: parseFloat(arrBuses[i].stops[j].latitude), lng: parseFloat(arrBuses[i].stops[j].longitude)};
//                    console.log("index: "+j+" - lat: "+arrBuses[i].stops[j].latitude+" lng: "+arrBuses[i].stops[j].longitude);
                }

                var PolyLine = new google.maps.Polyline({
                    path: rutaBusCoordenadas,
                    geodesic: true,
                    strokeColor: getRandomColor(),
                    strokeOpacity: 0.8,
                    strokeWeight: 5
                });
                arrPolyLine['polyLine'] = PolyLine;
                arrPolyLine['lineRoute'] = rutaBusCoordenadas;
                
                arrRoutesPolylines.push(arrPolyLine);
            }
            
            
            showBusRouteOnMap(corredorName, ramalName);
            
        },
        complete: function() {
            $("#div-loading-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
            $("#input-checkbox-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
        }
    });
}