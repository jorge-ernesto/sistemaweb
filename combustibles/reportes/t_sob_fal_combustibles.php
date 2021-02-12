<?php


class sob_fal_combustibles_Template extends Template {


    function titulo() {
        $titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
        return $titulo;
    }

    function errorResultado($errormsg) {
        return '<blink>' . $errormsg . '</blink>';
    }

    function Inicio($tanques, $estaciones) {
        ?>
        <div  align="center">
            <table>
                <tr>
                    <td colspan="5" style="text-align: center;"><h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;"><b>Sobrantes y Faltantes de ddCombustibles<b></h2>
                    </td>
                 </tr>
                                    <tr>
                                        <td align="right">Almacen: </td>
                                        <td >
                                        <select align="right" id="almacen">
                                        <?php
                                        foreach($estaciones as $value){
                                        echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
                                        }
                                        ?>
                                        </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="right" >Fecha Inicio</td>
                                        <td  ><input type='text' id='fecha_inicio' class='fecha_formato'/></td>
                                    </tr>
                                    <tr>
                                        <td align="right" >Fecha Final</td>
                                        <td  ><input type='text' id='fecha_final' class='fecha_formato'/></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Seleccionar Tanque</td>
                                        <td ><select id="tanque">
                                                <?php
                                                foreach ($tanques as $value) {
                                                    echo "<option value='" . $value['tanque'] . "'>" . $value['name'] . "</option>";
                                                }
                                                ?>
                                            </select></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Seleccionar Unidad de Medida</td>
                                        <td ><select id="unidadmedida">
                                                <option value='Galones'>Galones</option>
                                                <option value='Litros'>Litros</option>
                                             </select></td>
                                    </tr>
                                      
                                   <!--  <tr>
                                        <td >Detalle de Compras</td>
                                        <td ><select id="detallecompras">
                                                <option value='Si'>Si</option>
                                                <option value='No'>No</option>
                                            </select></td>
                                    </tr>-->
                                    <tr></br>
                                        <td align="right"><button id="executar">Consultar</button></td>
                                        <td ><button id="executar_excel">Reporte Excel</button></td>
                                    </tr>

                                    </table>
                                    </div>
                                    <div  align="center" id="tab_id_detalle">
                                    </div>

    <?php
    }


    function CrearTablaReporte($REP,$almacen) {
    //var_dump($REP);
    ?>
    </br>
    </br>
    <div style="width: auto;border: 1px;">
    <table cellspacing="0" cellpadding="0" border="0">	
     <tbody>

<table align="center" border="0" >
    <tr> 
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th colspan="2" style="font-size:0.7em; color:white;background-color: #369">TRANSFERENCIAS</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th style="font-size:0.7em; color:black;background-color: #369">&nbsp;</td>
        <th colspan="2" style="font-size:0.7em; color:white;background-color: #369">DIFERENCIA</td>
    </tr>
    <tr> 
        <th style="font-size:0.7em; color:white;background-color: #369">FECHA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">SALDO</td>
        <th style="font-size:0.7em; color:white;background-color: #369">COMPRA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">MEDICION</td>
        <th style="font-size:0.7em; color:white;background-color: #369">VENTA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">PRECIO</td>
        <th style="font-size:0.7em; color:white;background-color: #369">INGRESO</td>
        <th style="font-size:0.7em; color:white;background-color: #369">SALIDA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">PARTE</td>
        <th style="font-size:0.7em; color:white;background-color: #369">VARILLA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">DIARIA</td>
        <th style="font-size:0.7em; color:white;background-color: #369">ACUMULADA</td>
    </tr>
<?php 

    $PRECIO_VENTA = 0.00;

    for($i = 0; $i < count($REP); $i++){

        $PRECIO_VENTA = (empty($REP[$i][12]) ? '0.00' : $REP[$i][12]);

        $TOT_SALDO      =   $TOT_SALDO + $REP[$i][1];
        $TOT_COMPRA     =   $TOT_COMPRA + $REP[$i][2];
        $TOT_MEDICION   =   $TOT_MEDICION + $REP[$i][3];
        $TOT_VENTA      =   $TOT_VENTA + $REP[$i][4];
        $TOT_INGRESO    =   $TOT_INGRESO + $REP[$i][5];
        $TOT_SALIDA     =   $TOT_SALIDA + $REP[$i][6];
        $TOT_PARTE      =   $TOT_PARTE + $REP[$i][7];
        $TOT_VARILLA    =   $TOT_VARILLA + $REP[$i][8];
        $TOT_DIARIA     =   $TOT_DIARIA + $REP[$i][9];
        $TOT_ACUMULADA  =   $TOT_ACUMULADA + $REP[$i][10];

        $color = ($i%2==0?"#E8F0EA":"#FFFFFF");

        if ($REP[$i][9] >= 100)
            $dailycolor = "#FF0000";
        else{
            $dailycolor = ($i%2==0?"#E8F0EA":"#FFFFFF");
        }

        echo "<tr>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][0]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][1]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][2]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][3]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][4]."</td>\n";//VENTA
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$PRECIO_VENTA."</td>\n";//PRECIO VENTA
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][5]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][6]."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][7]."</td>\n";
        echo "<td style=\"background-color: $dailycolor; color: #000000\"><div align=\"center\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".$REP[$i][8]."</td>\n";
        echo "<td style=\"background-color: $dailycolor; color: #000000\"><div align=\"right\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".number_format($REP[$i][9],3)."</td>\n";
        echo "<td style=\"background-color: $color; color: #000000\"><div align=\"right\"><font size=\"-4\" face=\"Arial, Helvetica, sans-serif\">".number_format($REP[$i][10],3)."</td>\n";
        echo "</tr>\n";

    }

?>

    <tr>
        <td colspan="2" align="right" style="font-size:0.7em; color:white; background-color: #369"><b>TOTALES: </b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_COMPRA;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_MEDICION;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_VENTA;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369">-</b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_INGRESO;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_SALIDA;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_PARTE;?></b></td>
        <td align="center" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo $TOT_VARILLA;?></b></td>
        <td align="right" style="font-size:0.7em; color:white; background-color: #369"><b><?php echo number_format($TOT_DIARIA,3);?></b></td>
        <td align="right" style="font-size:0.7em; color:white; background-color: #369"><b>&nbsp;</b></td>
    </tr>
</table>

    </tbody>
    </table>
    </div>
<?php
}
}
?>

