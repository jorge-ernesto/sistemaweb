<?php

class cambio_precioTemplate extends Template {

    function titulo() {
        $titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
        return $titulo;
    }

    function errorResultado($errormsg) {
        return '<blink>' . $errormsg . '</blink>';
    }

    function FormularioPrincipal() {
        ?>

        <div style="text-align: center;position: absolute;width: 92%;">
            <div style="color: #336699; width: 400px;margin: 20px auto;"><h3 >Reporte de Cambios de Precios.</h3></div>
            <div style="margin: 20px auto;position: relative; text-align: center;width: 215px;">
                <table>
                    <tr>
                        <td  >Fecha Inicio </td>
                        <td ><input type='text' id='fecha_inicio' class='fecha_formato'/></td>

                    </tr>
                    <tr>
                        <td  >Fecha Final </td>
                        <td ><input type='text' id='fecha_final' class='fecha_formato'/></td>
                    </tr>
                    <tr>
                        <td  ><button id="btnseleccionar"><img align="right" src="/sistemaweb/images/search.png"/>Buscar</button></td>
                        <td  ><button ><img align="right" src="/sistemaweb/images/search.png">Cancelar</button></td>
                    </tr>
                </table>
            </div>
            <div  id="contenidoTablaSelecionar"  style="margin: 20px auto;position: relative;width: 400px;">

            </div>

        </div>




        <?php
    }

    function CrearTablaCambio_Precio($arrResponse, $lista, $cantidad) {?>
        <table cellspacing="3" cellpadding="3" border="0" align="center">	
            <thead>
                <tr>
                    <td class="th_cabe">F. Sistema</td>
                    <td class="th_cabe">F. Cambio</td>
                    <td class="th_cabe">Código</td>
                    <td class="th_cabe">Nombre</td>
                    <td class="th_cabe">Precio</td>
                </tr>
            </thead>
            <tbody id="registros">
            	<?php
                if ( $arrResponse['sStatus'] == 'success' ) {
                	$counter = 0;
                    foreach ($arrResponse['arrData'] as $rows) {
                    	$rows = (object)$rows;	                    	
                        $estila = "fila_registro_imppar";
                        if ($counter%2 == 0) {
                            $estila = "fila_registro_par";
                        }
                    	?>
	                    <tr class='<?php echo $estila; ?>'>
	                        <th><?php echo $rows->fe_sistema; ?></th>
	                        <th><?php echo $rows->fe_cambio; ?></th>
	                        <th><?php echo $rows->nu_codigo_item; ?></th>
	                        <th><?php echo $rows->no_nombre_item; ?></th>
	                        <th><?php echo $rows->ss_nuevo_precio; ?></th>
	                    </tr>
                    <?php
                    	++$counter;
                	}
                } else {
				?>
	                    <tr>
	                        <th colspan="5"><?php echo $arrResponse['sMessage']; ?></th>
	                    </tr>
	                <?php
	            }
                /*
                $i = 0;
                $fecha_actual = $registros[0]['hora_text'];
                
                $cont = 0;
                $salida_html = "";
                $cantidad_producto=$cantidad[0]+1;
                foreach ($registros as $llave => $value) {
                    if ($fecha_actual == $value['hora_text']) {

                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }

                        $salida_html.= "<tr class='$estila'>";
                        //$ch_cli = trim($value['ch_cliente']);
                        $salida_html.= "<td>" . $value['hora_text'] . "</td>";
                        $salida_html.= "<td>" . $value['hora_detalle'] . "</td>";

                        $salida_html.= "<td>";
                        $salida_html.= $array_prod[$value['product']];
                        $salida_html.= "</td>";

                        $salida_html.= "<td>" . $value['trapre'] . "-" . $cont . "</td>";

                        $salida_html.= "</tr>";
                        $i++;
                        $cont++;
                        echo $salida_html;
                    } else {
                        if ($cont >= $cantidad_producto) {
                            echo $salida_html;
                           
                        }
                        $salida_html = "";
                        $cont = 1;

                        $salida_html.= "<tr class='$estila'>";
                        //$ch_cli = trim($value['ch_cliente']);
                        $salida_html.= "<td>" . $value['hora_text'] . "</td>";
                        $salida_html.= "<td>" . $value['hora_detalle'] . "</td>";

                        $salida_html.= "<td>";
                        $salida_html.= $array_prod[$value['product']];
                        $salida_html.= "</td>";

                        $salida_html.= "<td>" . $value['trapre'] . "</td>";

                        $salida_html.= "</tr>";
                        $i++;
                    }
                    $fecha_actual = $value['hora_text'];
                }
                */
                ?>
            </tbody>
        </table>
 	<?php
    }
}
