<html>
    <head>
        <title>Interface Club Esmeralda</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script charset="utf-8" type="text/javascript"> 
		$(document).ready(function(){
			for(var i = 2014; i <= 2030; i++){
				$("select[name=year]").append(new Option(i,i));
			}

            $('#excel').click(function(){
				$('#cargardor').css({'display':'block'});
				$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

				$.ajax({
			        type	: "POST",
					url		: "reportes/c_interface_esmeralda_excel.php",
					data	: {
						accion		: 'excel',
						modulos		: $('#modulos').val(),
						sucursal	: $('#sucursal').val(),
						year		: $('#year').val(),
						month		: $('#month').val(),
					},
					success:function(xm){
						$('#cargardor').css({'display':'none'});
			            location.href="/sistemaweb/combustibles/reportes/interface_esmeralda_excel.php";
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
		include('reportes/t_interface_esmeralda_excel.php');
		include('reportes/m_interface_esmeralda_excel.php');

		$objtem = new Descuentos_Especiales_Template();
		$accion = $_REQUEST['accion'];
		$desde	= date('d/m/Y');
		$hasta	= date('d/m/Y');

		if($accion == "ni"){
			$estaciones	= Descuentos_Especiales_Model::ObtenerEstaciones();
			$lados		= Descuentos_Especiales_Model::obtieneLados();
			echo Descuentos_Especiales_Template::AgregarDescuento($estaciones, $lados);
		}else{
			$estaciones	= Descuentos_Especiales_Model::ObtenerEstaciones();
			echo Descuentos_Especiales_Template::Inicio($estaciones, $desde, $hasta);
		}

        ?>

    </body>
</html>
