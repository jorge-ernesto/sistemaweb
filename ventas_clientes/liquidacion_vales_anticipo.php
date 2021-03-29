<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
	<head>
		<title>Sistema de Ventas - LIQUIDACION VALES</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
        <script type="text/javascript" src="/sistemaweb/assets/js/helper/autocomplete.js"></script>
		<script type="text/javascript">
			var distancia=300;
			var int_contador=0,int_contador_monto_modif=0;
			var monto_actual_fact=parseFloat("0.00");
			var ch_cliente_guardado="";
			var ArregloCliente=new Array();
			var ArregloCliente_montoModificado=new Array();

			function formato_numero(numero, decimales, separador_decimal, separador_miles){ // v2007-08-06
                numero=parseFloat(numero);
                if(isNaN(numero)){
                    return "";
                }

                if(decimales!==undefined){
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
            
            
            function ordenar(tipo, data){
                console.log(tipo);
                console.log(data);

                //if( $('.chofer').is(':checked') ) {
                if ($('.chofer').prop('checked') || $('.preunit').prop('checked')){

                    var fecha_inicio = $('#fecha_inicio').val();
                    var fecha_final  = $('#fecha_final').val();
                    var ruc          = $('#idcodigoCliente').val();
                    var order        = tipo;

                    var elemento   = $("#contenidoTablaSelecionar");
                    var posicion   = elemento.position();
                    var marginleft = parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left       = ''+(posicion.left+marginleft+distancia)+'px';
                    var top        = ''+posicion.top+'px';

                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
       
                    $.ajax({
                        type    : "POST",
                        url     : "c_liquidacion_vales_relacion_anticipo.php",
                        data    : {
                                    accion          : 'ordenar',
                                    fecha_inicio    : fecha_inicio,
                                    fecha_final     : fecha_final,
                                    ruc             : ruc,
                                    order           : order
                        },success:function(xm){
                            $('#contenidoTablaSelecionar').html(xm);
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano)+"px";
                            $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                            if(tipo == "chofer"){
                                $( ".chofer" ).prop( "checked", true );
                            }else if(tipo == "precioUnitario"){
                                $( ".preunit" ).prop( "checked", true );
                            }
                            $( ".idselecAll" ).prop( "checked", false );
                            //$('.' + getnochofer).prop('checked', true );
                        }
                    });

                }else{

                    var fecha_inicio    = $('#fecha_inicio').val();
                    var fecha_final     = $('#fecha_final').val();
                    var ruc             = $('#idcodigoCliente').val();
                    var order           = null;

                    var elemento    = $("#contenidoTablaSelecionar");
                    var posicion    = elemento.position();
                    var marginleft  =parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left        =''+(posicion.left+marginleft+distancia)+'px';
                    var top         =''+posicion.top+'px';

                    $('#cargardor').css({'left':left,'top':top,'display':'block'});
       
                    $.ajax({
                        type    : "POST",
                        url     : "c_liquidacion_vales_relacion_anticipo.php",
                        data    : {
                                    accion          : 'ordenar',
                                    fecha_inicio    : fecha_inicio,
                                    fecha_final     : fecha_final,
                                    ruc             : ruc,
                                    order           : order
                        },success:function(xm){
                            $('#contenidoTablaSelecionar').html(xm);
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano)+"px";
                            $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                            $( ".chofer" ).prop( "checked", false );
                            $( ".preunit" ).prop( "checked", false );
                            $( ".idselecAll" ).prop( "checked", false );
                        }
                    });
                }

            }
            

            function marcarHermanos(referencia, getnochofer){

                var ch_vales    = referencia.value;

                actual_cantidad = parseFloat($('#total_cantidad').val());
                actual_importe  = parseFloat($('#total_importe').val());

                var nuvale          = null;
                var cantidad_tmp    = 0;
                var importe_tmp     = 0;
                var sumacantidad    = 0;
                var sumaimporte     = 0;
                var accion          = 'nada';
                checkboxes          = document.getElementsByTagName('input'); 


                //if( $('.chofer').is(':checked') ) {
                if ($('.chofer').prop('checked')){
                    if($('#' + ch_vales).is(':checked') ) {
                        $('.' + getnochofer).each(function(){
                            accion  = 'suma';
                            nuvale  = $(this).val();
                            $('#' + nuvale).prop( "checked", true );
                            if($('#' + nuvale).is(':checked') ) {
                                sumacantidad    += parseFloat($(this).attr('cantidad'));
                                sumaimporte     += parseFloat($(this).attr('importe'));
                            }
                        });
                    }else{
                        $('.' + getnochofer).each(function(){
                            accion = 'resta';
                            nuvale = $(this).val();
                            $('#' + nuvale).prop( "checked", false );
                            if(this.checked === false){
                                sumacantidad    += parseFloat($(this).attr('cantidad'));
                                sumaimporte     += parseFloat($(this).attr('importe'));
                            }
                        });
                        sumaimporte = parseFloat(sumaimporte);
                    }
                }else{
                    for(i = 0; i < checkboxes.length; i++){

                        if(checkboxes[i].type == "checkbox" && checkboxes[i].value==ch_vales){

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
                }

                if(accion=="suma"){
                    actual_cantidad=actual_cantidad+sumacantidad;
                    actual_importe=actual_importe+ sumaimporte;
                }else if(accion=="resta"){
                    actual_cantidad=actual_cantidad-sumacantidad;
                    actual_importe=actual_importe-sumaimporte; 
                }else{
                    actual_cantidad = 0;
                    actual_importe  = 0;
                }

                $('#lba_total_cantidad').html(formato_numero(actual_cantidad, 3, '.', ','));
                $('#lba_total_importe').html(formato_numero(actual_importe, 2, '.', ','));
                
                $('#total_cantidad').val(actual_cantidad);
                $('#total_importe').val(actual_importe);
                
            }

            function marcarCliente(){
                console.log('** function marcarCliente **');
                console.log('Array -> ArregloCliente');
                console.log(ArregloCliente);
                console.log('Array -> ArregloCliente_montoModificado');
                console.log(ArregloCliente_montoModificado);

                checkboxes=document.getElementsByTagName('input');
                
                for(x=0;x< ArregloCliente.length;x++){
                    //var index=ArregloCliente[x].indexOf("-");//Antes 2019-01-01
                    var index=ArregloCliente[x].indexOf("*");//Ahora 2019-07-02
                    var cade_ch_cliente=ArregloCliente[x].substr(0, index);
                    var vales=ArregloCliente[x].substr(index+1, ArregloCliente[x].length);
                   
                    for(i=0;i<checkboxes.length;i++) {
                        if(checkboxes[i].type == "checkbox" && checkboxes[i].value==cade_ch_cliente && vales!="NOALL"){
                            checkboxes[i].checked=1;                             
                            for(u=0;u<ArregloCliente_montoModificado.length;u++){
                                indexmonto=index=ArregloCliente_montoModificado[u].indexOf("-");
                                cade_ch_cliente_monto=ArregloCliente_montoModificado[u].substr(0, indexmonto);
                                monto_modi=ArregloCliente_montoModificado[u].substr(indexmonto+1, ArregloCliente_montoModificado[u].length);
                                cade_momto="idmonto_"+cade_ch_cliente_monto;
                                if(cade_ch_cliente_monto==checkboxes[i].value){
                                    $('#'+cade_momto).html(formato_numero(monto_modi, 2,'.', ','));
                                }
                            }                           
                        }
                    }
                    
                }
              
            }
           
            function marcar(source){
                if(source.checked){
                    $( '.idselecAll' ).prop( "checked", true );

                    $('#lba_total_cantidad').html(formato_numero($('#total_cantidad_siempre').val(), 3,'.', ','));
                    $('#lba_total_importe').html(formato_numero($('#total_importe_siempre').val(), 2,'.', ','));

                    $('#total_cantidad').val($('#total_cantidad_siempre').val());
                    $('#total_importe').val($('#total_importe_siempre').val()); 
                }else{
                    $( '.idselecAll' ).prop( "checked", false );

                    $('#lba_total_cantidad').html("0.000");
                    $('#lba_total_importe').html("0.00");

                    $('#total_cantidad').val("0.000");
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
                    ArregloCliente[int_contador]=cliente+"*"+estado;
                    int_contador++;
                }else{
                    ArregloCliente[posicion_repl_tmp]=cliente+"*"+estado;
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
                console.log('** function GuardarValesCliente ID -> ' + cliente + '**');

                var elemento=$("#contenidoTablaSelecionar");
                var posicion=elemento.position();
                var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                var left=''+(posicion.left+marginleft+distancia)+'px';
                var top=''+posicion.top+'px';

                $('#cargardor').css({'left':left,'top':top,'display':'block'});

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
                    ArregloCliente[int_contador]=cliente+"*"+cadenavales;
                    int_contador++;
                }else{
                    ArregloCliente[posicion_repl_tmp]=cliente+"*"+cadenavales;
                }

                monto_actual_fact=parseFloat($('#total_importe').val());

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

                setTimeout(() => { $('#cargardor').css({'display':'none'}); }, 300);
                marcarCliente();
                // $('#btnseleccionar').click();                
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
                    url: "c_liquidacion_vales_relacion_anticipo.php",
                    data: { accion:'ver_vales',fecha_inicio:$( "#fecha_inicio" ).val(),fecha_final:$( "#fecha_final" ).val(),ruc:cliente,valesselecionada:cade_vales,transacciones:$( "#txt-transacciones" ).val()},
                    success:function(xm){
                        $('#contenidoTablaSelecionar').html('');
                        if(xm == "Debe elegir un cliente anticipo"){
                            $('#cargardor').css({'display':'none'});
                            alert(xm);
                            return;
                        }
                            
                        $('#contenidoTablaSelecionar').html(xm);
                           
                        $('#cargardor').css({'display':'none'});
                        var ventana_ancho_mitad = ($(window).width()/2);
                        tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                        ventana_ancho_px=(tamano)+"px";
                        $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                   
                    }
                });
            }

		//GENERAR FACTURA PARA IMPRIMIR

		function GenerarFactura(tipo_doc, serie, num_documento, codcliente, _id){

            var elemento      = $("#contenidoTablaSelecionar");
	        var posicion      = elemento.position();
	        var marginleft    = parseInt($('#contenidoTablaSelecionar').css('margin-left'));
	        var left          = ''+(posicion.left+marginleft+distancia)+'px';
	        var top           = ''+posicion.top+'px';
            var idigv	      = $('#idigv'+_id+' option:selected').val();

		    $('#cargardor').css({'left':left,'top':top,'display':'block'});

		    $.ajax({
				type: "POST",
				url: "forms_popup/fac_impresiones_moderno.php",
				data: {
					accion		    : 'Generar',
					c_documento	    : tipo_doc,
					reimprimir	    : 'on',
					pdf		        : 'on',
					c_serie		    : serie,
					c_num_desde	    : num_documento,
					idigv		    : idigv,
					codcliente	    : codcliente
				},
				success:function(xm){
                    //console.log('xm: '+xm);
                	$('#cargardor').css({'display':'none'});
                    location.href='forms_popup/descarga.php';
				}
		    });
            
        }

        function generarDocumentoLV(iCodigoAlmacen, iTipoDocumento, sSerieDocumento, iNumeroDocumento, dFechaEmision, iNumeroLiquidacion, iIdCliente) {
            sAction = 'representacion_interna_pdf_sunat';
            /*
            console.log('iCodigoAlmacen -> ' + iCodigoAlmacen);
            console.log('iTipoDocumento -> ' + iTipoDocumento);
            console.log('sSerieDocumento -> ' + sSerieDocumento);
            console.log('iNumeroDocumento -> ' + iNumeroDocumento);
            console.log('dFechaEmision -> ' + dFechaEmision);
            console.log('iIdCliente -> ' + iIdCliente);
            console.log('iNumeroLiquidacion -> ' + iNumeroLiquidacion);
            console.log('sAction -> ' + sAction);
            */
            window.open('/sistemaweb/ventas_clientes/facturas_venta.php?action=pdf_representacion_interna_fe_sunat&iCodigoAlmacen=' + $.trim(iCodigoAlmacen) + '&iTipoDocumento=' + $.trim(iTipoDocumento) + '&sSerieDocumento=' + $.trim(sSerieDocumento) + '&iNumeroDocumento=' + $.trim(iNumeroDocumento) + '&dFechaEmision=' + $.trim(dFechaEmision) + '&iIdCliente=' + $.trim(iIdCliente) + '&iNumeroLiquidacion=' + $.trim(iNumeroLiquidacion) + '&sAction=' + sAction, '_blank');
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
                        url: "c_liquidacion_vales_relacion_anticipo.php",
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
                    //VALIDAR QUE HAYA INGRESADO FECHA DE LIQUIDACION
                    var fecha_liqui = $('#fecha_liqui').val();                    
                    console.log("fecha_liqui:", fecha_liqui);

                    if(fecha_liqui == ""){
                        alert("Debe ingresar fecha de liquidacion");
                        return;
                    }                    

                    //VALIDAR QUE HAYA INGRESADO DOCUMENTO DE REFERENCIA
                    var documentoRef = $('#txt-documentoRef').val();
                    documentoRef = documentoRef.trim();
                    console.log("documentoRef:", documentoRef);

                    if(documentoRef == ""){
                        alert("Debe ingresar documento de referencia");
                        return;
                    }

                    //OBTENEMOS SERIE INDICADO EN EL DOCUMENTO DE REFERENCIA
                    var porciones_documentoRef = documentoRef.split('-');
                    var serieRef = porciones_documentoRef[0]
                    serieRef = serieRef.trim();

                    // Verificar consolidación
                    // Parametros (día, turno y fecha)
                    var params = {
                        action: 'search-verify_consolidation',  
                        dFecha: $('#fecha_liqui').val(),
                        iTurno: 0,
                        iAlmacen: $( '#serie_doc' ).find(':selected').data('ialmacen'),
                    }
                    console.log( JSON.stringify(params) );
                    // return;

                    url = '/sistemaweb/ventas_clientes/facturas_venta.php';
                   
                    $.post( url, params, function( response ) {
                        console.log( JSON.stringify(response) );
                        // return;
                        if (response.sStatus == 'success') {
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
                          
                            var valor_negativo=($('#cons_nega').is(':checked'))?"1":"0";

                            var sCodigoImpuesto = '';
                            if (
                                $( '#sin_igv' ).is( ':checked' ) == true &&
                                $( '#trans_gratis' ).is( ':checked' ) == false
                            )
                                sCodigoImpuesto = 'S';//Exonerada

                            if (
                                $( '#sin_igv' ).is( ':checked' ) == false &&
                                $( '#trans_gratis' ).is( ':checked' ) == true
                            )
                                sCodigoImpuesto = 'T';//Gratuita

                            if (
                                $( '#sin_igv' ).is( ':checked' ) == true &&
                                $( '#trans_gratis' ).is( ':checked' ) == true
                            )
                                sCodigoImpuesto = 'U';// Exonerada + Gratuita

                            var arrDataPOST = {
                                accion          : 'liquidar_vales',
                                ruc             : $('#idcodigoCliente').val(),
                                accionInterno   : $('#accionInterno').val(),
                                vales           : ArregloCliente,
                                fecha_inicio    : $("#fecha_inicio").val(),
                                fecha_final     : $( "#fecha_final" ).val(),
                                documento       : tipodoc,
                                serie_actual    : serieRef,
                                tipo_opeacion   : $('#cmbtipooperacion').val(),
                                fecha_liqui     : $('#fecha_liqui').val(),
                                estado_negativo : valor_negativo,
                                sCodigoImpuesto : sCodigoImpuesto,
                                iIdCliente : $( '#idcodigoCliente' ).val(),
                                sSerieNumeroDocumento : $( '#txt-documentoRef' ).val(),
                            };
                            /* Validacion de envio de data */
                            console.log("Vales");
                            console.log( JSON.stringify(arrDataPOST) );
                            // return;
                            /* Fin Validacion de envio de data */

                            $.ajax({
                                type    : "POST",
                                url     : "c_liquidacion_vales_relacion_anticipo.php",
                                data    : arrDataPOST,
                                success:function(xm){
                                    /* Validacion de envio de data */
                                    console.log( JSON.stringify(xm) );
                                    // return;
                                    /* Fin Validacion de envio de data */

                                    var cadenaretorno=xm.substring(0, 7);

                                    if( cadenaretorno == "ERROR_:" ){
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
                                    tamano = ventana_ancho_mitad - ($('#contenidoTablaSelecionar').width()/2);
                                    ventana_ancho_px = (tamano + 70) + "px";

                                    $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px});

                                    ArregloCliente = new Array();//referencia la objecyo principañ
                                    int_contador = 0;//ontrador princapl
                                }
                            });
                        } else {
                            alert(response.sMessage);
                        }
                    }, "json");
                });

                $('#btnseleccionar').click(function(){
                    console.log('** BUTTON SELECCIONAR ID -> #btnseleccionar **');

                    var elemento=$("#contenidoTablaSelecionar");
                    var posicion=elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';

                    $('#cargardor').css({'left':left,'top':top,'display':'block'});

                    $.ajax({
                        type: "POST",
                        url: "c_liquidacion_vales_relacion_anticipo.php",
                        data: {
                            accion:'selecionabtn',
                            fecha_inicio:$( "#fecha_inicio" ).val(),
                            fecha_final:$( "#fecha_final" ).val()
                        },
                        success:function(xm){
                            $('#cargardor').css({'display':'none'});
                            
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
                                $('.estado').remove();
                                $('#contenidoTablaSelecionar').html(xm);
                                $('#cargardor').css({'display':'none'});
                            
                                marcarCliente();
                                var ventana_ancho_mitad = ($(window).width()/2);
                                tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                                ventana_ancho_px=(tamano+50)+"px";
                                $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px});
                            }
                        }
                    });
                });

                $('#btnseleccionar_verrerportevales').click(function(){
                    console.log('** BUTTON SELECCIONAR ID -> #btnseleccionar_verrerportevales **');
                    
                    //VALIDAR QUE SE HAYA INGRESADO CLIENTE
                    var cliente = $('#txt-Nu_Documento_Identidad').val();
                    cliente = cliente.trim();
                    console.log(cliente);

                    if(cliente == ""){
                        alert("Debe ingresar un cliente");
                        return;
                    }

                    verreportevales(cliente);
                });

                $( 'input[name=td]' ).click(function(){
                    var tipodoc=$(this).val();
                    var elemento = $("#contenidoTablaSelecionar");
                    var posicion = elemento.position();
                    var marginleft=parseInt($('#contenidoTablaSelecionar').css('margin-left'));
                    var left=''+(posicion.left+marginleft+distancia)+'px';
                    var top=''+posicion.top+'px';

                    $('#cargardor').css({'left':left,'top':top,'display':'block'});

                    $.ajax({
                        type: "POST",
                        url: "c_liquidacion_vales_relacion_anticipo.php",
                        data: {accion:'tipodocumento', documento:tipodoc},
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
            include('liquidacion_vales/t_liquidacion_vales_anticipo.php');
            include('liquidacion_vales/m_liquidacion_vales_anticipo.php');
            include('liquidacion_vales/c_liquidacion_vales_anticipo.php');
            $objtem = new LiquidacionValesTemplate();
            $selectexonerada = LiquidacionValesModel::GetTaxOptional();

            echo LiquidacionValesTemplate::FormularioPrincipal($selectexonerada);
            ?>
        <script type="text/javascript">
            //   $( "#fecha_inicio" ).datepicker( "option", "dateFormat","yy-mm-dd");
          
        </script>

    </body>
</html>
