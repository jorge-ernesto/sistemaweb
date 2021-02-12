<html>
	<head>
		<title>Formato de Inventario Fisico</title>
		<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
		<link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
		<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
		<script src="/sistemaweb/js/jquery-ui.js"></script>
		<script  type="text/javascript"> 
		$(document).ready(function() {
			$('#almacen').change(function() {
				var f_id=$(this).val();
				var valor_html = $("#almacen option:selected").html();
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});
				
				$.ajax({
					type: "POST",
					url: "reportes/c_formato_fisico.php",
					data: { accion:'find_ubica',cod_almacen:f_id},
					success:function(xm){				
						var json=eval('('+xm+')');
						$('#opt_ubica_id').html(json.msg);
						$('#cargardor').css({'display':'none'});
					}
				});
			});

			$('#buscar').click(function() {
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				$.ajax({
					type: "POST",
					url: "reportes/c_formato_fisico.php",
					data: {
						accion:'buscar',
						almacen:$('#almacen').val(),
						ubica:$('#opt_ubica_id').val(),
						stk:$('#stk').val(),
						orden:$('[name="myorden"]:checked').attr('value')
					},

					success:function(xm) {
						$('#cargardor').css({'display':'none'});
						$('#tab_id_detalle').html(xm);
					}
				});
			});

			$('#excel').click(function(){
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				$.ajax({
					type: "POST",
					url: "reportes/c_formato_fisico.php",
					data: {
						accion:'excel',
						almacen:$('#almacen').val(),
						ubica:$('#opt_ubica_id').val(),
						stk:$('#stk').val(),
						orden:$('[name="myorden"]:checked').attr('value')
					},

					success:function(xm){
						$('#cargardor').css({'display':'none'});
						location.href="/sistemaweb/inventarios/reportes/formato_fisico_excel.php";
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
		</div>
		<div id="footer">&nbsp;</div>
		<div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
		<?php
		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_formato_fisico.php');
		include('reportes/m_formato_fisico.php');

		$objtem = new Formato_Fisico_Template();
		$accion = $_REQUEST['accion'];

		if($accion == "ni") {
			echo Formato_Fisico_Template::FormularioPrincipal();
		} else {
			$estaciones = Formato_Fisico_Model::ObtenerEstaciones();
			$fecha		= Formato_Fisico_Model::FechaSistema();
			echo Formato_Fisico_Template::Inicio($estaciones, $fecha);
		}
		?>
	</body>
</html>
