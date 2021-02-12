<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
    <head>
        <title>Sistema de Ventas - MATRICULA DE TRABAJADOR</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript">
            function llamar_guardar_pv(){
                // alert('ssssss');
                $('#cargardor').css({'display':'block'});
                $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                    'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'
                });
                var data_comb=new Array();
                var data_pv=new Array();
                var data_noselecionado=new Array();
                var i=0;
                var y=0;
                var global_cont=0;
                $('.lado_cmb').each(function(){
                    var lado=$(this).val();
                    var pv=$(this).attr("pos");
                    var cod_tra=$('#pv_'+pv).val();
                    if(cod_tra!="-1"){
                        data_comb[i]={"lado":lado,"cod_tra":cod_tra};
                        i++;
                    }else{
                        data_noselecionado[global_cont]={"lado":lado,"cod_tra":cod_tra,"tipo":"C"};
                        global_cont++;
                    }
                });                    
                    
                $('.pv_market').each(function(){
                    var pv=$(this).val();
                    var cod_tra=$('#pv_'+pv).val();
                    if(cod_tra!="-1"){
                        data_pv[y]={"pv":pv,"cod_tra":cod_tra};
                        y++;
                        
                    }else{
                        data_noselecionado[global_cont]={"lado":pv,"cod_tra":cod_tra,"tipo":"M"};
                        global_cont++;
                    }
                });

                if(global_cont==0){
                    var total_legth=data_comb.length+data_pv.length;
                    if(total_legth>0){
                        $.ajax({
                            type: "POST",
                            url: "matricula_trabajador_ajax.php",
                            data: { accion:'guardar_info',
                                fecha_asignar:$('#fecha_inicio').val(),
                                turno:$('#id_turno').val(),
                                data_envia:data_comb,data_envia_pv:data_pv,no_select:data_noselecionado,
                                sucursal:$('#id_scucursal').val()
                            },
                            success:function(xm){ 
                                $('#cargardor').css({'display':'none'});
                                alert(xm);
                            }
                        })
                    }
                }else{
                    alert("Falta asignar trabajadores ("+global_cont+")");
                    $('#cargardor').css({'display':'none'});
                }
                    
                    
            }
            function llamar_guardar(){
                // alert('ssssss');
                $('#cargardor').css({'display':'block'});
                $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                    'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'
                });
                var data_comb=new Array();
                var data_pv=new Array();
                var data_noselecionado=new Array();
                var i=0;
                var y=0;
                var global_cont=0;
                $('.lado_cmb').each(function(){
                    var lado=$(this).val();
                    var cod_tra=$('#cmb_lad'+lado).val();
                    if(cod_tra!="-1"){
                        data_comb[i]={"lado":lado,"cod_tra":cod_tra};
                        i++;
                    }else{
                        data_noselecionado[global_cont]={"lado":lado,"cod_tra":cod_tra,"tipo":"C"};
                        global_cont++;
                    }
                });
                    
                    
                $('.pv_market').each(function(){
                    var pv=$(this).val();
                    var cod_tra=$('#pv_'+pv).val();
                    if(cod_tra!="-1"){
                        data_pv[y]={"pv":pv,"cod_tra":cod_tra};
                        y++;
                        
                    }else{
                        data_noselecionado[global_cont]={"lado":pv,"cod_tra":cod_tra,"tipo":"M"};
                        global_cont++;
                    }
                });
                if(global_cont==0){
                    var total_legth=data_comb.length+data_pv.length;
                    if(total_legth>0){
                        $.ajax({
                            type: "POST",
                            url: "matricula_trabajador_ajax.php",
                            data: { accion:'guardar_info',
                                fecha_asignar:$('#fecha_inicio').val(),
                                turno:$('#id_turno').val(),
                                data_envia:data_comb,data_envia_pv:data_pv,no_select:data_noselecionado,
                                sucursal:$('#id_scucursal').val()
                                
                            },
                            success:function(xm){ 
                                $('#cargardor').css({'display':'none'});
                                alert(xm);
                            }
                        })
                    }
                }else{
                    alert("Falta asignar trabajadores ("+global_cont+")");
                    $('#cargardor').css({'display':'none'});
                }
                    
                    
            }
            function llamar_actualizar(){
                $('#cargardor').css({'display':'block'});
                $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                    'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'
                });
                var data_comb=new Array();
                var data_pv=new Array();
                var data_noselecionado=new Array();
                var i=0;
                var y=0;
                var global_cont=0;
                $('.lado_cmb').each(function(){
                    var lado=$(this).val();
                    var cod_tra=$('#cmb_lad'+lado).val();
                    if(cod_tra!="-1"){
                        data_comb[i]={"lado":lado,"cod_tra":cod_tra};
                        i++;
                    }else{
                        data_noselecionado[global_cont]={"lado":lado,"cod_tra":cod_tra,"tipo":"C"};
                        global_cont++;
                    }
                });
                    
                    
                $('.pv_market').each(function(){
                    var pv=$(this).val();
                    var cod_tra=$('#pv_'+pv).val();
                    if(cod_tra!="-1"){
                        data_pv[y]={"pv":pv,"cod_tra":cod_tra};
                        y++;
                        
                    }else{
                        data_noselecionado[global_cont]={"lado":pv,"cod_tra":cod_tra,"tipo":"M"};
                        global_cont++;
                    }
                });
                var total_legth=data_comb.length+data_pv.length;
                if(global_cont==0){
                    if(total_legth>0){
                        $.ajax({
                            type: "POST",
                            url: "matricula_trabajador_ajax.php",
                            data: { accion:'guardar_info',
                                fecha_asignar:$('#fecha_inicio_actu').val(),
                                turno:$('#id_turno').val(),
                                data_envia:data_comb,data_envia_pv:data_pv,no_select:data_noselecionado
                            },
                            success:function(xm){ 
                                $('#cargardor').css({'display':'none'});
                                alert(xm);
                            }
                        })
                    }
                }else{
                    alert("Falta asignar trabajadores ("+global_cont+")");
                    $('#cargardor').css({'display':'none'});
                }
                    
            }

            	function verdetalle(fecha,turno,sucursal){
                	window.open ("informe_trabajdor_isla.php?fecha="+fecha+"&turno="+turno+"&sucursal="+sucursal, "mywindow","location=1,status=1,scrollbars=1, width=800,height=800")
            	}

		function actualizar_trabajador(fecha, turno, almacen){
			location.href="/sistemaweb/ventas_clientes/matricula_trabajador.php?accion=update&fecha="+fecha+"&turno="+turno+"&almacen="+almacen;
            	}

		function delete_trabajador(fecha, turno, almacen, fecha_ini, fecha_fin, cod_trabajador, ch_sucursal){
			//location.href="/sistemaweb/ventas_clientes/matricula_trabajador.php?accion=delete&fecha="+fecha+"&turno="+turno+"&almacen="+almacen;
			location.href="/sistemaweb/ventas_clientes/matricula_trabajador.php?accion=delete&fecha="+fecha+"&turno="+turno+"&almacen="+almacen+"&fecha_ini="+fecha_ini+"&fecha_fin="+fecha_fin+"&cod_trabajador="+cod_trabajador+"&ch_sucursal="+ch_sucursal;
            	}

            	function ver_tabajadores(){
		        var fecha_ini=$('#fecha_inicio').val();
		        var fecha_final=$('#fecha_final').val();
		        var cod_traba=$('#cod_traba').val();
		        location.href="/sistemaweb/ventas_clientes/matricula_trabajador_ajax.php?accion=pdf_trabajador&fecha_ini="+fecha_ini+"&fecha_final="+fecha_final+"&cod_trabajor="+cod_traba;    
            	}

            $(document).ready(function(){
                $( "#fecha_inicio" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_inicio" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $( "#fecha_final" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_final" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                
                $('#id_regresar').click(function(){
                    location.href="/sistemaweb/ventas_clientes/matricula_trabajador.php";
                });
                
                 
                
             
                $('#btnnuevo_registro').click(function(){
                    location.href='/sistemaweb/ventas_clientes/matricula_trabajador.php?accion=ni';
                });
                
                $.ajax({
                    type: "POST",
                    url: "matricula_trabajador_ajax.php",
                    data: { accion:'fecha_servidor'
                    },
                    success:function(xm){
                        var cadena=xm.split('?');
                        $('#id_scucursal').html(cadena[0]);
                        $('#fecha_inicio').val(cadena[1]);
                        $('#fecha_final').val(cadena[1]);
                        $('#cod_traba').val();
                    }
                });
                
                $('#btnmatricular_pv').click(function(){
                    $('#cargardor').css({'display':'block'});
                    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                        'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'
                    });
                   
                    $.ajax({
                        type: "POST",
                        url: "matricula_trabajador_ajax.php",
                        data: { accion:'btnmatricular_pv',fecha_inicio:$("#fecha_inicio").val(),id_turno:$("#id_turno").val(),
                            "sucursal":$('#id_scucursal').val()
                        },
                        success:function(xm){
                            
                            var cadenaretorno=xm.substring(0, 7);
                            if(cadenaretorno=="ERROR_:"){
                                var  error=xm.substr(7,xm.length);
                                $('.estado').remove();
                                var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                $('.contenedorprincipal').append($(estadodiv));
                                if ($(".estado:first").is(":hidden")) {
                                    $(".estado").show("slow");
                                } 
                            }else{
                                $('#contenidoTablaSelecionar').html(xm);
                                $('.estado').remove();
                            }
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano+50)+"px";
                            // $('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                          
                           
                        }
                    });
                   
                    
                });
                
                $('#btnmatricular').click(function(){
                    $('#cargardor').css({'display':'block'});
                    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                        'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'
                    });
                    $.ajax({
                        type: "POST",
                        url: "matricula_trabajador_ajax.php",
                        data: { accion:'matricula_buscar',fecha_inicio:$("#fecha_inicio").val(),id_turno:$("#id_turno").val(),
                            "sucursal":$('#id_scucursal').val()
                        },
                        success:function(xm){
                            
                            var cadenaretorno=xm.substring(0, 7);
                            if(cadenaretorno=="ERROR_:"){
                                var  error=xm.substr(7,xm.length);
                                $('.estado').remove();
                                var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                $('.contenedorprincipal').append($(estadodiv));
                                if ($(".estado:first").is(":hidden")) {
                                    $(".estado").show("slow");
                                } 
                            }else{
                                $('#contenidoTablaSelecionar').html(xm);
                                $('.estado').remove();
                            }
                            $('#cargardor').css({'display':'none'});
                            var ventana_ancho_mitad = ($(window).width()/2);
                            tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            ventana_ancho_px=(tamano+50)+"px";
                            //$('#contenidoTablaSelecionar').css({'margin-left':ventana_ancho_px})
                          
                           
                        }
                    });
                   
                    
                });
                
                $('#btnver_registro').click(function(){

			$('#cargardor').css({'display':'block'});
			$('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px', 'top': ($(window).height() / 2 - $('#cargardor').height() / 2) + 'px'});                    

			$.ajax({
                        	type: "POST",
                        	url: "matricula_trabajador_ajax.php",
                        	data: {
					accion:'reporte_trabajor',
					fecha_inicio:$("#fecha_inicio").val(),
					fecha_final:$("#fecha_final").val(),
					cod_trabajor:$("#cod_traba").val(),
					"sucursal":$('#id_scucursal').val()
				}, success:function(xm){ 

					var cadenaretorno=xm.substring(0, 7);

                            		if(cadenaretorno=="ERROR_:"){

						var error=xm.substr(7,xm.length);
                                		$('.estado').remove();
                                		var estadodiv= $("<div class='estado' style='color:#CC1005;font-size: 15px;font-weight: bold;display:none'>"+error+"</div>");
                                		$('.contenedorprincipal').append($(estadodiv));

                                		if($(".estado:first").is(":hidden")) {
							$(".estado").show("slow");
						} 
					}else{
                                		$('#contenidoTablaSelecionar').html(xm);
                                		$('.estado').remove();
                            		}

                            		$('#cargardor').css({'display':'none'});
                            		var ventana_ancho_mitad = ($(window).width()/2);
                            		tamano=ventana_ancho_mitad-($('#contenidoTablaSelecionar').width()/2);
                            		ventana_ancho_px=(tamano+50)+"px";

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
            <!--<div id="content_title">&nbsp;</div>
            <div id="content_body">&nbsp;</div>
            <div id="content_footer">&nbsp;</div>-->
        </div>

        <div id="footer">&nbsp;</div>
        <div id="cargardor" style="position: absolute;display: none"><img src="liquidacion_vales/cg.gif" /></div>

		<?php

		    	include('/sistemaweb/include/mvc_sistemaweb.php');
		    	include('TrabajorXisla/t_matricula_trabajador.php');
		    	include('TrabajorXisla/m_matricula_trabajador.php');
		    	$objtem = new matricula_personal_Template();
		    	$accion = $_REQUEST['accion'];

			if ($accion == "ni") {

		        	echo matricula_personal_Template::FormularioPrincipal();

			} else if ($accion == "update") {

				$fecha_find	= $_REQUEST['fecha'];
				$id_turno	= $_REQUEST['turno'];
				$almacen	= $_REQUEST['almacen'];

				$trabajadores_matriculado	= matricula_personal_Model::VerTrabajdor_X_Asignado($fecha_find, $id_turno, $almacen);
		        	$lados				= matricula_personal_Model::ObtenerLados($almacen);
		        	$trabajores			= matricula_personal_Model::ObtenerTrabajadores();
		        	$punto_vt_market		= matricula_personal_Model::ObtenerPuntoMarket($almacen);

				matricula_personal_Template::CrearTablaMatricula_Actualizar($lados, $trabajores, $punto_vt_market, $trabajadores_matriculado, $fecha_find, $id_turno, $almacen);

			}else if ($accion == "delete") {

				$fecha_find	= $_REQUEST['fecha'];
				$id_turno	= $_REQUEST['turno'];
				$almacen	= $_REQUEST['almacen'];
				$fecha_ini	= $_REQUEST['fecha_ini'];
				$fecha_fin	= $_REQUEST['fecha_fin'];
				$cod_trabajador	= trim($_REQUEST['cod_trabajador']);
				$sucursal	= $_REQUEST['ch_sucursal'];

				$delete		= matricula_personal_Model::Delete_Trabajadores($fecha_find, $id_turno, $almacen);
				//$registros	= matricula_personal_Model::ObtenerTrabajadores_Asignado($fecha_ini, $fecha_fin, $cod_trabajador, $sucursal);

				echo matricula_personal_Template::Inicio();

				//matricula_personal_Template::CrearTablaReporte($registros, $fecha_ini, $fecha_final, $cod_trabajador, $sucursal);

			} else {
				echo matricula_personal_Template::Inicio();
			}

		?>

    </body>

</html>
