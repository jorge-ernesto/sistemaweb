<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>
        <title>Detalle de Consumo de Vales</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
	    <link rel="stylesheet" href="/sistemaweb/assets/css/style.css">
	    <script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-1.12.4.js"></script>
	    <script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
	    <script type="text/javascript" src="/sistemaweb/assets/js/helper/autocomplete.js"></script>

        <script  type="text/javascript">
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

				$( "#fecha_inicio" ).datepicker({
					changeMonth: true,
					changeYear: true,
					onClose: function (selectedDate) {
						$("#fecha_final").datepicker("option", "minDate", selectedDate);
					},
				});

			    $( "#fecha_final" ).datepicker({
			    	changeMonth: true,
			    	changeYear: true,
					minDate: $("#fecha_inicio").val(),
					onClose: function (selectedDate) {
						$("#fecha_inicio").datepicker("option", "maxDate", selectedDate);
					}
			    });

				$('#buscar').click(function(){
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					var Nu_Documento_Identidad = $('#txt-Nu_Documento_Identidad').val();
					var No_Razsocial = $('#txt-No_Razsocial').val();
					if(No_Razsocial.length === 0)
						Nu_Documento_Identidad = '';

					$.ajax({
						type: "POST",
						url: "reportes/c_consumo_vales.php",
						data: {
							accion:'buscar',
					        almacen:$('#almacen').val(),
					        fecha_ini:$('#fecha_inicio').val(),
					        fecha_fin:$('#fecha_final').val(),
					        Nu_Documento_Identidad:Nu_Documento_Identidad,
					        liquidacion:$('#liquidacion').val(),
					        factura:$('#factura').val(),
							orden:$('[name="myorden"]:checked').attr('value'),
							hora: $("#chk-hora").prop("checked"),
							sPrecioPizarra: $("#chk-precio_pizarra").prop("checked"),
					        iTipoCliente:$('#cbo-tipo-cliente').val(),
							  iTipoVersion:$('#cbo-tipo-version').val(),
						},
						success:function(xm){
							$('#cargardor').css({'display':'none'});
						        $('#tab_id_detalle').html(xm);
						}
					});
				});

				$('#excel').click(function(){
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

					var Nu_Documento_Identidad = $('#txt-Nu_Documento_Identidad').val();
					var No_Razsocial = $('#txt-No_Razsocial').val();
					if(No_Razsocial.length === 0)
						Nu_Documento_Identidad = '';

					$.ajax({
						type: "POST",
						url: "reportes/c_consumo_vales.php",
						data: {
							accion:'excel',
					        almacen		:$('#almacen').val(),
					        fecha_ini	:$('#fecha_inicio').val(),
					        fecha_fin	:$('#fecha_final').val(),
							Nu_Documento_Identidad : Nu_Documento_Identidad,
					        liquidacion	:$('#liquidacion').val(),
					        factura		:$('#factura').val(),
							orden		:$('[name="myorden"]:checked').attr('value'),
							hora: $("#chk-hora").prop("checked"),
							sPrecioPizarra: $("#chk-precio_pizarra").prop("checked"),
					        iTipoCliente:$('#cbo-tipo-cliente').val(),
							  iTipoVersion:$('#cbo-tipo-version').val(),
						},
						success:function(xm){
							$('#cargardor').css({'display':'none'});
							location.href="/sistemaweb/ventas_clientes/reportes/consumo_vales_excel.php";
						}
					});
				});
			});

			function validacionFechaPorMes(check){
			    if ( $( "#chk-hora" ).prop( "checked" ) ) {
					$( "#span-msg-validacion-hora" ).text(" Nota: Solo se podrá filtrar información de un mismo mes");
			    } else {
			    	$( "#span-msg-validacion-hora" ).text("");
			    }
			    if ( $( "#chk-precio_contratado" ).prop( "checked" ) ) {
					$( "#span-msg-validacion-precio_contratado" ).text(" Nota: Solo se podrá filtrar información de un mismo mes");
			    } else {
			    	$( "#span-msg-validacion-precio_contratado" ).text("");
			    }
			}

        	function imprimir_comprobante_pdf(iAlmacen, sCajaTrans, dFecha, iTurno){
        		var arrFecha 	= dFecha.split('/');
        		var iYear 		= arrFecha[2];
        		var iMonth 		= arrFecha[1];
        		var iDay 		= arrFecha[0];
        		window.open('/sistemaweb/ventas_clientes/reportes/consumo_vales_pdf.php?iAlmacen=' + iAlmacen + '&sCajaTrans=' + sCajaTrans + '&iYear=' + iYear + '&iMonth=' + iMonth + '&iDay=' + iDay + '&iTurno=' + iTurno, '_blank');
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
		include('reportes/t_consumo_vales.php');
		include('reportes/m_consumo_vales.php');

		$objtem = new ConsumoValesTemplate();
		$accion = $_REQUEST['accion'];

		if($accion == "ni"){
			echo ConsumoValesTemplate::FormularioPrincipal();
		}else{
			$estaciones	= ConsumoValesModel::ObtenerEstaciones();
			echo ConsumoValesTemplate::Inicio($estaciones);
		}

        ?>

    </body>
</html>
