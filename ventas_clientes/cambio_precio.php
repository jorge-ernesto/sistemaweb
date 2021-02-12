<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
	<head>
		<title>Sistema de Ventas - REPORTE DE VALES DIARIOS</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script type="text/javascript">
		var distancia=300;
		var int_contador=0,int_contador_monto_modif=0;
		var monto_actual_fact=parseFloat("0.00");
		var ch_cliente_guardado="";
		var ArregloCliente=new Array();
		var ArregloCliente_montoModificado=new Array();
		/**/
		function formato_numero(numero, decimales, separador_decimal, separador_miles){ // v2007-08-06
			numero=parseFloat(numero);
			if(isNaN(numero)) {
				return "";
			}

			if(decimales!==undefined) {
			// Redondeamosf
				numero=numero.toFixed(decimales);
			}
			// Convertimos el punto en separador_decimal
			numero=numero.toString().replace(".", separador_decimal!==undefined ? separador_decimal : ",");

                if(separador_miles){
                    // Añadimos los separadores de miles
                    var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
                    while(miles.test(numero)) {
                        numero=numero.replace(miles, "$1" + separador_miles + "$2");
                    }
                }

                return numero;
            }
            
            
            
            
            
            
            function marcarHermanos(referencia){
                var ch_vales=referencia.value;
                
                actual_cantidad=parseFloat($('#total_cantidad').val());
                actual_importe=parseFloat($('#total_importe').val());
                var sumaimporte=0;
                var sumacantidad=0;
                var accion='nada';
                
                checkboxes=document.getElementsByTagName('input'); 

                for(i=0;i<checkboxes.length;i++) 
                {
                    if(checkboxes[i].type == "checkbox" && checkboxes[i].value==ch_vales)
                    {
                        cantidad_tmp=parseFloat($(checkboxes[i]).attr('cantidad'));
                        importe_tmp=parseFloat($(checkboxes[i]).attr('importe'));
                        
                        if($(referencia).is(':checked')){
                            checkboxes[i].checked=1; 
                            accion='suma';
                        }else{
                            checkboxes[i].checked=0;  
                            accion='resta';
                        }
                        sumacantidad+=cantidad_tmp;
                        sumaimporte+=importe_tmp;
                    }
                }
                
                
                /**/
                
                
                //sumas 
                if(accion=="suma"){
                    actual_cantidad=actual_cantidad+sumacantidad;
                    actual_importe=actual_importe+sumaimporte;
                }else if(accion=="resta"){
                    actual_cantidad=actual_cantidad-sumacantidad;
                    actual_importe=actual_importe-sumaimporte; 
                }else{
                    actual_cantidad=0;
                    actual_importe=0
                }
                // actual_cantidad=(Math.round(actual_cantidad*100)/100) ;
                // actual_importe=(Math.round(actual_importe*100)/100) ;
                $('#lba_total_cantidad').html(formato_numero(actual_cantidad, 2,'.'));
                $('#lba_total_importe').html(formato_numero(actual_importe, 2,'.'));
                
                $('#total_cantidad').val(actual_cantidad);
                $('#total_importe').val(actual_importe);
                
            }
            function marcarCliente() 
            {

                checkboxes=document.getElementsByTagName('input'); 
                
                for(x=0;x< ArregloCliente.length;x++){
                    var index=ArregloCliente[x].indexOf("-");
                    var cade_ch_cliente=ArregloCliente[x].substr(0, index);
                    var vales=ArregloCliente[x].substr(index+1, ArregloCliente[x].length);
                   
                    for(i=0;i<checkboxes.length;i++) 
                    {
                        if(checkboxes[i].type == "checkbox" && checkboxes[i].value==cade_ch_cliente && vales!="NOALL")
                        {
                            checkboxes[i].checked=1; 
                            
                            for(u=0;u<ArregloCliente_montoModificado.length;u++){
                                indexmonto=index=ArregloCliente_montoModificado[u].indexOf("-");
                                cade_ch_cliente_monto=ArregloCliente_montoModificado[u].substr(0, indexmonto);
                                monto_modi=ArregloCliente_montoModificado[u].substr(indexmonto+1, ArregloCliente_montoModificado[u].length);
                                cade_momto="idmonto_"+cade_ch_cliente_monto;
                                if(cade_ch_cliente_monto==checkboxes[i].value){
                                    $('#'+cade_momto).html(formato_numero(monto_modi, 2,'.'));
                                }
                            }
                           
                        }
                    }
                    
                }
              
            }
           
            function marcar(source) 
            {
                
                checkboxes=document.getElementsByTagName('input'); 
                for(i=0;i<checkboxes.length;i++)
                {
                    if(checkboxes[i].type == "checkbox") 
                    {
                        checkboxes[i].checked=source.checked; 
                    }
                }
                if(source.checked){
                    
                
                    $('#lba_total_cantidad').html($('#total_cantidad_siempre').val());
                    $('#lba_total_importe').html($('#total_importe_siempre').val());
                    $('#total_cantidad').val($('#total_cantidad_siempre').val());
                    $('#total_importe').val($('#total_importe_siempre').val()); 
                }else{
                    $('#lba_total_cantidad').html("0.00");
                    $('#lba_total_importe').html("0.00");
                    $('#total_cantidad').val("0.00");
                    $('#total_importe').val("0.00");   
                }
                
                
            }
            function GuardarArray(cliente,estado){
                var cadenavales='';
                var posicion_repl_tmp=0;
                var flagencontrado=false;
                for(i=0;i< ArregloCliente.length;i++){
                    var index=ArregloCliente[i].indexOf("-");
                    var cade_ch_cliente=ArregloCliente[i].substr(0, index);
                    if(cliente==cade_ch_cliente){
                        posicion_repl_tmp=i;
                        flagencontrado=true;
                    }
                    
                }
                if(flagencontrado==false){
                    ArregloCliente[int_contador]=cliente+"-"+estado;
                    int_contador++;
                }else{
                    ArregloCliente[posicion_repl_tmp]=cliente+"-"+estado;
                }
                   
            }
            function GuardarValesClienteGlobal(cliente,objref){
                if($(objref).is(':checked')){
                    GuardarArray(cliente,'ALL');
                   
                }else{
                    GuardarArray(cliente,'NOALL');
                    var cade_momto="idmonto_"+cliente;
                    $('#'+cade_momto).html($('#'+cade_momto).attr('montoantiguo'));
                    
                    
                    for(u=0;u<ArregloCliente_montoModificado.length;u++){
                        indexmonto=index=ArregloCliente_montoModificado[u].indexOf("-");
                        cade_ch_cliente_monto=ArregloCliente_montoModificado[u].substr(0, indexmonto);
                        monto_modi=ArregloCliente_montoModificado[u].substr(indexmonto+1, ArregloCliente_montoModificado[u].length);
                        cade_momto="idmonto_"+cade_ch_cliente_monto;
                        if(cade_ch_cliente_monto==cliente){
                            ArregloCliente_montoModificado[u]=cliente+"-"+$('#'+cade_momto).attr('montoantiguo');
                        }
                    }
                    
                    
                    
                }
            }
            function GuardarValesCliente(cliente){
                var cadenavales='';
                var posicion_repl_tmp=0;
                var flagencontrado=false;
                for(i=0;i< ArregloCliente.length;i++){
                    var index=ArregloCliente[i].indexOf("-");
                    var cade_ch_cliente=ArregloCliente[i].substr(0, index);
                    if(cliente==cade_ch_cliente){
                        posicion_repl_tmp=i;
                        flagencontrado=true;
                    }
                    
                }
                $('.idselecAll:checked').each(function(){
                    cadenavales+="'"+$(this).val()+"',";
                        
                });
                if(flagencontrado==false){
                    ArregloCliente[int_contador]=cliente+"-"+cadenavales;
                    int_contador++;
                }else{
                    ArregloCliente[posicion_repl_tmp]=cliente+"-"+cadenavales;
                }
                monto_actual_fact=parseFloat($('#total_importe').val());
                
                
                //-----------------
                var esta=false;
                for(u=0;u<ArregloCliente_montoModificado.length;u++){
                    indexmonto=index=ArregloCliente_montoModificado[u].indexOf("-");
                    cade_ch_cliente_monto=ArregloCliente_montoModificado[u].substr(0, indexmonto);
                    monto_modi=ArregloCliente_montoModificado[u].substr(indexmonto+1, ArregloCliente_montoModificado[u].length);
                    
                    if(cade_ch_cliente_monto==cliente){
                        ArregloCliente_montoModificado[u]=cliente+"-"+monto_actual_fact; 
                        esta=true;
                    }
                }
                if(esta==false){
                    ArregloCliente_montoModificado[int_contador_monto_modif]=cliente+"-"+monto_actual_fact;  
                    int_contador_monto_modif++;
                }
                
                
                
                
                $('#btnseleccionar').click();
                
                
            }
            function verreportevales(cliente){
                //selecionar sus vales correspondienmte
                var cade_vales="";
                for(x=0;x< ArregloCliente.length;x++){
                    var index=ArregloCliente[x].indexOf("-");
                    var cade_ch_cliente=ArregloCliente[x].substr(0, index);
                    var vales=ArregloCliente[x].substr(index+1, ArregloCliente[x].length);
                    
                    if(cliente==cade_ch_cliente)
                    {
                        cade_vales=vales;
                        
                    }
                }
                // ******************************************
                
                var elemento = $("#contenidoTablaSelecionar");
                var posicion = elemento.position();
                var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                var left=''+(posicion.left+marginleft+distancia)+'px';
                var top=''+posicion.top+'px';
                $('#cargardor').css({'left':left,'top':top,'display':'block'});
                $.ajax({
                    type: "POST",
                    url: "c_reporte_vales_relacion.php",
                    data: { accion:'ver_vales',fecha_inicio:$( "#fecha_inicio" ).val(),fecha_final:$( "#fecha_final" ).val(),ruc:cliente,valesselecionada:cade_vales},
                    success:function(xm){
                            
                        $('#contenidoTablaSelecionar').html(xm);
                           
                        $('#cargardor').css({'display':'none'});
                        var ventana_ancho_mitad = ($(window).width()/2);
                        tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                        ventana_ancho_px=(tamano)+"px";
                        $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                   
                    }
                });
            }
            function GenerarFactura(tipo_doc,serie,num_documento){
                var elemento = $("#contenidoTablaSelecionar");
                var posicion = elemento.position();
                var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                var left=''+(posicion.left+marginleft+distancia)+'px';
                var top=''+posicion.top+'px';
                $('#cargardor').css({'left':left,'top':top,'display':'block'});
                $.ajax({
                    type: "POST",
                    url: "forms_popup/fac_impresiones_moderno.php",
                    data: { accion:'Generar',c_documento:tipo_doc,reimprimir:'on',pdf:'on',c_serie:serie,c_num_desde:num_documento},
                    success:function(xm){
                        $('#cargardor').css({'display':'none'});
                        location.href='forms_popup/descarga.php';
                   
                    }
                });
            }
            $(function(){
                $('#content').remove();
                $('#footer').remove();
                var ventana_ancho = ($(window).width()/2)-250;
                
                ventana_ancho_px=ventana_ancho+"px";
                $('.contenedorprincipal').css({'margin-left':ventana_ancho_px});
                $.datepicker.regional['es'] = {
                    closeText: 'Cerrar',
                    prevText: '<Ant',
                    nextText: 'Sig>',
                    currentText: 'Hoy',
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
                    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
                    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
                    weekHeader: 'Sm',
                    dateFormat: 'dd/mm/yy',
                    firstDay: 1,
                    isRTL: false,
                    showMonthAfterYear: false,
                    yearSuffix: ''
                };
                $.datepicker.setDefaults($.datepicker.regional['es']);  
             
             
               
                $( "#fecha_inicio" ).datepicker( 
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_inicio" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $( "#fecha_final" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_final" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $( "#fecha_liqui" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_liqui" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                
                $('#btnbuscarliquidacion').click(function(){
                    var elemento = $("#contenidoTablaSelecionar");
                    var posicion = elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';
                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
                    
                    $.ajax({
                        type: "POST",
                        url: "c_reporte_vales_relacion.php",
                        data: { accion:'buscar_liquidacion',fecha_inicio:$("#fecha_inicio").val(),fecha_final:$("#fecha_final").val()
                        },
                        success:function(xm){
                            var cadenaretorno=xm.substring(0, 7);
                            if(cadenaretorno=="ERROR_:"){
                                var  error=xm.substr(7,xm.length);
                                $('.estado').remove();
                                var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                $('.contenedorprincipal').append($(estadodiv));
                                if ($(".estado:first").is(":hidden")) {
                                    $(".estado").show("slow");
                                }
                               
                                
                            }else{
                                $('#contenidoTablaSelecionar').html(xm);
                                $('.estado').remove();
                            }
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano+50)+"px";
                            $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                          
                           
                        }
                    });
                    
                    
                    
                });
                
                $('#btnliquidar').click(function(){
                    
                    var elemento = $("#contenidoTablaSelecionar");
                    var posicion = elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';
                    
                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
                    
                    var jsonvales=new Array();
                    var i=0;
                    var tipodoc=0;
                    if($('#tdf').is(':checked')){
                        tipodoc='10';
                    }else if($('#tdb').is(':checked')){
                        tipodoc='35';
                    }
                      
                         
                    $('.idselecAll:checked').each(function(){
                        jsonvales[i]=$(this).val();
                        i++;
                    });
                    $.ajax({
                        type: "POST",
                        url: "c_reporte_vales_relacion.php",
                        data: { accion:'liquidar_vales',ruc:$('#idcodigoCliente').val(),accionInterno:$('#accionInterno').val(),vales:ArregloCliente,
                            fecha_inicio:$("#fecha_inicio").val(),fecha_final:$( "#fecha_final" ).val(),
                            documento:tipodoc,serie_actual:$('#serie_doc').val(),tipo_opeacion:$('#cmbtipooperacion').val(),
                            fecha_liqui:$('#fecha_liqui').val()
                        },
                        success:function(xm){
                            
                            var cadenaretorno=xm.substring(0, 7);
                            if(cadenaretorno=="ERROR_:"){
                                
                                $('#contenidoTablaSelecionar').html("");
                                var  error=xm.substr(7,xm.length);
                                $('.estado').remove();
                                var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                $('.contenedorprincipal').append($(estadodiv));
                                if ($(".estado:first").is(":hidden")) {
                                    $(".estado").show("slow");
                                }  
                               
                                
                               
                                
                            }else{
                                $('#contenidoTablaSelecionar').html(xm);
                                $('.estado').remove();
                            }
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano+70)+"px";
                            $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                            ArregloCliente=new Array();//referencia la objecyo principañ
                            int_contador=0;//ontrador princapl
                          
                        }
                    });
                });
                $('#btnseleccionar').click(function(){
                
                    var elemento = $("#contenidoTablaSelecionar");
                    var posicion = elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';
                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
              
                    $.ajax({
                        type: "POST",
                        url: "/sistemaweb/ventas_clientes/reportes/c_cambio_precio.php",
                        data: { modo:'ajax',accion:'selecionabtn',fecha_inicio:$( "#fecha_inicio" ).val(),fecha_final:$( "#fecha_final" ).val(),tipo_cal:$('#tipo_cal').val()},
                        success:function(xm){
                          
                            $('#cargardor').css({'display':'none'});
                            //alert(xm);
                            //***************
                            var cadenaretorno=xm.substring(0, 7);
                            if(cadenaretorno=="ERROR_:"){
                                
                                $('#contenidoTablaSelecionar').html("");
                                var  error=xm.substr(7,xm.length);
                                $('.estado').remove();
                                var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                $('.contenedorprincipal').append($(estadodiv));
                                if ($(".estado:first").is(":hidden")) {
                                    $(".estado").show("slow");
                                }  
                               
                            }else{
                                //*************
                            
                                $('.estado').remove();
                                $('#contenidoTablaSelecionar').html(xm);
                            
                                $('#cargardor').css({'display':'none'});
                            
                                marcarCliente();
                                var ventana_ancho_mitad = ($(window).width()/2);
                                tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                                ventana_ancho_px=(tamano+50)+"px";
                              //  $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px});
                            }
                        }
                    });
                });
                $('input[name=td]').click(function(){
                    var tipodoc=$(this).val();
   
   
                    var elemento = $("#contenidoTablaSelecionar");
                    var posicion = elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';
                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
       

                    $.ajax({
                        type: "POST",
                        url: "c_reporte_vales_relacion.php",
                        data: { accion:'tipodocumento',documento:tipodoc},
                        success:function(xm){
                            $('#serie').html(xm);
                            $('#cargardor').css({'display':'none'});
                   
                        }
                    });
   
   
                });

            });

        </script>

    </head>
    <body>
        <?php include "../menu_princ.php"; ?>
        <div id="content">
            <script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
            <script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
            <div id="content_title">&nbsp;</div>
            <div id="content_body">&nbsp;</div>
            <div id="content_footer">&nbsp;</div>
        </div>

        <div id="footer">&nbsp;</div>
        <div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/images/cg.gif" /></div>
            <?php

            include('/sistemaweb/include/mvc_sistemaweb.php');
            include('reportes/t_cambio_precio.php');
            include('reportes/m_cambio_precio.php');
            
            $objtem = new cambio_precioTemplate();
            echo cambio_precioTemplate::FormularioPrincipal();
            ?>
        <script type="text/javascript">
            //   $( "#fecha_inicio" ).datepicker( "option", "dateFormat","yy-mm-dd");
          
        </script>
    </body>
</html>
