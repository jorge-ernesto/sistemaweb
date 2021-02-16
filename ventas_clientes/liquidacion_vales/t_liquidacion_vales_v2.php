<?php

class LiquidacionValesTemplate extends Template {

	function titulo(){
        $titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
        return $titulo;
	}

	function errorResultado($errormsg){
		return '<blink>' . $errormsg . '</blink>';
	}

	function FormularioPrincipal($selectexonerada){ ?>

	<div class='contenedorprincipal'>
		<div>
            <h3 style="color: #336699; text-align: center;">PROCESO DE LIQUIDACION DE VALES</h3>
        </div>
		<div class="separacion">
            <div class='fila'>
		<div class='etiquetavales' style="float: left;">Fecha Inicio</div>
            <div style="float: left;">
                <input type='text' id='fecha_inicio' class='fecha_formato'/>
            </div>
        </div>
        <div class='fila'>
            <div class='etiquetavales' style="float: left;">Fecha Final</div>
            <div style="float: left;">
                <input type='text' id='fecha_final' class='fecha_formato'/>
            </div>
        </div>
	</div>
    <div class="separacion">
        <div class='etiquetavales' style="float: left;">Tipo de Comprobante</div>
            <span style="font-size: 9px">Factura</span> <input type='radio' name='td' value='10' id="tdf"/>
            <span style="font-size: 9px">Boleta</span> <input type='radio' name='td' value='35' id="tdb"/>
        </div>

        <div class="separacion">
            <div class='etiquetavales' style="float: left;">Serie de Documento</div>
            <label id='serie'>---</label>
        </div>

        <div class="separacion">
            <div class='fila'>
                <div class='etiquetavales' style="float: left;">Tipo Operacion</div>
                <div style="float: left;">
                    <select id="cmbtipooperacion">
                        <option value="01">Cliente Normal</option>
                        <option value="02">Cliente Nota Despacho</option>
                        <option value="03">Cliente Placa</option>
                        <option value="04">Cliente Producto</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="separacion">
            <div class='fila'>
                <div class='etiquetavales' style="float: left;">Fecha Liquidacion </div>
                <div style="float: left;">
                    <input type='text' id='fecha_liqui' class='fecha_formato'/>
                </div>
            </div>
        </div>

        <?php
        $css_display_exonerado = 'display: none;';
        if ($selectexonerada==true)//Si es TRUE, mostramos el campo EXONERADO.
            $css_display_exonerado = '';
        ?>
        <div class="separacion">
            <div class='fila'>
                <!-- Verificar si la empresa esta exonerada -->
                <div style="<?php echo $css_display_exonerado; ?>; float: left;" class='etiquetavales'>Exonerado </div>
                <div style="<?php echo $css_display_exonerado; ?>; float: center;"><input type='checkbox' id='sin_igv' class='fecha_formato' /></div>
                <!-- /. Verificar si la empresa esta exonerada -->
                <div style="float: left;" class='etiquetavales'>Transferencia Gratuita </div>
                <div style="float: center;"><input type='checkbox' id='trans_gratis'  class='fecha_formato' /></div>
                <div style="float: left;" class='etiquetavales'>Considerar Negativos </div>
                <div style="float: center;"><input type='checkbox' id='cons_nega' class='fecha_formato'/></div>
            </div>
        </div>

        <div  class="separacion">
            <div class='fila'>
                <div class='etiquetavales' style="float: left;" title="Este valor solo será usado para el reporte de kardex SUNAT columna Liquidación">Documento Ref. (Solo para anticipos)</div>
                <div style="float: left;">
                    <input type='text' id='txt-documentoRef' placeholder="SERIE-NUMERO" title="formato: F001-00000001" alt="formato: F001-00000001" maxlength="30" class='fecha_formato'/> (opcional)
                </div>
                <div style="float: left;"></div>
            </div>
        </div>

		<div class="separacion">
            <div class='etiquetavales' style="float: left;margin-bottom: 5px;text-align: center;width: auto;">
                <button id="btnseleccionar">
                    <img align="right" src="/sistemaweb/images/search.png"/>Buscar Clientes
                </button>
            </div>
            <div class='etiquetavales' style="float: left;margin-bottom: 2px;margin-right: 2px;text-align: center;width: auto;">
                <button id="btnbuscarliquidacion">
                    <img align="right" src="/sistemaweb/images/search.png">Buscar Liquidaciones
                </button>
            </div>
            <div class='etiquetavales' style="float: left;margin-bottom: 5px;text-align: center;width: auto;">
                <button id="btnliquidar">
                    <img align="right" src="/sistemaweb/images/MasterDetail.gif">Liquidar vales
                </button>
            </div>
		</div>
    </div>

    <div class='contenedorprincipaltabla' id="contenidoTablaSelecionar"></div>
    <?php
    }

