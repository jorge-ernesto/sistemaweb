<?php
/*Templates para Tablas Generales
    @FAP*/

class PuntoVentaTemplate extends Template {
//METODO QUE DEVUELVE EL TITULO
	function titulo(){
		$titulo = '<div align="center"><h2>PUNTOS DE VENTA</h2></div><hr>';
		return $titulo;
	}
	//METODO QUE RETORNA UN MENSAJE DE ERROR
	function errorResultado($errormsg){
		?><script>alert('<?php echo $errormsg; ?>')</script><?php
		return '<blink>'.$errormsg.'</blink>';
	}
	
	function listado($registros,$disabledlados,$disabled,$pos){
	
		$titulo_grid = "PUNTOS DE VENTA";

		$columnas = array('LADOS','TODO','SUC','CC','POS','TICKET','TIPO','INTERF','LD01','LD02','LD03','LD04','LD05','LD06','LD07','LD08', 'LD09','LD10', 'LD11','LD12','LD13','LD14','LD15','LD16','IP','SERIE','AUT SUNAT','NOMBRE POS','IMPRESORA', 'DISPOSITIVO','EJECT','LINES', 'MENSAJE');

		$listado = '<div id="resultados_grid" class="grid" align="center"><br>';

		
		$form = new Form('', "resultados_grid", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "MAESTROS.PUNTOSVENTA"));

		$listado = '<table><thead align="center" valign="center" ><tr class="grid_header">';

		for($i=0;$i<count($columnas);$i++){
			$listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
		}
		
		$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
		
