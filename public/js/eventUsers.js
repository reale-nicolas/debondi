function onMenuSelectRamal(corredorName, ramalName)
{
    var checkbox = document.getElementById("li-corredor-"+corredorName+"-ramal-"+ramalName).getElementsByTagName('input')[0];

    if (checkbox.checked)
    {
        checkbox.checked = false;
        hideBusRouteOnMap(corredorName, ramalName);
        
    } else {
        checkbox.checked = true;
        
        if (showBusRouteOnMap(corredorName, ramalName) === false)
        {
            getLineRouteAjax(corredorName, ramalName);
        }
        
        checkbox.checked = true;
    }
}
