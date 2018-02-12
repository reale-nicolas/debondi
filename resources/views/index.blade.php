<!DOCTYPE html>
<html>
    <title>Que Bondi Salta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">-->
    <!--<link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}">-->
    
    <!--    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">-->
    <link rel="stylesheet" href="{{ URL::asset('css/w3.css') }}">
    
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=RobotoDraft' type='text/css'>
    
    <!--<link rel="stylesheet" href="{{ URL::asset('css/font-awesome.min.css') }}">-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/flags.css') }}">
    
    <style>
        html,body,h1,h2,h3,h4,h5 {font-family: "RobotoDraft", "Roboto", sans-serif;}
        .w3-bar-block .w3-bar-item{padding:16px}
    </style>
    
    
    
    <style type="text/css">
        .loader {
            border: 16px solid #f3f3f3; /* Light grey */
            border-top: 16px solid #3498db; /* Blue */
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }
        
        .loader-menu-option {
            border: 3px solid #c1c0c0; /* Light grey */
            border-top: 3px solid #3498db; /* Blue */
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        #menuDivReference:hover{
            animation: opac 0.8s;
        }
    </style>
    <body onload="initializeApplication()">
        
        <!-- Side Navigation -->
        <nav class="w3-sidebar w3-bar-block w3-collapse w3-white w3-animate-left w3-card-2" 
             style="z-index:3;width:300px;" id="mySidebar">
            <!---------------------------------------------------->
            <div class="w3-bar-item w3-border-bottom w3-large w3-center">
                <img src="{{ URL::asset('images/logoSALTA.JPG') }}" style="width:50%;">
            </div>
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-hide-large w3-large" 
               onclick="w3_close()" title="Close Sidemenu" style="border-top:2px solid;">Close 
                <i class="fa fa-remove"></i>
            </a>
            
