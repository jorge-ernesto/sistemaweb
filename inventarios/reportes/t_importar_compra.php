<?php
class TipodeCambioTemplate extends Template {

	function titulo() {
		return '<h2 align="center"><b>Importaci&oacute;n de Compras</b></h2>';
	}

	function formPag($paginacion, $vec) {
		$fecha 	  = $vec[0];
		$fecha2	  = $vec[1];

		$estaciones = TipodeCambioModel::obtenerEstaciones();
	    $formularios = TipodeCambioModel::Formularios();

		$form = new form2('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.IMPORTARCOMPRA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, '', array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Desde</td><td>:</td><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $fecha, '', 10, 12));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hasta: '));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "Hasta:", $fecha2, '', 10, 12));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
	    $form->addElement(FORM_GROUP_MAIN, new f2element_combo("formulario", "", "TODOS", $formularios, ''));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>No Doc. Ref</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("mov_docurefe", "", '', '', 10, 10));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Item</td><td>:</td><td align="left">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="in-order-product-id" name="art_desde" size="18" readonly>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" id="in-order-product-name" name="art_desde2" placeholder="Igresar código o nombre" onkeyup="autocompleteBridge(0)" size="32">'));

/*
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td><div id="label" style="display:block;">Art&iacute;culo</td></div><td><div id="label2" style="display:block;">:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde", "", '', '', 17, 13));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde2", "", '', '', 25, 30, '', array('readonly')));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" id="imgc" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Form.art_desde\',\'Form.art_desde2\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> '));
*/
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar"><img src="/sistemaweb/images/excel_icon.png" align="right" />Importar Compra</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
	
		if ($paginacion['paginas'] == 'P')
			$paginacion['paginas'] = '0';

	 	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_previa']."','".$fecha."','".$fecha2."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."',this.value,'".$fecha."','".$fecha2."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."','".$fecha."','".$fecha2."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosFecha('".$paginacion['pp']."','".$paginacion['ultima_pagina']."','".$fecha."','".$fecha2."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosFecha(this.value,'".$paginacion['primera_pagina']."','".$fecha."','".$fecha2."')")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));
		return $form->getForm();
    }

	function formSearch($fecha, $fecha2, $paginacion){

        $estaciones = TipodeCambioModel::obtenerEstaciones();
        $formularios = TipodeCambioModel::Formularios();

		$form = new form2('', "Form", FORM_METHOD_POST, "control.php", '', "control");
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.IMPORTARCOMPRA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" >'));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Almac&eacute;n</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("estacion", "", "TODAS", $estaciones, '', array("onfocus" => "getFechaEmision();")));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Desde</td><td>:</td><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha", "", $fecha, '', 10, 12));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hasta: '));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_text("fecha2", "", $fecha2, '', 10, 12));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Tipo de Formulario</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_combo("formulario", "", "TODOS", $formularios, ''));

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>No Doc. Ref</td><td>:</td><td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("mov_docurefe", "", '', '', 10, 10));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>Item</td><td>:</td><td align="left">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="hidden" id="in-order-product-id" name="art_desde" size="18" readonly>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<input type="text" id="in-order-product-name" name="art_desde2" placeholder="Igresar código o nombre" onkeyup="autocompleteBridge(0)" size="32">'));

        /*
        $form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde", "", '', '', 17, 13));
        //$form->addElement(FORM_GROUP_MAIN, new f2element_text("art_desde2", "", '', '', 25, 30, '', array('readonly')));
        //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<img src="/sistemaweb/images/help.gif" id="imgc" width="16" height="15" onMouseOver="this.style.cursor=\'pointer\'" onclick="javascript:mostrarAyuda(\'/sistemaweb/ventas_clientes/lista_ayuda.php\',\'Form.art_desde\',\'Form.art_desde2\',\'articulos\',\'\',\'<?php echo $valor;?>\');"> '));
        */

    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="3" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar"><img src="/sistemaweb/images/excel_icon.png" align="right" />Importar Compra</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp&nbsp&nbsp&nbsp&nbsp'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="4" align="center">'));

		//PAGINADOR
		$form->addGroup("GRUPO_PAGINA", "Paginacion"); 
		$paginacion['paginas'] = '0';
		$paginacion['primera_pagina'] = '0';
		$paginacion['pagina_previa'] = '0';
		$paginacion['pagina_siguiente'] = '0';
		$paginacion['ultima_pagina'] = '0';
		$paginacion['numero_paginas'] = '0';
		$paginacion['pp'] = '0';

 		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_freeTags('Registros por P&aacute;gina  : '));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));

		$form->addElement(FORM_GROUP_MAIN, new form_element_anytext(
		'<script>
			window.onload = function() {
				parent.document.getElementById("estacion").focus();
			}
		</script>'
		));

		return $form->getForm();
	}
    
	function resultadosBusqueda($resultados,$fecha,$fecha2) {
		$result = '';
		$result .= '<form><table align="center">';
		$result .= '<tr>';
		$result .= '<th class="grid_cabecera">&nbsp;</th>';
		$result .= '<th class="grid_cabecera">TIPO FORMULARIO</th>';
		$result .= '<th class="grid_cabecera">NRO. FORMULARIO</th>';
		$result .= '<th class="grid_cabecera">FECHA</th>';
		$result .= '<th class="grid_cabecera">ORDEN COMPRA</th>';
		$result .= '<th class="grid_cabecera">DOCUMENTO</th>';
		$result .= '<th class="grid_cabecera">ORIGEN</th>';
		$result .= '<th class="grid_cabecera">DESTINO</th>';
		$result .= '<th class="grid_cabecera">ALMACEN</th>';
		$result .= '<th class="grid_cabecera">ARTICULO</th>';
		$result .= '<th class="grid_cabecera">CANTIDAD</th>';
		$result .= '<th class="grid_cabecera">COSTO UNITARIO</th>';
		$result .= '<th class="grid_cabecera">COSTO TOTAL</th>';
		$result .= '</tr>';

		for ($i = 0; $i < count($resultados); $i++) {
			$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a = $resultados[$i];

			$formulario		= $a['formulario'];
			$tf				= $a['tipo_movimiento'];
			$fecha			= $a['fecha'];
			$orden_compra	= $a['orden_compra'];

			$result .= '<tr bgcolor="">';
				$result .= '<td class="'.$color.'" align="center"><input type="radio" name="cintillo" onClick="MostrarCintillo(\'' . $formulario . '\', \'' . $tf . '\', \'' . $fecha . '\', \'' . $orden_compra . '\')"</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['tipo_movimiento']) . ' - ' . htmlentities($a['des_movimiento']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['formulario']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['fecha']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['orden_compra']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['documento']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['almacen_origen']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['almacen_destino']) . '</td>';
				$result .= '<td class="'.$color.'" align="center">' . htmlentities($a['almacen']) . '</td>';
				$result .= '<td class="'.$color.'" align="left">' . htmlentities($a['articulo']) . '</td>';
				$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['cantidad'], 4, '.', ',')) . '</td>';
				$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['costo_unitario'], 4, '.', ',')) . '</td>';
				$result .= '<td class="'.$color.'" align="right">' . htmlentities(number_format($a['total'], 4, '.', ',')) . '</td>';
			$result .= '</tr>';
		}
		$result .= '</table>';
		return $result;
	}

	function ImportarDataExcel() {
		$form = new form2('', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "REPORTES.IMPORTARCOMPRA"));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="descargarFormatoExcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Descargar Formato Excel  </button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Seleccionar archivo Excel: <input type="file" name="ubica" id="ubica" size="70" onClick="Mostrar();">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><div id="ver" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar Compra Excel"><img src="/sistemaweb/images/excel_icon.png" align="right" />Importar Compra Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
	
		return $form->getForm();

    }

    function MostrarDataExcel($data, $filename) {

		$almacen	= '';
		$result		= '';
		$codproveedor 	= '';
		$fecha		= '';
		$tipo 		= '';
		$serie		= '';
		$numero		= '';
		$codmoneda	= 01;
		$procesar = false;

	    $almacen 	= TipodeCambioModel::Almacenes('');
	    $rubro 		= TipodeCambioModel::Rubros();
		$idcorrelativo	= TipodeCambioModel::Correlativo();

		$codproveedor 	= $data->val(1, 2);
		$fecha		= $data->val(3, 2);
		$tipo		= $data->val(4, 2);
		$serie		= $data->val(5, 2);
		$numero		= $data->val(6, 2);
		$codmoneda	= $data->val(2, 2);
		$nommoneda 	= $data->val(2, 3);

		$tc 		= TipodeCambioModel::TipoCambio($fecha);

		$color = "black";

		$contabilizar = array(
          0=>
          array(
            "contabilizar" => "No"
          ),
          1=>
          array(
            "contabilizar" => "Si"
          )
        );

		$hoy = date("d/m/Y");

		/* COMPLETAR CEROS */

		$serie 		= TipodeCambioModel::completarCeros($serie, 4, "0");
		$numero 	= TipodeCambioModel::completarCeros($numero, 8, "0");
		$codmoneda 	= TipodeCambioModel::completarCeros($codmoneda, 2, "0");

		/* VALIDACION COMPRA EXISTENTE */

		$validar = TipodeCambioModel::ValidarCompra($codproveedor, $tipo, $serie, $numero, null);

		if($validar[0]['existe'] >= 1){ //existe
			$color = "red";
		}

		/* VALIDACIONES TIPO, SERIE Y DOCUMENTO */

		if(strlen($tipo) > 2){
			echo "<script>alert('Error: Tipo debe tener maximo 2 digitos');</script>";
		}

		if(strlen($serie) > 4){
			echo "<script>alert('Error: Serie debe tener maximo 4 digitos');</script>";
		}

		if(strlen($numero) > 8){
			echo "<script>alert('Error: Numero debe tener maximo 8 digitos');</script>";
		}

		/* FORMATO DE FECHAS */

		$fsystem = TipodeCambioModel::FechaSistema();//pos_aprosys
		$dsystem = substr($fsystem,8,2);
		$msystem = substr($fsystem,5,2);
		$ysystem = substr($fsystem,0,4);

		$year	= substr($data->val(3, 2), 6, 4);
		$mes	= substr($data->val(3, 2), 3, 2);
		$dia	= substr($data->val(3, 2), 0, 2);

		$result .= '<form name="form"><table align="center"> ';

		$result .= '<tr>';
		$result .= '<th colspan="7" align="left" style="color:black;background-color: white">&nbsp;';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Fecha Sistema: </th>';
		$result .= '<th colspan="5" align="left" style="color:black;background-color: #D9F9B2">';
		$fsystem = $dsystem."-".$msystem."-".$ysystem;
		$result .= '<input type="hidden" name="fsystem" id="fsystem" value = "' . $fsystem . '"/>';
		$result .= $fsystem;
		$result .= '<th align="left" style="font-size:1em;color:black;background-color: #D9F9B2">T.C: '.$tc.'';		

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Tipo de Formulario:</th>';
		$result .= '<th colspan="6" align="left" style="font-size:1em; color:black;background-color: #D9F9B2">
			<input type="radio" id="tipo_formulario" name="tipo_formulario" value="01" >Ingreso por Compra Market &nbsp;&nbsp;&nbsp;
			<input type="radio" id="tipo_formulario" name="tipo_formulario" value="21" >Compra de Combustible
			<input type="radio" id="tipo_formulario" name="tipo_formulario" value="18" >Regularización
			<input type="radio" id="tipo_formulario" name="tipo_formulario" value="99" >Saldo inicial (Market y/o Combustible)
		';
		$result .= '</tr>';


		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Almacen: </th>';
		$result .= '<th colspan="6" align="left" style="color:black;background-color: #D9F9B2">
				<select name="almacen">';
					foreach($almacen as $value){
						$result .= '<option value='.$value['almacen'].'>'.$value['almacen'].' - '.$value['nombre'].'</option>';
					}
		$result .= '	</select>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Proveedor: </th>';
		
		/* VALIDAR PROVEEDOR */

		$proveedor = TipodeCambioModel::ValidarProveedor($codproveedor);

		/* TRAER DIAS DE VENCIMIENTO */

    		$proveedordays = TipodeCambioModel::ProveedorDias($proveedor[1]);

		//echo $fvencimiento;

		if($proveedor[0]){
			$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $codproveedor.' - '.$proveedor[0]; // el primer parametro es la fila, el segundo la columna.
		}else{
			$result .= '<th colspan="6" align="left" style="font-size:1em; color:red;background-color: #D9F9B2">';
			$result .= 'INEXISTENTE: '. $codproveedor; // el primer parametro es la fila, el segundo la columna.
		}

		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Moneda: </th>';
		$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
		$result .= $codmoneda.' - '.$nommoneda;
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Fecha Ingreso: </th>';

		$consolidacion = TipodeCambioModel::validaDia($fecha, $_SESSION['almacen']);

		if($year.$mes.$dia > $ysystem.$msystem.$dsystem){
			echo "<script>alert('La fecha de ingreso no puede ser mayor a la Fecha Sistema');</script>";
			$result .= '<th colspan="6" align="left" style="color:red;background-color: #D9F9B2"><blink>';
			$result .= $fecha;
			$result .= '</blink>';
		} else if ($mes < $msystem){
			echo "<script>alert('El Mes debe de ser el mismo al de Fecha Sistema !');</script>";
			$result .= '<th colspan="6" align="left" style="color:red;background-color: #D9F9B2"><blink>';
			$result .= $fecha;
			$result .= '</blink>';

		} else if($consolidacion == 1){
			$result .= '<th colspan="6" align="left" style="font-size:1em; color:red;background-color: #D9F9B2"><blink>';
			$result .= ' CONSOLIDADO: '.$fecha;
			$result .= '</blink>';
		} else {
			$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $fecha;
		}

		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Tipo Documento: </th>';
		$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
		$result .= $tipo;

		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Serie Documento: </th>';
		$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
		$result .= $serie;
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:1em; color:black;background-color: #D9F9B2">Numero Documento: </th>';
		$result .= '<th colspan="6" align="left" style="color:'.$color.';background-color: #D9F9B2">';
		$result .= $numero;

		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th colspan="7" align="left" style="color:blue;background-color: #D9F9B2">&nbsp;</th>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">NRO. CORRELATIVO</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">CODIGO ARTICULO</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">DESRIPCION ARTICULO</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">COSTO UNITARIO</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">CANTIDAD</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">TOTAL</td>';
		$result .= '<th style="font-size:1em; color:black;background-color: #D9F9B2">ESTADO</td>';
		$result .= '</tr>';

		$resultados = count($data->sheets[0]['cells']);
		$base = 0;
		$impuesto = 0;
		$total = 0;
		$sumcantidad = 0;
		$codigoexcel = '';
		$status = NULL;

		if (strlen($data->sheets[0]['cells'][9][2]) > 0 && strlen($data->sheets[0]['cells'][9][4]) > 0 && strlen($data->sheets[0]['cells'][9][5]) > 0) {
			for ($i = 9; $i <= ($resultados + 1); $i++) {
				$correlativo	= $data->sheets[0]['cells'][$i][1];
				$codigo			= $data->sheets[0]['cells'][$i][2];
				$costo			= $data->sheets[0]['cells'][$i][4];
				$cantidad		= $data->sheets[0]['cells'][$i][5];

				$codigo		= trim($codigo);
				$costo		= trim($costo);
				$cantidad	= trim($cantidad);

				if ((strlen($codigo) > 0 || strlen($codigo) <= 13) && strlen($costo) > 0 && strlen($cantidad) > 0){
					$validar =  TipodeCambioModel::ValidarCompra($codproveedor, $tipo, $serie, $numero, $codigo);//VALIDAR DOCUMENTO, PROVEEDOR Y PRODUCTOS DEL EXCEL
					if($validar[0]['existe'] == 1){
						$codigo = trim($validar[0]['codigo']);
						$colorletra	= "blue";
						$status = "NUEVO";

						if($codigo == "0"){ //INEXISTENTE
							$colorletra	= "red";
							$status		= "INEXISTENTE";
						} elseif($validar[0]['compraproducto'] >= "1"){ //existe
							$colorletra	= "red";
							$status		= "EXISTE";
						} elseif ($codigoexcel == $codigo) {
							$colorletra	= "red";
							$status		= "DUPLICADO";
						} else {
							$sumcantidad += (double)$cantidad;
							$base += ((double)$cantidad * (double)$costo);
							$procesar = true;
						}

						$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

						$result .= '<tr bgcolor="">';
							$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($correlativo) . '</td>';
							$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities((strlen($codigo) > 13 ? 'Error Codigo' : $codigo)) . '</td>';
							$result .= '<td class="'.$color.'" align = "left"><p style="color:'.$colorletra.';">' . htmlentities($validar[0]['descripcion']) . '</td>';
							$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities($costo) . '</td>';
							$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities($cantidad) . '</td>';
							$result .= '<td class="'.$color.'" align = "right"><p style="color:'.$colorletra.';">' . htmlentities(($cantidad * $costo)) . '</td>';
							$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($status) . '</td>';
						$result .= '</tr>';

						$codigoexcel = $codigo;
					} else {
						$result .= '<tr bgcolor="">';
						$result .= '<td colspan="7" class="'.$color.'" align = "center"><p style="font-size:12px; color:red;">Error al Importar Compra - Proveedor Invalido: '. htmlentities($codproveedor) . '</td>';
						$procesar = false;
						break;
					}
				}
			}
		} else {
			$result .= '<tr bgcolor="">';
			$result .= '<td colspan="7" align="center" class="'.$color.'" ><p style="font-size:12px; color:red;">No hay informacion</td>';
			$result .= '</tr>';
			$procesar = false;
		}

		$result .= '<tr bgcolor="F3FAF5">';
		$result .= '<th colspan="4" align="right" style="font-size:1em; color:black;">TOTALES: </td>';
		$result .= '<th align="right" style="font-size:1em; color:black;">'.htmlentities(number_format($sumcantidad, 3, '.', ',')).'</td>';
		$result .= '<th align="right" style="font-size:1em; color:black;">'.htmlentities(number_format($base, 2, '.', ',')).'</td>';
		$result .= '<td align="center">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr bgcolor="F3FAF5">';
		$result .= '<td colspan="7" align="center">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr bgcolor="F3FAF5">';
		$result .= '<td colspan="7" align="center">';
		$result .= '<HR width=100% align="left">';
		$result .= '</td></tr>';

		$result .= '<tr bgcolor="">';
		$result .= '<td colspan="7" align="center">&nbsp;</td>';
		$result .= '</tr>';

    	//REGISTRO DE COMPRAS
		$result .= '<tr bgcolor="">';
		$result .= '<th align="right" style="font-size:1em; color:black;">Registro de Compras Sunat: </td>';
		$result .= '<th colspan="6" align="left" style="font-size:1em;">
			<input type="checkbox" id="cuentaspagar" name="cuentaspagar" OnClick="CuentasPagar();" value="S" checked >';
		$result .= '</tr>';

		$result .= '<tr bgcolor="">';
		$result .= '<td colspan="7" align="center">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th id="celda" align="right" style="font-size:1em; color:black;background-color: white">Nro. Registro: ';
		$result .= '<th id="celda1" colspan="6" align="left" style="font-size:1em; color:black;background-color: white">'.$idcorrelativo.'</th>';
		$result .= '<input type="hidden" name="correlativo" id="correlativo" value = "' . $idcorrelativo . '"/>';

		$fvencimientoday	= ($dia + $proveedordays[0]['dias']);
		$fvencimientomes	= substr($hoy, 3, 2);
		$fvencimientoyear	= substr($hoy, 6, 4);

		if(strlen($fvencimientoday) < 2)
			$fvencimientoday = "0".$fvencimientoday;

		$fvencimiento = $fvencimientoday."/".$fvencimientomes."/".$fvencimientoyear;

		$result .= '<tr>';
		$result .= '<th id="celda7" align="right" style="font-size:1em; color:black;background-color: white">Fecha Vencimiento: ';
		$result .= '<th id="celda8" colspan="6" align="left">';
		$result .= '<input style="text-align:right" type="text" name="fvencimiento" id="fvencimiento" maxlength="10" size="10" value='.$fvencimiento.' />';
		$result .= '<a href="javascript:show_calendar('."'form.fvencimiento'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>';
		$result .= '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>';

		$result .= '<tr>';
		$result .= '<th id="celda2" align="right" style="font-size:1em; color:black;background-color: white">Contabilizar Otro Periodo: ';
		$result .= '<th id="celda3" align="left" style="font-size:1em; color:black;background-color: white">
				<select name="contabilizar" onChange="Contabilizar(this.value);">';
					foreach($contabilizar as $row)
						$result .= '<option value=' . $row["contabilizar"] . '> ' . $row["contabilizar"] . '</option>';
		$result .= '	</select>';
		$result .= '<th id="celda4" colspan="5" align="left" style="display:none; font-size:1em; color:black;background-color: white">Fecha periodo: ';
		$result .= '<input style="text-align:right" type="text" name="fperiodo" id="fperiodo" maxlength="10" size="10" value='.$hoy.' />';
		$result .= '<a href="javascript:show_calendar('."'form.fperiodo'".');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>';

		$result .= '<tr>';
		$result .= '<th id="celda5" align="right" style="font-size:1em; color:black;background-color: white">Rubro: </th>';
		$result .= '<th id="celda6" colspan="6" align="left" style="color:black;background-color: white">
				<select name="rubro">';
					foreach($rubro as $value)
						$result .= '<option value='.$value['codigo'].'>'.$value['codigo'].' - '.$value['nombre'].'</option>';
		$result .= '	</select>';

		$result .= '<tr bgcolor="">';
		$result .= '<td colspan="7" align="center">&nbsp;</td>';
		$result .= '</tr>';

		$limit = TipodeCambioModel::Limite();
		$nu_limite_pago = 0;
		if(!$limit)
			$nu_limite_pago = $limit['limite'];

		$perce = 0;
		$impuesto = ($base * 0.18);
		$total = ($base + $impuesto);

		$result .='<tr>';
		$result .='<td colspan="7" align="center">';
		$result .='<div id="RegistroCompra" style="display: block;">';
		$result .='Base Imponible:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<input style="text-align:right" type="text" name="base" id="base" maxlength="15" size="15" value="'.number_format($base, 2, '.', '').'" OnBlur="Totales(this.value);" onkeypress="return validar(event,3)" />';
		$result .='<input type="hidden" name="vali" id="vali" value="'.number_format($base, 2, '.', '').'"/>';
		$result .='<input type="hidden" name="limit" id="limit" value="'.number_format($nu_limite_pago, 2, '.', '').'"/>';
		$result .='&nbsp;&nbsp;&nbsp;&nbspImpuesto:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$result .='<input style="text-align:right" type="text" name="impuesto" id="impuesto" maxlength="15" size="15" value="'.number_format($impuesto, 2, '.', '').'" onkeypress="return validar(event,3)" />';
		$result .='&nbsp;&nbsp;&nbsp;&nbspImporte Total:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$result .='<input style="text-align:right" type="text" name="total" id="total" maxlength="15" size="15" value="'.number_format($total, 2, '.', '').'" onkeypress="return validar(event,3)" />';
		$result .='&nbsp;&nbsp;&nbsp;&nbspPercepcion:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp';
		$result .='<input style="text-align:right" type="text" name="perce" id="perce" maxlength="15" size="15" value="'.number_format($perce, 2, '.', '').'" onkeypress="return validar(event,3)" />';
		$result .='</div></tr>';

		$result .= '<tr bgcolor="">';
		$result .= '<td colspan="7" align="center">&nbsp;</td>';
		$result .= '</tr>';

		if($procesar){
			$result .= '<tr bgcolor="">';
			$result .= '<td colspan="7" align="right"><input type="button" onClick="Procesar(\'' . $filename . '\', \''.$codproveedor.'\',\''.$fecha.'\', \'' . $tipo . '\', \'' . $serie . '\', \'' . $numero . '\', \'' . $codmoneda . '\', \'' . $fvencimientoday . '\', \'' . $fvencimiento . '\');" value = "Procesar Compra"
					style="
						color:#126775;
						border-radius: 8px;
						padding:3px;
						border: 1px solid #999;
						border: inset 1px solid #333;
						-webkit-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
						-moz-box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
						box-shadow: 0px 0px 8px rgba(0, 0, 0, 0.3);
						background-color:white;
						font-weight:bold;
						font-size:12px;
					" />';
		}
		$result .= '</tr>';
		$result .= '</table></form>';
		return $result;
    }
}

