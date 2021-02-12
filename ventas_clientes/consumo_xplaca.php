<!DOCTYPE html>
<html>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consumos por Placa</title>
	<?php include "../header2.php"; ?>
	<?php include "../footer2.php"; ?>
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

				$("#fecha_inicial").datepicker({
					changeMonth: true,
	        		changeYear: true,
				});

		        $("#fecha_final").datepicker({
					changeMonth: true,
        		    changeYear: true,
				});

				$("#buscar").click(function(){
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					$.ajax({
						type : "POST",
						url : "reportes/c_consumo_xplaca.php",
						data : {
							accion		: 'buscar',
				        	almacen		: $('#almacen').val(),
				        	fecha_ini	: $('#fecha_inicial').val(),
				        	fecha_fin	: $('#fecha_final').val(),
				        	cliente		: $('#cliente').val(),
				        	placa		: $('#placa').val(),
							sMostrarReporte : $('[name="radio-mostrar_reporte"]:checked').attr('value'),
				    	},
					    success:function(xm){
							$('#cargardor').css({'display':'none'});
					       	$('#tab_id_detalle').html(xm);
						}
					});
		        });
			});

			function pdf(){
				var almacen		= $('#almacen').val();
				var fecha_ini	= $('#fecha_inicial').val();
				var fecha_fin	= $('#fecha_final').val();
				var cliente		= $('#cliente').val();
				var placa		= $('#placa').val();
				var sMostrarReporte = $('[name="radio-mostrar_reporte"]:checked').attr('value');
				//location.href 	= "/sistemaweb/ventas_clientes/reportes/c_consumo_xplaca.php?accion=pdf&almacen="+almacen+"&fecha_ini="+fecha_ini+"&fecha_fin="+fecha_fin+"&cliente="+cliente+"&placa="+placa;
				var url = "/sistemaweb/ventas_clientes/reportes/c_consumo_xplaca.php?accion=pdf&almacen="+almacen+"&fecha_ini="+fecha_ini+"&fecha_fin="+fecha_fin+"&cliente="+cliente+"&placa="+placa+"&sMostrarReporte="+sMostrarReporte;
				var win = window.open(url, '_blank');
				win.focus();
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
		include('reportes/t_consumo_xplaca.php');
		include('reportes/m_consumo_xplaca.php');

		$objtem = new Consumos_Placa_Template();
		$accion = $_REQUEST['accion'];
		$hoy = date('d/m/Y');

		if($accion == "ni"){
			echo Consumos_Placa_Template::FormularioPrincipal();
		}else{
			$estaciones	= Consumos_Placa_Model::ObtenerEstaciones();
			echo Consumos_Placa_Template::Inicio($estaciones, $hoy);
		}
        ?>
    </body>
</html>
