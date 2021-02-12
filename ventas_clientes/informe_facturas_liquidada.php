<?php
session_start();

include_once('/sistemaweb/include/dbsqlca.php');
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');
include('/sistemaweb/include/mvc_sistemaweb.php');
include('/sistemaweb/ventas_clientes/reportes/m_auditor_venta.php');
include('/sistemaweb/ventas_clientes/reportes/t_auditor_venta.php');
include('/sistemaweb/ventas_clientes/reportes/c_auditor_venta.php');






$yyyyaa = $_REQUEST['yyyyaa'];
$fecha_filtro = $_REQUEST['fecha_filtro'];
$accion = $_REQUEST['accion'];

try {
    ?>
    <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
    <link rel="stylesheet" href="/sistemaweb/css/vales_liquidacion.css" type="text/css">
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script type="text/javascript">
        $(function(){
                         
        }
    );
    </script>
    <?php
  ;

    $objmodel = new AuditorVentaModel();
    $objtem = new AuditorVentaTemplate();
    $objcomn = new AuditorVentaController();
    if($accion=="1"){

    $data = AuditorVentaModel::ventas_vales_facturadas($yyyyaa, $fecha_filtro);
    AuditorVentaTemplate::vales_liquidadas($data);
    }else{
       $data = AuditorVentaModel::diferencia_de_vales($fecha_filtro, $yyyyaa);
       $cantidad_pos_trans=count($data[0]);
       $cantidad_pos_vales=count($data[1]);
       $cantidad_pos_vales_complemento=count($data[2]);
      $maximo_fila= max($cantidad_pos_trans,$cantidad_pos_vales,$cantidad_pos_vales_complemento);
       
       
        AuditorVentaTemplate::diferencias_vales($data[0],$data[1],$data[2],$maximo_fila);
    }
} catch (Exception $r) {

    echo "{'estado':'error','mes':'" . $r->getMessage() . "'}";
}
