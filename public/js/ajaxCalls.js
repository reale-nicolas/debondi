function sendContactsFormAjax(subject, message, email)
{
    $.ajax(
    {
        url: "http://debondi.test/api/contacts/",
        type: 'POST',
        data: {email:email, subject: subject, message: message},
        beforeSend: function() {
            $("#btnSubmitContactsForm").toggleClass("w3-hide");
            $("#btnLoadingContactsForm").toggleClass("w3-hide");
        },
        success: function (response) 
        {
            if (response.result === 'SUCCESS')
            {
                $("#formContact").trigger("reset");
                $.notify("Mensaje enviado satisfactoriamente", 
                { 
                    globalPosition:"top right", 
                    className: "success"
                });
                $('#divContactenosForm').hide();
            } else {
                $.notify("Hubo un error al intentar guardar los datos, por favor intente nuevamente.", 
                {
                    globalPosition:"top right", 
                    className: "error"
                });
            }
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

function getRoutes(latFrom, lngFrom, latTo, lngTo, distance)
{
    $.ajax(
    {
        url: "http://debondi.test/api/route/?latFrom="+latFrom+"&lngFrom="+lngFrom+"&latTo="+latTo+"&lngTo="+lngTo+"&maxDistance="+distance,
        type: 'GET',
        async: true,
        beforeSend: function() {
//            $("#divComoLLegoForm").html("");
        },
        success: function (response) 
        {
            if (response.result === 'SUCCESS')
            {
                document.getElementById("divComoLLegoForm").innerHTML = '';
                var option = response.data;
                
                var div = document.createElement('div');
//                var ul = document.createElement('ul');
                for(var i = 0; i < option.length; i++)
                {
                    var a = document.createElement("a");
                    
                    a.className = "w3-bar-item w3-button";
                    a.href = '#';
                    var li = document.createElement("li");
                    for(var j = 0; j < option[i].route.length; j++)
                    {
                        a.id = "a-route-option-"+option[i].route[j].line+option[i].route[j].ramal.toLowerCase();
                        var img = document.createElement("img");
                        img.src = "http://debondi.test/images/"+option[i].route[j].line+option[i].route[j].ramal.toLowerCase()+".png";
                        img.style = "width:30px";
                        
                        a.appendChild(img);
                        
                        
//                        li.appendChild(img);
                    }
//                    ul.appendChild(li);
                    div.appendChild(a);
                }
                
                

                document.getElementById("divComoLLegoForm").appendChild(div);
            }
            
        },
        complete: function() {
//            $("#div-loading-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
//            $("#input-checkbox-corredor-"+corredorName+"-ramal-"+ramalName).toggleClass("w3-hide");
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