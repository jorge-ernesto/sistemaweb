<?php
class CanjesTemplate extends Template
{
    function titulo()
    {
	return "<h2><div align='center'>Canje de Promociones</div></h2>";
    }
    
    function formBuscar(){
	$result = '';
	$result .= '<form name="Buscar" method="post" target="control" action="control.php">';
	$result .= '<input type="hidden" name="rqst" value="PROMOCIONES.CANJES" />';
	$result .= '<p align="center">Tarjeta:';
	$result .= '<input type="text" name="busquedatarjeta" size="22" maxlength="22" />
	<img src="../images/help.gif" style="cursor:hand" width="16" height="15" onClick="javascript:mostrarAyuda('."'../combustibles/util/lista_ayuda_tarjeta.php', 'Buscar.busquedatarjeta','Buscar.itemdescripcion','tarjetas'".')"> Necesita ayuda?
	</p>';
	$result .= '<p align="center">';
	$result .= '<input type="submit" name="action" value="Buscar" /></p>';
	$result .= '</form>';
	return $result;
    }
    
    
    function mostrarDatos($tarjeta)
    {
	if ($tarjeta['es_titular']=='S') {
	    $articulos = CamjesModel::obtenerArticulosCanje($tarjeta['numero_tarjeta']);
	}
	
	$result = '<p align="center">';
	$result .= '<table border="1">';
	$result .= '<tr>';
	$result .= '<td colspan="2">Datos de Cuenta</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td colspan="2">Datos de Tarjeta</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Nro. Cuenta</td>';
	$result .= '<td>:' . htmlentities($tarjeta['numero_cuenta']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Nro. Tarjeta</td>';
	$result .= '<td>:' . htmlentities($tarjeta['numero_tarjeta']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Titular</td>';
	$result .= '<td>:' . htmlentities($tarjeta['nombre_cuenta']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Nombre</td>';
	$result .= '<td>:' . htmlentities($tarjeta['nombre_tarjeta']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>DNI: ' . htmlentities($tarjeta['dni_cuenta']) . '</td>';
	$result .= '<td>RUC: ' . htmlentities($tarjeta['ruc_cuenta']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>Placa</td>';
	$result .= '<td>:' . htmlentities($tarjeta['placa_tarjeta']) . '</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Direccion</td>';
	$result .= '<td>:' . htmlentities($tarjeta['direccion_cuenta']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td>Telefono</td>';
	$result .= '<td>:' . htmlentities($tarjeta['telefono_cuenta']) . '</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '<td>&nbsp;</td>';
	$result .= '</tr>';
	$result .= '<tr>';
	$result .= '<td colspan="5"><p align="center"><b>PUNTOS: ' . htmlentities($tarjeta['puntos_cuenta']) . '</b></p></td>';
	$result .= '</tr>';

	if ($tarjeta['es_titular']=='1') {
	    $articulos = CanjesModel::obtenerArticulosCanje($tarjeta['numero_tarjeta']);
	    if (count($articulos) > 0) {
    		$result .= '<tr>';
	        $result .= '<td colspan="2"><p align="right">Productos para canje:</p></td>';
	        $result .= '<td>&nbsp;</td>';
	        $result .= '<td colspan="2">';
	        $result .= '<form name="canje" target="control" method="post" action="control.php">';
	        $result .= '<input type="hidden" name="rqst" value="PROMOCIONES.CANJES"/>';
	        $result .= '<input type="hidden" name="tarjeta" value="' . htmlentities($tarjeta['numero_tarjeta']) . '"/>';
	        $result .= '<select name="articulo" size="1">';
	        for ($i = 0; $i < count($articulos); $i++) {
	    	    $result .= '<option value="' . htmlentities($articulos[$i]['id_item']) . '">' . htmlentities($articulos[$i]['ch_item_descripcion'].' - '.$articulos[$i]['nu_item_puntos']) . ' puntos</option>';
			}
	        $result .= '</select>';
	        $result .= '<input type="submit" name="action" value="Canjear"/><br/>';
		$result .= '<b>Observaciones:</b><br/>';
		$result .= '<textarea name="observaciones" rows="5" cols="25"></textarea>';
	        $result .= '</form>';
	        $result .= '</td>';
	        $result .= '</tr>';
	    }
	    else
	    {
		$result .= '<tr>';
		$result .= '<td colspan="5"><p align="center"><b>No tiene puntos suficientes para realizar ningun canje</b></p></td>';
		$result .= '</tr>';
	    }
	}
	else {
	    $result .= '<tr>';
	    $result .= '<td colspan="5"><p align="center"><b>DEBE SER EL TITULAR PARA CANJEAR</b></p></td>';
	    $result .= '</tr>';
	}

	$result .= '</table>';
	$result .= '</p>';
	return $result;
    }
    
    function mostrarError()
    {
	$result = '<p align="center"><b>Error procesando. Por favor verifique los datos.</b></p>';
	return $result;
    }
    
    function mostrarErrorBusqueda()
    {
	$result = '<p align="center"><b>No hay resultados.</b></p>';
	return $result;
    }

    function mostrarExito()
    {
	$result = '<p align="center"><b>Canje exitoso.</b></p>';
	return $result;
	}
	
	function mostrarExitoSinImpresion()
    {
	$result = '<p align="center"><b>Canje exitoso sin impresion de ticket.</b></p>';
	return $result;
    }
}

