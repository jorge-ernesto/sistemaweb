<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
<head>
	<title>REPORTE DESPACHOS ELECTRONICOS </title>
	<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	<link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
	<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
	<script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
	<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
	<script src="/sistemaweb/js/jquery-ui.js"></script>
	<script  type="text/javascript"> 
		$(document).ready(function() {
			$( "#fecha_inicio" ).datepicker(
				{changeMonth: true,
					changeYear: true,
					onSelect:function(fecha,obj) {
						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
							'top': 200 + 'px'
						});
						$.ajax({
							type: "POST",
							url: "reportes/c_despacho_electronico.php",
							data: { accion:'fecha_servidor',fecha_inicio:fecha},

							success:function(xm) { 
								var json=eval('('+xm+')');
								$('#cont_ini').html("<select id='opt_inicio'><option>Opcion</option></select>");
								$('#opt_inicio').html(json.msg);
								$('#cargardor').css({'display':'none'});
								$('#ch_manual_ini').attr('checked',false);
							}
						});
					}
				});

			$( "#fecha_inicio" ).datepicker("option", "dateFormat","yy-mm-dd");

			$( "#fecha_final" ).datepicker(
				{changeMonth: true,
					changeYear: true,
					onSelect:function(fecha,obj) {
						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
							'top': 200 + 'px'
						});
						$.ajax({
							type: "POST",
							url: "reportes/c_despacho_electronico.php",
							data: { accion:'fecha_servidor',fecha_inicio:fecha},
							success:function(xm){
								var json=eval('('+xm+')');
								$('#cont_final').html("<select id='opt_final'><option>Opcion</option></select>");
								$('#opt_final').html(json.msg);
								$('#cargardor').css({'display':'none'});
								$('#ch_manual_final').attr('checked',false);
							}
						});
					}
				});

			$( "#fecha_final" ).datepicker("option", "dateFormat","yy-mm-dd");

			$('#id_regresar').click(function() {
				location.href="/sistemaweb/ventas_clientes/matricula_trabajador.php";
			});

			$('#ch_manual_ini').click(function() {
				if($(this).is(':checked')) {
					$('#cont_ini').html("<input  id='opt_inicio'/>");
				} else {
					$('#cont_ini').html("<select id='opt_inicio'><option>Opcion</option></select>");
				}
			});

			$('#ch_manual_final').click(function() {
				if($(this).is(':checked')) {
					$('#cont_final').html("<input  id='opt_final'/>");
				} else {
					$('#cont_final').html("<select id='opt_final'><option>Opcion</option></select>");
				}
			});

			$('#opt_lados').change(function() {
				if($(this).val()=="00") {
				} else {
					var f_id=$(this).val();
					var valor_html = $("#opt_lados option:selected").html();
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
						'top': 200 + 'px'
					});
					$.ajax({
						type: "POST",
						url: "reportes/c_despacho_electronico.php",
						data: { accion:'find_grade',f_pump_id:f_id},
						success:function(xm){ 
						//alert(xm);
							var json=eval('('+xm+')');
							$('#id_lado').val(valor_html);
							$('#opt_grade_id').html(json.msg);
							$('#cargardor').css({'display':'none'});
						}
					});
				}
			});

			$('#opt_grade_id').change(function() {
				var valor_html = $("#opt_grade_id option:selected").html();
				var valor_id= $("#opt_grade_id").val();
				$('#id_manguera').val($(this).val());
			});

			$('#executar').click(function(){
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
					'top': 200 + 'px'
				});
				$.ajax({
					type: "POST",
					url: "reportes/c_despacho_electronico.php",
					data: { accion:'executar_reporte',
						fecha_ini:$('#opt_inicio').val(),
						fecha_fin:$('#opt_final').val(),
						lado:$('#id_lado').val(),
						manguera:$('#id_manguera').val()
					},
					success:function(xm){ 
						$('#tab_id_detalle').html(xm);
						$('#cargardor').css({'display':'none'});
					}
				});
			});

			$('#executar_excel').click(function(){
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
					'top': 200 + 'px'
				});
				$.ajax({
					type: "POST",
					url: "reportes/c_despacho_electronico.php",
					data: { accion:'executar_reporte_excel',
						fecha_ini:$('#opt_inicio').val(),
						fecha_fin:$('#opt_final').val(),
						lado:$('#id_lado').val(),
						manguera:$('#id_manguera').val()
					},
					success:function(xm){ 
						$('#cargardor').css({'display':'none'});
						location.href="/sistemaweb/combustibles/reportes/reporte_excel_despacho_electronico.php";
					}
				});
			});
		} );
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
	<div id="cargardor" style="position: absolute;display: none">
		<img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" />
	</div>
	<?php
	include('/sistemaweb/include/mvc_sistemaweb.php');
	include('reportes/t_despacho_electronico.php');
	include('reportes/m_despacho_electronico.php');

	$objtem = new depacho_electronico_Template();
	$accion = $_REQUEST['accion'];
	if($accion == "ni") {
		echo depacho_electronico_Template::FormularioPrincipal();
	} else if($accion == "update") {
		$fecha_find = $_REQUEST['fecha'];
		$id_turno = $_REQUEST['turno'];

		/*$trabajadores_matriculado = depacho_electronico_Model:: VerTrabajdor_X_Asignado($fecha_find, $id_turno);
		$lados = depacho_electronico_Model::ObtenerLados();
		$trabajores = depacho_electronico_Model::ObtenerTrabajadores();
		$punto_vt_market = depacho_electronico_Model::ObtenerPuntoMarket(); */

		//matricula_personal_Template::CrearTablaMatricula_Actualizar($lados, $trabajores, $punto_vt_market, $trabajadores_matriculado, $fecha_find, $id_turno);
	} else {
		$lados = depacho_electronico_Model::ObtenerLados();
		echo depacho_electronico_Template::Inicio($lados);
	}
	?>
</body>
</html>
