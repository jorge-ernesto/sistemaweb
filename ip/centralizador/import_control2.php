<?php
require('central.inc.php');
$migerr = NULL;
$preciocont=0;
?>
<!-- Espere mientras termine el Proceso de Importacion<br><img src='img/load.gif'><br><br> -->
<form action='import_control.php'>
    <input type=submit value='REGRESAR'> <font style='font-size:12px'><b>REGRESAR</b> </font>
    <font color='blue' style='font-size:11px'><b>SOLO CUANDO TODOS LOS DATOS DE LAS ESTACIONES SELECCIONADAS HAYAN TERMINADO POR COMPLETO </b></font>
    <br>
</form>
<?php
$estaciones = Array('EDS Ica 1', 'EDS Chimbote', 'EDS Barranco', 'EDS Independencia', 'EDS Carabayllo', 'EDS Venezuela', 'EDS La Marina', 'EDS Argentina', 'EDS La Molina', 'EDS La Victoria 1', 'EDS Chiclayo', 'EDS Ica 2');
$ches = count($_POST['checkbox']);
$array_check = $_POST['checkbox'];




//$deleteId = implode(",", $_POST['checkbox']);


if ($_GET['cambio'] == 0) {





    /* $compactada=serialize($array_check);

      $compactada=urlencode($compactada); */

/*for ($i = 0; $i < $ches; ++$i) {
   echo  $array_check[$i];
}
EXIT;*/
    for ($i = 0; $i < $ches; ++$i) {

        $id_remoto = $array_check[$i];
        $sql = 'BEGIN;';
        if ($sqlca->query($sql) < 0){
            $migerr = 'Error interno (1)';
        }else {
            $sql = "  SELECT
                        first(r.ip),
                        to_char(max(p.systemdate),'YYYYMMDD'),
                        to_char(max(p.systemdate) + interval '1 day','YYYYMMDD'),
                        to_char(max(p.systemdate) + interval '1 day','YYYY-MM-DD')
                FROM
                        mig_process p
                        JOIN mig_remote r USING (mig_remote_id)
                WHERE
                        r.mig_remote_id = {$id_remoto};";
      
            if ($sqlca->query($sql) <= 0)
                $migerr = 'Nodo remoto desconocido';
            else {
                $rR = $sqlca->fetchRow();
                if (importProcess("http://{$rR[0]}/sistemaweb/centralizer.php", $rR[2])) {
                    $sql = "INSERT INTO mig_process (mig_remote_id,created,systemdate) VALUES ({$id_remoto},now(),'{$rR[3]}');";
                    $sqlca->query($sql);

                    $sql = 'COMMIT;';
                     echo "La estacion : <strong>".$estaciones[$id_remoto-1]."</strong>  no se presentaron problemas.<br/> ";
                    $sqlca->query($sql);
                } else {
                    echo "<span style='color:#FF0100'>Problema en la estacion : <strong>".$estaciones[$id_remoto-1]."</strong>  =></span> ";
                    if ($migerr==NULL) {
                        $migerr = "Error Conexion (Conexion debil) " ;
                    }else
                    {
                      
                    }
                    echo "<span style='color:#FF0100'>".$migerr."</span><br/>";
                    $sql = 'ROLLBACK;';
                    $sqlca->query($sql);
                }
            }
           
            
        }
    }
    
     echo "<br/><br/><strong> <span style='color:#0000FF'>Centralizacion de Integrado a Opensoft fue Existos</span></strong>";
    
} else {

//echo "<br>".$ches." - ".$array_check[0];



    if (isset($_GET[checkbox])) {

        $a = stripslashes($_GET[checkbox]);

        $array_check = unserialize($a);
    }
    $ches = count($array_check);

    for ($k = 0; $k < $ches; ++$k) {
        if ($array_check[$k] != 3) {
            if ($array_check[$k] != 8) {
                ?>

                <?php echo $array_check[$k] . ' - ' . $estaciones[$array_check[$k] - 1] . ': '; ?><iframe width=500 height=28 scrolling=no frameborder=0 src='import_control.php?do=migrate&remote=<?php echo $array_check[$k]; ?>&img=1'></iframe><br>
                <?php
            }
        }
    }
    $img = '';
}
?>

<?php /*
  //Código a insertar al final de la web
  $TiempoFinal = getTiempo();
  $Tiempo = $TiempoFinal - $TiempoInicial;
  $Tiempo = round($Tiempo,6);
  conversor_segundos($Tiempo);

  function conversor_segundos($seg_ini) {

  $horas = floor($seg_ini/3600);
  $minutos = floor(($seg_ini-($horas*3600))/60);
  $segundos = $seg_ini-($horas*3600)-($minutos*60);
  echo $horas.'h:'.$minutos.'m:'.$segundos.'s';

  } */
