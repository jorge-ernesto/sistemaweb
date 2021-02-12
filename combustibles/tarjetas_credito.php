<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>
        <title>Tarjetas de Credito</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript"> 
            
            $(document).ready(function(){
        		getTurnosxFecha();

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
                            url: "reportes/c_tarjetas_credito.php",
                            data: { accion:'fecha_servidor',fecha_inicial:fecha
                            },
                            success:function(xm){
			                	$('#cargardor').css({'display':'none'});
			                	if(xm.length < 50){
									$('#opt_inicial').html('<option> ' + xm + ' </option>');
			                	} else {
				                    var json=eval('('+xm+')');
				                    $('#opt_inicial').html(json.msg);
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
                            url: "reportes/c_tarjetas_credito.php",
                            data: { accion:'fecha_servidor',fecha_inicial:fecha
                            },
                            success:function(xm){
			                	$('#cargardor').css({'display':'none'});
			                	if(xm.length < 50){
									$('#opt_final').html('<option> ' + xm + ' </option>');
			                	} else {
				                    var json=eval('('+xm+')');
				                    $('#opt_final').html(json.msg);
			                	}
                            }
                        });
                    }
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
				            url: "reportes/c_tarjetas_credito.php",
				            data: { accion:'buscar',
				                almacen:$('#almacen').val(),
				                fecha_ini:$('#fecha_inicial').val(),
				                fecha_fin:$('#fecha_final').val(),
				                turno_ini:$('#opt_inicial').val(),
				                turno_fin:$('#opt_final').val(),
				                tipo:$('#tipo').val(),
				                tarjeta:$('#tarjeta').val()
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
				            url: "reportes/c_tarjetas_credito.php",
				            data: { accion:'excel',
				                almacen:$('#almacen').val(),
				                fecha_ini:$('#fecha_inicial').val(),
				                fecha_fin:$('#fecha_final').val(),
				                turno_ini:$('#opt_inicial').val(),
				                turno_fin:$('#opt_final').val(),
				                tipo:$('#tipo').val(),
				                tarjeta:$('#tarjeta').val()
				            },
				            success:function(xm){
								$('#cargardor').css({'display':'none'});
				            	location.href="/sistemaweb/combustibles/reportes/tarjetas_credito_excel.php";
				            }
				        });
					}
                });
	        });
		
			function getTurnosxFecha(){
			    $('#cargardor').css({'display':'block'});
			    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				$.ajax({
	                type: "POST",
	                url: "reportes/c_tarjetas_credito.php",
	                data: {
	                	accion 			: 'fecha_servidor',
	                	fecha_inicial 	: $( "#fecha_inicial" ).val()
	                },
	                success:function(xm){
	                	$('#cargardor').css({'display':'none'});
	                	if(xm.length < 50){
							$('#opt_inicial').html('<option> ' + xm + ' </option>');
							$('#opt_final').html('<option> ' + xm + ' </option>');
	                	} else {
		                    var json=eval('('+xm+')');
		                    $('#opt_inicial').html(json.msg);
		                    $('#opt_final').html(json.msg);
	                	}
	                }
	            })
			}
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
		include('reportes/t_tarjetas_credito.php');
		include('reportes/m_tarjetas_credito.php');
		extract($_REQUEST);

		$objtem = new Tarjetas_Credito_Template();
		$accion = $_REQUEST['accion'];
		$hoy	= date('d/m/Y');

		$tarjetas 	= Tarjetas_Credito_Model::ObtenerTarjetas();
		$estaciones	= Tarjetas_Credito_Model::ObtenerEstaciones();
		echo Tarjetas_Credito_Template::Inicio($tarjetas, $estaciones, $hoy);
		

        ?>

    </body>
</html>
