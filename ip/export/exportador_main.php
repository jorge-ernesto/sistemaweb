<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>

<script type="text/javascript">
    $(function(){
        $('input[type=checkbox]').click(function(){
            if(!$(this).is(':disabled') && $(this).is(':checked')){
                id=$(this).attr("id").substr(5);
                $("#m"+id).removeAttr('disabled');
              
              
            }else{
                id=$(this).attr("id").substr(5);
                $("#m"+id).attr('disabled',"false");
            }  
        });
        
         $('#id_product').click(function(){
              
                $('#aa').css('display','block');
                $('#ab').css('display','block');
         
                $.ajax({
                    url: "exportador_exe.php",
                    data:{'do':'precio'}
                    ,success:function(data){
                        $('#aa').css('display','none');
                        $('#ab').css('display','none');
                        $('#info').html(data);
                    }
                });
            
         });
         
        $('#expo_all').click(function(){
            jsonall=new Array();
            jsonfecha=new Array();
            ir=false;
            cont=0;
            $('input[type=checkbox]').each(function(){
                if(!$(this).is(':disabled') && $(this).is(':checked')){
                    id=$(this).attr("id").substr(5);
                    cant=$("#m"+id).attr("cant");
                    jsonfecha[cont]=$("#m"+id).attr("fecha");
                    tmp=new Array();
                    cont_tem=0;
                    for(i=1;i<=cant;i++){
                        valor=$('#check'+id).attr("attr_"+i);
                        tmp[cont_tem]=valor;
                        cont_tem++;
                        
                    }
                    jsonall[cont]=tmp;
                    cont++;
                    ir=true;
                 
                }
                
            });
            if(ir=true){
                $('#aa').css('display','block');
                $('#ab').css('display','block');
         
                $.ajax({
                    url: "exportador_exe.php",
                    data:{'data':jsonall,'ultD':jsonfecha,'do':'exportall'}
                    ,success:function(data){
                        $('#aa').css('display','none');
                        $('#ab').css('display','none');
                        $('#info').html(data);
                    }
                });
            }
        });
        $('.one-cke').click(function(){
            var id=$(this).attr('id').substr(1);
            var fecha=$(this).attr('fecha');
            cantidad_attr=parseInt($(this).attr('cant'));
            json=new Array();
            if(!$('#check'+id).is(':disabled')){
                for(i=1;i<=cantidad_attr;i++){
                    valor=$('#check'+id).attr("attr_"+i);
                
                    json[i-1]=valor;
                }
                $('#aa').css('display','block');
                $('#ab').css('display','block');
         
                $.ajax({
                    url: "exportador_exe.php",
                    data:{'data':json,'ultD':fecha,'do':'export'}
                    ,success:function(data){
                        $('#aa').css('display','none');
                        $('#ab').css('display','none');
                        $('#info').html(data);
                    }
                });
                
            }else{
                alert("No se puede Migrar por que esta desabilitada.");
            }
            
            
        });
    });
</script>
<?php
require('central.inc.php');

$migerr = NULL;
$id_bloqlear = array(1,11);//3,10
$cterr = NULL;
$crlist = Array();

$sql = "
        SELECT
                first(r.mig_remote_id),
                first(r.name),
                max(p.mig_export_id),
                to_char(max(p.created),'DD/MM/YYYY HH24:MI:SS'),
                to_char(max(p.systemdate) + interval '1 day','DD/MM/YYYY'),
                 p.mig_remote_id
        FROM
                mig_export p
                JOIN mig_remote r USING (mig_remote_id)


        GROUP BY
                p.mig_remote_id,
                p.mig_remote_id
        ORDER BY
                p.mig_remote_id;";

if ($sqlca->query($sql) <= 0)
    $cterr = 'Error interno (1)';
else {
    for ($i = 0; $i < $sqlca->numrows(); $i++) {
        $rR = $sqlca->fetchRow();
        $crlist[] = Array($rR[0], $rR[1], $rR[2], $rR[3], $rR[4], $rR[5]);
    }
}