    function CrearTablaSeleccionarCliente($registros, $fecha_inicio, $fecha_final) {
        ?>
        <div style="float: left;width: auto;border: 1px;color:red;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe_dos_cabe">-</th>
                <th class="th_cabe">Codigo Cliente</th>
                <th class="th_cabe">Razon Social</th>
                <th class="th_cabe">Total vales</th>
                <th class="th_cabe" alt="ssss">Pendiente Anteriores  </th>
                <th class="th_cabe">Pendiente Posteriores </th>
                <th class="th_cabe">Seleccionar</th>
                </thead>
                <tbody id="registros">
                    <?php
                    $i = 0;
                    foreach ($registros as $llave => $value) {
                        $ch_cli = trim($value['ch_cliente']);
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        echo "<tr class='$estila'>";
                        echo "<td class='th_cabe_dos'><input id='id_" . trim($value['ch_cliente']) . "' type='checkbox' value='" . trim($value['ch_cliente']) . "' class='cliente_selecionado' onclick=GuardarValesClienteGlobal('" . $ch_cli . "',this) /></td>";
                        echo "<td>" . $value['ch_cliente'] . "</td>";
                        echo "<td>" . $value['rz'] . "</td>";
                        echo "<td class='td_tabla_selecinar' id='idmonto_" . trim($value['ch_cliente']) . "' montoantiguo='" . $value['totalliquidar'] . "' >" . number_format ($value['totalliquidar'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format ($value['faltaliquidaranterior'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format ($value['faltaliquidarposterio'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar' style='text-align: center;'><a style='text-decoration:underline;cursor:pointer;' identificador='" . trim($value['ch_cliente']) . "' onClick=verreportevales('" . trim($value['ch_cliente']) . "');><img src='/sistemaweb/images/gif_lupa2.gif' /></a></td>";
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
                
            </table>
        </div>
        <?php
    }

    function CrearTablaVervales($registros, $datos_cliente, $fecha_inicio, $fecha_final, $vales_sele) { ?>
		<input type="hidden" id="idcodigoCliente" value="<?php echo $datos_cliente['cli_codigo']; ?>"/>
        <input type="hidden" id="accionInterno" value="unicoCliente"/>
        <input type="hidden" id="fecha_inicio_tmp" value="<?php echo $fecha_inicio; ?>"/>
        <input type="hidden" id="fecha_final_tmp" value="<?php echo $fecha_final; ?>"/>

        <div style="float:center; width:auto; border: 1px;">
            <table align="center" cellspacing="2" cellpadding="2" border="0">
            	<tr>
	            	<td align="right"><p style="font-size:1.2em; color:black;"><b>Codigo Cliente: </th>
	            	<th align="left"><?php echo $datos_cliente['cli_codigo']; ?></th>
	            </tr>
				<tr>
	            	<th align="right"><p style="font-size:1.2em; color:black;"><b>RUC: </th>
	            	<th align="left"><?php echo $datos_cliente['cli_ruc']; ?></th>
	            <tr>
	            	<th align="rigth"><p style="font-size:1.2em; color:black;"><b>Razon Social: </th>
	            	<th align="left"><?php echo $datos_cliente['cli_razsocial']; ?></th>
	            <tr>
	            	<th align="right"><p style="font-size:1.2em; color:black;"><b>Anticipo: </th>
	            	<th align="left"><?php echo $datos_cliente['cli_anticipo']; ?></th>
	            <tr>
	            	<th align="right"><p style="font-size:1.2em; color:black;"><b>Ordenar Chofer: </th>
	            	<th align="left"><input type="checkbox" id="nochofer" name="nochofer" class="chofer" onclick="ordenar('chofer', this)" value="S"></th>
	            </tr>
                <tr>
	            	<th align="right"><p style="font-size:1.2em; color:black;"><b>Ordenar por Precio Unitario: </th>
	            	<th align="left"><input type="checkbox" id="preunit" name="preunit" class="preunit" onclick="ordenar('precioUnitario', this)" value="S"></th>
	            </tr>
            </table>
            <br/>
        <div style="float:center; width:auto; border: 1px;">
            <table align="center" cellspacing="0" cellpadding="0" border="0">	
                <th class="th_cabe">Almacen</th>
                <th class="th_cabe">Fecha Emision</th>
                <th class="th_cabe">Fecha Registro</th>
                <th class="th_cabe"># Vale</th>
                <th class="th_cabe">Chofer</th>
                <th class="th_cabe">Placa</th>
                <th class="th_cabe">Producto</th>
                <th class="th_cabe">Cantidad</th>
                <th class="th_cabe">Precio</th>
                <th class="th_cabe">Importe</th>
                <th class="th_cabe"><input type="checkbox"  onclick="marcar(this)" /></th>
                <th class="th_cabe"><button  onclick="GuardarValesCliente('<?php echo trim($datos_cliente['cli_codigo']); ?>')"  style=""><img align="center" src="/sistemaweb/images/Recordset.gif" />Guardar</button></th>
                </thead>
                <tbody>
                    <?php

                    $suma_cantidad_total_simpre     = 0.00;
                    $suma_importe_total_simpre 		= 0.00;
                    $ckecked 						= "";
                    $verificar 						= false;
                    $verificar_all 					= false;
                    $cantidad 						= 0.000;
                    $importe 						= 0.00;
                    $vales_sele_veri 				= array();
                    $i 								= 0;

                    if ($vales_sele == "NOALL") {
                        $ckecked = "";
                    } else if ($vales_sele == "ALL") {
                        $ckecked = "checked";
                        $verificar_all = true;
                    } else {
                        $vales_sele = substr($vales_sele, 0, -1);
                        $vales_sele_veri = explode(",", $vales_sele);
                        $verificar = true;
                    }


                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        if ($verificar == true) {
                            $ch_documento_veri = trim($value['ch_documento']);
                            if (in_array($ch_documento_veri, $vales_sele_veri)) {
                                $ckecked = 'checked';
                                $cantidad   += $value['nu_cantidad'];
                                $importe    += $value['nu_importe'];
                            } else {
                                $ckecked = null;
                            }
                        }
                        if ($verificar_all == TRUE) {
                            $cantidad   += $value['nu_cantidad'];
                            $importe    += $value['nu_importe'];
                        }

                        $nu_cantidad = $value['nu_cantidad'];
                        $nu_importe = $value['nu_importe'];


                        $suma_cantidad_total_simpre += $nu_cantidad;
                        $suma_importe_total_simpre  += $nu_importe;

                       	$nochofer = trim($value['nochofer']);

                        echo "<tr class='$estila'>";
                        echo "<td align='center'>" . $value['ch_sucursal'] . "</td>";
                        echo "<td align='center'>" . $value['dt_fecha'] . "</td>";
                        echo "<td align='center'>" . $value['fecha_replicacion'] . "</td>";
                        echo "<td align='center'>" . $value['ch_documento'] . "</td>";
                        echo "<td align='center'>" . $nochofer . "</td>";
                        echo "<td align='center'>" . $value['ch_placa'] . "</td>";
                        echo "<td align='left'>" . $value['art_codigo'] . " - " . $value['desproducto'] . "</td>";
                        echo "<td class='td_tabla_selecinar' align='right'>" . number_format($value['nu_cantidad'], 3, '.', ',') . "</td>";
                        echo "<td class='td_tabla_selecinar' align='right'>" . number_format($value['art_precio'], 2, '.', ',') . "</td>";
                        echo "<td class='td_tabla_selecinar' align='right'>" . number_format($value['nu_importe'], 2, '.', ',') . "</td>";
                        echo "<td class='td_tabla_selecinar' style='text-align:center'><input type = 'checkbox' $ckecked id='".$value['ch_documento']."' class = 'idselecAll ".$nochofer."' cantidad = '" . $nu_cantidad . "' importe = '" . $nu_importe . "' onclick='marcarHermanos(this, \"" .$nochofer."\")' value='" . trim($value['ch_documento']) . "'/></td>";
                        echo "<td class='td_tabla_selecinar' style='background-color:white'></td>";
                        echo "</tr>";

                        $i++;

                    }

                ?>
                </tbody>
                <tfoot>
                    <th class="th_cabe" colspan="7" align="right">Total: </th>
                    <th class="th_cabe" id="lba_total_cantidad" align="right"><?php echo number_format($cantidad, 3, '.', ','); ?> </th>
                        <input type="hidden" value="<?php echo $cantidad; ?>" id="total_cantidad"/>
                    <th class="th_cabe" id="lba_total_importe" align="right"><?php echo number_format($importe, 2, '.', ','); ?></th>
                        <input type="hidden" value="<?php echo $importe; ?>" id="total_importe"/>
                    <th class="th_cabe" align="center">
                        <input type="checkbox" id='gg' value="fff" onclick="marcar(this)"/></th>
                        <input type="hidden" value="<?php echo $suma_cantidad_total_simpre; ?>" id="total_cantidad_siempre"/>
                        <input type="hidden" value="<?php echo $suma_importe_total_simpre; ?>" id="total_importe_siempre"/>
                </tfoot>
            </table>
            <br/>
            <br/>
        </div>

        <?php
    }

    function CrearTabladatosLiquidacion($registros, $ArrayMontos, $fecha_liqui) {
        ?>

        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe">Nº Liquidacion</th>
                <th class="th_cabe">Tipo </th>
                <th class="th_cabe">Documento </th>
                <th class="th_cabe">Fecha </th>
                <th class="th_cabe">Cliente </th>
                <th class="th_cabe">Valor venta </th>
                <th class="th_cabe">Impuesto</th>
                <th class="th_cabe">Total</th>
                <th class="th_cabe_images">-</th>
                

                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        $doc                = $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        $tipo_doc           = $value['tipo_doc'];
                        $numero_docdumento  = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);

                        echo "<tr class='$estila'>";
                        echo "<td>" . $value['num_liquidacion'] . "</td>";
                        echo "<td>" . $value['tipo'] . "</td>";
                        echo "<td>" . $value['serie'] . "-" . $numero_docdumento . "</td>";
                        echo "<td>" . $fecha_liqui . "</td>";
                        echo "<td>" . $value['cli']."-". $value['rz']. "</td>";
                        echo "<td class='td_tabla_selecinar' >" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valorbruto'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_impuesto1'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valortotal'],2) . "</td>";
                        echo "<td style='text-align: right;padding:0px;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=normal&parametro_accion=10101&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank' style='text-align:center'><img src='/sistemaweb/images/icono_pdf.gif' /> </a>";
                        $_cod_cliente = trim($value['cli']);
                        //echo "<td style='text-align: right;padding:0px;'><a href='excel_liquidacion_vales_personalizados.php?forma=normal&parametro_accion=10101&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank' style='text-align:center'><img src='/sistemaweb/icons/gexcel.png' /> </a>";
                        //echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        if(preg_match("/^[a-zA-Z][0-9]{3}+$/", trim($value['serie'])) == 1) {
                            //echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(0,\''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\',\''.$_cod_cliente.'\',0)"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';//kwn
                            //\''.trim($ArrayMontos[$value['num_liquidacion']]['nu_almacen']).'\'
                            echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(\''.trim($value['nu_almacen_factura']).'\', \''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\', \''.trim($ArrayMontos[$value['num_liquidacion']]['fe_emision']).'\', \''.trim($value['num_liquidacion']).'\', \''.trim($_cod_cliente).'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        }else{
                            echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';    
                        }
                        echo "</tr>";

                        $i++;
                    }
                    ?>
                </tbody>
               
            </table>
            <div style="margin: 10px"></div>
        </div>
        <?php
    }

    function CrearTabladatosLiquidacionND($registros, $ArrayMontos, $fecha_liqui) {
        ?>

        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe">Nº Liquidacion</th>
                <th class="th_cabe">Tipo </th>
                <th class="th_cabe">Documento </th>
                <th class="th_cabe">N.Desapcho </th>
                <th class="th_cabe">Fecha </th>
                <th class="th_cabe">Cliente </th>
                <th class="th_cabe">Valor venta </th>
                <th class="th_cabe">Impuesto</th>
                <th class="th_cabe">Total</th>
                <th class="th_cabe_images">-</th>
                

                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        $doc                = $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        $tipo_doc           = $value['tipo_doc'];
                        $numero_docdumento  = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        echo "<tr class='$estila'>";
                        echo "<td>" . $value['num_liquidacion'] . "</td>";
                        echo "<td>" . $value['tipo'] . "</td>";
                        echo "<td>" . $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT) . "</td>";
                        echo "<td>dddd" . $value['ND'] . "</td>";
                        echo "<td>" . $fecha_liqui . "</td>";
                        echo "<td>" . $value['cli']."-". $value['rz'] . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valorbruto'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_impuesto1'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valortotal'],2) . "</td>";
                        echo "<td style='text-align: right;padding:0px;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=ND&parametro_accion=" . $value['ND'] . "&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank'><img src='/sistemaweb/images/icono_pdf.gif' /> </a>";
                        //echo "<td style='text-align: right;padding:0px;'><a href='excel_liquidacion_vales_personalizados.php?forma=normal&parametro_accion=10101&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank' style='text-align:center'><img src='/sistemaweb/icons/gexcel.png' /> </a>";
                        //echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        $_cod_cliente = trim($value['cli']);
                        if(preg_match("/^[a-zA-Z][0-9]{3}+$/", trim($value['serie'])) == 1) {
                            //echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(0,\''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\',0)"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';//kwn
                            echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(\''.trim($value['nu_almacen_factura']).'\', \''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\', \''.trim($ArrayMontos[$value['num_liquidacion']]['fe_emision']).'\', \''.trim($value['num_liquidacion']).'\', \''.trim($_cod_cliente).'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        }else{
                            echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';    
                        }
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
                
            </table>
        </div>
        <?php
    }

    function CrearTabladatosLiquidacionPlaca($registros, $ArrayMontos, $fecha_liqui) {
        ?>

        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe">Nº Liquidacion</th>
                <th class="th_cabe">Tipo </th>
                <th class="th_cabe">Documento </th>
                <th class="th_cabe">N.Placa </th>
                <th class="th_cabe">Fecha </th>
                <th class="th_cabe">Cliente </th>
                <th class="th_cabe">Valor venta </th>
                <th class="th_cabe">Impuesto</th>
                <th class="th_cabe">Total</th>
                <th class="th_cabe_images">-</th>
                

                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        $doc                = $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        $tipo_doc           = $value['tipo_doc'];
                        $numero_docdumento  = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        echo "<tr class='$estila'>";
                        echo "<td>" . $value['num_liquidacion'] . "</td>";
                        echo "<td>" . $value['tipo'] . "</td>";
                        echo "<td>" . $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT) . "</td>";
                        echo "<td>" . $value['placa'] . "</td>";
                        echo "<td>" . $fecha_liqui . "</td>";
                        echo "<td>" . $value['cli']."-". $value['rz'] . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valorbruto'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_impuesto1'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valortotal'],2) . "</td>";
                        echo "<td style='text-align: right;padding:0px;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=PLACA&parametro_accion=" . $value['placa'] . "&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank'><img src='/sistemaweb/images/icono_pdf.gif' /> </a>";
                        //echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        $_cod_cliente = trim($value['cli']);
                        if(preg_match("/^[a-zA-Z][0-9]{3}+$/", trim($value['serie'])) == 1) {
                            //echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(0,\''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\',0)"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';//kwn
                            echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(\''.trim($value['nu_almacen_factura']).'\', \''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\', \''.trim($ArrayMontos[$value['num_liquidacion']]['fe_emision']).'\', \''.trim($value['num_liquidacion']).'\', \''.trim($_cod_cliente).'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        }else{
                            echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';    
                        }
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
                
            </table>
        </div>
        <?php
    }

    function CrearTabladatosLiquidacionProducto($registros, $ArrayMontos, $fecha_liqui) {
        ?>

        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe">Nº Liquidacion</th>
                <th class="th_cabe">Tipo </th>
                <th class="th_cabe">Documento </th>
                <th class="th_cabe">Producto </th>
                <th class="th_cabe">Fecha </th>
                <th class="th_cabe">Cliente </th>
                <th class="th_cabe">Valor venta </th>
                <th class="th_cabe">Impuesto</th>
                <th class="th_cabe">Total</th>
                <th class="th_cabe_images">-</th>
                

                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";
                        if ($i % 2 == 0) {
                            $estila = "fila_registro_par";
                        }
                        $doc                = $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        $tipo_doc           = $value['tipo_doc'];
                        $numero_docdumento  = str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT);
                        echo "<tr class='$estila'>";
                        echo "<td>" . $value['num_liquidacion'] . "</td>";
                        echo "<td>" . $value['tipo'] . "</td>";
                        echo "<td>" . $value['serie'] . "-" . str_pad($value['num_doc'], 7, '0', STR_PAD_LEFT) . "</td>";
                        echo "<td>" . $value['producto'] . "</td>";
                        echo "<td>" . $fecha_liqui . "</td>";
                        echo "<td>" . $value['cli']."-". $value['rz'] . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valorbruto'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_impuesto1'],2) . "</td>";
                        echo "<td class='td_tabla_selecinar'>" . number_format($ArrayMontos[$value['num_liquidacion']]['nu_fac_valortotal'],2) . "</td>";
                        echo "<td style='text-align: right;padding:0px;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=PRODUCTO&parametro_accion=" . $value['producto'] . "&ch_liquidacion=" . $value['num_liquidacion'] . "&ch_cliente=" . $value['cli'] . "&ch_documneto=" . $doc . "' target='_blank'><img src='/sistemaweb/images/icono_pdf.gif' /> </a>";
                        //echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        $_cod_cliente = trim($value['cli']);
                        if(preg_match("/^[a-zA-Z][0-9]{3}+$/", trim($value['serie'])) == 1) {
                            //echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(0,\''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\',0)"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';//kwn
                            echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(\''.trim($value['nu_almacen_factura']).'\', \''.trim($tipo_doc).'\',\''.trim($value['serie']).'\',\''.trim($numero_docdumento).'\', \''.trim($ArrayMontos[$value['num_liquidacion']]['fe_emision']).'\', \''.trim($value['num_liquidacion']).'\', \''.trim($_cod_cliente).'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                        }else{
                            echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).','.trim($value['serie']).',' . trim($numero_docdumento) . ', '.trim($value['cli']).','.$i.')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';    
                        }
                        echo "</tr>";
                        $i++;
                    }
                    ?>
                </tbody>
                
            </table>
        </div>
        <?php
    }

    
    
