<html>
    <head>
        <title>Ajuste de Inventario Fisico</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
	<script  type="text/javascript"> 

	function ChequearTodos(chkbox) {
		for (var i=0;i < document.forms[0].elements.length;i++) {
			var elemento = document.forms[0].elements[i];
			if (elemento.type == "checkbox") {
				elemento.checked = chkbox.checked
			}
		}
	}

	function validar(e,tipo){
		tecla=(document.all)?e.keyCode:e.which;
		if (tecla==13 || tecla==8)
			return true;
	
		switch(tipo){
			/*letras y numeros, puntos */
			case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
			/*solo numeros enteros */
			case 2: patron=/[0-9]/;break;
			/*solo numeros dobles*/
			case 3: patron=/[0-9.]/;break;
			/*solo letras*/
			case 4: patron=/[A-Z a-z]/;break;
			/*telefonos y faxes*/
			case 5: patron=/[0-9/-]/;break;
		}
		teclafinal=String.fromCharCode(tecla);
		return patron.test(teclafinal);
	}


	$(document).ready(function(){

                $('#almacen').change(function(){
                        var f_id=$(this).val();
                        var valor_html = $("#almacen option:selected").html();
                        $('#cargardor').css({'display':'block'});
                        $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});
                        $.ajax({
                            type: "POST",
                            url: "reportes/c_ajuste_fisico.php",
                            data: { accion:'find_ubica',cod_almacen:f_id
                            },
                            success:function(xm){				
				var json=eval('('+xm+')');
				$('#opt_ubica_id').html(json.msg);
				$('#cargardor').css({'display':'none'});
                            }
                        });
                });
                
                $('#buscar').click(function(){

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type: "POST",
				url: "reportes/c_ajuste_fisico.php",
				data: {
					accion:'buscar',
					almacen:$('#almacen').val(),
					ubica:$('#opt_ubica_id').val(),
					orden:$('[name="myorden"]:checked').attr('value')
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

			$.ajax({
				type: "POST",
				url: "reportes/c_ajuste_fisico.php",
				data: {
					accion:'excel',
					almacen:$('#almacen').val(),
					ubica:$('#opt_ubica_id').val(),
					orden:$('[name="myorden"]:checked').attr('value')
					},
				            success:function(xm){
						$('#cargardor').css({'display':'none'});
				            	location.href="/sistemaweb/inventarios/reportes/ajuste_fisico_excel.php";
				            }
				});

                });
                             
                $('#procesar').click(function(){
alert('buscar');
			/*$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': 200 + 'px'});

			$.ajax({
				type: "POST",
				url: "reportes/c_ajuste_fisico.php",
				data: {
					accion:'buscar',
					almacen:$('#almacen').val(),
					ubica:$('#opt_ubica_id').val(),
					orden:$('[name="myorden"]:checked').attr('value')
					},
				            success:function(xm){
						$('#cargardor').css({'display':'none'});
				                $('#tab_id_detalle').html(xm);
				            }
				});*/

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
		include('reportes/t_ajuste_fisico.php');
		include('reportes/m_ajuste_fisico.php');

		$objtem = new Ajuste_Fisico_Template();
		$accion = $_REQUEST['accion'];
?><script>alert("<?php echo '+++ la campania es: '.$accion ; ?> ");</script><?php
		if($accion == "ni"){
			echo Ajuste_Fisico_Template::FormularioPrincipal();
		}else{
			$estaciones	= Ajuste_Fisico_Model::ObtenerEstaciones();
			echo Ajuste_Fisico_Template::Inicio($estaciones);
		}

        ?>

    </body>
</html>