		//detalle
		$d1 = $disabled;
		$d2 = $disabledlados;
		$d3 = '';
		foreach($registros as $reg){
			$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
			$regCod = $reg[2];
			if ($pos != 'x'){
				if ($regCod == $pos ) {$disabled = $d1; $disabledlados = $d2;}
				else 		       {$disabled = 'disabled'; $disabledlados =  'disabled';}
			}
			$listado .= '<td align="center">';
			if ($reg[3] == 'Market'){
				$disabledlados =  'disabled';
				$listado .= '<a href="control.php?rqst=MAESTROS.PUNTOSVENTA&task=PUNTOSVENTA&action=ModificarLadosMarket&pos='.$reg[2].'" target="control"><img src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0" alt="Modificar Lado"/></A>';
			} else {
				$listado .= '<a href="control.php?rqst=MAESTROS.PUNTOSVENTA&task=PUNTOSVENTA&action=ModificarLado&pos='.$reg[2].'" target="control"><img src="/sistemaweb/icons/kedit32x32.png" align="middle" border="0" alt="Modificar Lado"/></A>';
			}
			$listado .= '</td>';
			$listado .= '<td align="center"><a href="control.php?rqst=MAESTROS.PUNTOSVENTA&task=PUNTOSVENTA&action=Modificar&pos='.$reg[2].'" target="control"><img src="/sistemaweb/icons/open.gif" align="middle" border="0" alt="Modificar"/></A></td>';
$listado .= '<td class="grid_item" align="center" style="color:black">'.$reg[0] .'</td>'; //Sucursal
$listado .= '<td class="grid_item" align="center" style="color:black">'.$reg[1] .'</td>'; //CC
$listado .= '<td class="grid_item" align="center" style="color:black">'.$reg[2] .'</td>'; //POS
$listado .= '<td class="grid_item" align="center" style="color:black">'.$reg[33].'</td>'; //TICKET
$listado .= '<td class="grid_item" align="center" style="color:black">'.$reg[3] .'</td>'; //Tipo
$listado .= '<td class="grid_item" align="center"><select name="interf'.$regCod.'" style="color:black" '.$disabled.'><option value="0" '.('0' == $reg[6] ? ' selected': '').'>0-Manual<option value="1" '.('1' == $reg[6] ? ' selected': '').'>1-Automatico</select>';

$listado .= '<input name="id'.$regCod.'" type="hidden" value="'.$regCod .'"></td>';

$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L1" type="radio" value="'.$reg[7] .'" '.('S' == $reg[7]  ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L2" type="radio" value="'.$reg[8] .'" '.('S' == $reg[8]  ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L3" type="radio" value="'.$reg[9] .'" '.('S' == $reg[9]  ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L4" type="radio" value="'.$reg[10].'" '.('S' == $reg[10] ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L5" type="radio" value="'.$reg[11].'" '.('S' == $reg[11] ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L6" type="radio" value="'.$reg[12].'" '.('S' == $reg[12] ? ' checked': '').' '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L7" type="radio" value="'.$reg[13].'" '.('S' == $reg[13] ? ' checked': '').' name="LD07" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L8" type="radio" value="'.$reg[14].'" '.('S' == $reg[14] ? ' checked': '').' name="LD08" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L9" type="radio" value="'.$reg[15].'" '.('S' == $reg[15] ? ' checked': '').' name="LD09" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L10" type="radio" value="'.$reg[16].'" '.('S' == $reg[16] ? ' checked': '').' name="LD10" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L11" type="radio" value="'.$reg[17].'" '.('S' == $reg[17] ? ' checked': '').' name="LD11" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L12" type="radio" value="'.$reg[18].'" '.('S' == $reg[18] ? ' checked': '').' name="LD12" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L13" type="radio" value="'.$reg[19].'" '.('S' == $reg[19] ? ' checked': '').' name="LD13" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L14" type="radio" value="'.$reg[20].'" '.('S' == $reg[20] ? ' checked': '').' name="LD14" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L15" type="radio" value="'.$reg[21].'" '.('S' == $reg[21] ? ' checked': '').' name="LD15" '.$disabledlados.'></td>';
$listado .= '<td class="grid_item" align="center" bgcolor="#F2F5A9"><input name="L16" type="radio" value="'.$reg[22].'" '.('S' == $reg[22] ? ' checked': '').' name="LD16" '.$disabledlados.'></td>';

$listado .= '<td class="grid_item" align="center"><input type="text" name="ip'.$regCod.'" value="'.$reg[23].'" size="12" style="text-align:center;color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="serie'.$regCod.'" value="'.$reg[24].'" size="14" style="text-align:center;color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="sunat'.$regCod.'" value="'.$reg[25].'" size="14" style="text-align:center; color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="nombrepos'.$regCod.'" value="'.$reg[26].'" size="8" style="text-align:center; color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="impre'.$regCod.'" value="'.$reg[27].'" size="10" style="text-align:center; color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="disp'.$regCod.'" value="'.trim($reg[29]).'" size="12" style="text-align:center; color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><select name="eject'.$regCod.'" style="color:black" '.$disabled.'><option value="0" '.('0' == $reg[30] ? ' selected': '').'>0-Sin salto<option value="1" '.('1' == $reg[30] ? ' selected': '').'>1-Con salto y corte<option value="2" '.('2' == $reg[30] ? ' selected': '').'>2-Doble salto</select></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="line'.$regCod.'" value="'.$reg[31].'" size="3" style="text-align:center; color:black" '.$disabled.'></td>';
$listado .= '<td class="grid_item" align="center"><input type="text" name="mens'.$regCod.'" value="'.trim($reg[32]).'" size="18" style="text-align:center;color:black" '.$disabled.'></td>';
			$listado .= '</tr>';
		}

		$listado .= '<tr>';

		$listado .= '<td colspan="4" align="center"><input style="width: 130px; color: blue; font-weight:bold; background-color: yellow; height:24px" type="submit" value="GUARDAR" name="action"></td>';
		$listado .= '</tr></tbody></table>';
		$listado .= '<input name="seleccion" type="hidden" value="'.$pos.'">';
		$form->addElement(FORM_GROUP_MAIN,new form_element_anytext($listado));

		//return $listado;
		return $form->getForm();
	}
}

