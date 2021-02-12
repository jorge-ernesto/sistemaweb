<?php

class ReimpresionTemplate extends Template {

    function titulo(){
	$titulo = '<div align="center"><h2>Cuentas Por Pagar Seleccion Docs Reimpresion Cintillo</h2></div><hr>';
	return $titulo;
    }

    // Solo Formularios y otros
    function formParametros(){
	$CbSiNo = array('N'=>'No',
			'S'=>'Si');

	
	$form = new form2('', 'Parametros', FORM_METHOD_GET, 'control.php', '', 'control');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'PARAMETROS'));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.REIMPRESION'));
		
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td>'));	
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametro[pro_codigo]','Proveedor :'.espacios(8), '', '', 8, 6));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametro[pro_cab_fecha_registro_ini]','Fecha Inicio :'.espacios(1), date('Y-m-d') , '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametro[pro_cab_fecha_registro_fin]','Fecha Fin :'.espacios(1), date('Y-m-d') , '', 12, 10));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametro[pro_cab_almacen]','Almancen :'.espacios(1), '' , '', 12, 10));
			
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Parametros',''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));	

	return $form->getForm();
    }


    // Solo Formularios y otros
    function formBuscarCPAG(){
	$busqueda=$_REQUEST["parametro"];
	$parametros=$_REQUEST["parametro"];
		
	$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CUADRO1'));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.REIMPRESION'));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0"><tr><td><b>'));	
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametros[pro_codigo]','Proveedor :'.espacios(8), $parametros["pro_codigo"] , espacios(2), 8, 6,array(),array('readonly') ));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametros[pro_cab_fecha_registro_ini]','Fecha Inicio :'.espacios(1),  $parametros["pro_cab_fecha_registro_ini"] , espacios(2), 12, 10,array(),array('readonly') ));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametros[pro_cab_fecha_registro_fin]','Fecha Fin    :'.espacios(1),  $parametros["pro_cab_fecha_registro_fin"] , espacios(2), 12, 10,array(),array('readonly') ));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('parametros[pro_cab_almacen]','Almacen :'.espacios(1),  $parametros["pro_cab_almacen"] , '', 12, 10,array(),array('readonly') ));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</b><br><br></td></tr><tr><td align="center">'));

	
	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[pro_codigo]', $busqueda["pro_codigo"]));

	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[pro_cab_seriedocumento]','Serie :', '', espacios(2), 12, 10));
	$busqueda["pro_cab_numdocumento"]=$_REQUEST["num_documento"];
        $NDOC_CBArray = ReimpresionModel::NDOC_CBArray('',$busqueda);
	$form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[pro_cab_numdocumento]', 'NUM DOCUMENTO:', @$busqueda["pro_cab_numdocumento"],'<div id="NumDoc_perfil" style="display:inline;">'.$NDOC_CBArray["'".$busqueda["pro_cab_numdocumento"]."'" ].'</div>',10, 9, array("onKeyUp"=>"Setfn2(this.form,this,'".$busqueda["pro_codigo"]."','".$busqueda["pro_cab_fecha_registro_ini"]."','".$busqueda["pro_cab_fecha_registro_fin"]."','".$busqueda["pro_cab_almacen"]."',event )") ));

        $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Marcar',espacios(3)));
        $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Regresar',''));
		
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center">'));

        $form->addElement(FORM_GROUP_MAIN, new f2element_button('action', 'Imprimir', '', array("onclick"=>'openPDFWindow()')));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));	
	
        return $form->getForm();
    }

    function listarCPAG($lstCPAG){
	if (!is_array($lstCPAG))
	    return $lstCPAG;

	$columnas = array( 'SELECC', 'TIPO DOC', 'SERIE', 'DOCUMENTO', 'PROVEEDOR', 'FECHA REG', 'ALMACEN', 'TOTAL','#REGISTRO', 'CINTILLO' );

	$listado = '<div class="grid" id="resultados_grid" align="center"><br>
			<table class="form_body">
                        <caption class="grid_title">DOCUMENTOS CPAG</caption>
                        <thead align="center" valign="center" >
                        <tr class="grid_header">';
					  
	for($i=0;$i<count($columnas);$i++)
	{
	    $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
	}
	
	$listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';

	//detalle
	foreach($lstCPAG as $clave => $reg)
	{
		// $regCod = $reg[0].'--'.$reg[1];
		$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
		$listado .= '<td class="grid_item"><input type="checkbox" name="m_clave" value="'.htmlentities($clave).'" '.(trim($reg[8])!=''?'checked':'').' >'.$reg[8].'</td>';			
		for ($i=0; $i<8; $i++){
		    $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
		}
		// $listado .= '<td class="grid_item">'.($reg[7]!=0?'<input type="text" value="'.$reg[7].'" >':espacios(1)).'</td>';
		$listado .= '<td>&nbsp;</td></tr>';
	}
	$listado .= '</tbody></table><br></div>';
	return $listado;
    }

    function setNDOC($codigo='',$parametro1, $parametro2,$parametro3,$parametro4)
    {
        $parametros=array();
        $parametros["pro_codigo"]=$parametro1;
        $parametros["pro_cab_fecha_registro_ini"]=$parametro2;
        $parametros["pro_cab_fecha_registro_fin"]=$parametro3;
        $parametros["pro_cab_almacen"]=$parametro4;

        $RegistrosCB = ReimpresionModel::NDOC_CBArray( pg_escape_string($codigo), $parametros );
	$result = '<blink><span class="MsgError">Error..</span></blink>';
	if (count($RegistrosCB) == 1) {
	    foreach($RegistrosCB as $cod => $descri){
		$result = $descri." <script language=\"javascript\">top.setNDOC('".trim($cod)."');</script>";
	    }
	}
	if (count($RegistrosCB) > 1){
	    $att_opt = array();
	    foreach($RegistrosCB as $cod => $descri){
	        $att_opt[trim($cod)] = array("onclick"=>"getNDOC('".trim($cod)."' ,'".$parametros["pro_codigo"]."','".$parametros["pro_cab_fecha_registro_ini"]."','".$parametros["pro_cab_fecha_registro_fin"]."','".$parametros["pro_cab_almacen"]."');");
	    }
	    $cb = new f2element_combo('cbDatos', '','', $RegistrosCB,'',array("onkeyup"=>"Setfn(this.form,this,'".$parametros["pro_codigo"]."','".$parametros["pro_cab_fecha_registro_ini"]."','".$parametros["pro_cab_fecha_registro_fin"]."','".$parametros["pro_cab_almacen"]."',event);","size"=>"5"), array(), $att_opt);
	    $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
        }
        return $result;
    }

    function cintilloPDF($results)
    {

	$cabecera = Array(
			'art_codigo'		=>	'Codigo',
			'art_descripcion'	=>	'Descripcion',
			'art_unidad'		=>	'UNI',
			'com_num_compra'	=>	'No. O/C',
			'mov_cantidad'		=>	'Cantidad',
			'mov_costototal'	=>	'Cost.Total',
			'mov_costounitario'	=>	'Cost.Unit.',
			'mov_precio'		=>	'Precio',
			'mov_ganancia_unitaria'	=>	'Diferencia',
			'mov_ganancia_porcentaje' =>	'(%)',
			'stock'			=>	'Stock'
			);

        $fontsize = 7;

	$reporte = new CReportes2('L','pt', array('250','580'));
        $reporte->SetMargins(5, 5, 5);
	$reporte->SetFont("courier", "", $fontsize);
	    
	$reporte->definirColumna("art_codigo", $reporte->TIPO_TEXTO, 13, "L");
	$reporte->definirColumna("art_descripcion", $reporte->TIPO_TEXTO, 30, "L");
	$reporte->definirColumna("art_unidad", $reporte->TIPO_TEXTO, 6, "L");
	$reporte->definirColumna("com_num_compra", $reporte->TIPO_TEXTO, 10, "L");
	$reporte->definirColumna("mov_cantidad", $reporte->TIPO_IMPORTE, 6, "R");
	$reporte->definirColumna("mov_costototal", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("mov_costounitario", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("mov_precio", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("mov_ganancia_unitaria", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("mov_ganancia_porcentaje", $reporte->TIPO_IMPORTE, 10, "R");
	$reporte->definirColumna("stock", $reporte->TIPO_IMPORTE, 6, "R");

	$reporte->definirColumna("rotulo", $reporte->TIPO_TEXTO, 69, "R", "_total");
	$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 10, "R", "_total");

        $reporte->definirCabecera(1, "L", "Asesoria Comercial S.A.");
	$reporte->definirCabecera(1, "C", "Cintillo de Liquidacion de Compras");
	$reporte->definirCabecera(1, "R", "Pagina %p");
	$reporte->definirCabecera(2, "R", "%f");
	$reporte->definirCabecera(3, "R", " ");

	$reporte->definirCabeceraPredeterminada($cabecera);

	foreach($results['documentos'] as $key=>$factura) {
	    $DatosCabecera = 'Fecha : '.$factura['cabecera']['pro_cab_fecharegistro'].'  ';
	    $DatosCabecera .= 'Proveedor : '.$factura['cabecera']['pro_codigo'].'  ';
	    $DatosCabecera .= 'Doc. Int.. : '.$factura['cabecera']['pro_cab_documento'].'  ';
	    $DatosCabecera .= 'Nro. Registro : '.$factura['cabecera']['pro_cab_numreg'].'/  ';
	    $DatosCabecera .= date("m-Y");

	    $DatosCabecera2 = 'Rubro : '.$factura['cabecera']['pro_cab_rubrodoc'].'  ';
	    $DatosCabecera2 .= 'Emision : '.$factura['cabecera']['pro_cab_fechaemision'].'  ';
	    $DatosCabecera2 .= 'Moneda : '.$factura['cabecera']['pro_cab_moneda'].'  ';
    	    $DatosCabecera2 .= 'Valor : '.$factura['cabecera']['pro_cab_valor'].'  ';
    	    $DatosCabecera2 .= 'Impuesto : '.$factura['cabecera']['pro_cab_impuesto'].'  ';
	    $DatosCabecera2 .= 'Total : '.$factura['cabecera']['pro_cab_imptotal'].'  ';
//	    $DatosCabecera2 .= 'TIPO CAMBIO : '.$factura['cabecera']['TIPO CAMBIO'].'  ';

	    $reporte->definirCabecera(2, "L", "ALMACEN : ".$factura['cabecera']['pro_cab_almacen']);
	    $reporte->definirCabecera(4, "L", $DatosCabecera);
	    $reporte->definirCabecera(5, "L", $DatosCabecera2);
	    $reporte->definirCabecera(6, "R", " ");
	    
	    $reporte->AddPage();
//	    var_dump($factura);
	    foreach($factura['detalle'] as $key=>$documento) {
//		var_dump($documento['cabecera']);
		$reporte->Ln();
		$linea  = "Formulario Compra:" . $documento['cabecera']['mov_numero'] . ' ';
		$linea .= "Orden Compra:" . $documento['cabecera']['com_num_compra'] . ' ';
		$linea .= "Doc. Ext.:" . $documento['cabecera']['mov_docurefe'] . ' ';
		$linea .= "Fecha:" . $documento['cabecera']['mov_fecha'] . ' ';
		
		$reporte->Cell(0, $fontsize, $linea, 1, 1, 'L');

		foreach($documento['articulos'] as $art_codigo=>$articulo) {
		    $reporte->nuevaFila($articulo);
		}
		
		$array = array();
	        $array['total'] = $documento['total'];
	        $array['rotulo'] = "Total Orden de Compra:";
	        $reporte->nuevaFila($array, "_total");

		$reporte->Ln();
	    }
	    $array = array();
	    $array['total'] = $factura['total'];
	    $array['rotulo'] = "Total Documento:";
	    $reporte->nuevaFila($array, "_total");

	}
		    
	/*$reporte->Ln();
	$reporte->cell(0,10,'Fecha : '.$reporte_array['CABECERA']['FECHA'].'',1,0,'L');
	$reporte->Lnew();*/
						
						    
	$reporte->Output();						
    }
}

?>