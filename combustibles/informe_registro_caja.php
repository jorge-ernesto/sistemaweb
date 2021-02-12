<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');

include('movimientos/m_ingreso_caja.php');
include('movimientos/c_ingreso_caja.php');
include('movimientos/t_ingreso_caja.php');

$objmodel = new RegistroCajasModel();
$objtem = new RegistroCajasTemplate();
$objcomn = new RegistroCajaController("");


$accion = $_REQUEST['accion'];
$id_recibo = $_REQUEST['id_recibo'];
try {
    ?>
    <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
    <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script type="text/javascript">
        $(function(){
            $('#activar_edicion_id').click(function(){
                $('.edicion').each(function(){
                    var valor=$(this).text();
                    var id_act=$(this).attr('id_actualizar');
                            
                    $(this).html("<input type='text' value='"+valor+"' class='ok_update' at_id='"+id_act+"'/>");
                });
            });
                    
            $('#guaradar_edicion_id').click(function(){
                var Arregloupadte=new Array();
                var cont=0;
                $('.ok_update').each(function(){
                    var valor=$(this).val();
                    var id_act=$(this).attr('at_id');
                    Arregloupadte[cont]=id_act+"*"+valor;
                    $(this).parent().html(valor);
                    cont++;
                            
                });
                $.ajax({
                    type: "POST",
                    url: "c_ingreso_caja_relacion.php",
                    data:{"accion":"upadte_datos","data": Arregloupadte},
                    success:function(xm){ 
                       alert('Se Actualizo correctamente');
                               
                    }
                });
                        
            });
                    
                    
        }
    );
    </script>
    <?php
    $data_cabecera = RegistroCajasModel::DetalleReporteRecibo($id_recibo);
    $data_detalle = RegistroCajasModel::DetalleReporteRecibo_complemento_registro($id_recibo);
    $data_medios_pago = RegistroCajasModel::DetalleReporteRecibo_medio_pago($id_recibo);

    RegistroCajasTemplate::viewtabla_detalle_recibo($data_cabecera, $data_detalle, $data_medios_pago);
} catch (Exception $r) {

    echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}
