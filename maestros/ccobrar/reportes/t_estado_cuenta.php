<?php

include('../include/reportes2.inc.php');

class EstadoCuentaTemplate extends Template {

  	function titulo(){
    		$titulo = '<div align="center"><h2>REPORTE DE ESTADO DE CUENTA</h2></div><hr>';
    		return $titulo;
  	}

  	function errorResultado($errormsg){
    		return '<blink>'.$errormsg.'</blink>';
  	}

	function ReportePDF($result,$fecha) {

		$fecha_trans = $_REQUEST['busqueda']['fecha'];

		$cabecera1 = array(
					'CAMPO1'=>'',
					'CAMPO2'=>'',
					'CAMPO3'=>'',
					'IMPORTE'=>'---------IMPORTE INICIAL---------',
					'SALDO'=>'--------------SALDO--------------'
				  );

		$cabecera2 = array(
					'MONEDA'=>'MONEDA',
					'DOCUMENTO'=>'DOCUMENTO',
					'FECHAE'=>'F. EMISION', 
					'INDOLARES'=>'DOLARES',
					'INSOLES'=>'SOLES',
					'SADOLARES'=>'DOLARES',
					'SASOLES'=>'SOLES',
					'FECHAV'=>'F.VCMTO'
				  );

		$CabCli = array( 	"NOMCLI"          =>  " "  );
		$contdocumento=0;
		$fontsize = 8;
		$reporte = new CReportes2();
		$reporte->SetMargins(8, 5, 8);
		$reporte->SetFont("courier", "", $fontsize);
		
		$reporte->definirColumna('CAMPO1', $reporte->TIPO_TEXTO, 7, 'L','Cabecera1');
		$reporte->definirColumna('CAMPO2', $reporte->TIPO_TEXTO, 16, 'L','Cabecera1');
		$reporte->definirColumna('CAMPO3',$reporte->TIPO_TEXTO, 11, 'R','Cabecera1');
		$reporte->definirColumna('IMPORTE',$reporte->TIPO_TEXTO, 33, 'C','Cabecera1');
		$reporte->definirColumna('SALDO',$reporte->TIPO_TEXTO, 33, 'C','Cabecera1');
		$reporte->definirColumna('MONEDA', $reporte->TIPO_TEXTO, 7, 'L');
		$reporte->definirColumna('DOCUMENTO', $reporte->TIPO_TEXTO, 16, 'L');
		$reporte->definirColumna('FECHAE',$reporte->TIPO_TEXTO, 11, 'R');
		$reporte->definirColumna('INDOLARES',$reporte->TIPO_IMPORTE, 16, 'R');
		$reporte->definirColumna('INSOLES',$reporte->TIPO_IMPORTE, 16, 'R');
		$reporte->definirColumna('SADOLARES',$reporte->TIPO_IMPORTE, 16, 'R');
		$reporte->definirColumna('SASOLES',$reporte->TIPO_IMPORTE, 16, 'R');
		$reporte->definirColumna('FECHAV',$reporte->TIPO_TEXTO, 12, 'R');

		$reporte->definirColumna("NOMCLI", $reporte->TIPO_TEXTO, 100, "L", "CLI");

		$reporte->definirCabecera(1, "L", "OPENSOFT");
		$reporte->definirCabecera(1, "C", "ESTADO DE CUENTA AL ".$fecha_trans);
		$reporte->definirCabecera(1, "R", "PAG.    %p");
		$reporte->definirCabecera(2, "R", " ");
		$reporte->definirCabecera(3, "R", "%f");
		$reporte->definirCabecera(4, "L", ' ');
		
		$reporte->definirCabeceraPredeterminada($cabecera1,'Cabecera1');
		$reporte->definirCabeceraPredeterminada($cabecera2);
		$reporte->AddPage();
		$reporte->Ln();

		$contdocumento = 0;
		$conta = 0;
						
		for($i=0; $i<count($result); $i++) {

			if ($result[$i-1]['CLIENTE'] != $result[$i]['CLIENTE']){

				$reporte->Ln();
				$codigo = trim($result[$i]['CLIENTE']). ' ' .trim($result[$i]['RAZONSOCIAL']);
				$arr = array("NOMCLI"=>$codigo);
				$reporte->nuevaFila($arr, "CLI"); 		
				$reporte->Ln();
				$reporte->lineaH();
				$reporte->Ln();

			}

					$datos["MONEDA"] 	 = $result[$i]["MONEDA"];
					$datos["DOCUMENTO"] 	 = $result[$i]["TIPODOCUMENTO"];
					$datos["SASOLES"] 	 = $result[$i]["SALDO"];
					$datos["INSOLES"] 	 = $result[$i]["IMPORTEINICIAL"];


					if($datos["DOCUMENTO"] == '10'){

						$datos["DOCUMENTO"] = 'FACT '.trim($result[$i]['SERIEDOCUMENTO']).'-'.trim($result[$i]['NUMDOCUMENTO']);

						if($result[$i]["MONEDA"] == '01')
							$datos["MONEDA"] = 'S/.';
						else
							$datos["MONEDA"] = 'US$';

						if($result[$i]["MONEDA"] == '01')
							$subtotalsoles += $datos["SASOLES"];
						else
							$subtotaldolares += $datos["SASOLES"];

						$datos['FECHAE'] 	= $result[$i]['FECHAEMISION'];
						$datos['FECHAV'] 	= $result[$i]['FECHAVENCIMIENTO'];

						$contdocumento ++;

						$reporte->nuevaFila($datos);

					}elseif($datos["DOCUMENTO"] == '11'){
						$datos["DOCUMENTO"] = 'N/DB '.trim($result[$i]['SERIEDOCUMENTO']).'-'.trim($result[$i]['NUMDOCUMENTO']);

							if($result[$i]["MONEDA"] == '01')
								$datos["MONEDA"] = 'S/.';
							else
								$datos["MONEDA"] = 'US$';

							if ($result[$i]["MONEDA"] == '01')
								$subtotalsoles += $datos["SASOLES"];
							else
								$subtotaldolares += $datos["SASOLES"];

						$datos['FECHAE'] 	= $result[$i]['FECHAEMISION'];
						$datos['FECHAV'] 	= $result[$i]['FECHAVENCIMIENTO'];
												
						$contdocumento ++;

						$reporte->nuevaFila($datos);
					}elseif($datos["DOCUMENTO"] == '20'){
						$datos["DOCUMENTO"] = 'N/CR '.trim($result[$i]['SERIEDOCUMENTO']).'-'.trim($result[$i]['NUMDOCUMENTO']);

							if($result[$i]["MONEDA"] == '01')
								$datos["MONEDA"] = 'S/.';
							else
								$datos["MONEDA"] = 'US$';

							if ($result[$i]["MONEDA"] == '01')
								$subtotalsoles += $datos["SASOLES"];
							else
								$subtotaldolares += $datos["SASOLES"];

						$datos['FECHAE'] 	= $result[$i]['FECHAEMISION'];
						$datos['FECHAV'] 	= $result[$i]['FECHAVENCIMIENTO'];
												
						$contdocumento ++;

						$reporte->nuevaFila($datos);
					}elseif($datos["DOCUMENTO"] == '21'){
						$datos["DOCUMENTO"] = 'ANT '.trim($result[$i]['SERIEDOCUMENTO']).'-'.trim($result[$i]['NUMDOCUMENTO']);

							if($result[$i]["MONEDA"] == '01')
								$datos["MONEDA"] = 'S/.';
							else
								$datos["MONEDA"] = 'US$';

							if ($result[$i]["MONEDA"] == '01')
								$subtotalsoles += $datos["SASOLES"];
							else
								$subtotaldolares += $datos["SASOLES"];

						$datos['FECHAE'] 	= $result[$i]['FECHAEMISION'];
						$datos['FECHAV'] 	= $result[$i]['FECHAVENCIMIENTO'];
												
						$contdocumento ++;

						$reporte->nuevaFila($datos);
					}elseif($datos["DOCUMENTO"] == '22'){
						$datos["DOCUMENTO"] = 'RESU '.trim($result[$i]['SERIEDOCUMENTO']).'-'.trim($result[$i]['NUMDOCUMENTO']);

							if($result[$i]["MONEDA"] == '01')
								$datos["MONEDA"] = 'S/.';
							else
								$datos["MONEDA"] = 'US$';

							if ($result[$i]["MONEDA"] == '01')
								$subtotalsoles += $datos["SASOLES"];
							else
								$subtotaldolares += $datos["SASOLES"];

						$datos['FECHAE'] 	= $result[$i]['FECHAEMISION'];
						$datos['FECHAV'] 	= $result[$i]['FECHAVENCIMIENTO'];
												
						$contdocumento ++;

						$reporte->nuevaFila($datos);
					}

					//totales por tipo de documento

					if($result[$i]['TIPODOCUMENTO'] != $result[$i+1]['TIPODOCUMENTO']){	

							$totales_doc['DOCUMENTO'] = 'TOTAL: '.$contdocumento.' '.$datos['DOCUMENTO'];
							$reporte->Ln();
							$reporte->nuevaFila($totales_doc);

						$totales_cliente['DOCUMENTO'] = "TOTAL SALDO:";
						$totales_cliente['SASOLES'] = -$subtotalsoles;
						$reporte->Ln();
						$reporte->nuevaFila($totales_cliente);
						$reporte->Ln();	
						$reporte->lineaH();
						$reporte->Ln();
						//$subtotalsoles = 0;
						$contdocumento = 0;
					}
				
					//totales por cada cliente 

					if($result[$i]['CLIENTE'] != $result[$i+1]['CLIENTE']){	

						$totales_cliente['DOCUMENTO'] = "TOTAL CLIENTE:";
						$totales_cliente['SASOLES'] = $subtotalsoles;
						$reporte->Ln();
						$subtotalsoles = 0;
						$contdocumento = 0;
					}

		}
		//Fin de foreach
			
		$reporte->Output("/sistemaweb/ccobrar/estado_cuenta_fecha.pdf", "F");
		return '<script>window.open("/sistemaweb/ccobrar/estado_cuenta_fecha.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}

  // Solo Formularios y otros
	function formBuscar() {
		$fecha = @$_REQUEST['busqueda']['fecha'];
		$combo_opcion = @$_REQUEST['busqueda']['combo'];
		$codi_cli = @$_REQUEST['busqueda']['codigo'];
		//$radio_detalle = @$_REQUEST['busqueda']['radio_det'];
		//$radio_resumen = @$_REQUEST['busqueda']['radio_res'];
		$fecha = date("d/m/Y");
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.ESTADOCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'ESTADOCUENTA'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha]', @$fecha));
		//$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[combo]', @$combo_opcion));
		//$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[codigo]', @$codi_cli));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('fecha','Busqueda hasta  :', $_REQUEST['busqueda']['fecha']?@$_REQUEST["busqueda"]["fecha"]:@$fecha, espacios(3), 11, 11));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif" border=0></a>&nbsp;&nbsp;'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><br>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('busqueda[combo]','Tipo de Busqueda',$_REQUEST['busqueda']['combo'],array('01'=>'Todos','02'=>'Por Cliente'),espacios(3), array("onChange"=>"display_cod_cliente(this);")));

			if($_REQUEST['busqueda']['combo']=='01' || $_REQUEST['busqueda']['combo']=='')
				$estilo = "display:none";
			else
				$estilo = "display:inline";

		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','', $_REQUEST['busqueda']['codigo'], espacios(5), 20, 18,array("class"=>"form_input_numeric", "style" =>$estilo)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br><br>'));
		//AQUI VAN LOS RADIOS
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(0)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
		return $form->getForm();
	}
}

