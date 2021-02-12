<?php
require('central.inc.php');

$migerr = NULL;
$preciocont=0;

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'migrate') {

    if (!isset($_REQUEST['remote']) || !is_numeric($_REQUEST['remote']))
        $migerr = 'Nodo remoto no v&aacute;lido';
    else {
        $MrI = $_REQUEST['remote'];
        $sql = 'BEGIN;';
        if ($sqlca->query($sql) < 0)
            $migerr = 'Error interno (1)';
        else {
            $sql = "
SELECT
	first(r.ip),
	to_char(max(p.systemdate),'YYYYMMDD'),
	to_char(max(p.systemdate) + interval '1 day','YYYYMMDD'),
	to_char(max(p.systemdate) + interval '1 day','YYYY-MM-DD')
FROM
	mig_process p
	JOIN mig_remote r USING (mig_remote_id)
WHERE
	r.mig_remote_id = {$MrI};";
            if ($sqlca->query($sql) <= 0)
                $migerr = 'Nodo remoto desconocido';
            else {
                $rR = $sqlca->fetchRow();
//10.0.26.1
                if (importProcess("http://{$rR[0]}/sistemaweb/centralizer.php", $rR[2])) {
                    $sql = "INSERT INTO mig_process (mig_remote_id,created,systemdate) VALUES ({$MrI},now(),'{$rR[3]}');";
                    //$errr=importProcess("http://{$rR[0]}/sistemaweb/centralizer.php",$rR[2]);
                    $sqlca->query($sql);

                    $sql = 'COMMIT;';
                    $sqlca->query($sql);
                } else {
                    if ($migerr == NULL) {
                        $migerr = "Error Conexion (Conexion debil) " . $errr;
                        echo $migerr;
                    } else {
                        $migerr = "Error encontrado : " . $errr;
                        if (strlen(trim($errr)) == 0)
                            $migerr.="Posible conexion debil.";
                    }
                    echo "<span style='color:#FF0100'>" . $migerr . "</span><br/>";
                    $sql = 'ROLLBACK;';
                    $sqlca->query($sql);
                }
            }
        }
    }
}

$cterr = NULL;
$crlist = Array();

$sql = "
SELECT
	first(r.mig_remote_id),
	first(r.name),
	max(p.mig_process_id),
	to_char(max(p.created),'DD/MM/YYYY HH24:MI:SS'),
	to_char(max(p.systemdate),'DD/MM/YYYY')
FROM
	mig_process p
	JOIN mig_remote r USING (mig_remote_id)
--WHERE
	 --mig_remote_id = 8
GROUP BY
	p.mig_remote_id
ORDER BY
	p.mig_remote_id;";

if ($sqlca->query($sql) <= 0)
    $cterr = 'Error interno (1)';
else {
    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $rR = $sqlca->fetchRow();
        //print_r($rR);
        $crlist[] = Array($rR[0], $rR[1], $rR[2], $rR[3], $rR[4]);
    }
}

require('header.inc.php');

if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'migrate') {
    ?><table>
        <tr>
            <td class="borderedcell <?php if ($migerr == NULL) echo "greencell"; else echo "highvaluecell"; ?>" style="text-align: center; font-weight: bold;"><?php if ($migerr == NULL) echo "<font color=white>Migraci&oacute;n satisfactoria</font><br> " . $errr; else echo '<font color=white>' . $migerr . '</font>'; ?></td>
        </tr>
    </table>

    <script language="javascript">
        window.onload = disable_enable(<?php echo $n; ?>);
    </script>

    <?php
}

if ($cterr == NULL) {
    ?>
    <form id="form1" method="post" action="import_control2.php?cambio=0">
        <table cellSpacing=3 cellPadding=3>
            <tr>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 10%;">ID</td>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 25%;">Estaci&oacute;n</td>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 20%;">&Uacute;ltima migraci&oacute;n</td>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 20%;">&Uacute;ltimo proceso</td>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 10%;">Acci&oacute;n</td>
                <td class=borderedcell style="text-align: center; font-weight: bold; width: 15%;">Seleccionar</td>

            </tr>
            <script type='text/javascript'>

                function confirmar(url,est,n){  
         
                    confi=confirm('Desea Importar datos de '+est+'?'); 
                    if (confi){
                        //alert(url);
                        disable_enable(n);
                        window.location=url;
                    }
        	
                    else {  //alert(url);
                    }
           
                } 	
                function confirmar2(url){  
         
                    window.location=url;
        	
           
                } 	
            </script>
    <?php
//$ches=count($_POST['checkbox']);
//$array_check=$_POST['checkbox'];
//echo 'hm '.$ches;
//$y=0;
    $j = 1;
    $esp = '';
    $a = 1;
    foreach ($crlist as $rV) {
        ++$y;
        $esta = '';
        for ($i = 0; $i < 15; ++$i) {

            $e = substr($rV[1], 4 + $i, 1);
            if ($e == ' ') {
                $esp = '\u00A0';
            } else {
                $esta = $esta . $esp . substr($rV[1], 4 + $i, 1);
                ++$a;
                $esp = '';
            }
        }$a = 1;

        if ( $j == 1 || $j == 2  || $j == 4 || $j == 11) {
            $checked = '';
        } else {
            $checked = 'checked=checked';
        }
        if ($j == 1 || $j == 2 ||  $j == 4 || $j == 11) {
            $disabled = 'disabled=true';
        } else {
            $disabled = '';
        }

        echo '<tr>';
        echo "<td class=\"borderedcell\">{$rV[0]}</td>";
        echo "<td class=\"borderedcell\">" . htmlentities($rV[1]) . "</td>";
        echo "<td class=\"borderedcell\">{$rV[4]}</td>";
        echo "<td class=\"borderedcell\">{$rV[3]}</td>";
        echo "<td align=center>";
        ?><input type=button <?php echo $disabled; ?>  id='m<?php echo $j; ?>' name='m<?php echo $j; ?>' value=Centralizar onclick=confirmar('import_control.php?do=migrate&remote=<?php echo $rV[0]; ?>','<?php echo $esta; ?>',<?php echo $n; ?>);><?php
        echo "</td>";


        echo "<td align=center><input type='checkbox' name='checkbox[]' id='check{$j}' value='{$rV[0]}' {$checked}  {$disabled}></td>";
        //	echo "<td align=center>";
        /* for($z=0;$z<$ches;++$z){
          if($j==$array_check[$z]){$iframe="<iframe width=500 height=28 scrolling=no frameborder=0 src='import_control.php?do=migrate&remote=".$array_check[$z]."'></iframe></td>";}
          } */

        /* echo $iframe;
          $iframe=""; */
        ++$j;
        $n = $j;
    }
    ?></table><BR>
        <input type=button id='m<?php echo $j; ?>' name='m<?php echo $j;
        ++$j;
    ?>' value=RESTAURAR onclick=confirmar2('import_control.php');>
        <input type=submit id='m<?php echo $j; ?>' name='m<?php echo $j; ?>' value="Centralizar Seleccionados">
    </form>
    <form action="import_control_precio.php" method="post">
        <input type="hidden" name="do" value="migrate" />
       <input type=submit  name='ce_precio' value="Centralizar Productos"> 
    </form>
    <?php
}
?>

<?php
require('footer.inc.php');
