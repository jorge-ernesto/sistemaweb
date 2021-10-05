<html>
	<head>
		<title>Sistema de Combustibles - Desconsolidar Turno</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/combustibles.js"></script>
		<script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
		<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
      <script src="/sistemaweb/js/jquery-ui.js"></script>
		<script  type="text/javascript"> 
            
			function obtenerDatePicker(fecha){
				console.log('obtenerDatePicker');
				console.log(fecha);				

				$( "#fecha_" ).datepicker(
					{changeMonth: true,
					changeYear: true}
				);
				$( "#fecha_" ).datepicker("option", "dateFormat","yy-mm-dd");

				$('#fecha_').val(fecha);
			}

			function obtenerTurno(){
				console.log('otbenerTurno');

				var nu_almacen 	= $( "#almacen_ option:selected" ).val();
	   		var Fe_Emision 	= $( '#fecha_' ).val();
	   		var nu_tipo_venta = 'C';

				//Formateamos fecha como se requiere en el helper
				var porciones = Fe_Emision.split("-");
				Fe_Emision    = porciones[2]+'/'+porciones[1]+'/'+porciones[0];
				//Cerrar Formateamos fecha como se requiere en el helper
				
				if(nu_tipo_venta.length > 0){					
					$.post( "/sistemaweb/assets/helper.php", {
						accion 			: 'getValuesFechaTCL',//TCL = Get Values: Turno, Caja, Lado
						nu_almacen 		: nu_almacen,
						Fe_Emision 		: Fe_Emision,
						nu_tipo_venta 	: nu_tipo_venta
					}, function(data){

						var arrTurnos = data.arrTurnos;

						$("select[name=turno_]").html('<option value="-">Seleccionar..</option>');
						for (var i = 1; i <= arrTurnos[0].turno; i++){
							$("select[name=turno_]").append(new Option(i, i));
						}						
					}, 'JSON');
				}else{
					$("select[name=turno_]").html('<option value="-"></option>');
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
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MOVIMIENTOS.DESCONSOLIDAR" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