require('header.inc.php');
if ($cterr == NULL) {
    ?>
    <div id="aa" style="background-color: black;
         left: 0;
         opacity: 0.35;
         position: absolute;
         top: 0;
         z-index: 9999;width: 100%;height: 100%;display: none;" >


    </div>
    <div id="ab" style="
         font-weight: normal;
         left: 0;
         top:100px;
         line-height: 0;
         position: absolute;
         text-align: center;
         width: 100%;
         z-index: 10000;display: none;">
        <img src="img/cg.gif"/>

    </div>
    <div id="info">
        <form id="form1" method="post" action="import_control2.php?cambio=0">
            <table cellSpacing=3 cellPadding=3>
                <tr>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 10%;">ID</td>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 25%;">Estaci&oacute;n</td>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 20%;">Dia a migrar</td>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 20%;">&Uacute;ltimo proceso</td>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 10%;">Acci&oacute;n</td>
                    <td class=borderedcell style="text-align: center; font-weight: bold; width: 15%;">Seleccionar</td>

                </tr>
                <script type='text/javascript'>

                    function confirmar(url,est,n){  
                                                         
                        confi=confirm('Desea migrar datos de '+est+'?'); 
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
                    }
                    $a = 1;
                    $sql = "select c_org_id from mig_cowmap where id_remote={$rV[0]};";
                    $valores = "";
                    if ($sqlca->query($sql) <= 0) {
                        continue;
                    } else {
                        $ini = 1;
                        $fecha = $rV[4];
                        while ($c_org_ids = $sqlca->fetchRow()) {
                            $valores.=" attr_$ini='" . $c_org_ids['c_org_id'] . "' ";
                            $ini++;
                        }
                        $ini--;
                        $cant = " cant='$ini'  fecha='$fecha'";
                    }

                    // $dia_exop=date("Y-m-d",  strtotime($rV[4]." +1 day "));
                    echo '<tr>';
                    echo "<td class=\"borderedcell\">" . $rV[0] . "</td>";
                    echo "<td class=\"borderedcell\">" . htmlentities($rV[1]) . "</td>";
                    echo "<td class=\"borderedcell\">{$rV[4]}</td>";
                    echo "<td class=\"borderedcell\">{$rV[3]}</td>";
                    echo "<td align=center>";
                    ?>

                    <?php
                    if (in_array($rV[0], $id_bloqlear)) {
                        $checked = '';
                    } else {
                        $checked = 'checked=checked';
                    }
                    if (in_array($rV[0], $id_bloqlear)) {
                        $disabled = 'disabled=true';
                    } else {
                        $disabled = '';
                    }
                    ?>


                              <!--<input type=button id='m<?php //echo $j;             ?>' name='m<?php //echo $j;             ?>' value=Migrar onclick=confirmar('exportador_exe.php?do=migrate&remote=<?php //echo $rV[0];             ?>','<?php //echo $esta;             ?>',<?php //echo $n;             ?>);>-->
                    <input type=button id='m<?php echo $j; ?>' <?php echo $disabled; ?> name='m<?php echo $j; ?>' value=Migrar  class="one-cke" <?php echo $cant; ?> >
                    <?php
                    echo "</td>";


                    echo "<td align=center><input type='checkbox'  {$valores}  id='check{$j}' value='{$rV[0]}' {$checked}  {$disabled}></td>";
                    ++$j;
                    $n = $j;
                }
                ?></table><BR>
            <input type=button id='m<?php echo $j; ?>' name='m<?php
            echo $j;
            ++$j;
                ?>' value=RESTAURAR onclick=confirmar2('import_control.php');>
            <input type=button id='expo_all' name='m<?php echo $j; ?>' value="Migrar Seleccionados">
            <input type=button id='id_product'  value="Migrar Productos">
        </form>
    </div>
    <?php
}
?>

<?php
require('footer.inc.php');