    function CrearTabladatosLiquidacionCuentasPorCobrar($registros, $cod_cli) {
        ?>

        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="0" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe">Nº Liquidacion</th>
                <th class="th_cabe">Tipo </th>
                <th class="th_cabe">Documento </th>
                <th class="th_cabe">Fecha </th>
                <th class="th_cabe">Cliente </th>
                <th class="th_cabe">Monto Total </th>
                <th class="th_cabe_images">-</th>


                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    foreach ($registros as $ksur => $Asursal) {
                        foreach ($Asursal as $kliqui => $value) {
                            $estila = "fila_registro_imppar";
                            if ($i % 2 == 0) {
                                $estila = "fila_registro_par";
                            }
                            $doc = $value['num_serie'] . "-" . str_pad($value['num_docu'], 7, '0', STR_PAD_LEFT);
                            $num_liqui = str_pad($value['num_liquidacion'], 10, '0', STR_PAD_LEFT);
                            echo "<tr class='$estila'>";
                            echo "<td>" . $num_liqui . "</td>";
                            echo "<td>CUENTAS POR COBRAR</td>";
                            echo "<td>" . $value['num_serie'] . "-" . str_pad($value['num_docu'], 7, '0', STR_PAD_LEFT) . "</td>";
                            echo "<td>" . $value['fecha_liqui'] . "</td>";
                            echo "<td>" . $value['cod_cli']."-". $value['rz'] . "</td>";
                            echo "<td class='td_tabla_selecinar'>" . number_format($value['totalimporte'],2) . "</td>";
                            echo "<td style='text-align: right;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=XCOBRAR&parametro_accion=-&ch_liquidacion=" . $num_liqui . "&ch_cliente=" . $value['cod_cli'] . "&ch_documneto=" . $doc . "'  target='_blank'><img src='/sistemaweb/images/icono_pdf.gif' /> </a></td>";

                            echo "</tr>";
                            $i++;
                        }
                    }
                    ?>
                </tbody>
                
            </table>
        </div>
        <?php
    }

