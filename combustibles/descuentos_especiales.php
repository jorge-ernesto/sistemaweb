<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>
        <title>Descuentos Especiales</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript"> 

		$(document).ready(function(){

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

                	$( "#fecha_inicial" ).datepicker({
				changeMonth: true,
        		        changeYear: true,
				onSelect:function(fecha,obj){

			                $('#cargardor').css({'display':'block'});
			                $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			                $.ajax({
			                	type: "POST",
			                	url: "reportes/c_descuentos_especiales.php",
			                	data: { accion:'fecha_servidor',fecha_inicial:fecha
			            		},
			               		success:function(xm){
							if(xm == 'Error'){
								$('#cargardor').css({'display':'none'});
							        $('#tab_id_detalle').html("No hay turnos en esta fecha");
							}else{
								var json=eval('('+xm+')');
								$('#opt_inicial').html(json.msg);
								$('#cargardor').css({'display':'none'});
							        $('#tab_id_detalle').html("");
							}
						}
			                });
				}    
                	});
                
                	$( "#fecha_final" ).datepicker({
				changeMonth: true,
                    		changeYear: true,
                    		onSelect:function(fecha,obj){

                       	 		$('#cargardor').css({'display':'block'});
                        		$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

                        		$.ajax({
				            	type: "POST",
				            	url: "reportes/c_descuentos_especiales.php",
				            	data: { accion:'fecha_servidor',fecha_final:fecha
				            	},
				            	success:function(xm){ 
			 				if(xm == 'Error'){
								$('#cargardor').css({'display':'none'});
								$('#tab_id_detalle').html("No hay turnos en esta fecha");
							}else{
								var json=eval('('+xm+')');
								$('#opt_final').html(json.msg);
								$('#cargardor').css({'display':'none'});
								$('#tab_id_detalle').html("");
							}
				            	}
                        		});
                    		}
                	});

			//GET PRODUCTO DE DESCUENTO

		       $('#txtnum_caja').change(function(){

				var nualmacen	= $('#almacen option:selected').val();
		                var dtfecha	= $('#fecha').val();
		                var nuticket	= $('#txtnum_tickes').val();
		                var nucaja	= $(this).val();

				$('#cargardor').css({'display':'block'});
		               	$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

		                $.ajax({
					type		: "POST",
					url		: "reportes/c_descuentos_especiales.php",
					dataType	: "JSON",
		                    	data		: {
								accion		: 'GetProductCode',
								nualmacen	: nualmacen,
								dtfecha		: dtfecha,
								nuticket	: nuticket,
								nucaja		: nucaja
					},
					success: function(data){
						if(data == false){
							$(".DetailError").show("slow");
							$(".DetailMsg").hide("slow");
							$(".DetailDescuento").hide("slow");
							
						} else{
							if (data[0].status == 'D'){
								$(".DetailMsg").show("slow");
								$(".DetailAction").show("slow");
								$(".DetailError").hide("slow");
								$("#guardar").prop("disabled", true);
							}else{
								$(".DetailDescuento").show("slow");
								$(".DetailAction").show("slow");
								$(".DetailError").hide("slow");
								$("#guardar").prop("disabled", false);
								for(var i = 0; i < data.length; i++){
									$('#nuproducto').val(data[i].codigo);
									$('#nocliente').val(data[i].nocliente);
									$('#nuimporte').val(data[i].nudescuento);
								}
							}
						}

						$('#cargardor').css({'display':'none'});

					}
		                });

		        });

			$('#agregar').click(function(){

        			location.href="/sistemaweb/combustibles/descuentos_especiales.php?accion=ni";

	                });

        		$("#fecha").datepicker({
				changeMonth: true,
				changeYear: true,
				onSelect:function(fecha,obj){

					$('#cargardor').css({'display':'block'});
				     	$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					var fecha 	= $('#fecha').val();
					var almacen	= $('#almacen').val();

					data = {
						accion			: 'Turnos',
						'txtnualmacen'	: almacen,
						'txtfecha'		: fecha
					}
					datos = data;
					//datos = {'txtnualmacen':almacen,'txtfecha':fecha,"accion":"Turnos"};

					$.ajax({

					  	type: "POST",
					    	url: "/sistemaweb/ventas_clientes/c_anular_tickes_relacion.php",
					    	data:{data:datos},
					    	success:function(xm){

							if(xm == 'Error'){

								$('#cargardor').css({'display':'none'});
								$('#opt_final').html("");
								$('#tab_turnos').html("No hay turnos en esta fecha");
								$('#txtnum_caja').html("");
								$('#tab_cajas').html("No hay cajas en esta fecha");

							}else{

								var json=eval('('+xm+')');
								$('#txtnum_turno').html(json.msg);
								$('#cargardor').css({'display':'none'});
								$('#txtnum_caja').html(json.msg2);
								$('#tab_turnos').html("");
								$('#tab_cajas').html("");
							}

						}

					});
	
				}
				
			});

                	$('#guardar').click(function(){

				if($('#fecha').val().length < 1){
					alert('Seleccionar Fecha Emision');
				}else if($('#txtnum_tickes').val().length < 1){
					alert('Ingresar Nro. Ticket');
				}else{

					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					$.ajax({
						type: "POST",
						url: "reportes/c_descuentos_especiales.php",
						data: {
							accion		: 'guardar',
						        nualmacen	: $('#almacen').val(),
						        dtfecha		: $('#fecha').val(),
						        nuticket	: $('#txtnum_tickes').val(),
						        nucaja		: $('#txtnum_caja').val(),
						        nuproducto	: $('#nuproducto').val(),
						        nuprecio	: $('#nuimporte').val()
						},
						success:function(xm){

							if(xm == 'Error'){
								$('#cargardor').css({'display':'none'});
								alert('Error: No existe el Nro. ticket: ' + $('#txtnum_tickes').val() + ' Caja: ' + $('#txtnum_caja').val());
							} else {
								$('#cargardor').css({'display':'none'});
								alert('Datos guardados correctamente');
								location.href="/sistemaweb/combustibles/descuentos_especiales.php";
							}

						}
					});

				}

	                });

                	$('#regresar').click(function(){
	                	location.href="/sistemaweb/combustibles/descuentos_especiales.php";
	                });
                

                	$('#buscar').click(function(){

				if($('#fecha_inicial').val() > $('#fecha_final').val()){
					alert('La fecha inicial no puede ser mayor a la final');
				}else if($('#fecha_inicial').val().length < 1){
					alert('Seleccionar una Fecha inicial');
				}else if($('#fecha_final').val().length < 1){
					alert('Seleccionar una Fecha final');
				}else{
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					$.ajax({
						    type: "POST",
						    url: "reportes/c_descuentos_especiales.php",
						    data: {
							accion		:'buscar',
						        almacen		:$('#almacen').val(),
						        fecha_ini	:$('#fecha_inicial').val(),
						        fecha_fin	:$('#fecha_final').val(),
						        turno_ini	:$('#opt_inicial').val(),
						        turno_fin	:$('#opt_final').val(),
						        tv		:$('#txtnum_tv').val(),
						        td		:$('#txtnum_td').val(),
						        tarjeta		:$('#tarjeta').val(),
						    },
						    success:function(xm){
							$('#cargardor').css({'display':'none'});
						        $('#tab_id_detalle').html(xm);
						    }
					});

				}

                	});

                	$('#excel').click(function(){
				if($('#fecha_inicial').val() > $('#fecha_final').val()){
					alert('La fecha inicial no puede ser mayor a la final');
				}else if($('#fecha_inicial').val().length < 1){
					alert('Seleccionar una Fecha inicial');
				}else if($('#fecha_final').val().length < 1){
					alert('Seleccionar una Fecha final');
				}else{
				    $('#cargardor').css({'display':'block'});
				    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				     $.ajax({

				            type: "POST",
				            url: "reportes/c_descuentos_especiales.php",
				            data: { accion:'excel',
				                almacen		:$('#almacen').val(),
				                fecha_ini	:$('#fecha_inicial').val(),
				                fecha_fin	:$('#fecha_final').val(),
				                turno_ini	:$('#opt_inicial').val(),
				                turno_fin	:$('#opt_final').val(),
				                tv		:$('#txtnum_tv').val(),
				                td		:$('#txtnum_td').val(),
				                tarjeta		:$('#tarjeta').val(),
				            },

				            success:function(xm){
						$('#cargardor').css({'display':'none'});
				            	location.href="/sistemaweb/combustibles/reportes/descuentos_especiales_excel.php";
				              
				            }
				        });
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
        <?php

		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_descuentos_especiales.php');
		include('reportes/m_descuentos_especiales.php');

		$objtem = new Descuentos_Especiales_Template();
		$accion = $_REQUEST['accion'];
		$desde	= date('d/m/Y');
		$hasta	= date('d/m/Y');

		if($accion == "ni") {
			$estaciones	= Descuentos_Especiales_Model::ObtenerEstaciones();
			$lados		= Descuentos_Especiales_Model::obtieneLados();
			echo Descuentos_Especiales_Template::AgregarDescuento($estaciones, $lados);
		} else {
			$estaciones	= Descuentos_Especiales_Model::ObtenerEstaciones();
			$tarjetas 	= Descuentos_Especiales_Model::ObtenerTarjetas();
			echo Descuentos_Especiales_Template::Inicio($estaciones, $desde, $hasta, $tarjetas);
		}

        ?>

    </body>
</html>
