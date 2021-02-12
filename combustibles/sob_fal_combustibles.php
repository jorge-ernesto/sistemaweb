<html>
    <head>
        <title>SOBRANTES Y FALTANTES DE COMBUSTIBLES </title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
        <script language="JavaScript" src="/sistemaweb/ventas_clientes/js/sisfacturacion.js"></script>
        <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>

        <link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
        <script src="/sistemaweb/js/jquery-ui.js"></script>
        <script  type="text/javascript"> 

            $(document).ready(function(){
                $( "#fecha_inicio" ).datepicker(
                {changeMonth: true,
                    changeYear: true,
                    onSelect:function(fecha,obj){
                        $.ajax({
                            type: "POST",
                            url: "reportes/c_sob_fal_combustibles.php",
                            data: { accion:'fecha_servidor',fecha_inicio:fecha
                            },
                            success:function(xm){ 
                                var json=eval('('+xm+')');
                            }
                        });
                    }    
                });
            
                $( "#fecha_inicio" ).datepicker("option", "dateFormat","dd-mm-yy");
                
                
                $( "#fecha_final" ).datepicker(
                {changeMonth: true,
                    changeYear: true,
                    onSelect:function(fecha,obj){
                        $.ajax({
                            type: "POST",
                            url: "reportes/c_sob_fal_combustibles.php",
                            data: { accion:'fecha_servidor',fecha_inicio:fecha
                            },
                            success:function(xm){ 
                        
                                var json=eval('('+xm+')');
                                $('#opt_final').html(json.msg);
                            }
                        });
                    }
                });
                $( "#fecha_final" ).datepicker("option", "dateFormat","dd-mm-yy");
            
            
                $('#executar').click(function(){
                    
                    $('#cargardor').css({'display':'block'});
                    $('#cargardor').css({'left': ($(window).width() / 2 - $('#cargardor').width() / 2) + 'px',
                        'top': 200 + 'px'
                    });
                    $.ajax({
                        type: "POST",
                        url: "reportes/c_sob_fal_combustibles.php",
                        data: { accion:'executar_reporte',
                            almacen:$('#almacen').val(),
                            unidadmedida:$('#unidadmedida').val(),
                            detallecompras:$('#detallecompras').val(),
                            fecha_inicio:$('#fecha_inicio').val(),
                            fecha_final:$('#fecha_final').val(),
                            tanque:$('#tanque').val()
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
                        url: "reportes/c_sob_fal_combustibles.php",
                        data: { 
                            accion:'executar_reporte_excel',
                            almacen:$('#almacen').val(),
                            unidadmedida:$('#unidadmedida').val(),
                            detallecompras:$('#detallecompras').val(),
                            fecha_inicio:$('#fecha_inicio').val(),
                            fecha_final:$('#fecha_final').val(),
                            tanque:$('#tanque').val()
                        },
                        success:function(xm){ 
                            $('#cargardor').css({'display':'none'});
                            location.href="/sistemaweb/combustibles/reporte_excel_sobyfal.php";
                              
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
        </div>

        <div id="footer">&nbsp;</div>
        <div id="cargardor" style="position: absolute;display: none"><img src="/sistemaweb/ventas_clientes/liquidacion_vales/cg.gif" /></div>
            <?php
            include('/sistemaweb/include/mvc_sistemaweb.php');
            include('reportes/t_sob_fal_combustibles.php');
            include('reportes/m_sob_fal_combustibles.php');
            extract($_REQUEST);

            $objtem = new sob_fal_combustibles_Template();
            $accion = $_REQUEST['accion'];
    
            $tanques = sob_fal_combustibles_Model::ObtenerTanques();
            $estaciones = sob_fal_combustibles_Model::ObtenerEstaciones();
            echo sob_fal_combustibles_Template::Inicio($tanques, $estaciones);
            ?>
    </body>
</html>