    function CrearTabladatosLiquidacionProducto_Busqueda($registros,$taxOptional) {
        ?>
        <div style="float: left;width: auto;border: 1px;">
            <table cellspacing="1" cellpadding="0" border="0">	
                <thead>
                <th class="th_cabe" align="center">Nº Liquidacion</th>
                <th class="th_cabe" align="center">Documento</th>
                <th class="th_cabe" align="center">Tipo Operacion</th>
                <th class="th_cabe" align="center">Opcion</th>
                <th class="th_cabe" align="center">Cliente</th>
                <th class="th_cabe" align="center">Importe</th>
                <th class="th_cabe_images" align="center">Exportar</th>
                

                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $_id = 0;
                    foreach ($registros as $llave => $value) {
                        $estila = "fila_registro_imppar";

                        if ($i % 2 == 0)
                            $estila = "fila_registro_par";

                        $doc                = trim($value['ch_fac_seriedocumento']) . "-" . str_pad($value['ch_fac_numerodocumento'], 7, '0', STR_PAD_LEFT);
                        $tipo_doc 		    = $value['ch_fac_tipodocumento'];
                        $numero_docdumento 	= str_pad($value['ch_fac_numerodocumento'], 7, '0', STR_PAD_LEFT);

            			if($value['igv'] > 0)
            				$selected = "";
            			else
            				$selected = "selected";

                        echo "<tr class='$estila'>";

                        echo "<td align='center'>" . $value['ch_liquidacion'] . "</td>";

                        if ( $value['operacion'] != "POR-COBRAR" ) {
                            echo "<td align='left'>" . $value['desc_tipodocumento'] . " - " . $value['ch_fac_seriedocumento'] . " - " . $numero_docdumento . "</td>";
                        } else {
                            echo "<td align='left'>FACTURA - " . $value['no_documento_referencia'] . "</td>";
                        }

                        echo "<td align='center'>" . $value['opcion'] . "</td>";
                        echo "<td align='center'>" . $value['operacion'] . "</td>";
                        echo "<td align='center'>" . $value['nombre'] . "</td>";

                        echo "<td align='rigth' class='td_tabla_selecinar'>" . number_format($value['total'],2) . "</td>";
                        
                        echo "<td style='text-align: center; width: 80px;'><a href='generar_liquidacion_vales_pdf_personalizado.php?forma=" . trim($value['opcion']) . "&parametro_accion=" . trim($value['operacion']) . "&ch_liquidacion=" . trim($value['ch_liquidacion']) . "&ch_cliente=" . trim($value['ch_cliente']) . "&ch_documneto=" . $doc . "' target='_blank'><img src='/sistemaweb/images/icono_pdf.gif' /> </a>";
                        echo "<a href='excel_liquidacion_vales_personalizados.php?forma=" . trim($value['opcion']) . "&parametro_accion=" . trim($value['operacion']) . "&ch_liquidacion=" . trim($value['ch_liquidacion']) . "&ch_cliente=" . trim($value['ch_cliente']) . "&no_tipo_documento=" . trim($value['desc_tipodocumento']) . "&nu_tipo_documento=" . trim($value['ch_fac_tipodocumento']). "&nu_serie_documento=" . trim($value['ch_fac_seriedocumento']). "&nu_numero_documento=" . trim($value['ch_fac_numerodocumento']). "' href='#' style='text-align:center'><img src='/sistemaweb/icons/gexcel.png' /> </a>";

                        if ( $value['operacion'] != "POR-COBRAR" ) {
                            if(preg_match("/^[a-zA-Z][0-9]{3}+$/", trim($value['ch_fac_seriedocumento'])) == 1) {
                                echo '<a style="cursor: pointer;" data-documento="'.trim($numero_docdumento).'" onclick="generarDocumentoLV(\''.trim($value['nu_almacen_factura']).'\', \''.trim($tipo_doc).'\',\''.trim($value['ch_fac_seriedocumento']).'\',\''.trim($value['ch_fac_numerodocumento']).'\', \''.trim($value['fe_emision']).'\', \''.trim($value['ch_liquidacion']).'\', \''.trim($value['ch_cliente']).'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';//kwn
                            }else{
                                echo '<a href="javascript:void(0)" onClick="GenerarFactura('.trim($tipo_doc).',\''.trim($value['ch_fac_seriedocumento']).'\',\'' . trim($numero_docdumento) . '\', \''.trim($value['ch_cliente']).'\',\''.$i.'\')"><img src="/sistemaweb/images/icono_imprimir.gif"/></a>';
                            }
                        }

                        echo "</td>";
                        echo "</tr>";

                        $_id++;
                        $i++;

                    }

                    ?>
                </tbody>
                
               
            </table>
        </div>
        <?php
    }

}
