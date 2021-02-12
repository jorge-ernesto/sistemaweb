<?php

class CancelacionTemplate extends Template {
	function Titulo() {
		$titulo = '<div align="center"><h2>Obtener Cancelaciones</h2></div><hr>';
		return $titulo;
	}
	function errorResultado($errormsg) {
		return '<center><blink>'.$errormsg.'</blink></center>';
	}

	function formBuscar() {
		$meses = array('00'=>'Seleccione','01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Setiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'OBTCANCELACION.OBTENERCANCELACION'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'SINTAREA'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center><br />'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_combo('mes','Mes:','',$meses,espacios(3)));
		
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechaini]','Fecha Inicio: ', @$datos["fechaini"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>&nbsp;&nbsp;&nbsp;'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechafin]','Fecha Fin ', @$datos["fechafin"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));
		
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(1),array("onclick"=>"document.getElementById('Mensaje').style.visibility='visible'; document.getElementById('Mensaje').style.display='block'; document.getElementById('ResultadosCancelaciones').style.display='none';")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center><br /><div id="Mensaje" style="display:none; visibility:hidden;"><center><h2><blink>Procesando...</blink></h2></center></div>'));
		return $form->getForm();
	}

	function CabeceraListado() {
		$columnas = array('CLIENTE','Nro DOCUMENTO','IMPORTE','IMPORTE CANCELADO','FECHA CANCELACION','ESTADO');
		$titulo_grid = 'CANCELACIONES';
		$cabecera = '<table border="0" width="100%" id="ResultadosCancelaciones"><caption class="grid_title">'.$titulo_grid.'</caption>';
		$cabecera .= '<tr>';
		for($i=0;$i<count($columnas);$i++){
			$cabecera .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
		}
		$cabecera.='</tr>';
		return $cabecera;
	}

	function FinListadoCancelaciones($ok=0,$error=0, $eliminados=0) {
		$listado = '<tr><th colspan="6">Cancelados:'.$ok.' || No cancelados o con error:'.$error.' || Cancelaciones eliminadas en postgres: '.$eliminados.'</th></tr>';
		$listado .= '<tr><th colspan="6">&nbsp;</th></tr></table>';
		return $listado;
	}

	function ListadoCancelaciones($registro,$estado) {
//		if ($estado != 2) {
			$listado = '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
			$listado .= '<td class="grid_item" align="center">'.$registro['CODI_GC'].'</td>';
			$listado .= '<td class="grid_item" align="center">'.$registro['NDOC_GN'].'</td>';
			$listado .= '<td class="grid_item" align="right">'.$registro['IMPO_CC'].'</td>';
			$listado .= '<td class="grid_item" align="right">'.$registro['IMPO_CC'].'</td>';
			$fecha = substr($registro['ACCI_CC'],6,2).'/'.substr($registro['ACCI_CC'],4,2).'/'.substr($registro['ACCI_CC'],0,4);
			$listado .= '<td class="grid_item" align="left">'.$fecha.'</td>';
			if ($estado == 1) {
				$listado .= '<td class="grid_item" align="center">Nuevo Cancelado</td></tr>';
			} elseif ($estado == 0) {
				$listado .= '<td class="grid_item" align="center">Error de insert into (por referencia)</td></tr>';
			} elseif ($estado == 2) {
				$listado .= '<td class="grid_item" align="center">Registro ya existe</td></tr>';
			} elseif($estado==3){
				$listado .= '<td class="grid_item" align="center">Registro borrado y no existe</td></tr>';
			}elseif($estado==4){
				$listado .= '<td class="grid_item" align="center">Se elimino de postgres</td></tr>';
			}
//		}
    return $listado;
	}


}
?>