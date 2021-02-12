
<?php
echo "<pre>";
print_r($this->reporte);
echo "</pre>";
?>
<center><h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;align=center;"><b>Ventas con Tarjetas de Cr&eacute;dito<b></center></h2>
                <form method="post" action="control.php" target="control">
                    <input type="hidden" id="rqst" name="rqst" value="REPORTES.REPORTETURNO">
                    <table align="center" class="normal">
                        <tr>
                            <td align="center">	
                                <select name="ch_almacen">
                                    <?php
                                    for ($i = 0; $i < count($this->estaciones); $i++) {

                                        echo "<option value='" . $this->estaciones[$i]["ch_almacen"] . "'>" . $this->estaciones[$i]["btrim"] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        <tr><td align="center">Periodo<input type="input" name="periodo" value="<?php echo date("Y"); ?>" size="4"></td></tr>
                        <tr><td align="center">Mes<input type="input" name="mes" value="<?php echo date("m"); ?>" size="2"></td></tr>
                        <tr><td align="center">Desde<input type="input" name="desde" value="<?php echo date("d"); ?>"  size="2">
                                Hasta<input type="input" name="hasta" value="<?php echo date("d"); ?>"  size="2"></td></tr>
                        <tr><td align="center"><input type="submit" value="Reporte"></td></tr>
                        </tr>
                    </table>
                </form>
                <div id="content_footer">

                    <button onclick="javascript:parent.location.href='control.php?rqst=REPORTES.VENTASDIARIAS&amp;action=pdf&amp;desde=16%2F03%2F2014&amp;hasta=16%2F03%2F2014&amp;modo=RESUMIDO&amp;estacion=TODAS';return false" value="" name="fm">

                        <img alt="left" src="/sistemaweb/images/icono_pdf.gif"> PDF</button>
                    <table border="1">
                        <tbody>
                            <?php
                            $array_data = array("11620301", "11620302", "11620303", "11620304", "11620305", "11620307", "T", "M");
                            $array_view_row = array();
                            $int_con = 0;
                            $condicion = true;
                            $turno = false;
                            $fecha = false;
                            echo count($this->reporte);
                            for ($i = 0; $i < count($this->reporte); $i++) {

                                if ($fecha != $this->reporte[$i]["dia"]) {
                                    ?>
                                    <tr>
                                        <td>Dia <?php echo date("Y-m-d", strtotime($this->reporte[$i]["dia"])); ?></td>
                                        <td align="center" colspan="2">84</td>
                                        <td align="center" colspan="2">90</td>
                                        <td align="center" colspan="2">95</td>
                                        <td align="center" colspan="2">97</td>
                                        <td align="center" colspan="2">D2</td>
                                        <td align="center" colspan="2">GLP</td>
                                        <td align="center" colspan="2">Total Combustible</td>

                                        <td align="center">Market</td>

                                    </tr>
                                    <?php
                                    $fecha = $this->reporte[$i]["dia"];
                                }


                                if ($turno != $this->reporte[$i]["turno"]) {
                                    ?>
                                    <tr>
                                        <td>Turno <?php
                            echo $this->reporte[$i]["turno"];


                            $siguiente_fila = NULL;
                            $imprimir = true;
                                    ?></td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Galones</td>
                                        <td>Importe</td>
                                        <td>Cantidad</td>
                                        <td>Importe</td>

                                    </tr>
                                    <tr>
                                        <td> </td>
                                        <?php
                                        $turno = $this->reporte[$i]["turno"];
                                    }

                                    if ($siguiente_fila == NULL || ($siguiente_fila == $this->reporte[$i]["dia"] . "" . $this->reporte[$i]["turno"])) {
                                        $siguiente_fila = $this->reporte[$i]["dia"] . "" . $this->reporte[$i]["turno"];
                                        $codigo = ($this->reporte[$i]["tipo"] == 'M') ? 'M' : trim($this->reporte[$i]["codigo"]);
                                        $array_view_row[$codigo]['C'] = $this->reporte[$i]["cantidad"];
                                        $array_view_row[$codigo]['I'] = $this->reporte[$i]["importe"];
                                        $array_view_row['T']['C'] += ($codigo!="M")?$this->reporte[$i]["cantidad"]:"0";
                                        $array_view_row['T']['I'] += ($codigo!="M")?$this->reporte[$i]["importe"]:"0" ;
                                    }
                                    $impre_tmp = false;
                                    $siguiente_fila_im = $this->reporte[$i]["dia"] . "" . $this->reporte[$i]["turno"];
                                    $siguiente_fila_im_dos = $this->reporte[$i + 1]["dia"] . "" . $this->reporte[$i + 1]["turno"];
                                    if ($siguiente_fila_im != $siguiente_fila_im_dos) {
                                        $impre_tmp = true;
                                    }
                                    if ($impre_tmp) {
                                        foreach ($array_data as $fila => $valor) {
                                            ?>
                                            <td align="right"><?php echo (empty($array_view_row[$valor]["C"])) ? "0.0" : $array_view_row[$valor]["C"]; ?></td>
                                            <td align="right"><?php echo (empty($array_view_row[$valor]["I"])) ? "0.0" : $array_view_row[$valor]["I"]; ?></td>
                                            <?php
                                        }
                                        echo "</tr>";
                                        $array_view_row = array();
                                        $imprimir = false;
                                    }
                                    ?>
                                    <?php
                                    if ($turno != $this->reporte[$i]["turno"]) {
                                        echo '<td align="right">xxxx</td>' . "</tr>";
                                    }
                                }
                                ?>
                        </tbody>
                    </table>
                </div>
