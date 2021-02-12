<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="utf-8">
	    <title>Regeneración de Saldos</title>

	    <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
	    <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">

	    <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
	    <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

	    <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />

	    <script src="/sistemaweb/js/jquery-ui.js"></script>
	    <script src="/sistemaweb/js/alertify.js"></script>
	    <script src="/sistemaweb/js/alertify.min.js"></script>
	    
	    <style type="text/css">
	    	.is-notification {
	    		color: #fff;
				border-radius: 3px;
				padding: 1.25rem 2.5rem 1.25rem 1.5rem;
				position: relative;
				text-align: center;
				font-size: 1rem;
				font-weight: 400;
				line-height: 1.5;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", "Helvetica", "Arial", sans-serif;
	    	}

	    	.progress {
				display: flex;
			    height: 1rem;
			    overflow: hidden;
			    font-size: .75rem;
			    background-color: #b6b6b6;
			    border-radius: .25rem;
	    	}

	    	.progress-bar {
				display: flex;
				flex-direction: column;
				justify-content: center;
				color: #000000;
				text-align: center;
				white-space: nowrap;
				background-color: #007bff;
				transition: width .6s ease;
				width: 0%;
	    	}
	    </style>

		<script  type="text/javascript">
			var url = 'reportes/c_regenerar_saldos.php';
			var iCantidadTotalItem = 0;
			var interval = null;

			$(document).ready(function(){
				$( '.is-info' ).hide();
				$( '.progress' ).hide();
				$( '.progress-bar' ).hide();

				// Verificar estado de proceso cuando ingreso por primera vez a la opción
				// Solo en caso de cerrar sesión, se fue internet o se fue a otra opción
				// mientras esperaba que se terminará el proceso de regeneración
				getStatusBalanceRegeneration();

				function getStatusBalanceRegeneration(){
					var arrData = {
						sAction 	: 'get_process_now',
					};

					$.ajax({
						type		: "POST",
						url 		: url,
						dataType 	: "JSON",
						data 		: arrData,
						success:function( response ){
							console.log( 'status -> ');
							console.log( response );

							$( '.is-notification' ).show();
							$( '.is-notification' ).html( '' );

							if ( response.sStatus == 'success' ) {
								$ ( '#btn-procesar' ).prop( 'disabled', true);

								iCantidadTotalItem = response.iCantidadTotalItem;
								interval = setInterval(getStatusProcessItem, 500);
							} else {
								$ ( '#btn-procesar' ).prop( 'disabled', false);

								$( '.progress' ).hide();
								$( '.progress-bar' ).hide();
							}
						}
					});
				}

				$( '#btn-procesar' ).click(function(){
					confirmar = confirm("Estas seguro de procesar?");
					if(confirmar){
						executeBalanceRegeneration();
					}
		        });//Fin de procesado masivo

				function executeBalanceRegeneration(){
					var arrData = {
						sAction 	: 'procesar',
						iWarehouse 	: $( '#cbo-iWarehouse' ).val(),
						iYear 		: $( '#hidden-iYear' ).val(),
						iMonth 		: $( '#hidden-iMonth' ).val(),
					};

					$.ajax({
						type		: "POST",
						url 		: url,
						dataType 	: "JSON",
						data 		: arrData,
						success:function( response ){
							console.log( 'execute -> ');
							console.log( response );

							$( '.is-notification' ).show();
							$( '.is-notification' ).html( '' );

							if ( response.sStatus == 'success' ) {
								// Deshabilitar button
								$ ( '#btn-procesar' ).prop( 'disabled', true);

								$( '.is-notification' ).css( "background-color", "#3273dc" );//blue
								$( '.is-notification' ).html( response.sMessage );

								iCantidadTotalItem = response.iCantidadTotalItem;
								interval = setInterval(getStatusProcessItem, 500);
							} else {
								$ ( '#btn-procesar' ).prop( 'disabled', false);

								$( '.is-notification' ).css( "background-color", "#ff3860" );//red
								$( '.is-notification' ).html( response.sMessage );

								$( '.progress' ).hide();
								$( '.progress-bar' ).hide();
							}
						}
					});
				}

				function getStatusProcessItem(){
					var params = {
						sAction : 'verify_status_process',
					};

					$.post(url, params, function( response ){
						console.log( 'Cantidad total de item -> ' + iCantidadTotalItem );
						console.log( 'Cantidad total de item -> ' + response.iCantidadItemProcesado );
						$( '.is-notification' ).html( '' );

						if ( response.sStatus == 'success' ) {
							$( '.is-notification' ).hide();

							$( '.progress' ).show();
							$( '.progress-bar' ).show();

							// Calculo para el porcentaje que se va procesando
							var iPorcentajeProcesoRegeneracion = ((100 * response.iCantidadItemProcesado) / iCantidadTotalItem);
							// Setear el ancho del CSS del porcentaje calucado
							$( ".progress-bar" ).css( "width", iPorcentajeProcesoRegeneracion + "%" );
							// Mostrar en HTML el porcentaje calucado
							$( ".progress-bar" ).html( iPorcentajeProcesoRegeneracion.toFixed(1) + " %" );

							if ( iCantidadTotalItem == response.iCantidadItemProcesado ) {
								clearInterval(interval);

								var params = {
									sAction : 'stop_process_balance',
								};

								$.post(url, params, function( response ){
									if ( response.sStatus == 'success' ) {
										alert(response.sMessage);
										$ ( '#btn-procesar' ).prop( 'disabled', false);
									} else {
										alert(response.sMessage);
										$ ( '#btn-procesar' ).prop( 'disabled', false);
									}
								}, 'JSON');
							}
						} else {
							$( '.is-notification' ).show();
							$( '.is-notification' ).css( "background-color", "#ff3860" );//red
							$( '.is-notification' ).html( response.sMessage );
							
							$ ( '#btn-procesar' ).prop( 'disabled', false);
						}
					}, 'JSON');
				}
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
        <?php
		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_regenerar_saldos.php');
		include('reportes/m_regenerar_saldos.php');

		$objModel = new Regenerar_Saldos_Model();
		$objTemplate = new Regenerar_Saldos_Template();

		$cierre 	= $objModel->CierresInventario();
		$estaciones	= $objModel->ObtenerEstaciones();
		$objTemplate->Inicio($cierre, $estaciones);
        ?>
    </body>
</html>
