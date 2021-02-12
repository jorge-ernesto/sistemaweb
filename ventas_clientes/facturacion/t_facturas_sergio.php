<?php
  /*
     Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
//include('../include/reportes2.inc.php');
class FacturasTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Facturaci&oacute;n</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }
  
  function TmpReportePDF($datos)
  {

	if($datos)
	{
	var_dump($datos);
		$serie = $datos[0]['serie'];
//		echo "El primer dato Serie es : ".$serie;
		$columnas = array('FECHA' => 'FECHA MOVIMIENTO',
						  'SERIE' => 'SERIE DOCUMENTO',
						  'TIPO' => 'TIPO DOCUMENTO',
						  'NUMERO' => 'NUMERO DOCUMENTO',
						  'VALOR_VENTA' => 'VALOR_VENTA',
						  'IGV' => 'IGV',
						  'TOTAL_VENTA' => 'TOTAL_VENTA',
						  'CREDITO' => 'CREDITO',
						  'ANTICIPO' => 'ANTICIPO');
		$fontsize = 9;
		$reporte = new CReportes2("L");
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
		$reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 20, "L");
		$reporte->definirColumna("TIPO", $reporte->TIPO_TEXTO, 20, "L");
		$reporte->definirColumna("SERIE", $reporte->TIPO_TEXTO, 20, "L");
		$reporte->definirColumna("NUMERO",$reporte->TIPO_TEXTO, 20, "L");
		$reporte->definirColumna("VALOR_VENTA", $reporte->TIPO_IMPORTE, 20, "R");
		$reporte->definirColumna("IGV", $reporte->TIPO_IMPORTE, 10, "R");
		$reporte->definirColumna("TOTAL_VENTA",$reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("CREDITO",$reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("ANTICIPO",$reporte->TIPO_TEXTO, 10, "L");
		$contador = count($datos);
		echo "contador es $contador\n";
		for($j=0;$j</*count($datos)*/$contador;$j++)
		{
			echo "fila\n";
			if (($datos[$j-1][0] != $datos[$j][0]) && $row != $contador)
			{
				echo "nuevo cliente\n";
				$codigo = $datos[$j][0];
				$raz_social = $datos[$j][1];
				$reporte->definirCabecera(1, "L", "SISTEMA WEB");
				$reporte->definirCabecera(1, "C", "REPORTE DE FACTURACION");
				$reporte->definirCabecera(1, "R", "PAG.%p");
				$reporte->definirCabecera(2, "C", "");
				$reporte->definirCabecera(3, "L", "CODIGO DE CLIENTE : ".$codigo);
				$reporte->definirCabecera(3, "C", "RAZON SOCIAL : ".$raz_social);
				$reporte->definirCabecera(3, "R", "Emitido : "." %f");
				$reporte->definirCabeceraPredeterminada($columnas);
				$reporte->AddPage();
				$reporte->lineaH();
			 }
			 $datos2['FECHA'] = $datos[$j]['fecha'];
			 $datos2['SERIE'] = $datos[$j]['serie'];
			 $datos2['TIPO'] = $datos[$j]['tipo'];
			 $datos2['NUMERO'] = $datos[$j]['numero'];
			 $datos2['VALOR_VENTA'] = $datos[$j]['valor_venta'];
			 $datos2['IGV'] = $datos[$j]['igv'];
			 $datos2['TOTAL_VENTA'] = $datos[$j]['total_venta'];
			 $datos2['CREDITO'] = $datos[$j]['credito'];
			 $datos2['ANTICIPO'] = ($datos[$j]['anticipo']==''?'N':'S');
			 if($datos[$j]['tipo'] == 'FACTURA')
			 {
				$total_facturas_vventa += $datos[$j]['valor_venta'];
				$total_facturas_igv += $datos[$j]['igv'];
				$total_facturas += $datos[$j]['total_venta'];
			 }
			 if($datos[$j]['tipo'] == 'N/CREDITO')
			 {
			 	$total_ncredito_vventa += $datos[$j]['valor_venta'];
				$total_ncredito_igv += $datos[$j]['igv'];
				$total_ncredito += $datos[$j]['total_venta'];
			 }
        	 if($datos[$j]['tipo'] == 'N/DEBITO')
			 {
			 	$total_ndebito_vventa += $datos[$j]['valor_venta'];
				$total_ndebito_igv += $datos[$j]['igv'];
				$total_ndebito += $datos[$j]['total_venta'];
			 }
			 if($datos[$j]['tipo'] == 'BOL/VENTA')
			 {
			 	$total_bolventa_vventa += $datos[$j]['valor_venta'];
				$total_bolventa_igv += $datos[$j]['igv'];
				$total_bolventa += $datos[$j]['total_venta'];
			 }
			 $reporte->nuevaFila($datos2);
			 $row = $row + 1;
			
			 if ($datos[$j+1][0] != $datos[$j][0])
			 {
			 	echo "total cliente\n";
				$total['NUMERO'] = "TOTAL MOV. CLIENTE : ";
				$total['VALOR_VENTA'] = ($total_facturas_vventa + $total_ndebito_vventa + $total_bolventa_vventa) - $total_ncredito_vventa;
				$total['IGV'] = ($total_facturas_igv + $total_ndebito_igv + $total_bolventa_igv) - $total_ncredito_igv;
				$total['TOTAL_VENTA'] = ($total_facturas + $total_ndebito + $total_bolventa) - $total_ncredito;
				$reporte->lineaH();
				$reporte->nuevaFila($total);
	            $total_facturas_vventa = 0;
				$total_facturas_igv = 0;
				$total_facturas = 0;
			 	$total_ncredito_vventa = 0;
				$total_ncredito_igv = 0;
				$total_ncredito = 0;
			 	$total_ndebito_vventa = 0;
				$total_ndebito_igv = 0;
				$total_ndebito = 0;
			 	$total_bolventa_vventa = 0;
				$total_bolventa_igv = 0;
				$total_bolventa = 0;
			 }
		 }
		 echo "fin";
		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_facturacion.pdf", "F");
		return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/reporte_facturacion.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}
	else
	{
		$titulo_grid = "NO EXISTEN DATOS PARA LA TRANSACCION SOLICITADA";
		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table border="0" width="1000px">
                      <caption class="grid_title"><h2>'.$titulo_grid.'</h2></caption>
                      <thead align="center" valign="center">
                      <tr class="grid_header"></div>';
		return $listado;
	}
}

