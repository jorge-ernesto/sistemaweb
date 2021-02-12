<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>
        <title>Sistema de Ventas - Egreso de caja</title>
        <meta name="description" content="The HTML5 Herald">
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/styles.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
    	<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
    	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
    	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>

        <script type="text/javascript">
            var distancia              = 300;
        	var int_contador		   = 0,
        	int_contador_monto_modif   = 0;
        	var monto_actual_fact	   = parseFloat("0.00");
        	var ch_cliente_guardado	   = "";
        	var ArregloCliente		   = new Array();
            var MIN_LENGTH 			   = 1;

            function verdetalle(id_recibo,sucursal){
                window.open ("informe_registro_caja_egreso.php?id_recibo="+id_recibo+"&sucursal="+sucursal+"", "mywindow","location=1,status=1,scrollbars=1, width=950,height=600")
            }
            
            function anular(id_recibo, iWarehouse, dEntry){
                console.log( 'iWarehouse -> ' + iWarehouse );
                console.log( 'dEntry -> ' + dEntry );
                pregunta = 'Deseas anular el recibo?';
                if ( confirm(pregunta) ) {
                    $('#cargardor').css({'display':'inline'});
                    $.ajax({
                        type: "POST",
                        url: "c_caja_egreso_relacion.php",
                        data: {
                            'accion' : 'anular_ingreso',
                            'id_transacion' : id_recibo,
                            'iWarehouse' : iWarehouse,
                            'dEntry' : dEntry,
                        },
                        success:function(xm){
                            var obj=eval('('+xm+')'); 
                            if (obj.sStatus != 'success') {
                                alert( obj.sMessage );
                            }
                            $('#cargardor').css({'display':'none'});
                            $('#btnseleccionar').click();
                        }
                    });
                }
            }
            
            function eliminarTr(obj){
                $(obj).parent().parent().remove();
                var monto_global=parseFloat(0);
                $('.medio_pago').each(function(){
                    var cadena_mp_split=$(this).attr('campos').split("*");
                    monto_global+= cadena_mp_split[6];
                });
                $('#importe_fp_id').attr('value_se',monto_global);
                $('#importe_fp_id').html(formato_numero(monto_global, 2,'.'));
               
            }

		//BUSCAR CUENTAS BANCARIAS SEGUN BANCO

		function vercuentasbancarias(ref){

	                var valorcmb		= ref.value;
			var valor_moneda	= $('#cmnmoneda_id').val();

		        $('#cargardor').css({'display':'inline'});

		        $.ajax({
				type: "POST",
				url: "c_caja_egreso_relacion.php",
				data: {
		                	accion:'cuentas_bancarias',
				        'cuenta':valorcmb,
				        'moneda':valor_moneda,
		            	},
		            	success:function(xm){
		               		var obj=eval('('+xm+')'); 
		               		$('#cuentas_cmb_mostrar').html(obj.datos);
		                	$('#cargardor').css({'display':'none'});
				}
		        });

		}

		//BUSCAR MONEDA SEGUN CUENTA BANCARIA

            	function vercuentasbancariasMoneda(ref){

	                var valorcmb = ref.value;

	                $('#cargardor').css({'display':'inline'});

                	$.ajax({
		            	type: "POST",
		            	url: "c_caja_egreso_relacion.php",
		            	data: {
				        accion:'cuentas_bancarias_moneda',
				        'cuenta':valorcmb
		            	},
		            	success:function(xm){
				        var obj=eval('('+xm+')'); 
				        $('#txtmostarmoneda').html(obj.datos);
				        $('#cargardor').css({'display':'none'});
				}
			});

		}

		//BUSCAR CLIENTE DOCUMENTOS POR COBRAR POR MONEDA

            	function DocumentosCobrarMoneda(){

			ArregloCliente = new Array();

	            	var valor_dni 		= $('#id_cliente_auto_ruc').val();
	            	var valor_tc 		= $('#txttipo_cambio').val();
			var valor_moneda	= $('#cmnmoneda_id').val();

			if(valor_dni!="" || valor_dni.length>0){

			        $('#cargardor').css({'display':'inline'});

                        	$.ajax({
                            		type: "POST",
                            		url: "c_caja_egreso_relacion.php",
                            		data: { accion:'buscar_cliente',moneda:valor_moneda,tc:valor_tc,ruc:valor_dni,fecha:$('#fecha_mostar').val()},
                            		success:function(xm){
		                    		$('#contenidoTablaSelecionar').html(xm);
		                    		$('#cargardor').css({'display':'none'});
                            		}
                       		});
			}

		}

		var html_sin_cliente="";

		//MOSTRAR OPERACION DE CLIENTE O TICKET RECIBO

		function SelecionararAyuda(obj){
            $( "#fecha_mostar" ).prop("disabled", false);
                
			var estado	= $('#cmnoperacion_id option:selected').attr("mostarayuda");
                	var tipo_moneda = $('#cmnmoneda_id').val();

			if(tipo_moneda == "01")
				desc_moneda = "S/";
			else
				desc_moneda = "$";

                	if(estado=="2"){
		            	$('.ayuda_clientes_id').slideDown();
		            	$('#contenidoTablaSelecionar').html("");
		            	$('#tipo_accion').val('con_cliente');
		            	$('#btnbuscar').css('display','inline');
			}else{
                    
				$.ajax({
                        type: "POST",
                        url: "c_caja_egreso_relacion.php",
                        data: { accion:'tipo_documento_gennerar', fecha:$('#fecha_mostar').val()},
		                success:function(xm){
						if (xm == 1 || xm == '1') {
							alert('Dia consolidado, seleccionar otra fecha !');
						} else if (xm == 2 || xm == '2') {
                            alert('La fecha seleccionada, es menor a la fecha de inicio del sistema');
                        } else{
                            $( "#fecha_mostar" ).prop("disabled", true);

						    // var html_sin_cliente="<div style='float: left;width: auto;border: 1px;color:red;'>";
						    html_sin_cliente=" <span style='color:#30767F;font-weight: bold;'>Detalle de Recibo</span>";
						    html_sin_cliente+="<table cellspacing='1' cellpadding='1' border='0' style='text-align: left;position: relative;margin: 5px auto;'>";	
						    html_sin_cliente+="<thead>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Tipo</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Serie</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Numero</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Num. Referencia</th>";
				                    html_sin_cliente+="<th align='center' class='th_cabe'>Moneda</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Importe</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'></th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Tasa</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Moneda</th>";
						    html_sin_cliente+="<th align='center' class='th_cabe'>Monto a Pagar</th>";
						    html_sin_cliente+="</thead>";
						    html_sin_cliente+="<tbody id='registros_sin_cliente'>";
						    html_sin_cliente+="<tr>";
				
						    html_sin_cliente+="<td>"+xm+"</td>";
						    html_sin_cliente+="<td><input type='text' id='txtserie_tmp'/></td>";
						    html_sin_cliente+="<td><input type='text' id='txtnumero_tmp'/></td>";
						    html_sin_cliente+="<td><input type='text' id='txtreferencia_tmp'/></td>";
						    html_sin_cliente+="<td><select id='cmbtipo_moneda'><option value='01'>Soles S/</option><option value='02'>Dolares $</option></select></td>";
						    html_sin_cliente+="<td><input type='text' id='txtimporte_tmp'/></td>";
						    html_sin_cliente+="<td><input type='button' value='Anadir' id='datos_fac_tmp'/></td></tr>";
						    html_sin_cliente+="</tbody>";
						    html_sin_cliente+="<tbody><tr><td><input type='button' value='Mostrar Medio Pago' id='Mostarmediopago_id'/></td></tr></tbody>";
						    html_sin_cliente+="</table>";
						    html_sin_cliente+="<div id='medio_pago_externo_id'></div>";
						    
						    $('.ayuda_clientes_id').slideUp();  
						    $('#contenidoTablaSelecionar').html(html_sin_cliente);
						    $('#tipo_accion').val('sin_cliente');
						    $('#btnbuscar').css('display','none');

						}

					}

				});

			}
                
			for(i=0;i<ArregloCliente.length;i++){
				alert(ArregloCliente[i].medio_pago);
			}

			ArregloCliente=new Array();

		}
            
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
            
            function MostarNumeroRecibo(obj){
                var valorcmb=obj.value;
                $('#cargardor').css({'display':'inline'});
                $.ajax({
                    type: "POST",
                    url: "c_caja_egreso_relacion.php",
                    data: {
                        accion:'num_recibo',
                        'almacen':valorcmb
                    },
                    success:function(xm){ 
                        var obj=eval('('+xm+')');  
                        $('#recibe_nro').val(obj.dato);
                        
                        $('#cargardor').css({'display':'none'});
                   
                    }
                });
            	}

            	function irhome(){
                	window.location.href='/sistemaweb/combustibles/egreso_caja.php';
            	}

            	function ViewClient(obj){

                	var client=obj.value;

			if(client=='N'){
				$('.ayuda_clientes_id').slideDown();
				$('#keyword').val("");
			}else{
				$('.ayuda_clientes_id').slideUp();
				$('#id_cliente_auto_ruc').html(""); 
			}

            	}

            	function ViewAcount(obj){
                    var cuenta		    = obj.value;
        			var currency 		= $('#cmnmoneda_id').val();
        			var typemoneda 		= Array();
        			var value_moneda 	= 0;
        			var desc_moneda 	= 0;

        			if(currency == '01'){
        				value_moneda    = '01';
        				desc_moneda     = 'SOLES';
        			}else{
        				value_moneda    = '02';
        				desc_moneda     = 'DOLARES AMERICANOS';
        			}

        			typemoneda = "<option value="+value_moneda+">"+desc_moneda+"</option>";

                    if ($( "#cmbmediopago option:selected" ).attr('validacion') == "0") {
        				$('.ayuda_cuentas_id').slideUp();
        				$('#txtmostarmoneda').html(typemoneda);
        			}else
				        $('.ayuda_cuentas_id').slideDown();
            	}

            	$(function(){            

                    $.fn.limpiarmedio_pago = function(){
                        $("#cmbmediopago option:nth-child(1)").attr('selected','true');
                        $("#cuentas_cmb_mostrar option:nth-child(1)").attr('selected','true');
                        $('#txtnum_referencia').val(""); 
                        $('#txtfecha').val(""); 
                        $('#txtimporteG').val("");
                    }

                $.fn.validardatosfactura=function(){
                    
                    var txtserie_tmp=$('#txtserie_tmp').val();
                    var txtnumero_tmp=$('#txtnumero_tmp').val();
                    var txtreferencia_tmp=$('#txtreferencia_tmp').val();
                    var txtimporte_tmp=$('#txtimporte_tmp').val();
                    
                    
                    var enteros = /[0-9]+$/;
                    var floatRegex = '[-+]?([0-9]*\.[0-9]+|[0-9]+)'; 
                    var estado_ejecucuion=true;
                    
                    
                    if(!txtserie_tmp.match(enteros)){
                        alert('Numero de serie, solo acepta numeros.'); 
                        estado_ejecucuion=false;
                    }
                    if(!txtnumero_tmp.match(enteros)){
                        alert('Numero correlativo, solo acepta numeros.'); 
                        estado_ejecucuion=false;
                    }
                    
                    /*if(!(txtreferencia_tmp.trim().length>2)){
                        alert('Ingrese referencia (minimo 2 caracteres).');
                        estado_ejecucuion=false;
                    }*/
                    if(!(txtimporte_tmp.match(floatRegex))){
                        alert('Ingrese solo numeros en el importe(acepta decimales) .');
                        estado_ejecucuion=false;
                    }
                    return estado_ejecucuion;
                      
                    
                }
                $.fn.validarCuentaCliente=function(){
                    var txtnumero_cuenta=$('#txtnumero_cuenta').val();
                    var cmbbanco_cliente=$('#cmbbanco_cliente').val();
                    var txtnombrecuenta=$('#txtnombrecuenta').val();
                    var txtinicuenta=$('#txtinicuenta').val();
                    
                    var enteros = /[0-9]+$/;
                    var estado_ejecucuion=true;
                    
                     
                    if(!(cmbbanco_cliente.trim()!="-1")){
                        alert('Seleccione Banco'); 
                        estado_ejecucuion=false;
                    }
                    if(!txtnumero_cuenta.match(enteros)){
                        alert('Numero de cuenta, solo acepta numeros.'); 
                        estado_ejecucuion=false;
                    }
                    
                    if(!(txtnombrecuenta.trim().length>4)){
                        alert('Ingrese nombre (minimo 5 caracteres).');
                        estado_ejecucuion=false;
                    }
                    if(!(txtinicuenta.trim().length>2)){
                        alert('Ingrese Iniciales .');
                        estado_ejecucuion=false;
                    }
                    return estado_ejecucuion;
                      
                    
                }
                $.fn.validarGeneral=function(){
                    var cmnsucursal_id=$('#cmnsucursal_id').val();
                    var fecha_mostar=$('#fecha_mostar').val();
                    var txttipo_cambio=$('#txttipo_cambio').val();
                    var id_observacion=$('#id_observacion').val();
                    
                    var floatRegex = '[-+]?([0-9]*\.[0-9]+|[0-9]+)'; 
                    var estado_ejecucuion=true;
                    
                     
                    if(!(fecha_mostar.trim().length==10)){
                        alert('Ingrese Fecha General'); 
                        estado_ejecucuion=false;
                    }
                    if(cmnsucursal_id=="-1"){
                        alert('Seleccione Sucursal.'); 
                        estado_ejecucuion=false;
                    }
                   
                    
                    if(!txttipo_cambio.match(floatRegex)){
                        alert('Ingrese Tipo cambio correcto.'); 
                        estado_ejecucuion=false;
                    }
                    
                    
                    return estado_ejecucuion;
                      
                    
                }
             
                var tmpclientes=null;
               
                /*------*/
                
                $('#content').remove();
                $('#footer').remove();
                var ventana_ancho = ($(window).width()/2)-250;
                
                ventana_ancho_px=ventana_ancho+"px";
                //$('.contenedorprincipal').css({'margin-left':ventana_ancho_px});
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
             
             
               
                $( "#fecha_inicial" ).datepicker( 
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_inicial" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $( "#fecha_final" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_final" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $('#btnguardar_cuenta').off('click');
                $(document).on('click','#btnguardar_cuenta',function(){
                    
                    var dataviaje=new Array();
                    var txtnumero_cuenta=$('#txtnumero_cuenta').val();
                    var cmbbanco_cliente=$('#cmbbanco_cliente').val();
                    var txtnombrecuenta=$('#txtnombrecuenta').val();
                    var txtinicuenta=$('#txtinicuenta').val();
                    dataviaje={'numero':txtnumero_cuenta,'banco':cmbbanco_cliente,'nombre':txtnombrecuenta,'inicuenta':txtinicuenta};
                    if($.fn.validarCuentaCliente()){
                        $('#cargardor').css({'display':'inline'});
                        $.ajax({
                            type: "POST",
                            url: "c_caja_egreso_relacion.php",
                            data: { accion:'guardar_cuenta_bancaria',data:dataviaje},
                            success:function(xm){
                                var obj=eval('('+xm+')');
                                alert(obj.mes);
                                $('#cargardor').css({'display':'none'});
                                irhome();
                            }
                        });
                    }
                    
                });
                
                $('#id_nuevo_cuenta_bank').click(function(){
                    $('#cargardor').css({'display':'inline'});
                    $.ajax({
                        type: "POST",
                        url: "c_caja_egreso_relacion.php",
                        data: { accion:'count_bank'},
                        success:function(xm){
                            $('#id_nuevo_registro_view').html(xm);
                            $('#cargardor').css({'display':'none'});
                        }
                    });
                });

		//CONSULTAR CLIENTES

                $('#btnseleccionar').click(function(){

			$('#cargardor').css({'display':'inline'});

			$.ajax({
		                type: "POST",
		                url : "c_caja_egreso_relacion.php",
		                data: {
					accion		: 'mostar_resultado_data',
					'fecha_inicio'	: $('#fecha_inicial').val(),
					'fecha_final'	: $('#fecha_final').val(),
					'sucursal'	: $('#serie_doc').val(),
					'limit_mostrar'	: $('#limit').val(),
					'ruc'		: $('#id_cliente_auto_ruc').val(),
                    'operacion' : $('#id_operacion').val(),
				},
                        	success:function(xm){
                            		$('#contenidoTablaSelecionar').html(xm);
                            		$('#cargardor').css({'display':'none'});
				}
			});

                });


                $('#Mostarmediopago_id').off('click');
                $(document).on('click','#Mostarmediopago_id',function(){
                    var c=0;
                
                    $('.fac_insertar').each(function(){
                        c++;
                    });
                    if(c>0){
                        $('#cargardor').css({'display':'inline'});
                        $.ajax({
                            type: "POST",
                            url: "c_caja_egreso_relacion.php",
                            data: { accion:'mostar_medio_pago'},
                            success:function(xm){
                                $('#medio_pago_externo_id').html(xm);
                                $( "#txtfecha" ).datepicker( 
                                {changeMonth: true,
                                    changeYear: true});
                            
                                $( "#txtfecha" ).datepicker("option", "dateFormat","yy-mm-dd");
                                $('#cargardor').css({'display':'none'});
                                $('#txtfecha').val($('#fecha_tmp').val());
                            }
                        });
                    }else{
                        alert('No se puede Crear formulario de medio de pago,ya que no ha ingresado ningun items.');
                    }
                });
                
		$('#datos_fac_tmp').off('click');

                var intzebra=0;

		//TABLA DE AGREGAR RECIBO SIN PROVEEDOR

                $(document).on('click','#datos_fac_tmp',function(){

			if($.fn.validardatosfactura() == true){

                        	var cmbtipo_doc		= $('#cmbtipo_doc').val();
		                var txtserie_tmp	= $('#txtserie_tmp').val();
		                var txtnumero_tmp	= $('#txtnumero_tmp').val();
		                var txtreferencia_tmp	= $('#txtreferencia_tmp').val();
		                var tipo_moneda		= $('#cmnmoneda_id').val();
		                var txtimporte_tmp	= $('#txtimporte_tmp').val();
		                var txttc_tmp		= $('#txttipo_cambio').val();
				var cmbtipo_moneda	= $('#cmbtipo_moneda').val();
		                var sebra		= "fila_registro_imppar";

                        	if(intzebra%2==0){
					sebra="fila_registro_imppar"; 
				}else{
                            		sebra="fila_registro_par";   
				}

                        	intzebra++;
                    
				if(tipo_moneda=='01' && cmbtipo_moneda=='02'){
					txtimportepay_tmp = (txtimporte_tmp * txttc_tmp);
				}else if(tipo_moneda=='02' && cmbtipo_moneda=='01'){
					txtimportepay_tmp = (txtimporte_tmp / txttc_tmp);
				}else{
					txtimportepay_tmp = txtimporte_tmp;
				}
	
		                var mone	= (cmbtipo_moneda=='01')?'S/':'$';
		                var monepay	= (tipo_moneda=='01')?'S/':'$';

                        	cmbtipo_doc_texto = $("#cmbtipo_doc option:selected").html();

		                var cadena_html=  "<tr class='"+sebra+"'>";
		                cadena_html+= "<td align='center'>" + cmbtipo_doc_texto+"</td>";
		                cadena_html+= "<td align='center'>" + txtserie_tmp + "</td>";
		                cadena_html+= "<td align='center'>" + txtnumero_tmp+"</td>";
		                cadena_html+= "<td align='center'>" + txtreferencia_tmp + "</td>";
		                cadena_html+= "<td align='center'>" + mone + "</td>";
		                cadena_html+= "<td align='right'>" + formato_numero(txtimporte_tmp, 2,'.') + "</td>";
		                cadena_html+= "<td></td>";
		                cadena_html+= "<td align='center'>" + txttc_tmp + "</td>";
		                cadena_html+= "<td align='center'>" + monepay + "</td>";
		                cadena_html+= "<td align='right'>" + formato_numero(txtimportepay_tmp, 2,'.') + "</td>";
		                cadena_html+= "<td class='td_tabla_selecinar'><input type='hidden' class='fac_insertar' name='doc_$i' value='"+cmbtipo_doc+"*"+txtserie_tmp+"*"+txtnumero_tmp+"*"+txtimporte_tmp+"*"+txtreferencia_tmp+"*"+cmbtipo_moneda+"' /></td></tr>";

		                $('#registros_sin_cliente').append(cadena_html);
		                $('#txtserie_tmp').val("");
		                $('#txtnumero_tmp').val("");
		                $('#txtreferencia_tmp').val("");
		                $('#txtimporte_tmp').val("");

			}
                  
		});

             	//BUSCADOR DE PROVEEDOR AUTOMATICO

		$("#keyword").keyup(function() {

			var keyword = $("#keyword").val();

			if (keyword.length >= MIN_LENGTH) {

				$.ajax({
                            		type: "POST",
                            		url: "c_caja_egreso_relacion.php",
                            		data: {accion:'SearchCliente', keyword: keyword},
		                    	success:function(data){
						$("#keyword").autocomplete({
							source: tmpclientes,
							select: function( event, ui ) {
								$('#id_cliente_auto_ruc').text(ui.item.id);
							}
						});
					}
                       		});

			} else {
				$('#results').html('');
			}

		});

		$("#keyword").blur(function(){
	    		$("#results").fadeOut(500);
	    	})

		.focus(function() {		
	    	    $("#results").show();
	    	});

                $('#id_nuevo_registro').click(function(){

                    $('#cargardor').css({'display':'inline'});
                    $('#contenidoTablaSelecionar').html("");
                    $.ajax({
                        type: "POST",
                        url: "c_caja_egreso_relacion.php",
                        data: { accion:'nuevo_registro'},
                        success:function(xm){
                            $('#cargardor').css({'display':'none'});
                            $('#id_nuevo_registro_view').html(xm);
                            $('#cargardor').css({'display':'none'});
				//CAMBIADO KWS OBTNER T.C.

				$( "#fecha_mostar" ).datepicker({
					changeMonth: true,
                                	changeYear: true,
					onSelect:function(fecha,obj){

		              			$('#cargardor').css({'display':'block'});
		               			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

						var fecha = $('#fecha_mostar').val();

						$.ajax({
						  	type: "POST",
							url: "c_caja_egreso_relacion.php",
						    	data:{
								accion:'TipoCambio',
								fecha:fecha,
						    	},
						    	success:function(xm){
								$('#cargardor').css({'display':'none'});
								$('#txttipo_cambio').val(xm);
							}
						});

					}
				});
                            
                            $( "#fecha_mostar" ).datepicker("option", "dateFormat","yy-mm-dd");
                            
                            /*$( "#id_cliente_auto" ).autocomplete({
                                source: tmpclientes,
                                select: function( event, ui ) {
                                    $('#id_cliente_auto_ruc').val(ui.item.id);
                                }
                                
                               
                            });*/
                            $('#fecha_mostar').val($('#fecha_tmp').val());
                            $('#txttipo_cambio').val();
                            
                        }
                    });
                });
                
                $('#cmnoperacion_id').off('change');
                $( document ).on( "change", "#cmnoperacion_id",function(){
                    var valor=$(this).val();
                    if(valor==1){
                        
                    }
                    return true;
                });

		//BUSCAR PROVEEDOR DOCUMENTOS POR PAGAR

                $('#btnbuscar').off('click');

                $(document).on('click','#btnbuscar',function(){
                    $( "#fecha_mostar" ).prop("disabled", false);

                    ArregloCliente = new Array();

                	var valor_dni 		= $('#id_cliente_auto_ruc').val();
                	var valor_tc 		= $('#txttipo_cambio').val();
                    var valor_moneda	= $('#cmnmoneda_id').val();
		
                	if(valor_dni=="" || valor_dni.length==0){
                    	alert('Error al buscar Facturas del Cliente :'+valor_dni.length);
                	}else{
                        $.ajax({
                            type: "POST",
                            url: "c_caja_egreso_relacion.php",
                            data: { accion:'tipo_documento_gennerar', fecha:$('#fecha_mostar').val()},
                            success:function(xm){
                                if (xm == 1 || xm == '1') {
                                    alert('Dia consolidado, seleccionar otra fecha !');
                                } else if (xm == 2 || xm == '2') {
                                    alert('La fecha seleccionada, es menor a la fecha de inicio del sistema');
                                } else{
                                    $( "#fecha_mostar" ).prop("disabled", true);
                                    $('#cargardor').css({'display':'inline'});
                                    $.ajax({
                                		type: "POST",
                                		url: "c_caja_egreso_relacion.php",
                                		data: { accion:'buscar_cliente',moneda:valor_moneda,tc:valor_tc,ruc:valor_dni,fecha:$('#fecha_mostar').val()},
                                		success:function(xm){
        		                    		$('#contenidoTablaSelecionar').html(xm);
        		                    		$('#cargardor').css({'display':'none'});
                                		}
                                    });
                                }
                            }
                        });
                    }
                });

		//FIN PROVEEDOR DOCUMENTOS POR PAGAR

		//AGREGAR DOCUMENTOS POR PAGAR

                $('#btnselec_elem').off('click');

                $(document).on('click','#btnselec_elem',function(){

			var valor_dni 		= $('#id_cliente_auto_ruc').val();
                    	var valor_tc 		= $('#txttipo_cambio').val();
			var valor_moneda	= $('#cmnmoneda_id').val();

			if(valor_dni=="" || valor_dni.length==0){

				alert('Error al buscar documentos Proveedor :'+valor_dni.length);

			}else{

                        	var areglo_documento=Array();
                        	var i=0;

                        	$('.cliente_selecionado').each(function(){
					if($(this).is(':checked')){
                                		areglo_documento[i]=$(this).val();
                                		i++;
                            		}
                       		});

                        	$('#cargardor').css({'display':'inline'});

                        	$.ajax({
                            		type: "POST",
                            		url: "c_caja_egreso_relacion.php",
                            		data: { accion:'buscar_cuenta_cobrar_recivo',moneda:valor_moneda,tc:valor_tc,ruc:valor_dni,num_doc:areglo_documento},
		                    	success:function(xm){
				                $('#contenidoTablaSelecionar').html(xm);
				                $('#cargardor').css({'display':'none'});
				                $( "#txtfecha" ).datepicker({changeMonth: true, changeYear: true});
				                $( "#txtfecha" ).datepicker("option", "dateFormat","yy-mm-dd");
				                $('#txtfecha').val($('#fecha_tmp').val());
					}
				});
			}
                });


		//GUARDAR MEDIO DE PAGO

		$('#guardartmpcliente').off('click');

                $(document).on('click','#guardartmpcliente',function(){
                                
			var cmbmediopago		= $('#cmbmediopago').val(); 
                    	var estado_validacion		= $('#cmbmediopago option:selected').attr('validacion');
                    	var txtomediopago 		= $("#cmbmediopago option:selected").html();
                    	var txtnum_referencia		= $('#txtnum_referencia').val(); 
                    	var txtfecha			= $('#txtfecha').val(); 
                    	var cmbbanco			= $('#cmbbanco').val(); 
                    	var txtobanco			= $("#cmbbanco option:selected").html();
                    	var cuentas_cmb_mostrar		= $('#cuentas_cmb_mostrar').val()    
                    	var txtmostarmoneda		= $('#txtmostarmoneda').val();
                    	var txtimporteG			= $('#txtimporteG').val();
                    	var floatRegex 			= '[-+]?([0-9]*\.[0-9]+|[0-9]+)'; 
                    	var estado_ejecucuion		= true;
		            
			if(estado_validacion=="1"){

                        	if(txtnum_referencia.trim().length==0){
                            		alert('Ingrese numero de referencia.');
                            		estado_ejecucuion=false; 
                        	}

                        	if(cmbbanco=="-1"){
                            		alert('Seleccione banco');
                            		estado_ejecucuion=false;
				}

                        	if(cuentas_cmb_mostrar=="-" ){
					alert('Seleccione Numero de cuenta');
					estado_ejecucuion=false;
				}
                    
			}else{
                        
                        	if(cmbbanco=="-1"){
                        		cmbbanco=0;
                        		txtobanco='-';
                        	}

				if(cuentas_cmb_mostrar=="-" || cuentas_cmb_mostrar==null){
					cuentas_cmb_mostrar=0;
                        	}

			}

                    	if(!txtimporteG.match(floatRegex)){
                        	alert('Ingrese Solo numero en Importe.'); 
                        	estado_ejecucuion=false;
			}
                    
                    	if(!txtnum_referencia.trim().length>5){
                        	txtnum_referencia='--'
			}

                    	if(txtfecha.trim().length!=10){
                        	alert('Ingrese Fecha.'); 
                        	estado_ejecucuion=false;
			}

			if(estado_ejecucuion==true){

                        	var monto_ir=parseFloat(txtimporteG);

                        	ArregloCliente[int_contador]={
								'medio_pago':cmbmediopago,
						    		'num_referencia':txtnum_referencia,
						    		'fecha':txtfecha,
						    		'banco':cmbbanco,
						    		'cuentas_cmb_mostrar':cuentas_cmb_mostrar,
						    		'mostarmoneda':txtmostarmoneda,
						    		'importeg':monto_ir
				};

				if(txtmostarmoneda=='02')
					txtmostarmoneda=txtmostarmoneda.trim();

				if(cuentas_cmb_mostrar!='0')			
					cuentas_cmb_mostrar=cuentas_cmb_mostrar.trim();

				if(cmbbanco=='1')
                        		cmbbanco=cmbbanco.trim();

                        	var cadena_mp=""+cmbmediopago+"*"+txtnum_referencia+"*"+txtfecha+"*"+cmbbanco+"*"+cuentas_cmb_mostrar+"*"+txtmostarmoneda+"*"+monto_ir;

                        	var valor=parseFloat($('#importe_fp_id').attr('value_se'));

		                var type_moneda = (txtmostarmoneda=='01')?'S/':'$';

                        	var cadena='<tr class="fila_registro_par">';
		                cadena+='<td>'+txtomediopago+'</td>';
		                cadena+='<td>'+txtnum_referencia+'</td>';
		                cadena+='<td>'+txtfecha+'</td>';
		                cadena+='<td>'+txtobanco+'</td>';
		                cadena+='<td>'+cuentas_cmb_mostrar+'</td>';
		                cadena+='<td>'+type_moneda+'</td>';
		                cadena+='<td style="text-align: right;" class="medio_pago" campos='+cadena_mp+'>'+formato_numero(txtimporteG, 2,'.')+'</td>';
		                cadena+='<td><button onclick="eliminarTr(this)">Eliminar</button></td>';
		                cadena+='</tr>';
		                cadena+='</tr>';
		                valor+=parseFloat(txtimporteG);
		                $('#importe_fp_id').attr('value_se',valor);
		                $('#importe_fp_id').html(formato_numero(valor, 2,'.'));
		                $('#registros_pymes').append(cadena);
		                int_contador++;
		                $.fn.limpiarmedio_pago();
			}

                });

		/* FIN GUARDAR MEDIO DE PAGO */

		/* GUARDANDO REGISTRO DE CAJA INGRESO FINAL */

                $('#finalizarproceso').off('click');
		$(document).on('click','#finalizarproceso',function(){

			if($.fn.validarGeneral()==true){
                        
	                        $('#cargardor').css({'display':'inline'});
	                        $('#finalizarproceso').css({'display':'none'});

	                        var datos_facturas_pagar=Array();
	                        var datos_principales=Array();
	                        var conta=0;

	                        //atrapamos las data de las cajas de texto box;

                        	var almacen		= $('#cmnsucursal_id').val();
                        	var txttipo_cambio	= $('#txttipo_cambio').val();
		                var cmnmoneda_id	= $('#cmnmoneda_id').val();
                       	 	var num_recibo		= $('#recibe_nro').val();
				var fecha_general	= $('#fecha_mostar').val();
                        	var caja		= $('#cmncaja_id').val();
                        	var tipo_operacion	= $('#cmnoperacion_id').val();
                        	var observacion		= $('#id_observacion').val();
                        	var ruc_cliente		="";

	   	                if($('#tipo_accion').val()=="con_cliente"){
					ruc_cliente=$('#id_cliente_auto_ruc').val();
			        }else{
					ruc_cliente='99999999';
			        }
		            
		        	datos_principales = [{
							'almacen'		:almacen,
							'num_recibo'		:num_recibo,
							'fecha_general'		:fecha_general,
		                			'caja'			:caja,
							'tipo_operacion'	:tipo_operacion,
							'observacion'		:observacion,
			     	                   	"ruc_cliente"		:ruc_cliente,
							"txttipo_cambio"	:txttipo_cambio,
							"cmnmoneda_id"		:cmnmoneda_id
						}];

				$('.fac_insertar').each(function(){
		            		datos_facturas_pagar[conta]=$(this).val();
		            		conta++;
				});
                        
		                var medio_pago_tmp=new Array();
		                // var cadena_mp=""+cmbmediopago+"*"+txtnum_referencia+"*"+txtfecha+"*"+cmbbanco+"*"+cuentas_cmb_mostrar+"*"+txtmostarmoneda+"*"+monto_ir;
		                
                        	var cc=0;

                        	$('.medio_pago').each(function(){

		                    	var cadena_mp_split=$(this).attr('campos').split("*");
		                    
		                    	medio_pago_tmp[cc]={
				                'medio_pago':cadena_mp_split[0],
				                'num_referencia':cadena_mp_split[1],
				                'fecha':cadena_mp_split[2],
				                'banco':cadena_mp_split[3],
				                'cuentas_cmb_mostrar':cadena_mp_split[4],
				                'mostarmoneda':cadena_mp_split[5],
				                'importeg':cadena_mp_split[6]
		                    	};

		                    	cc++;

                        	});

                       		$.ajax({
		                    	type: "POST",
		                    	url: "c_caja_egreso_relacion.php",
		                    	data: { accion:'finalizar_proceso',datos_medio_pago:medio_pago_tmp,datos_factura:datos_facturas_pagar,'datos_generales':datos_principales,'tipo_accion':$('#tipo_accion').val()},
		                    	success:function(xm){

		                        	$('#cargardor').css({'display':'none'});

		                        	int_contador=0;

		                        	var obj=eval('('+xm+')');

		                        	if(obj.estado=='error'){
		                            		alert(obj.mes);  
		                            		$("#finalizarproceso").css({"display":"inline"});
		                        	}else if(obj.estado=='bien'){
		                            		alert(obj.mes); 
		                            		irhome();
						}
                   
					}

				});

			}

                });

                $.ajax({
                	type: "POST",
                	url: "c_caja_egreso_relacion.php",
                	dataType: 'json',
                    data: {
                        accion:'tipodocumento',
                        documento:"-",
                    },
                	success:function(xm) {
						//var obj=eval('('+xm+')');
						//var obj=eval('('+xm+')');
						//alert(xm.dato);
						//tmpclientes=obj.cliente;
						$('#cmbtipooperacion').html(xm.dato);
						//$('#cmbtipooperacion').html('hola');
						$('#cargardor').css({'display':'none'});
						$('#fecha_inicial').val($('#tmp_ini').val());
						$('#fecha_final').val($('#tmp_final').val());
					}
				});// /. Ajax Clientes

				$.ajax({
					type: "POST",
					url: "c_caja_egreso_relacion.php",
					data: { accion:'cmboperacion',documento:"-"},
					success:function(xm) {
						var obj=eval('('+xm+')');
						tmpop=obj.operacion;
						$('#cmboperacion').html(obj.dato);
						$('#cargardor').css({'display':'none'});
					}
				});

                  $('#executar_excel').click(function(){
                    
                    $('#cargardor').css({'display':'block'});
                    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                        'top': 200 + 'px'
                    });
                    $.ajax({
                        type: "POST",
                        url: "c_caja_egreso_relacion.php",
                        data: { 
                        accion:'executar_reporte_excel',
                            fecha_inicio  : $('#fecha_inicial').val(),
                            fecha_final  : $('#fecha_final').val(),
                            sucursal  : $('#serie_doc').val(),
                            limit_mostrar : $('#limit').val(),
                            ruc    : $('#id_cliente_auto_ruc').html(),
                            operacion : $('#id_operacion').val()
                        },
                        success:function(xm){ 
                            $('#cargardor').css({'display':'none'});
                            location.href="/sistemaweb/combustibles/movimientos/reporte_excel_caja_egreso.php";
                              
                        }
                    });
                });

            });


            function autocompleteBridge(type) {
                if (type == 0) {
                    //new
                    var No_Producto = $("#id_cliente_auto");
                    if(No_Producto.val() !== undefined) {
                        generalAutocomplete('#id_cliente_auto', '#id_cliente_auto_ruc', 'getPartnersByRucOrName', []);
                    }
                } else {
                    //buscar

                }
            }

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

        <?php
        include('/sistemaweb/include/mvc_sistemaweb.php');
        include('movimientos/t_egreso_caja.php');
        include('movimientos/m_egreso_caja.php');
        include('movimientos/c_egreso_caja.php');
        $objtem = new RegistroCajasTemplate();
        echo RegistroCajasTemplate::FormularioPrincipal();
        ?>
        <script type="text/javascript">
            //   $( "#fecha_inicio" ).datepicker( "option", "dateFormat","yy-mm-dd");
          
        </script>
    </body>
</html>