<!--            <div class="alert alert-warning alert-dismissable fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                <strong>Precaución.</strong> Recuerde usar la aplicacion con precaución, la misma fue desarrollada tomando
                información de la web de SAETA, la cuál puede ser incorrecta o estar desactualizada.
            </div>-->
            
            <!---------------OPCION ¿COMO LLEGO?------------------------------------->
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-red w3-button w3-hover-black w3-left-align" 
               onclick="showHideMenuOption('divComoLLegoForm', new Array('divRecorridosLineaList','divConfiguracion'))"  
               style="border-top:2px solid;">
                <i class="fa fa-search w3-margin-right"></i>
                ¿Cómo llego?
            </a>
            <div id="divComoLLegoForm" class="w3-show w3-animate-left">
                <div class="w3-container">
                    <br>   
                    <form action="#" class="">
                        <p>
                            <div class="w3-row">
                                <div class="w3-col">
                                    <div class="w3-row">
                                        <div class="input-group">
                                            <span class="input-group-addon" style="cursor:pointer" onclick="getLocation()" id="origen-span-addon">
                                                <i class="fa fa-map-marker"></i>
                                            </span>
                                            <label for="destino-input" class="control-label sr-only">Desde:</label>
                                            <input type="text" id="origen-input" class="w3-input input-lg form-control" placeholder="Desde" tabindex="1" 
                                               aria-describedby="origen-span-addon"/>
                                        </div>

                                        <!---------------------------->
                                        <input type="hidden" id="origen-street_number"/>
                                        <input type="hidden" id="origen-route"/>
                                        <input type="hidden" id="origen-locality"/>
                                        <input type="hidden" id="origen-administrative_area_level_1"/>
                                        <input type="hidden" id="origen-postal_code"/>
                                        <input type="hidden" id="origen-country"/>
                                        <input type="hidden" id="origen-latitud"/>
                                        <input type="hidden" id="origen-longitud"/>
                                    </div>
                                    <div class="w3-row">
                                        <div class="w3-center">
                                            <a href="#" id="btn-address-invest" class="fa fa-refresh" style="color:#337ab7" onclick="investAdsress()"></a>
                                        </div>
                                    </div>
                                    <div class="w3-row">
                                        <div class="input-group">
                                            <span class="input-group-addon" style="cursor:pointer" id="destino-span-addon">
                                                <i class="fa fa-map-marker"></i>
                                            </span>
                                            <label for="destino-input" class="control-label sr-only">Hasta:</label>
                                            <input type="text" id="destino-input" class="w3-input input-lg form-control" placeholder="Hasta" tabindex="2" 
                                                   aria-describedby="destino-span-addon"/>
                                        </div>
                                        <!---------------------------->
                                        <input type="hidden" id="destino-street_number"/>
                                        <input type="hidden" id="destino-route"/>
                                        <input type="hidden" id="destino-locality"/>
                                        <input type="hidden" id="destino-administrative_area_level_1"/>
                                        <input type="hidden" id="destino-postal_code"/>
                                        <input type="hidden" id="destino-country"/>
                                        <input type="hidden" id="destino-latitud"/>
                                        <input type="hidden" id="destino-longitud"/>
                                    </div>
                                </div>
                            </div>
                        </p>
                        <div class="w3-row">
                            <div class="w3-col">
                                <div class="input-group w3-margin-top"><p>
                                    <label for="distancia-input">Distancia maxima a caminar: </label><label id="lblDistancia"> 500 mts.</label>
                                    <input type="range" id="distancia-input" class="form-control" min="150" max="1600" value="800" 
                                           step="10" oninput="$('#lblDistancia').text(' '+value+' mts.') " tabindex="3"/></p>
                                </div>
                            </div>
                        </div>
                        <br/>
                        <div class="form-group text-right">
                            <button id="btnCalcularRutaOptima" class="btn btn-primary btn-lg" tabindex="4">Calcular</button>
                        </div>
                    </form>
                </div>
                
            </div>
            
            
            <!---------------OPCION RECORRIDOS X LINEA------------------------------------->
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-dark-grey w3-button w3-hover-black w3-left-align" 
               onclick="showHideMenuOption('divRecorridosLineaList', new Array('divComoLLegoForm','divConfiguracion'))" style="border-top:2px solid;">
                <i class="fa fa-bus w3-margin-right"></i>
                Recorridos x linea
            </a>
            
            <div id="divRecorridosLineaList" class="w3-hide w3-animate-left">
                @foreach ($lines as $line => $arrRamal)
                    <a id="a-corredor-{{ $line }}" href="#" class="w3-bar-item w3-button" onclick="$('#div-corredor-{{ $line }}').toggleClass('w3-hide');">
                        <i class="fa fa-bus w3-margin-right"></i>
                        <span>Línea {{ $line }} </span>
                    </a>
                
                    <div id="div-corredor-{{ $line }}" class="w3-hide">
                        <ul id="ul-corredor-{{ $line }}" class="w3-ul w3-right" style="width:90%">
                            
                            @foreach ($arrRamal as $ramal => $arrZone)
                                <li id="li-corredor-{{ $line }}-ramal-{{ $ramal }}" class="w3-padding-16"
                                    style="cursor: pointer; background-color: rgb(255, 255, 255);" 
                                    onclick="onMenuSelectRamal('{{ $line }}','{{ $ramal }}')">
                                    <span class="w3-button w3-white w3-right" style="padding: 0px;height: 20px;">
                                        <div id="div-loading-corredor-{{ $line }}-ramal-{{ $ramal }}" class="loader-menu-option w3-hide" 
                                            style="width:20px;height:20px;">

                                        </div>
                                        <input type="checkbox" id="input-checkbox-corredor-{{ $line }}-ramal-{{ $ramal }}" class="w3-check"
                                               style="cursor:pointer;margin:0px;top:0px;width:20px;height:20px;" 
                                               onclick="onMenuSelectRamal('{{ $line }}','{{ $ramal }}')">
                                    </span>
                                    <img class="w3-left w3-margin-right" style="width:30px" 
                                         src="http://debondi.test/images/{{ $line }}{{ strtolower($ramal) }}.png">
                                    <span>Ramal  {{ $ramal }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            
            
            <!---------------OPCION CONFIGURACION------------------------------------>
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-dark-grey w3-button w3-hover-black w3-left-align" 
               onclick="showHideMenuOption('divConfiguracion', new Array('divComoLLegoForm','divRecorridosLineaList'))" 
               style="border-top:2px solid;">
                <i class="fa fa-cogs w3-margin-right"></i>
                Configuración
            </a>
            <div id="divConfiguracion" class="w3-hide w3-animate-left">
                <a href="#" class="w3-bar-item w3-button" onclick="$('#div-config-idioma').toggleClass('w3-hide')">
                   <i class="fa fa-globe w3-margin-right"></i>
                   Idioma
                </a>
                <div id="div-config-idioma" class="w3-hide">
                    <ul id="ul-config-idioma" class="w3-ul w3-right" style="width:90%">
                        <li id="li-config-idioma-espanol" class="w3-padding-16"
                            style="cursor: pointer; background-color: rgb(255, 255, 255);"">
                            <span class="w3-button w3-white w3-right" style="padding: 0px;height: 20px;">
                                <div id="div-loading-config-idioma-espanol" class="loader-menu-option w3-hide" 
                                    style="width:20px;height:20px;">

                                </div>
                                <input type="radio" id="input-radio-config-idioma-espanol" class="w3-check" checked="checked"
                                       style="cursor:pointer;margin:0px;top:0px;width:20px;height:20px;">
                            </span>
                            <img src="{{ URL::asset('images/blank.gif') }}" class="w3-left w3-margin-right flag flag-es" alt="Español" />
<!--                            <img class="" style="width:30px" 
                                 src="http://debondi.test/images/{{ $line }}{{ strtolower($ramal) }}.png">-->
                            <span>Español</span>
                        </li>
                        
                        
                    </ul>
                </div>
            </div>
            
            
            <!---------------OPCION CONTACTENOS------------------------------------->
            <a href="javascript:void(0)" class="w3-bar-item w3-button w3-dark-grey w3-button w3-hover-black w3-left-align" 
               onclick="document.getElementById('divContactenosForm').style.display='block'" style="border-top:2px solid;">
                <i class="fa fa-comment w3-margin-right"></i>
                Contactenos
            </a>
        </nav>

        <!-- Overlay effect when opening the side navigation on small screens -->
        <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="Close Sidemenu" id="myOverlay"></div>

        <!-- Page content -->
        <div class="w3-main" style="margin-left:300px;">
            <i id="btnDisplayMenu" class="fa fa-bars w3-red w3-button  w3-hide-large w3-xlarge " 
               style="display: block; position: fixed; z-index: 999" onclick="w3_open()"></i>
            <!--<a href="javascript:void(0)" class="w3-hide-large w3-red w3-button w3-right  w3-margin-right" onclick="document.getElementById('id01').style.display = 'block'"><i class="fa fa-pencil"></i></a>-->

            <!--<div class="person">-->
            <div id="map" style="position: static">
            </div>
                
            <!--</div>-->
        </div>
        
        
        
        <!-- Modal that pops up when you click on "New Message" -->
        <div id="divContactenosForm" class="w3-modal" style="z-index:4">
            <div class="w3-modal-content w3-animate-zoom">
                <div class="w3-container w3-padding w3-red">
                    <span onclick="document.getElementById('divContactenosForm').style.display = 'none'"
                          class="w3-button w3-red w3-right w3-xxlarge"><i class="fa fa-remove"></i></span>
                    <h2>Contáctenos</h2>
                </div>
                <div class="w3-panel">
                    <form id="formContact" action="/" novalidate>
                        <div class="input-group w3-margin-bottom">
                            <input type="text" id="txtSubject" name="txtSubject" class="w3-input w3-border" placeholder="Asunto">
                        </div>
                        
                        <div class="input-group w3-margin-bottom">
                            <input type="text" id="txtEmail" name="txtEmail" class="w3-input w3-border" placeholder="Tu E-mail">
                        </div>
                        
                        <div class="input-group w3-margin-bottom">
                            <textarea  name="txtMessage" id="txtMessage" class="w3-input w3-border" placeholder="Mensaje" style="height:150px"></textarea>
                            <!--<input type="text" class="form-control w3-input w3-border" placeholder="Mensaje" required style="height:150px">-->
                        </div>
                        
                        <div class="w3-section">
                            <button type="button" class="w3-button w3-red" onclick="document.getElementById('divContactenosForm').style.display = 'none'">
                                Cancelar <i class="fa fa-remove"></i>
                            </button>
                            <button type="submit" id="btnSubmitContactsForm" class="w3-button w3-light-grey w3-right">
                                Enviar <i class="fa fa-paper-plane"></i>
                            </button>
                            <button type="button" id="btnLoadingContactsForm" class="w3-button w3-light-grey w3-right w3-hide">
                                Enviando... <i class="fa fa-spinner fa-spin fa-fw"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<!--<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>-->


<script 
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB3FxKEgf10vNGHSUUYms4rl8cusliiVgM&libraries=geometry,places">
</script><!--
    -->
    
    
    <!-- jQuery library -->
    <!--<script src="https://code.jquery.com/jquery-latest.js"></script>-->
    <!--<script src="{{ URL::asset('js/jquery-latest.js') }}"></script>-->
<script src="{{ URL::asset('js/jquery-3.2.1.min.js') }}"></script>
    <!--<script src="{{ URL::asset('js/jquery-migrate-1.4.1.min.js') }}"></script>-->
    <!--<script src="{{ URL::asset('js/jquery-migrate-3.0.0.min.js') }}"></script>-->
   
<script src="{{ URL::asset('js/jquery.validate.min.js') }}"></script>
    <!-- Latest compiled JavaScript -->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>-->
    <!--<script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>-->

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>

    <script src="{{ URL::asset('js/notify.min.js') }}"></script>
    <!-- Scripts propios -->
    <script src="{{ URL::asset('js/spin.min.js') }}"></script>
    <script src="{{ URL::asset('js/scripts.js') }}"></script>
    <script src="{{ URL::asset('js/ajaxCalls.js') }}"></script>
    <script src="{{ URL::asset('js/eventUsers.js') }}"></script>
    <!--<script src="{{ URL::asset('js/callbackFunctions.js') }}"></script>-->
    <!--<script src="{{ URL::asset('js/ContextMenu.js') }}"></script>-->
    
    <script>
        $( document ).ready(function() 
        {
            $("#formContact").validate({
                submitHandler: function(form) {
                    var subject = $("#txtSubject").val();
                    var message = $("#txtMessage").val();
                    var email   = $("#txtEmail").val(); 
                    sendContactsFormAjax(subject, message, email);
                    console.log("submitt");
                },
                rules: {
                    txtSubject: {
                        required: true
                    },
                    txtEmail: {
                        required: true,
                        email: true
                    },
                    txtMessage: {
                        required: true
                    }
                },
                messages: {
                    txtSubject: "Por favor ingrese el asunto.",
                    txtEmail: {
                        email: "La dirección de email ingresada es incorrecta"
                    },
                    txtMessage: "Por favor ingrese el mensaje.",
                }
            });
        });
//            );
            
    </script>
    </body>
</html>