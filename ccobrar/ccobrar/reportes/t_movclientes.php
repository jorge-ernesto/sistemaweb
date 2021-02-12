<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
include('../include/reportes2.inc.php');
class MovClientesTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>REPORTE DE MOVIMIENTOS POR CLIENTE</h2></div><hr>';
    return $titulo;
  }
  
  function formBuscar(){
  	$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.MOVIMIENTOS'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CLIENTES'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbmoneda','Moneda. </td><td>: ', $_REQUEST['cbmoneda'], array('01'=>'01 - Soles','02'=>'02 - Dolares'), espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecinicio','Fecha Inicio'.espacios(2).'</td><td>: ', (!isset($_REQUEST['fecinicio'])?date('d/m/Y'):$_REQUEST['fecinicio']), '', 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecfin','&nbsp;Fecha Fin'.espacios(2).': ', (!isset($_REQUEST['fecfin'])?date('d/m/Y'):$_REQUEST['fecfin']), '', 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbclientes','Tipo de Busqueda</td><td>: ',$_REQUEST['cbclientes'], array('S'=>'Todos los Clientes','N'=>'Un Cliente'), espacios(3),array("onChange"=>"mostrarCliente(this.value);")));
    if ($_REQUEST['cbclientes']=='N') $estilo = "display:inline";
    else $estilo = "display:none";
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('txtcliente','- ', $_REQUEST['txtcliente'], '', 10, 10, array("class"=>"form_input_numeric", "style" =>$estilo, 'OnKeyUp'=>'javascript:this.value=this.value.toUpperCase();')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('cbtipo','Tipo de Reporte</td><td>: ',$_REQUEST['cbtipo'], array('01'=>'Detallado','02'=>'Resumido'), espacios(3)));
 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td align="center" colspan="2">'));
 	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte', espacios(2)));
 	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    return $form->getForm();
  }
  
  function ReportePDF($resultado){
  	$Cabecera = array( 
		    "FECHA"           => "FECHA",
		    "ACCION"         => "ACCION",
		    "DOCUMENTO"        => "DOCUMENTO",
		    "REFERENCIA"                => "REFERENCIA",
		    "MONEDA"           => "MONEDA",
		    "CARGO" => "CARGO",
		    "ABONO"           => "ABONO",
		    "SALDO"             => "SALDO"
		    );

    $fontsize = 7;
    $reporte = new CReportes2();
    $reporte->SetMargins(5, 5, 5);
    $reporte->SetFont("courier", "", $fontsize);
    
    $reporte->definirColumna("FECHA", $reporte->TIPO_TEXTO, 10, "L");
    $reporte->definirColumna("ACCION", $reporte->TIPO_TEXTO, 8, "L");
    $reporte->definirColumna("DOCUMENTO", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("REFERENCIA", $reporte->TIPO_TEXTO, 20, "L");
    $reporte->definirColumna("MONEDA", $reporte->TIPO_TEXTO, 10, "R");
    $reporte->definirColumna("CARGO", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("ABONO", $reporte->TIPO_IMPORTE, 10, "R");
    $reporte->definirColumna("SALDO", $reporte->TIPO_IMPORTE, 10, "R");
    
    $reporte->definirColumna("CODIGO", $reporte->TIPO_TEXTO, 9, "L","_DETALLE");
    $reporte->definirColumna("DESCRIPCION", $reporte->TIPO_TEXTO, 50, "L","_DETALLE");
    $reporte->definirColumna("SALDO", $reporte->TIPO_TEXTO, 50, "L","_DETALLE");
    
    $reporte->definirCabecera(1, "L", "ACOSA-OFICINA CENTRAL");
    $reporte->definirCabecera(1, "C", "MOVIMIENTOS DE CUENTA DEL CLIENTE");
    $reporte->definirCabecera(1, "R", "PAG.%p");
    $reporte->definirCabecera(2, "L", trim($resultado[0]['cli_codigo']).' - '.trim($resultado[0]['cli_razsocial']));
    $reporte->definirCabecera(2, "C", "MONEDA: ".($_REQUEST['cbmoneda']=='01'?'NUEVOS SOLES':'DOLARES AMERICANOS'));

    $reporte->definirCabecera(2, "R", "%f");
    $reporte->definirCabecera(3, "R", " ");
    $reporte->definirCabeceraPredeterminada($Cabecera);
    $reporte->AddPage();
    for($i=0;$i<count($resultado);$i++){
    	/*$datos2['CODIGO']=$resultado[$i]['cli_codigo'];
    	$datos2['DESCRIPCION']=$resultado[$i]['cli_razsocial'];
    	$datos2['SALDO']=$resultado[$i]['suma'];
    	$reporte->Ln();
	    $reporte->nuevaFila($datos2,"_DETALLE");*/
	    
	    $movimientos=array();
   		$movimientos = MovClientesModel::ObtenerMovimientosdeCliente($_REQUEST['cbmoneda'],$resultado[$i]['cli_codigo'],$_REQUEST['fecinicio'],$_REQUEST['fecfin']);
   		$detalle="";
   		$saldoacumulado = $resultado[$i]['suma'];
   		$totalcargo = 0;
   		$totalabono = 0;
    	if ($_REQUEST['cbtipo']=='01'){
	   		for($j=0;$j<count($movimientos);$j++){
	   			$saldoacumulado = $saldoacumulado-($movimientos[$j]['cargo']-$movimientos[$j]['abono']);
	   			//$detalle .= "<tr><td>".$movimientos[$j]['dt_fechamovimiento']."</td><td>".$movimientos[$j]['tipo']."</td><td>".$movimientos[$j]['numero']."</td><td>".$movimientos[$j]['ch_numdocreferencia']."</td><td>".$movimientos[$j]['mon']."</td><td align=right>".round($movimientos[$j]['cargo'],2)."</td><td align=right>".round($movimientos[$j]['abono'],2)."</td><td align=right>(".round($saldoacumulado,2).")</td></tr>";
	   			$datos['FECHA']=$movimientos[$j]['dt_fechamovimiento'];
	   			$datos['ACCION']=$movimientos[$j]['tipo'];
	   			$datos['DOCUMENTO']=$movimientos[$j]['numero'];
	   			$datos['REFERENCIA']=$movimientos[$j]['ch_numdocreferencia'];
	   			$datos['MONEDA']=$movimientos[$j]['mon'];
	   			$datos['CARGO']=round($movimientos[$j]['cargo'],2);
	   			$datos['ABONO']=round($movimientos[$j]['abono'],2);
	   			print_r($j.'='.$saldoacumulado.'/n');
	   			$datos['SALDO']=($saldoacumulado==0)?'0.00':round($saldoacumulado,2);
	   			$totalcargo += $movimientos[$j]['cargo'];
	   			$totalabono += $movimientos[$j]['abono'];
	   			$reporte->nuevaFila($datos);
	   		}
	   		//$saldoacumulado=0;
	   		//$detalle .= "<tr><td colspan=9><hr/></td></tr>";
	   		$reporte->Ln();
	   		$reporte->lineaH();
	   		$datos['FECHA']='TOTAL';
	   		$datos['ACCION']='GENERAL';
	   		$datos['DOCUMENTO']='';
	   		$datos['REFERENCIA']='';
	   		$datos['MONEDA']='';
	   		$datos['CARGO']=$totalcargo;
	   		$datos['ABONO']=$totalabono;
	   		$datos['SALDO']=($saldoacumulado==0)?'0.00':round($saldoacumulado,2);
	   		$reporte->nuevaFila($datos);
   			//$detalle .= "<tr><td>TOTAL GENERAL</td><td colspan=4>&nbsp;</td><td align=right>".$totalcargo."</td><td align=right>".$totalabono."</td><td>(".round($saldoacumulado,2).")</td></tr>";
   		}else{
   			for($j=0;$j<count($movimientos);$j++){
	   			$saldoacumulado = $saldoacumulado-($movimientos[$j]['cargo']-$movimientos[$j]['abono']);
	   			$totalcargo += $movimientos[$j]['cargo'];
	   			$totalabono += $movimientos[$j]['abono'];
	   		}
	   		$reporte->Ln();
	   		$reporte->lineaH();
	   		$datos['FECHA']='TOTAL';
	   		$datos['ACCION']='GENERAL';
	   		$datos['DOCUMENTO']='';
	   		$datos['REFERENCIA']='';
	   		$datos['MONEDA']='';
	   		$datos['CARGO']=$totalcargo;
	   		$datos['ABONO']=$totalabono;
	   		$datos['SALDO']=($saldoacumulado==0)?'0.00':round($saldoacumulado,2);
	   		$reporte->nuevaFila($datos);
   		}
   		$reporte->lineaH();
   		$reporte->Ln();
    }
    
    
    $reporte->Output("/acosa/ccobrar/estado_cliente.pdf", "F");
    return '<script> window.open("/acosa/ccobrar/estado_cliente.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
  }
  
  function tmlistadoReporte($resultado){
  	//$arrayResult = pg_fe
  	$listado = "<table border=0 cellpadding=2>";
  	

  	//print_r($resultado);
  	for($i=0;$i<count($resultado);$i++){
  	//foreach ($resultado as $k => $v){
  		$listado .= "<tr><td>CLIENTE</td><td colspan=9>RAZON SOCIAL</td></tr>";
  		$listado .= "<tr><td>FECHA</td><td>ACCION</td><td>DOCUMENTO</td><td>REFERENCIA</td><td>MON</td><td>CARGO</td><td>ABONO</td><td>SALDO</td></tr>";
   		$listado .= "<tr><td>".$resultado[$i]['cli_codigo']." 
  				</td><td colspan=3>".$resultado[$i]['cli_razsocial']." 
  				</td><td colspan=6 align=right> (".$resultado[$i]['suma'].")
  				</td></tr>";
   		
   		$movimientos=array();
   		$movimientos = MovClientesModel::ObtenerMovimientosdeCliente($_REQUEST['cbmoneda'],$resultado[$i]['cli_codigo'],$_REQUEST['fecinicio'],$_REQUEST['fecfin']);
   		//print_r($movimientos);
   		$detalle="";
   		$saldoacumulado = $resultado[$i]['suma'];
   		//$detalle = "<table border=1 cellpadding=2>";
   		$totalcargo = 0;
   		$totalabono = 0;
   		if ($_REQUEST['cbtipo']=='01'){
	   		for($j=0;$j<count($movimientos);$j++){
	   			$saldoacumulado = $saldoacumulado-($movimientos[$j]['cargo']-$movimientos[$j]['abono']);
	   			$detalle .= "<tr><td>".$movimientos[$j]['dt_fechamovimiento']."</td><td>".$movimientos[$j]['tipo']."</td><td>".$movimientos[$j]['numero']."</td><td>".$movimientos[$j]['ch_numdocreferencia']."</td><td>".$movimientos[$j]['mon']."</td><td align=right>".round($movimientos[$j]['cargo'],2)."</td><td align=right>".round($movimientos[$j]['abono'],2)."</td><td align=right>(".round($saldoacumulado,2).")</td></tr>";
	   			$totalcargo += $movimientos[$j]['cargo'];
	   			$totalabono += $movimientos[$j]['abono'];
	   		}
	   		//$saldoacumulado=0;
	   		$detalle .= "<tr><td colspan=9><hr/></td></tr>";
   			$detalle .= "<tr><td>TOTAL GENERAL</td><td colspan=4>&nbsp;</td><td align=right>".$totalcargo."</td><td align=right>".$totalabono."</td><td>(".round($saldoacumulado,2).")</td></tr>";
   		}else{
   			for($j=0;$j<count($movimientos);$j++){
	   			$saldoacumulado = $saldoacumulado-($movimientos[$j]['cargo']-$movimientos[$j]['abono']);
	   			//$detalle .= "<tr><td>".$movimientos[$j]['dt_fechamovimiento']."</td><td>".$movimientos[$j]['tipo']."</td><td>".$movimientos[$j]['numero']."</td><td>".$movimientos[$j]['ch_numdocreferencia']."</td><td>".$movimientos[$j]['mon']."</td><td align=right>".$movimientos[$j]['cargo']."</td><td align=right>".$movimientos[$j]['abono']."</td><td align=right>(".round($saldoacumulado,2).")</td></tr>";
	   			$totalcargo += $movimientos[$j]['cargo'];
	   			$totalabono += $movimientos[$j]['abono'];
	   		}
	   		$detalle .= "<tr><td colspan=9><hr/></td></tr>";
   			$detalle .= "<tr><td>TOTAL GENERAL</td><td colspan=4>&nbsp;</td><td align=right>".$totalcargo."</td><td align=right>".$totalabono."</td><td>(".round($saldoacumulado,2).")</td></tr>";
   		}
   		//$detalle .=	"</table>";
   		//$listado .= "<tr><td colspan=9>".$detalle."</td></tr>";
   		$listado .= $detalle."<tr><td colspan=9><hr/></td></tr>";
   	}
  	$listado .=	"</table>";
  	return $listado;
  }
  
}

