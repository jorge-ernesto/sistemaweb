<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
        <title>Interface SISCONT</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript"> 

			var fecha		= new Date();
			var annosistema	= "2014";
			var anno		= fecha.getFullYear();
			var annoact		= anno + 6;
			var month 		= fecha.getMonth() + 1;

			$(document).ready(function(){

				for(var i = annosistema; i < annoact; i++){
					$("select[name=year]").append(new Option(i,i));
				}

				$( "select[name=year]" ).val( anno );

				if(month < 10){
					month = "0" + month;
				}

				$( "select[name=month]" ).val( month );

	          	$( '#asientos' ).click(function(){
          			$( ".span-msg" ).text('');
					$('#cargardor').css({'display':'block'});
					$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});
					
					var url = "movimientos/c_interface_siscont.php";
					var $arrPOST = {
						accion		: 'asientos',
						modulos		: $( '#modulos' ).val(),
						sucursal	: $( '#sucursal' ).val(),
						year		: $( '#year' ).val(),
						month		: $( '#month' ).val(),
						decimales	: $( '#CboDecimales' ).val(),
						nu_tipo_venta : $('#cbo-tipo-venta').val(),
						nu_nota_despacho : $('#cbo-nota-despacho').val(),
						nu_tarjeta_credito : $('#cbo-tarjeta-credito').val(),
					};
					console.log($arrPOST); //Agregado 2020-01-16

					$.post( url, $arrPOST, function( response ){
						console.log(response);
						// return; //Agregado 2020-01-16
						if ( response.sStatus == "success") {
							location.href="/sistemaweb/combustibles/movimientos/interface_siscont_texto.php";
						} else {
							alert( response.sMessage );
						}
						$('#cargardor').css({'display':'none'});
					}, 'json')
                    .fail(function() {
						$('#cargardor').css({'display':'none'});
                    });
				});


	          	$( '#btn-excel' ).click(function(){

					var nu_tipo_venta = $('#cbo-tipo-venta').val();

	          		if( nu_tipo_venta == 0 ) {
	          			$( ".span-msg" ).text('Debes de seleccionar un tipo de venta');
	          		} else {
	          			$( ".span-msg" ).text('');
						$('#cargardor').css({'display':'block'});
						$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

						$.ajax({
							dataType: "json",
							type	: "POST",
							url 	: "movimientos/c_interface_siscont.php",
							data	: {
									accion		: 'AsientosExcel',
									modulos		: $('#modulos').val(),
									sucursal	: $('#sucursal').val(),
									year		: $('#year').val(),
									month		: $('#month').val(),
									decimales	: $('#CboDecimales').val(),
									nu_tipo_venta : nu_tipo_venta,
									nu_nota_despacho : $('#cbo-nota-despacho').val(),
							},
							success:function( response ){
								console.log(response);
								$('#cargardor').css({'display':'none'});
								if ( response.sStatus == "success") {
									location.href="/sistemaweb/combustibles/movimientos/interface_siscont_excel.php";
								} else {
									alert( response.sMessage );
								}
								/*
								if(response){
									$('#cargardor').css({'display':'none'});			            		
									location.href="/sistemaweb/combustibles/movimientos/interface_siscont_excel.php";
								}else{
									alert('No hay datos');
									$('#cargardor').css({'display':'none'});	
								}
								*/
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
		include('movimientos/t_interface_siscont.php');
		include('movimientos/m_interface_siscont.php');

		$objModel = new Siscont_Model();
		$objTemplate = new Siscont_Template();

		$arrWarehouse = $objModel->ObtenerEstaciones();
		$dataTarjetasCredito = $objModel->getTarjetasCredito();
		$objTemplate->Inicio($arrWarehouse, $dataTarjetasCredito);
        ?>
    </body>
</html>
