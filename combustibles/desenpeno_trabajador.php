<html>
    <head>

        <title>Sistema de Ventas - Desempe&ntilde; Trabajador</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
        <script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>




        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

        <script type="text/javascript">
            var distancia=300;
            var int_contador=0,
            int_contador_monto_modif=0;
            var monto_actual_fact=parseFloat("0.00");
            var ch_cliente_guardado="";
            var ArregloCliente=new Array();


            function formato_numero(numero, decimales, separador_decimal, separador_miles){ // v2007-08-06
                numero=parseFloat(numero);
                if(isNaN(numero)){
                    return "";
                }

                if(decimales!==undefined){
                    // Redondeamosf
                    numero=numero.toFixed(decimales);
                }

                // Convertimos el punto en separador_decimal
                numero=numero.toString().replace(".", separador_decimal!==undefined ? separador_decimal : ",");

                if(separador_miles){
                    // Añadimos los separadores de miles
                    var miles=new RegExp("(-?[0-9]+)([0-9]{3})");
                    while(miles.test(numero)) {
                        numero=numero.replace(miles, "$1" + separador_miles + "$2");
                    }
                }

                return numero;
            }
           
            function marcar(source) 
            {
                
                checkboxes=document.getElementsByTagName('input'); 
                for(i=0;i<checkboxes.length;i++)
                {
                    if(checkboxes[i].type == "checkbox") 
                    {
                        checkboxes[i].checked=source.checked; 
                    }
                }
                if(source.checked){
                    
                
                    $('#lba_total_cantidad').html($('#total_cantidad_siempre').val());
                    $('#lba_total_importe').html($('#total_importe_siempre').val());
                    $('#total_cantidad').val($('#total_cantidad_siempre').val());
                    $('#total_importe').val($('#total_importe_siempre').val()); 
                }else{
                    $('#lba_total_cantidad').html("0.00");
                    $('#lba_total_importe').html("0.00");
                    $('#total_cantidad').val("0.00");
                    $('#total_importe').val("0.00");   
                }
                
                
            }
            
            function MostarNumeroRecibo(obj){
                var valorcmb=obj.value;
                $('#cargardor').css({'display':'inline'});
                $.ajax({
                    type: "POST",
                    url: "c_ingreso_caja_relacion.php",
                    data: {
                        accion:'num_recibo',
                        'almacen':valorcmb
                    },
                    success:function(xm){ 
                        var obj=eval('('+xm+')');  
                        $('#recibe_nro').val(obj.dato);
                        
                        $('#cargardor').css({'display':'none'});
                   
                    }
                });
            }     
            function irhome(){
                window.location.href='/sistemaweb/combustibles/desenpeno_trabajador.php';
            }
       
            $(function(){            
                /*------*/
            
               
                
                var tmpclientes=null;
               
                /*------*/
                
                $('#content').remove();
                $('#footer').remove();
                var ventana_ancho = ($(window).width()/2)-250;
                
                ventana_ancho_px=ventana_ancho+"px";
                //$('.contenedorprincipal').css({'margin-left':ventana_ancho_px});
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
             
             
               
                $( "#fecha_inicial" ).datepicker( 
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_inicial" ).datepicker("option", "dateFormat","yy-mm-dd");
                
                $( "#fecha_final" ).datepicker(
                {changeMonth: true,
                    changeYear: true});
                $( "#fecha_final" ).datepicker("option", "dateFormat","yy-mm-dd");
                
          
           
              
          
                
                $('#btnseleccionar').click(function(){
                    $('#cargardor').css({'display':'inline'});
                    $.ajax({
                        type: "POST",
                        url: "c_desenpeno_trabajador_relacion.php",
                        data: { accion:'mostar_resultado_data','fecha_inicio':$('#fecha_inicial').val(),'type_view':$('#type_view').val(),
                            'fecha_final':$('#fecha_final').val(),
                            'sucursal':$('#serie_doc').val(),
                        'limit_mostrar':$('#limit').val()},
                        success:function(xm){
                        	
                        	
                            $('#contenidoTablaSelecionar').html(xm);
                            $('#cargardor').css({'display':'none'});
                        }
                    });
                });
                
                
           
                
             
                var intzebra=0;
              
             
                $('#id_nuevo_registro').click(function(){
                    $('#cargardor').css({'display':'inline'});
                    $('#contenidoTablaSelecionar').html("");
                    $.ajax({
                        type: "POST",
                        url: "c_desenpeno_trabajador_relacion.php",
                        data: { accion:'nuevo_registro'},
                        success:function(xm){
                            
                            $('#cargardor').css({'display':'none'});
                            $('#id_nuevo_registro_view').html(xm);
                            
                            $('#cargardor').css({'display':'none'});
                            $( "#fecha_mostar" ).datepicker( 
                            {changeMonth: true,
                                changeYear: true});
                            
                            $( "#fecha_mostar" ).datepicker("option", "dateFormat","yy-mm-dd");
                            
                            $( "#id_cliente_auto" ).autocomplete({
                                source: tmpclientes,
                                select: function( event, ui ) {
                                    $('#id_cliente_auto_ruc').val(ui.item.id);
                                }
                                
                               
                            });
                            $('#fecha_mostar').val($('#fecha_tmp').val());
                            $('#txttipo_cambio').val();
                            
                        }
                    });
                });
                
            
          
                $('#btninsert').off('click');
                $(document).on('click','#btninsert',function(){

                    var data={
                    	'almacen':$('#cmnsucursal_id').val(),
                    	'codigo_trabajador':$('#cmncaja_id').val(),
                    	'turno':$('#cmnturno_id').val(),
                    	'dia':$('#fecha_mostar').val(),
                    	'cantidad':$('#txtcantida').val(),
                    	'importe':$('#txtimporte').val()
                    };
                    
                    if(false){
                     $('#contenidoTablaSelecionar').html(xm);   
                    }else{
                        $('#cargardor').css({'display':'inline'});
                        $.ajax({
                            type: "POST",
                            url: "c_desenpeno_trabajador_relacion.php",
                            data: { accion:'insert_gnv',dataenv:data,fecha:$('#fecha_mostar').val()},
                            success:function(xm){
                                $('#contenidoTablaSelecionar').html(xm);
                                $('#cargardor').css({'display':'none'});
                   
                            }
                        });
                    }
                });
                
                
                
                
                    $('.idelimnargnv').off('click');
                $(document).on('click','.idelimnargnv',function(){
                	var da=$(this).val();
                	 $('#cargardor').css({'display':'inline'});
                        $.ajax({
                            type: "POST",
                            url: "c_desenpeno_trabajador_relacion.php",
                            data: { accion:'del_gnv',dataenv:da},
                            success:function(xm){
                                $('#contenidoTablaSelecionar').html(xm);
                                $('#cargardor').css({'display':'none'});
                   
                            }
                        });
                        
                });
                
          
          
          $('#btnbuscar').off('click');
                $(document).on('click','#btnbuscar',function(){

                    var data={
                    	'almacen':$('#cmnsucursal_id').val(),
                    	'dia':$('#fecha_mostar').val()
                    };
                    
                    if(false){
                        alert('Error al buscar Facturas del Cliente :'+valor_dni.length);
                    }else{
                        $('#cargardor').css({'display':'inline'});
                        $.ajax({
                            type: "POST",
                            url: "c_desenpeno_trabajador_relacion.php",
                            data: { accion:'get_gnv',dataenv:data},
                            success:function(xm){
                                $('#contenidoTablaSelecionar').html(xm);
                                $('#cargardor').css({'display':'none'});
                   
                            }
                        });
                    }
                });
                
                
                
              $('#btnexcel').click(function(){
                    $('#cargardor').css({'display':'inline'});
                    $.ajax({
                        type: "POST",
                        
                        url: "c_desenpeno_trabajador_relacion.php",
                        data: { accion:'mostar_resultado_data_excel','fecha_inicio':$('#fecha_inicial').val(),'type_view':$('#type_view').val(),
                            'fecha_final':$('#fecha_final').val(),
                            'sucursal':$('#serie_doc').val(),
                        'limit_mostrar':$('#limit').val()},
                        success:function(xm){
                        	$('#cargardor').css({'display':'none'});
                            location.href="/sistemaweb/combustibles/report.xls"
                            
                        }
                    });
                });




                $.ajax({
                    type: "POST",
                    url: "c_desenpeno_trabajador_relacion.php",
                    data: { accion:'tipodocumento',documento:"-"},
                    success:function(xm){
                             
                        var obj=eval('('+xm+')');  
                        tmpclientes=obj.cliente;
                            
                        $('#cmbtipooperacion').html(obj.dato);
                        $('#cargardor').css({'display':'none'});
                        $('#fecha_inicial').val($('#tmp_ini').val());
                        $('#fecha_final').val($('#tmp_final').val());
                   
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
            <div id="content_title">&nbsp;</div>
            <div id="content_body">&nbsp;</div>
            <div id="content_footer">&nbsp;</div>
        </div>

        <div id="footer">&nbsp;</div>

        <?php
        include('/sistemaweb/include/mvc_sistemaweb.php');
        include('movimientos/t_desenpeno_trabajador.php');
        include('movimientos/m_desenpeno_trabajador.php');
        include('movimientos/c_desenpeno_trabajador.php');
        $objtem = new DesenpenoTemplate();
        echo DesenpenoTemplate::FormularioPrincipal();
        ?>
        <script type="text/javascript">
            //   $( "#fecha_inicio" ).datepicker( "option", "dateFormat","yy-mm-dd");
          
        </script>
    </body>
</html>