////////////////////FUNCION FORM REPORTE///////////////
function ResultExcel($datos){
	//header("Content-type: application/vnd.ms-excel");
   // header("Content-Disposition: attachment; filename=reporte_diario_vales_x_centro_costo.xls");
      			
   
}


  function formReporte(){
	$serie = @$_REQUEST['busqueda']['serie'];
	$fecha_inicio = @$_REQUEST['busqueda']['fecha_ini'];
	$fecha_final = @$_REQUEST['busqueda']['fecha_fin'];
	$fecha_cod_cli = @$_REQUEST['busqueda']['codigo'];
	$tipo_docu = @$_REQUEST['busqueda']['tipo'];
	$num_doc = @$_REQUEST['busqueda']['numero'];
	$fecha_ini = date("d/m/Y");
	$fecha_fin = date("d/m/Y");

	$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return pasarfechas();"');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));

	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[serie]', @$serie));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_ini]', @$fecha_inicio));
	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_fin]', @$fecha_final));

	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha_ini','Fecha de Inicio :', $_REQUEST['busqueda']['fecha_ini']?@$_REQUEST['busqueda']['fecha_ini']:@$fecha_ini, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fecha_ini'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div><br/>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha_fin','Fecha Fin :', $_REQUEST['busqueda']['fecha_fin']?@$_REQUEST['busqueda']['fecha_fin']:@$fecha_fin, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fecha_fin'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo de Cliente :', $_REQUEST['busqueda']['codigo']?@$_REQUEST['busqueda']['codigo']:@$cod_cli, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[serie]','Serie Documento :', $_REQUEST['busqueda']['serie']?@$_REQUEST['busqueda']['serie']:@$serie, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[tipo]','Tipo de Documento :', $_REQUEST['busqueda']['tipo']?@$_REQUEST['busqueda']['tipo']:@$tipo_docu, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[numero]','Numero Documento :', $_REQUEST['busqueda']['numero']?@$_REQUEST['busqueda']['numero']:@$num_doc, '', 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Generar Reporte',''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;'));
	if ($_REQUEST['action']=='Generar Reporte')
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="generar_xls.php?codigo='.$_REQUEST['busqueda']['codigo'].'&fecha_ini='.$_REQUEST['busqueda']['fecha_ini'].'&fecha_fin='.$_REQUEST['busqueda']['fecha_fin'].'&serie='.$_REQUEST['busqueda']['serie'].'&tipo='.$_REQUEST['busqueda']['tipo'].'&numero='.$_REQUEST['busqueda']['numero'].'">Excel</a>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></table>'));
	return $form->getForm();
  }

  function listado($registros){
    
    $Money = array('01'=>'Soles',
                   '02'=>'Dolares');
    
    $titulo_grid = "FACTURAS";
    //formulario de busqueda
    $columnas = array('T. DOC', 'S. DOC', 'N&#186; DOC.', 'C. CLIE.', 'RAZ&Oacute;N SOCIAL', 'FECHA', 'P.V.', 'MON.', 'T.C.', 'VAL. BRUTO', 'IMPUES.', 'VAL. TOTAL', 'CRED.', 'ANUL.', 'ANTIC.', 'N&#186; LIQ.');
    $listado ='<div id="error_body" align="center"></div>';
    $listado .= '<div id="resultados_grid" class="grid" align="center"><br>
                      <table border="0" width="100%">
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center">
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.$columnas[$i].'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(4).'</th></tr><tbody class="grid_body" >';
   
    foreach($registros as $reg){
      $reg[7] = $Money[trim($reg[7])];
      $alinear[9] = ' align="right"';
      $alinear[10] = ' align="right"';
      $alinear[11] = ' align="right"';
      $listado .= '<tr height="10px;" class="grid_row" >';
      $regCod = trim($reg["codigo"]);
      for ($i=0; $i < count($columnas); $i++){
             $listado .= '<td class="grid_item"'.@$alinear[$i].'>'.$reg[$i].'</td>';
      }
      $listado .= '<td><A href="control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS&montorecargo='.trim($reg[19]).'&recargo='.trim($reg[18]).
                  ''.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[f_desde]='.$_REQUEST['busqueda']['f_desde'].'&busqueda[f_hasta]='.$_REQUEST['busqueda']['f_hasta'].
                  '&action=Modificar&registroid='.trim($reg[0]).' '.trim($reg[1]).' '.trim($reg[2]).' '.trim($reg[3]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
     // if ($_SESSION["autorizacion"]){  
      	 if($reg[13]!='S'){
			$listado .= '<A href="javascript:confirmarLink(\'Desea eliminar el documento '.$regCod.'?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS'.
	                  ''.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[f_desde]='.$_REQUEST['busqueda']['f_desde'].'&busqueda[f_hasta]='.$_REQUEST['busqueda']['f_hasta'].'&action=Eliminar&registroid='.trim($reg[0]).' '.trim($reg[1]).' '.trim($reg[2]).' '.trim($reg[3]).'\', \'control\')"><img src="/sistemaweb/icons/delete.gif" alt="Borrar" align="middle" border="0"/></A>';      	 	
      	 }
	        $listado .= '<A href="javascript:confirmarLink(\'Desea Anular el documento '.$regCod.'?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS'.
	                  ''.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[f_desde]='.$_REQUEST['busqueda']['f_desde'].'&busqueda[f_hasta]='.$_REQUEST['busqueda']['f_hasta'].'&action=Anular&registroid='.trim($reg[0]).' '.trim($reg[1]).' '.trim($reg[2]).' '.trim($reg[3]).'\', \'control\')"><img src="/sistemaweb/icons/anular.gif" alt="Anular" align="middle" border="0"/></A><td>';
	      if($reg[12]=='S'){            
	        $listado .= '<A href="javascript:confirmarLink22(\'Desea pasar el documento a contado '.$regCod.'?\',\'control.php?rqst=FACTURACION.FACTURAS&task=FACTURAS'.          
	        			''.'&busqueda[codigo]='.$_REQUEST['busqueda']['codigo'].'&busqueda[f_desde]='.$_REQUEST['busqueda']['f_desde'].'&busqueda[f_hasta]='.$_REQUEST['busqueda']['f_hasta'].'&action=Contado&registroid='.trim($reg[0]).' '.trim($reg[1]).' '.trim($reg[2]).' '.trim($reg[3]).'\', \'control\')"><img src="/sistemaweb/icons/actualizar.gif" alt="Contado" align="middle" border="0"/></A><td>';
	      }           
     // }            
      $listado .= '</tr>';
     }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
    $Codigo = @$_REQUEST['busqueda']['codigo']?@$_REQUEST['busqueda']['codigo']:@$_REQUEST['paginacion']['codigo'];
    $F_Desde = @$_REQUEST['busqueda']['f_desde']?@$_REQUEST['busqueda']['f_desde']:@$_REQUEST['paginacion']['f_desde'];
    $F_Hasta = @$_REQUEST['busqueda']['f_hasta']?@$_REQUEST['busqueda']['f_hasta']:@$_REQUEST['paginacion']['f_hasta'];
    //echo "ENTRO BUSCAR\n";
    $f_desde = date("d/m/Y");
    $f_hasta = date("d/m/Y");
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));
    
    /*Datos Ocultos de paginación*/

    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('paginacion[codigo]', @$Codigo));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('paginacion[f_desde]', @$F_Desde));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('paginacion[f_hasta]', @$F_Hasta));

    /*---------------------------*/

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', @$Codigo, espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('f_desde','Desde :', @$F_Desde, espacios(2), 10, 10));
$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.f_desde'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('f_hasta','Hasta :', @$F_Hasta, espacios(2), 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.f_hasta'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Agregar',espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(0).'<br><br>'));
	
	//if ($_SESSION['autorizacion']) 
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('forma_eliminar','Forma Eliminar: ', '0', array('0'=>'[Escoger Opcion]','1'=>'Sin Soltar Vales','2'=>'Soltando Vales'), espacios(3)));
	
	
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    return $form->getForm();
  }

  
  function formFacturas($datos, $DatosArrayArticulos)
  {
    //print_r($datos);
    if ($_REQUEST['action']=='Modificar'){
    	$deshabilitar = 'disabled';
    }else {
    	$deshabilitar = '';
    }
    
    
    $CbSiNo = array('N'=>'No',
                    'S'=>'Si');
    
    if(empty($datos['dt_fac_fecha']) && !$datos["registroid"])
    {
	$fecha_tp = VariosModel::diaactual();
        $fechaDiv = explode('-',$fecha_tp);
        $datos['dt_fac_fecha'] = $fechaDiv[2]."/".$fechaDiv[1]."/".$fechaDiv[0];
//        $datos['dt_fac_fecha'] = date("d/m/Y");
//        $fecha_tp = date("Y-m-d");
        echo "fecha: " . $datos['dt_fac_fecha'];
    }else{
        $fechaDiv = explode('-',$datos['dt_fac_fecha']);
        $datos['dt_fac_fecha'] = $fechaDiv[2]."/".$fechaDiv[1]."/".$fechaDiv[0];
        $fecha_tp = $fechaDiv[0]."-".$fechaDiv[1]."-".$fechaDiv[2];
    }
    
    if(empty($datos['nu_tipocambio']))
    {
        $datos['nu_tipocambio'] = VariosModel::tipoCambioLibre(@$fecha_tp);
        echo "fecha: " . $fecha_tp;
        echo "tc: " . $datos['nu_tipocambio'] . "xx";
    }
    if(empty($datos["ch_factipo_descuento1"]) || $datos["ch_factipo_descuento1"]<=0)
    {
        $datos["ch_factipo_descuento1"] = '01';
    }
    
    if(empty($datos["ch_fac_cd_impuesto1"]))
    {
        $datos["ch_fac_cd_impuesto1"] = VariosModel::ObtCodIgv();
    }
    
    if(empty($datos["porce_fac_impuesto1"]))
    {
        $datos["porce_fac_impuesto1"] = 1+VariosModel::ObtIgv();
        print_r(VariosModel::ObtIgv());
    }

    $CbListaAlmacenes   = FacturasModel::ListadosVarios("almacenes");
    $CbListaTipoDoc     = FacturasModel::ListadosVarios("documentos_sunat");
    $CbListaMoneda      = FacturasModel::ListadosVarios("monedas");
    $CbListaSeriesDoc   = FacturasModel::ListadosVarios("series_documentos_sunat");
    //print_r($CbListaPrecio);
    if(!empty($datos["cli_codigo"]))
    {
        $DescCliente = FacturasModel::ClientesCBArray("trim(cli_codigo)='".pg_escape_string(trim($datos["cli_codigo"]))."'");
    }
    if(!empty($datos["ch_fac_seriedocumento"]) && !empty($datos["ch_fac_tipodocumento"]))
    {
        $DescSerie = FacturasModel::TiposSeriesCBArray("doc.num_seriedocumento='".pg_escape_string(trim($datos["ch_fac_seriedocumento"]))."'", trim($datos["ch_fac_tipodocumento"]));
    }
    
    if(!empty($datos["ch_fac_forma_pago"]) && !empty($datos["ch_fac_credito"]))
    {
        $DescFPago = FacturasModel::FormaPagoCBArray("substring(tab_elemento for 2 from length(tab_elemento)-1 ) = '".trim($datos["ch_fac_forma_pago"])."'", trim($datos["ch_fac_credito"]));
    }
    
    if(!empty($DatosArrayArticulos[1]["pre_lista_precio"]))
    {
        $DescLprecios = FacturasModel::ListaPreciosCBArray("tab_elemento ~ '".trim($DatosArrayArticulos[1]["pre_lista_precio"])."'");
    }
    
    if(!empty($datos["ch_factipo_descuento1"]))
    {
        $DescDescuento = FacturasModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '".trim($datos["ch_factipo_descuento1"])."'");
    //print_r($DescDescuento);
    }
    
    $form = new form2('FACTURAS', 'form_facturas', FORM_METHOD_POST, 'control.php','', 'control',' onSubmit="return verificar_completo()"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.FACTURAS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'FACTURAS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$_REQUEST["registroid"]));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_fac_cd_impuesto1]', trim(@$datos["ch_fac_cd_impuesto1"])));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('porce_fac_impuesto1', trim(@$datos["porce_fac_impuesto1"])));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('porce_recargo', 0));
    
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('referencia_fecha', ''));
    /*Datos Ocultos de paginación*/
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', @$_REQUEST["busqueda"]["codigo"]));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[f_desde]', @$_REQUEST["busqueda"]["f_desde"]));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[f_hasta]', @$_REQUEST["busqueda"]["f_hasta"]));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('acumulado', ($DatosArrayArticulos?count($DatosArrayArticulos):0)));
   
    /*---------------------------*/

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2" > <tr><td valign="top">'));

    /*Inicio de Datos del TD 1*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="3" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('N&uacute;mero</td><td class="form_label">:'.espacios(2).'<b><div id="Numero" style="display:inline;">'.trim(@$datos["ch_fac_numerodocumento"]).'</div></b></td></tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Almacen</td><td class="form_label">:'.espacios(2).'<b><div id="Almacen" style="display:inline;">'.trim(@$datos["ch_almacen"]).'</div></b></td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_tipodocumento]','Tipo Doc. </td><td>: ', trim(@$datos["ch_fac_tipodocumento"]), $CbListaTipoDoc, espacios(3), array("onChange"=>"javascript:ClearSerieAlmacen(this.value);"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_fac_seriedocumento]','Serie</td><td>: ', @$datos["ch_fac_seriedocumento"], '', 4, 4, array("onKeyUp"=>"getRegistro(this.value)", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_series_doc" style="display:inline;">'.$DescSerie['Datos'][trim($datos["ch_fac_seriedocumento"])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('dt_fac_fecha','Fecha'.espacios(2).'</td><td>: ', @$datos["dt_fac_fecha"], '', 10, 10,array(),array($deshabilitar)));
$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'form_facturas.dt_fac_fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[cli_codigo]','C&oacute;digo de Cliente </td><td>: ', @$datos["cli_codigo"], '', 10, 8, array("onKeyUp"=>"javascript:this.value=this.value.toUpperCase();getRegistroCli(this.value)", "class"=>"form_input_numeric", "$params" => "$val"),array($deshabilitar)));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="desc_cliente" style="display:inline;">'.@$DescCliente[trim($datos["cli_codigo"])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>&nbsp;</td></tr><tr><td colspan="2" align="right" class="form_label" valign="bottom">')); 
   // $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<H2><span color="#FFCC00"><div id="checking"><blink>ACTUALIZAR STOCK SI/NO? ==></blink></div></td></span></H2>'));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
    /*Fin de Datos del TD 1*/

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td><td valign="top">'));

    /*Inicio de Datos del TD 2*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_fac_numerodocumento]', trim(@$datos["ch_fac_numerodocumento"])));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_almacen]', trim(@$datos["ch_almacen"])));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[ch_punto_venta]', trim(@$datos['ch_punto_venta'])));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('c_dias_pago', trim($DescFPago['Dias'][trim($datos["ch_fac_forma_pago"])])));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_credito]','Cr&eacute;dito</td><td>: ', trim(@$datos["ch_fac_credito"]), $CbSiNo, espacios(3), array("onChange"=>"ClearCredito(); Mostrar_Liquidacion(this.value);"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_liquidacion]','Liquidacion: </td><td valign="top" rowspan="8">', @trim($datos["ch_liquidacion"]), '', 10, 10, array("class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);", "style" =>"display:none"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_fac_forma_pago]','F. de Pago</td><td>: ', @trim($datos["ch_fac_forma_pago"]), '', 3, 3, array("onKeyUp"=>"getRegistroFP(this.value)", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_forma_pago" style="display:inline;">'.trim($DescFPago['Datos'][trim($datos["ch_fac_forma_pago"])]).'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_anticipo]','Anticipado</td><td>: ', trim(@$datos["ch_fac_anticipo"]), $CbSiNo, espacios(3),array("style"=>"display:inline"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('articulos[pre_lista_precio]','L. de Precios</td><td>: ', (isset($articulos["pre_lista_precio"])?@trim($articulos["pre_lista_precio"]):substr(trim($DatosArrayArticulos[1]["pre_lista_precio"]),0,2)), '', 3, 3, array("onKeyUp"=>"getRegistroLPRE(this.value)", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_lista_precios" style="display:inline;">'.@$DescLprecios[trim($DatosArrayArticulos[1]["pre_lista_precio"])].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[ch_fac_moneda]','Moneda</td><td>: ', trim(@$datos["ch_fac_moneda"]), $CbListaMoneda, espacios(3),array("onChange"=>"limpiar_articulos();"),array($deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[nu_tipocambio]','T. Cambio</td><td>: ', @$datos['nu_tipocambio'], '', 5, 5,array("class"=>"form_input_numeric"), array("readonly",$deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[ch_factipo_descuento1]','Dscto</td><td>: ', @trim($datos["ch_factipo_descuento1"]), '', 3, 3, array("onKeyUp"=>"getRegistroDesc(this.value);", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,2);"),array("readonly",$deshabilitar)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_descuento" style="display:inline;">'.@$DescDescuento['Datos'][trim($datos["ch_factipo_descuento1"])].'</div>'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('datos[nu_fac_descuento1]', @$DescDescuento['Desc'][trim($datos["ch_factipo_descuento1"])]));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('datos[ch_descargar_stock]', 'Descargar stock </td><td>: ', 'S', '', array(), (isset($datos['ch_descargar_stock'])?array("checked",$deshabilitar):array($deshabilitar))));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></div></tr><tr><td><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    /*Fin de Datos del TD 2*/

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
    $form->addGroup('dcomplementarios','DATOS COMPLEMENTARIOS');
    $form->addElement('dcomplementarios', new f2element_button('Complemento', 'Complemetarios', espacios(2), array("onClick"=>"javascript:win_complemento('".($_REQUEST['action']=='Modificar'?'S':'N')."');")));
  
  	$form->addGroup ('articulos', 'DATOS ART&Iacute;CULOS');
    if ($_REQUEST['action']!='Modificar'){
	    
	    $form->addElement('articulos', new f2element_freeTags('<table border="0"><tr valign="top">'));
	    $form->addElement('articulos', new f2element_freeTags('<td><div id="descrip_codigo" style="float:left;display:inline">ART&Iacute;CULO</div></td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td>DESCRIPCI&Oacute;N</td>'));
	    $form->addElement('articulos', new f2element_freeTags ('<td align="center">CANT.</td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td align="center">PRECIO (IGV)</td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td align="center">NETO</td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td align="center">I.G.V.</td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td align="center">DSCTO.</td>'));
	    $form->addElement('articulos', new f2element_freeTags('<td align="center">TOTAL</td>'));
	    $form->addElement('articulos', new f2element_freeTags('</tr><tr valign="top"><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][cod_articulo][]','', '', '', 20, 13, array("onKeyUp"=>"this.value=this.value.toUpperCase();limpiar_articulos2();getRegistroArt(this.value);", "class"=>"form_input_numeric")));      
	    $form->addElement('articulos', new f2element_freeTags('<div id="desc_articulo[]" style="float:left;display:inline"></div>'));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_textarea ('interface[dato_articulo][desc_articulo][]','', '', '', 30, 3, array("class"=>"form_input","onKeyUp"=>"this.value=this.value.toUpperCase();","onKeyDown"=>"return maximaLongitud(this,199)"),array("disabled")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][cant_articulo][]','', '', '', 7, 7, array("onKeyUp"=>"CalcularValores();","class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,3);")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][precio_articulo][]','', '', '', 8, 10, array("onKeyUp"=>"CalcularValores();", "class"=>"form_input_numeric", "onKeyPress"=>"return validar(event,3);"),array("disabled")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][neto_articulo][]','', '', '', 8, 10, array("class"=>"form_input_numeric"), array("readonly")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][igv_articulo][]','', '', '', 8, 10, array("onKeyUp"=>"CalcularValores();", "class"=>"form_input_numeric"),array("readonly")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][dscto_articulo][]','', '', '', 8, 10, array("class"=>"form_input_numeric"), array("readonly")));
	    $form->addElement('articulos', new f2element_freeTags('</td><td>'));
	    $form->addElement('articulos', new f2element_text ('interface[dato_articulo][total_articulo][]','', '', '', 8, 10, array("class"=>"form_input_numeric"), array("readonly")));
	    $form->addElement('articulos', new f2element_freeTags('</td></tr></table>'));
        $form->addElement('articulos', new f2element_freeTags('<table border="0" width="100%"><tr valign="top"><td>'));
    	$form->addElement('articulos', new f2element_freeTagsLinkJs ('Agregar art&iacute;culo..', linea_h(1), array("href"=>"javascript:AgregaArticulo()")));
    	$form->addElement('articulos', new f2element_freeTags('</td></tr></table>'));
    }
    	 
    if($DatosArrayArticulos)
    {
        //echo "ENTRO \n";
        $_SESSION["ARTICULOS"] = $DatosArrayArticulos;
        $_SESSION["TOTAL_ARTICULOS"] = count($DatosArrayArticulos);
        $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados">'.FacturasTemplate::addArticulos($DatosArrayArticulos).'</div>'));
        $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados_totales">'.FacturasTemplate::addTotales(FacturasModel::CalcularTotales($DatosArrayArticulos),$datos).'</div>'));
    }else{
        //echo "ENTRO ELSE\n";
        $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados"></div>'));
        $form->addElement('articulos', new f2element_freeTags('<div id="datos_agregados_totales"></div>'));
    }
    
    $form->addGroup ('buttons', '');
    if ($_REQUEST['action']!='Modificar'){
    	$form->addElement('buttons', new f2element_submit('action','Guardar', espacios(2)));
    }
    $form->addElement('buttons', new f2element_button('action','Regresar', espacios(2),array('onClick'=>"document.form_facturas.submit();")));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }


  function addArticulos($lista)
  {
    if($lista!='')
    {
    	//print_r($lista);
     $formulario .= '<table border="0">'."\n";
     $k=0;
    foreach($lista as $llave => $valor)
    {
    	$k++;
		if(empty($valor['dscto_articulo']) && $valor['dscto_articulo'] <=0 )
		{
		    $valor['dscto_articulo']='0.00';
		}
		$formulario .= '<tr valign="top"><td>'."\n".
		    '</td></tr><tr valign="top"><td>'."\n".
		    '<input type="text" name="cod_articulo[]" value="'.trim($valor['cod_articulo']).'" readonly class="form_input_numeric_disabled" size="20">'.
		    '</td><td>'."\n".
		    '<input type="text" name="desc_articulo[]" value="'.trim($valor['desc_articulo']).'" readonly class="form_input_disabled" size="25">'.
		    '</td><td>'."\n".
		    '<input type="text" name="cant_articulo[]" value="'.money_format("%.2n",round($valor['cant_articulo'],2)).'" readonly class="form_input_numeric_disabled" size="5">'.
		    '</td><td>'."\n".
		    '<input type="text" name="precio_articulo[]" value="'.$valor['precio_articulo'].'" readonly class="form_input_numeric_disabled" size="8">'.
		    '</td><td>'."\n".
		    '<input type="text" name="neto_articulo[]" value="'.$valor['neto_articulo'].'" readonly class="form_input_numeric_disabled" size="8">'.
		    '</td><td>'."\n".
		    '<input type="text" name="igv_articulo[]" value="'.$valor['igv_articulo'].'" readonly class="form_input_numeric_disabled" size="8">'.
		    '</td><td>'."\n".
		    '<input type="text" name="dscto_articulo[]" value="'.$valor['dscto_articulo'].'" readonly class="form_input_numeric_disabled" size="8">'.
		    ' - '.round(($valor['dscto_articulo']*100)/(money_format("%.2n",round($valor['cant_articulo'],2))*$valor['precio_articulo']),2).'% </td><td>'."\n".
		    '<input type="text" name="total_articulo[]" value="'.$valor['total_articulo'].'" readonly class="form_input_numeric_disabled" size="8">'.
		    '</td><td>'."\n".
		    ($_REQUEST['action']!='Modificar'?"<a href=\"javascript:EliminarArticulo('".trim($valor['cod_articulo'])."',".$_SESSION["TOTAL_ARTICULOS"].")\">X</a>"."\n":"").
		    '</td>'."\n".
		    '</tr>'."\n";
	}
        $formulario .= '</table>'."\n";
   
    }
     return $formulario;
  }

  function addTotales($lista, $datos=null)
  {
  //if($lista)
  //{
  if ($_REQUEST['action']=='Modificar'){
  	$lista['total_cant_articulo']='';
  	$lista['total_precio_articulo']='';
  	$lista['total_neto_articulo']=$datos['nu_fac_valorbruto'];
  	$lista['total_igv_articulo']=$datos['nu_fac_impuesto1'];
  	$lista['total_dscto_articulo']=$datos['nu_fac_descuento1'];
  	$lista['total_total_articulo']=$datos['nu_fac_valortotal'];
  }
  
  if($_SESSION["TOTAL_ARTICULOS"]>0){
    $formulario .= '<table border="0" align="right">'."\n";
    $formulario .= '<tr valign="top"><td>'."\n".
    				
                   '</td></tr><tr valign="top"><td>ISC: <input type="text" name="datos[nu_fac_impuesto2]" value="'.@$datos['nu_fac_impuesto2'].'" class="form_input_numeric" size="7" onKeyPress="return validar(event,3);" > &nbsp;Recargo: <input type="text" name="datos[nu_fac_recargo2]" value="'.(isset($_REQUEST['montorecargo'])?$_REQUEST['montorecargo']:$lista['total_recargo']).'" disabled class="form_input_numeric" size="5">
                   TOTALES : '."\n".
                  '</td><td>&nbsp;'."\n".
                  //'<input type="text" name="desc_articulo[]" value="'.$valor['desc_articulo'].'" disabled class="form_input" size="25">'.
                  '</td><td>'."\n".
                  '<input type="text" name="total_cant_articulo" value="'.$lista['total_cant_articulo'].'" readonly class="form_input_numeric" size="5">'.
                  '</td><td>'."\n".
                  '<input type="text" name="total_precio_articulo" value="'.money_format("%.2n", $lista['total_precio_articulo']).'" readonly class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="datos[nu_fac_valorbruto]" value="'.money_format("%.2n", $lista['total_neto_articulo']).'" readonly class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="datos[nu_fac_impuesto1]" value="'.money_format("%.2n", $lista['total_igv_articulo']).'" readonly class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="datos[nu_fac_descuento1]" value="'.money_format("%.2n", $lista['total_dscto_articulo']).'" readonly class="form_input_numeric" size="8">'.
                  '</td><td>'."\n".
                  '<input type="text" name="datos[nu_fac_valortotal]" value="'.money_format("%.2n", round($lista['total_total_articulo'],2)).'" readonly class="form_input_numeric" size="8">'.
                  '</td><td>&nbsp;&nbsp;'."\n".
                  //"<a href=\"javascript:EliminarArticulo(".$llave.")\">_</a>"."\n".
                  '</td>'."\n".
                  '</tr>'."\n";
    $formulario .= '</table>'."\n";
  }
    return $formulario;
  
  }


  function setRegistros($codigo, $codtdoc)
  {
    $RegistrosCB = FacturasModel::TiposSeriesCBArray("doc.num_seriedocumento ~ '".trim($codigo)."'", $codtdoc);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
      	$auxi1 = explode('-',$RegistrosCB['Fechafin'][trim($cod)]);
      	$RegistrosCB['Fechafin'][trim($cod)]=$auxi1[2].'/'.$auxi1[1].'/'.$auxi1[0];
        $result = $descri." <script language=\"javascript\">top.setRegistro('".trim($cod)."', '".$RegistrosCB['Numeros'][trim($cod)]."','".$RegistrosCB['Almacen'][trim($cod)]."','".$RegistrosCB['Fechafin'][trim($cod)]."');</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistro('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosSeries', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosFormaPago($codigo, $codcred)
  {
    $RegistrosCB = FacturasModel::FormaPagoCBArray("substring(tab_elemento for 2 from length(tab_elemento)-1 ) ~ '".pg_escape_string($codigo)."'", $codcred);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroFP('".$cod."', '".$RegistrosCB['Dias'][trim($cod)]."');</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroFP('".$cod."');");
      }
      $cb = new f2element_combo('cbDatosFormaPago', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosListaPrecios($codigo)
  {
  //empty($codigo)?$codigo:'*';
    $RegistrosCB = FacturasModel::ListaPreciosCBArray("tab_elemento ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroLPRE('".$cod."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onClick"=>"getRegistroLPRE('".$cod."');");
      }
      $cb = new f2element_combo('cbDatosListaPrecios', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosCliente($codigo)
  {
    $RegistrosCB = FacturasModel::ClientesCBArray("trim(cli_codigo)||''||trim(cli_razsocial) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
      	$recargo = FacturasModel::obtenerRecargoMantenimiento(trim($cod));
      	$complementarios1 = FacturasModel::obtenerComplementarios(trim($cod));
      	$lprecios = FacturasModel::obtenerListadePrecios(trim($cod));
      	$porcdesc = FacturasModel::obtenerporcDesc(trim($cod));
      	//print_r($complementarios1);
      	$COMP["razon_social"] = $complementarios1['cli_razsocial']; 
		$COMP["direccion"] = $complementarios1['cli_direccion'];
		$COMP["ruc"] = $complementarios1['cli_ruc'];
		$COMP["comp_dir"] = $complementarios1['cli_comp_direccion'];
		$COMP["obs1"] = '';
		$COMP["obs2"] = '';
		$COMP["obs3"] = '';
		$_SESSION["ARR_COMP"] = $COMP;
		$result = $descri." <script language=\"javascript\">top.setRegistroCli('".trim($cod)."','".$recargo."','".trim($lprecios[0])."','".trim($lprecios[1])."','".trim($porcdesc[0])."','".trim($porcdesc[1])."','".trim($porcdesc[2])."','".trim($porcdesc[3])."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array("onclick"=>"getRegistroCli('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCliente', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosDesc($codigo)
  {
    $RegistrosCB = FacturasModel::DescuentosCBArray("substring(tab.tab_elemento for 2 from length(tab_elemento)-1) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB['Datos']) == 1) {
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $result = $descri." <script language=\"javascript\">top.setRegistroDesc('".trim($cod)."', '".$RegistrosCB['Desc'][trim($cod)]."');top.CalcularValores();</script>";
      }
    }
    if (count($RegistrosCB['Datos']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['Datos'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroDesc('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosDesc', '','', $RegistrosCB['Datos'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosArticulos($codigo, $codlpre)
  {
    $RegistrosCB = FacturasModel::ArticulosCBArray("trim(art.art_codigo)||trim(art.art_descripcion) ~ '".pg_escape_string($codigo)."'", $codlpre);
    //print_r($RegistrosCB);
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    
    if (count($RegistrosCB['DATOS_VER']) == 1) {
      foreach($RegistrosCB['DATOS_VER'] as $cod => $descri){
        $descripcion = trim($RegistrosCB['DESCRIPCION'][trim($cod)]);
        $precio      = money_format('%.2n',trim($RegistrosCB['PRECIO'][trim($cod)]));
        $editable = $RegistrosCB['EDITABLE'][trim($cod)];
        $result = " <script language=\"javascript\">top.setRegistroArt('".trim($cod)."','".htmlspecialchars($descripcion)."','".$precio."','".trim($editable)."');</script>";
      }
    }
    if (count($RegistrosCB['DATOS_VER']) > 1){
      $att_opt = array();
      foreach($RegistrosCB['DATOS_VER'] as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroArt('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosArticulos', '','', $RegistrosCB['DATOS_VER'],'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }

  function setRegistrosCuentas($codigo)
  {
  //echo "FIELDS : $fields";
    $RegistrosCB = FacturasModel::CuentasCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = " <script language=\"javascript\">top.setRegistroCodCta('".trim($cod)."','".$descri."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroCodCta('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
  
  function setRegistrosTipoCtaBan($codigo)
  {
  //echo "FIELDS : $fields";
    $RegistrosCB = FacturasModel::TipoCtaBanCBArray("trim(tab_elemento)||''||trim(tab_descripcion) ~ '".pg_escape_string($codigo)."'");
    $result = '<blink><span class="MsgError">Error..</span></blink>';
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $cod => $descri){
        $result = " <script language=\"javascript\">top.setRegistroTipoCtaBan('".trim($cod)."','".$descri."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $cod => $descri){
        $att_opt[trim($cod)] = array(" onclick"=>"getRegistroTipoCtaBan('".trim($cod)."');");
      }
      $cb = new f2element_combo('cbDatosTipoCtas', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    return $result;
  }
}

?>
