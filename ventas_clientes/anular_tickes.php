<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>

        <title>Anular ticket</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
		<script type="text/javascript">

			$(function(){            
              
               			$.datepicker.regional['es'] = {
					    closeText: 'Cerrar',
					    prevText: '<Ant',
					    nextText: 'Sig>',
					    currentText: 'Hoy',
					    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
					    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
					    dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
					    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
					    weekHeader: 'Sm',
					    dateFormat: 'dd/mm/yy',
					    firstDay: 1,
					    isRTL: false,
					    showMonthAfterYear: false,
					    yearSuffix: ''
				};
             
			        $.datepicker.setDefaults($.datepicker.regional['es']); 
	               
                		$( "#fecha_inicial" ).datepicker({
					changeMonth: true,
					changeYear: true,
					onSelect:function(fecha,obj){

						var data		= new Array();
						var txtnualmacen 	= $('#nualmacen').val();
						var txtfecha 		= $('#fecha_inicial').val();

						if(txtnualmacen == 'T'){
							alert('Debes de seleccionar un Almacen');
						}else{

							$('#cargardor').css({'display':'block'});
						     	$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

							data = {
									'accion'	: 'Turnos',
									'txtnualmacen'	: txtnualmacen,
									'txtfecha'	: txtfecha
							};

							$.ajax({
							  	type	: "POST",
							    	url	: "c_anular_tickes_relacion.php",
							    	data	: {data:data},
							    	success	: function(xm){

									if(xm == 'Error'){

										$('#cargardor').css({'display':'none'});
										$('#opt_final').html("");
										$('#tab_turnos').html("No hay turnos en esta fecha");
										$('#txtnum_caja').html("");
										$('#tab_cajas').html("No hay cajas en esta fecha");

									}else{

										$('#cargardor').css({'display':'none'});

										var json = eval('('+xm+')');

										$('#txtnum_turno').html(json.msg);
										$('#cargardor').css({'display':'none'});
										$('#txtnum_caja').html(json.msg2);
										$('#tab_turnos').html("");
										$('#tab_cajas').html("");

									}

								}

							});
						}
					}
					
				});

				/* BUSCAR TICKET */
				$(document).on('click','#btnseleccionar',function(){

					$('#table_fill').html("");
					$('#table_anular_final').html("");

					var data		= new Array();
					var txtnualmacen 	= $('#nualmacen').val();
					var txttickes		= $('#txtnum_tickes').val();
					var txtcaja		= $('#txtnum_caja').val();
					var txtfecha		= $('#fecha_inicial').val();
					var txttd		= $('#txtnum_td').val();
					var txttv		= $('#txtnum_tv').val();
					var txttm		= $('#txtnum_tm').val();
					var txtturno		= $('#txtnum_turno').val();

					data = {
						accion		: 'buscar',
						'txtnualmacen'	: txtnualmacen,
						'txttickes'	: txttickes,
						'txtcaja'	: txtcaja,
						'txtfecha'	: txtfecha,
						'txttd'		: txttd,
						'txttv'		: txttv,
						'txtturno'	: txtturno,
						'txttm'		: txttm
					};

					if(txtnualmacen == 'T'){
						alert('Debes seleccionar un Almacen');
					}else if(txtcaja.length < 1){
						alert('Debes seleccionar una Caja');
					}else if(txtturno.length < 1){
						alert('Debes seleccionar un Turno');
					}else{

						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

						$.ajax({
							type	: "POST",
							url	: "c_anular_tickes_relacion.php",
							data	: {data:data},
							success	: function(xm){

								$('#cargardor').css({'display':'none'});

							    	var obj			= eval('('+xm+')');
							    	var table_fill_json	= "";
							    	window.objarray		= obj;

							    	if(obj.estado == 'S'){

									table_fill_json += "<tr><td align='center' colspan='6'><a>Nro. Ticket "+txttickes+" </a></td></tr>";

									if(obj.dato[0]['td'] == 'Nota Despacho'){

										table_fill_json += "<tr><td align='center' colspan='6'><br/>";

										table_fill_json += "<tr><td align='center' colspan='6'><a> "+obj.dato[0]['td']+" </a></td></tr>";
										table_fill_json += "<tr><td align='center' colspan='6'><a> Cuenta: "+obj.dato[0]['cuenta']+" </a></td></tr>";
										table_fill_json += "<tr><td align='center' colspan='6'><a> Tarjeta: "+obj.dato[0]['tarjeta']+" </a></td></tr>";
										table_fill_json += "<tr><td align='center' colspan='6'><a> Placa: "+obj.dato[0]['placa']+" </a></td></tr>";

									} else if(obj.dato[0]['td'] == 'Factura'){

										table_fill_json += "<tr><td align='center' colspan='6'><br/>";

										table_fill_json += "<tr><td align='center' colspan='6'><a> "+obj.dato[0]['td']+" </a></td></tr>";
										table_fill_json += "<tr><td align='center' colspan='6'><a> Ruc: "+obj.dato[0]['ruc']+" </a></td></tr>";

									}

									table_fill_json += "<tr><td align='center' colspan='6'><br/>";

									table_fill_json +=	"<tr>"+
													"<td align='center'><a>Producto</a></td>"+
													"<td align='center'><a>Cantidad</a></td>"+
													"<td align='center'><a>Precio</a></td>"+
													"<td align='center'><a>Sub-Total</a></td>"+
													"<td align='center'><a>IGV (18.00%)</a></td>"+
													"<td align='center'><a>Importe</a></td>"+
												"</tr>";

									for(i = 0; i < obj.dato.length; i++){

										table_fill_json += "<tr><td align='left'>"+obj.dato[i]['codigo']+" - "+obj.dato[i]['producto']+"</td>";
										table_fill_json += "<td align='right'>"+obj.dato[i]['cantidad']+"</td>";
										table_fill_json += "<td align='right'>"+obj.dato[i]['precio']+"</td>";
										table_fill_json += "<td align='right'>"+obj.dato[i]['bi']+"</td>";
										table_fill_json += "<td align='right'>"+obj.dato[i]['igv']+"</td>";
										table_fill_json += "<td align='right'>"+obj.dato[i]['importe']+"</td></tr>";

									}

									table_fill_json += "<tr><td align='center' colspan='6'><br/>";

									table_fill_json += "<tr><td colspan='6' style='text-align: center;'><button id='btnanularinicial'><img align='right' src='/sistemaweb/icons/gdelete.png'/>Anular</button></td></tr>"   ;

									$('#table_fill').html(table_fill_json);

								}else{
									alert(obj.msg);
								}
							}
						});
					}
				});

				$('#btnanularinicial').off('click');

				//BUTTON ANULAR
				$(document).on('click','#btnanularinicial',function(){
                    
					var table_fill_json	= "";
					var obj			= objarray;

					table_fill_json += "<tr><td colspan=8 style='text-align: center;'><a>Confirmacion de la Anulacion</a></td></tr>";				    
					table_fill_json += "<tr><td align='center' colspan='6'><br/>";
					table_fill_json += "<tr><td colspan='6' style='color: red;' align='left'><input type='checkbox' id='chk_validacion1' name='chk_validacion1' value='SI'/>Esta seguro que desea anular el ticket</td></tr>"   ;
					table_fill_json += "<tr><td colspan='6' style='color: red;' align='left'><input type='checkbox' id='chk_validacion2' name='chk_validacion2' value='SI'/>Esta consciente que una vez que se anule el ticket ya no prodra revertir el ticket</td></tr>";
					table_fill_json += "<tr><td align='center' colspan='6'><br/>";
					table_fill_json += "<tr><td colspan='6' style='text-align: center;'><button id='btnanularfinal'><img align='right' src='/sistemaweb/icons/gdelete.png'/>Anular Definitivamente</button></td></tr>"   ;

					$('#table_anular_final').html(table_fill_json); 

				});

				//BUTTON ANULAR

				$('#btnanularfinal').off('click');

				$(document).on('click','#btnanularfinal',function(){

					if($('#chk_validacion1').is(':checked') && $('#chk_validacion2').is(':checked')){

						var dataviaje		= new Array();
						var txtnualmacen 	= $('#nualmacen').val();
						var txttickes		= $('#txtnum_tickes').val();
						var txttickes		= $('#txtnum_tickes').val();
						var txtcaja		= $('#txtnum_caja').val();
						var txtfecha		= $('#fecha_inicial').val();
						var txttd		= $('#txtnum_td').val();
						var txttv		= $('#txtnum_tv').val();
						var txttm		= $('#txtnum_tm').val();
				    		var txtturno		= $('#txtnum_turno').val();

						dataviaje={'txtnualmacen':txtnualmacen, 'txttickes':txttickes,'txtcaja':txtcaja,'txtfecha':txtfecha,'txttd':txttd,'txttv':txttv,'txtturno':txtturno,'txttm':txttm,"accion":"anular_tickes","chk_validacion1":$('#chk_validacion1').val(),"chk_validacion2":$('#chk_validacion2').val()};

						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

						$.ajax({

							type	: "POST",
							url	: "c_anular_tickes_relacion.php",
							data	: {data:dataviaje},
							success	: function(xm){
								$('#cargardor').css({'display':'none'});

								var obj = eval('('+xm+')');

								if(obj.estado == 'SI' || obj.estado == 'si'){
									alert('Anulacion Finalizada');
									$('#table_anular_final').html(""); 
									$('#table_fill').html(""); 
								}else{
									alert('Problemas al realizar la anulacion, consulte con el area de soporte'); 
								}
							}

						});
						       
					}else{
						alert('Tienes que aceptar las condicones para poder Anular');
					}

				});
					     
			});

		</script>

    </head>
    <body>
        <?php include "../menu_princ.php"; ?>
        	<div id="content">
		    	<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
		    	<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
        	</div>
        	<div id="footer">&nbsp;</div>
		<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
        	</div>

		<div id="footer">&nbsp;</div>

        <?php
		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('Anular_tickes/t_anular_tickes.php');
		include('Anular_tickes/m_anular_tickes.php');
		include('Anular_tickes/c_anular_tickes.php');

		$objtem		= new AnularTickesTemplate();
		$estaciones	= AnularTickesModel::GetAlmacen();

		echo AnularTickesTemplate::FormularioPrincipal($estaciones);

        ?>
    </body>
</html>
