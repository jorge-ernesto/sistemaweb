<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<!DOCTYPE html>
<html lang="en">
	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>Modificar formulario | Inventarios</title>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="js/compras.js"></script>
<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
<script src="/sistemaweb/js/jquery-ui.js"></script>
<script  type="text/javascript">

window.onload = function () {

	$(function(){
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

		$( "#fecha" ).datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect:function(fecha,obj){

				$('#cargardor').css({'display':'block'});
			    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				var fecha = $("#fecha").val();
				var almacen = $("#almacen").val();

				fecha = fecha.substr(6, 4) + '-' + fecha.substr(3, 2) + '-' + fecha.substr(0, 2);

				$.ajax({
					type: "POST",
					url: "/sistemaweb/inventarios/forms/fecha.php",
					data:{
						fecha:fecha,
						almacen:almacen
					},
					success:function (response){
						console.log(response);
						$('#cargardor').css({'display':'none'});
						if(response.length > 12){//VALIDAR PARA QUE NO MUESTRE EL ECHO DE FECHA EN EL FRONT END
							$("#resultado").html(response);
							$("#buscar").prop( "disabled", true );
						}else{
							$("#resultado").html('');
							$("#buscar").prop( "disabled", false );
						}
					}
				});
			}
		})

		$( "#dato_change" ).datepicker({
			changeMonth: true,
			changeYear: true,
			onSelect:function(fecha,obj){

				$('#cargardor').css({'display':'block'});
			    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});
			    
				var almacen = $("#almacen").val();

				fecha = fecha.substr(6, 4) + '-' + fecha.substr(3, 2) + '-' + fecha.substr(0, 2);

				$.ajax({
					type: "POST",
					url: "/sistemaweb/inventarios/forms/fecha.php",
					data:{
						fecha:fecha,
						almacen:almacen
					},
					success:function (response){
						console.log(response);
						$('#cargardor').css({'display':'none'});
						if(response.length > 12){//VALIDAR PARA QUE NO MUESTRE EL ECHO DE FECHA EN EL FRONT END
							$("#resultado2").html(response);
							$("#change_date").prop( "disabled", true );
						}else{
							$("#resultado2").html('');
							$("#change_date").prop( "disabled", false );
						}
					}
				});
			}
		})

	})
}

function getFechaEmision(){
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

	$( "#fecha" ).datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect:function(fecha,obj){
			$('#cargardor').css({'display':'block'});
		    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			var fecha = $("#fecha").val();
			var almacen = $("#almacen").val();

			fecha = fecha.substr(6, 4) + '-' + fecha.substr(3, 2) + '-' + fecha.substr(0, 2);

			$.ajax({
				type: "POST",
				url: "/sistemaweb/inventarios/forms/fecha.php",
				data:{
					fecha:fecha,
					almacen:almacen
				},
				success:function (response){
					console.log(response);
					$('#cargardor').css({'display':'none'});
					if(response.length > 12){//VALIDAR PARA QUE NO MUESTRE EL ECHO DE FECHA EN EL FRONT END
						$("#resultado").html(response);
						$("#buscar").prop( "disabled", true );
					}else{
						$("#resultado").html('');
						$("#buscar").prop( "disabled", false );
					}
				}
			});
		}
	})

	$( "#dato_change" ).datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect:function(fecha,obj){
			$('#cargardor').css({'display':'block'});
		    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			var almacen = $("#almacen").val();

			fecha = fecha.substr(6, 4) + '-' + fecha.substr(3, 2) + '-' + fecha.substr(0, 2);

			$.ajax({
				type: "POST",
				url: "/sistemaweb/inventarios/forms/fecha.php",
				data:{
					fecha:fecha,
					almacen:almacen
				},
				success:function (response){
					console.log(response);
					$('#cargardor').css({'display':'none'});
					if(response.length > 12){//VALIDAR PARA QUE NO MUESTRE EL ECHO DE FECHA EN EL FRONT END
						$("#resultado2").html(response);
						$("#change_date").prop( "disabled", true );
					}else{
						$("#resultado2").html('');
						$("#change_date").prop( "disabled", false );
					}
				}
			});
		}
	})
}

</script>
</head>
<body>
<?php include "../menu_princ.php"; ?>
<div id="content">
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=FORMS.MODIFICAR&pagina=1&clear=1" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
