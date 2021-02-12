<?php

class TarjetasMagneticasTemplate extends Template {
 
	var $bandera_num_tarjeta;
	var $bandera_num_placa;

	function titulo(){
		$titulo = '<div align="center"><h2>Tarjetas Magneticas</h2></div><hr>';
		return $titulo;
	}

	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}

	function TemplateReportePDF($reporte_array) {
		//print_r($reporte_array);
		$datos = array();
		$Cabecera = array(
	    	"NUM_TARJETA"  		=> "NUM_TARJETA",
			"PLACA"  			=> "PLACA",
	    	"USUARIO"      		=> "USUARIO",
	    	"BLOQUEADA"   		=> "BLOQUEADA",
	    	"LIMITE_GALONES" 	=> "LIMITE_GALONES",
	    	"LIMITE_IMPORTE" 	=> "LIMITE_IMPORTE",
	    	"PERIODO"    		=> "PERIODO"
	    );

		//$Totales_new = array_merge_recursive($Totales, $Totales2);
		//print_r($Totales_new);
		
		$fontsize = 7;
		$reporte = new CReportes2();
		$reporte->SetMargins(5, 5, 5);
		$reporte->SetFont("courier", "", $fontsize);
	
		$reporte->definirColumna("NUM_TARJETA", $reporte->TIPO_TEXTO, 20, "L");
		$reporte->definirColumna("PLACA", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("USUARIO", $reporte->TIPO_TEXTO, 35, "L");
		$reporte->definirColumna("BLOQUEADA",$reporte->TIPO_TEXTO,15,"L");
		$reporte->definirColumna("LIMITE_GALONES", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("LIMITE_IMPORTE", $reporte->TIPO_TEXTO, 15, "L");
		$reporte->definirColumna("PERIODO", $reporte->TIPO_TEXTO, 15, "L");
		
    	foreach($reporte_array as $llave => $valores) {
	      	/*foreach($valores as $key => $value)
	      	{*/
  			//echo "llave : $llave => valor : ".$valores['cli_codigo']."\n";
			if ($ch_cliente!=$valores['cod_cliente']){
				/*$total['TOTAL_VENTA']="TOTAL TARJETAS";
				$reporte->lineaH();
				$reporte->nuevaFila($total);
				*/
				$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
				$reporte->definirCabecera(1, "C", "REPORTE DE TARJETAS MAGNETICAS");
				$reporte->definirCabecera(1, "R", "PAG.%p");
				$reporte->definirCabecera(2, "C", " ");
				$reporte->definirCabecera(3, "R", "%f");
				$reporte->definirCabecera(4, "L", "CODIGO DE CLIENTE : ".$valores['cod_cliente']);
				$reporte->definirCabecera(4, "C", "RAZON SOCIAL : ".$valores['razon_social']);
				$reporte->definirCabecera(4, "R", " ");
				$reporte->definirCabeceraPredeterminada($Cabecera);
				$reporte->AddPage();
				$reporte->lineaH();				
			}

			$datos['NUM_TARJETA']    = $valores['num_tarjeta'];
			$datos['PLACA']     		= $valores['placa'];
			$datos['USUARIO']        = $valores['usuario'];
			$datos['BLOQUEADA']	     = $valores['bloqueada'];
			$datos['LIMITE_GALONES']     = $valores['nu_limite_galones'];
			$datos['LIMITE_IMPORTE']  = $valores['nu_limite_importe'];
			
			 print_r($valores['periodo']);
			
			if ( trim($valores['periodo'])=="M"){
				$datos['PERIODO']   = "MENSUAL";
			}
			else if ( trim($valores['periodo'])=="S") {
				$datos['PERIODO']   = "SEMANAL";
			}
			else{
				$datos['PERIODO']   = "";
			}
			
      			//}
		      	//$reporte->Ln();
		      	//$reporte->lineaH();
      			$reporte->nuevaFila($datos);
						
			$ch_cliente=$valores['cod_cliente'];
			
			
   		}
    		//print_r($Totales);
	
    		$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf", "F");
    		
		return '<center><iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf" width="1000" height="500"></iframe></center>';
			//return '<script> window.open("/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf","miwin","width=750,height=550,scrollbars=yes, resizable=yes, menubar=no");</script>';
	}	
	  
	function listado($registros){
		$titulo_grid = "<br>";

//		$columnas = array('COD. TARJETA','COD. CLIENTE', 'RAZON SOCIAL','COD. USUARIO', 'COD. PLACA', 'BLOQ.','T','C. LIM.','LIM. IMP.','LIM. GAL.','IMP. ACUM.','GAL. ACUM.','ESTACION');
		$columnas = array('COD. CLIENTE', 'RAZON SOCIAL','COD. TARJETA', 'COD. PLACA', 'COD. USUARIO', 'BLOQ.','ESTACION');

		$listado = '<div id="resultados_grid" class="grid" align="center"><br>
				      <table style = "border:0.5px">
				      <caption class="grid_title">'.$titulo_grid.'</caption>
				      <thead class="grid_cabecera" align="center" valign="center">
				      <tr class="grid_cabecera">
		';

		for ($i = 0; $i < count($columnas); $i++)
			$listado .= '<th class="grid_cabecera"> '.strtoupper($columnas[$i]).'</th>';

    	$listado .= '<th class="grid_cabecera">'.espacios(10).'</th></tr><tbody class="grid_body" style="height:20px;">';
		
		$a=0;

    	foreach($registros as $reg) {
      		$listado .= '<tr class="grid_row" '.resaltar('#CFD8B4','#F0F5DD').'>';
      		$regCod = $reg["numtar"];

			$color = ($a%2==0?"grid_detalle_par":"grid_detalle_impar");
			$a++;

		   	for ($i=0; $i < count($columnas); $i++)
		    	$listado .= '<td class="' . $color . '" style="font-size:0.6em;">'.$reg[$i].'</td>';

  			$listado .= '<td class="' . $color . '" align="center"><A href="control.php?rqst=MAESTROS.TARJMAG&task=TARJMAG'.'&action=Modificar&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" border="0"/></A></td>';
  			$listado .= '</tr>';
		}

   		$listado .= '</tbody></table></div>';
   		return $listado;
  	}

	function formBuscar($paginacion, $datos){

		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TARJMAG'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARJMAG'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rxp', @$_REQUEST['rxp']));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pagina', @$_REQUEST['pagina']));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center><table border ="0"><tr><td align="center">'));
		///$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Buscar por:', '', espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Buscar por: ', @$datos['codigo'], espacios(2), 20, 18));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Buscar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Agregar"><img src="/sistemaweb/icons/gadd.png" align="right" />Agregar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Importar"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Tarjetas</button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="submit" value="Total"><img src="/sistemaweb/images/excel_icon.png" alt="left" /> Total</button>'));
	
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td colspan="2" align="center"><br>'));

		$form->addGroup("GRUPO_PAGINA", "Paginacion");
 
		if ($paginacion['paginas'] === 'P')
			$paginacion['paginas'] = '0';

    	$form->addElement("GRUPO_PAGINA", new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
		$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistrosTarjetasMagneticas('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
	   	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistrosTarjetasMagneticas('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistrosTarjetasMagneticas('".$paginacion['pp']."',this.value)")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistrosTarjetasMagneticas('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    	$form->addElement("GRUPO_PAGINA", new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistrosTarjetasMagneticas('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
		$form->addElement("GRUPO_PAGINA", new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistrosTarjetasMagneticas(this.value,'".$paginacion['primera_pagina']."')")));

    	return $form->getForm();
  	}

	function ImportarDataExcel() {

		$form = new form2('Lista de Placas', 'form_listacompras', FORM_METHOD_POST, 'control.php', '', 'control','enctype="multipart/form-data"');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden("rqst", "MAESTROS.TARJMAG"));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARJMAG'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/><table border="0" align="center">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="descargarFormatoExcel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Descargar Formato Excel </button>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(' Seleccionar archivo Excel: <input type="file" name="ubica" id="ubica" size="70" onClick="Mostrar();">'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td><div id="ver" style="display:none;">'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Importar Lista Excel"><img src="/sistemaweb/icons/gexcel.png" align="right" />Importar Lista Excel</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</div>'));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br/>'));
	
		return $form->getForm();

    	}

	function MostrarDataExcel($data, $filename) {

		$result			= '';
		$cliente 		= '';
		$codcliente 	= '';
		$nomcliente 	= '';
		$codtar 		= '';
		$color 			= '';

//		$codcliente 	= $data->val(1, 2); cai
		$arrCeldasNombres = $data->sheets[0]['cells'];
		$arrCeldas = $data->sheets[0]['cellsInfo'];
		$codcliente	= $arrCeldas[1][2]['raw'];

		if( $codcliente == '' || $codcliente == NULL ){
			$codcliente = $data->sheets[0]['cells'][1][2];
		}

		$color			= 'black';

		$cliente 		= TarjetasMagneticasModel::ValidarExcel($codcliente, NULL);

		$exitecliente	= $cliente[0][0];
		$nomcliente		= $cliente[0][1];
		$codtar			= $cliente[0][2];
		$codtar 		= trim($codtar);

		if($exitecliente == '1')
			$color		= 'black';
		else
			$color		= 'red';

		$result .= '<table align="center"> ';

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Codigo Cliente: </th>';

		if($nomcliente){
			$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $codcliente;
		}else{
			$result .= '<th colspan="7" align="left" style="color:'.$color.'; color:red;background-color: #D9F9B2">';
			$result .= 'INEXISTENTE: '. $codcliente;
		}

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Razon Social: </th>';

		if($nomcliente){
			$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
			$result .= $nomcliente;
		}else{
			$result .= '<th colspan="7" align="left" style="color:'.$color.'; color:red;background-color: #D9F9B2">';
			$result .= 'INEXISTENTE: '. $nomcliente;
		}

		$result .= '<tr>';
		$result .= '<th align="right" style="font-size:0.7em; color:black;background-color: #D9F9B2">Cod. Tarjeta Magnetica: </th>';

		if($codtar){

			/* VALIDACIONES CODIGO TARJETA MAGNETICA */
			if(strlen($codtar) < 3){
				echo "<script>alert('Error: Codigo Tarjeta Magnetica debe tener 3 digitos');</script>";
				$color = 'red';
				$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
				$result .= 'ERROR: '. $codtar;
			} else {
				$result .= '<th colspan="7" align="left" style="color:'.$color.';background-color: #D9F9B2">';
				$result .= $codtar;
			}

		}else{
			$result .= '<th colspan="7" align="left" style="color:'.$color.'; color:red;background-color: #D9F9B2">';
			$result .= 'INEXISTENTE: '. $codtar;
		}

		$result .= '<tr>';
		$result .= '<th colspan="8" style="font-size:0.7em; color:black;background-color: #D9F9B2">&nbsp;</td>';
		$result .= '</tr>';

		$result .= '<tr>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">NRO. TARJETA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">NRO. PLACA</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">NOM. USUARIO</td>';
		$result .= '<th style="font-size:0.7em; color:black;background-color: #D9F9B2">ESTADO</td>';
		$result .= '</tr>';

		$resultados 	= count($data->sheets[0]['cells']);
		$codigoexcel	= '';
		$a 				= 0;
		$z 				= 0;

		if($exitecliente == '1'){

			if(strlen($data->sheets[0]['cells'][4][1]) > 0 && strlen($data->sheets[0]['cells'][4][2]) > 0){

				for ($i = 4; $i <= ($resultados + 1); $i++) {

					$placa		= $data->sheets[0]['cells'][$i][1];
					$usuario	= $data->sheets[0]['cells'][$i][2];

					$color = ($i%2==0?"grid_detalle_par":"grid_detalle_impar");

					if (strlen($placa) > 0 && strlen($usuario) > 0){

						//VALIDAR ARCHIVO EXCEL PLACAS QUE EXISTAN B.D
						$datos	= TarjetasMagneticasModel::validarExcel($codcliente, $placa);
						//var_dump($datos);
						$codtar	= TarjetasMagneticasModel::CodigoTarjetaMagnetica($codcliente);
						$codtar = trim($codtar);

						if($codigoexcel == $placa){

							$colorletra	= "red";
							$status		= "DUPLICADO";

						} elseif($datos[0]['codplaca'] == NULL){

							$a++;//CANTIDAD DE PRODUCTOS INSERTADOS

							$maxtar	= TarjetasMagneticasModel::ObtenerTarjetaMagnetica($codtar);

							if (is_null($maxtar)){

								if(strlen($a) == 1)
									$correlativo = "00".$a;
								elseif(strlen($a) == 2)
									$correlativo = "0".$a;
								else
									$correlativo = $a;

								$tarjeta = "7055".$codtar.$correlativo;

							}else{

								settype($maxtar, int);

								if($maxtar > $z)
									$z = $maxtar;

								$z++;

								if (substr($maxtar,0,4)=='7056'){
					    				$tarjeta = "EXCEDIO_LIMITE";
					    			}else{

									if(strlen($z) == 1)
										$correlativo = "00".$z;
									elseif(strlen($z) == 2)
										$correlativo = "0".$z;
									else
										$correlativo = $z;

						    			$tarjeta = '7055'.$codtar.$correlativo;
								}


							}

							$colorletra	= "blue";
							$status 	= "NUEVO";

							if(strlen($codtar) >= 3 && strlen($codtar) <= 3)
								$procesar 	= true;

						} else {

							$colorletra 	= "red";
							$status 	= "EXISTE";

						}

						$result .= '<tr bgcolor="">';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($tarjeta) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($placa) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($usuario) . '</td>';
						$result .= '<td class="'.$color.'" align = "center"><p style="color:'.$colorletra.';">' . htmlentities($status) . '</td>';
						$result .= '</tr>';

						$codigoexcel = $placa;
				
					}

				}

			} else {

				$result .= '<tr bgcolor="">';
				$result .= '<td colspan="4" align="center" class="'.$color.'" ><p style="font-size:12px; color:red;">No hay informacion</td>';
				$result .= '</tr>';
				$procesar = false;

			}

			$result .= '<tr bgcolor="F3FAF5">';
			$result .= '<td colspan="4" align="center">&nbsp;</td>';
			$result .= '</tr>';

			if($procesar){
				$result .= '<tr bgcolor="F3FAF5">';
				$result .= '<td colspan="4" align="right">';
				$result .= '<A href="control.php?rqst=MAESTROS.TARJMAG&task=TARJMAG&action=Actualizar&filename='.$filename.'&codcliente='.$codcliente.'" target="control"><button><img src="/sistemaweb/icons/importar_excel.png" align="right" />Procesar Excel </button></A></td>';
				$result .= '</tr>';
			} else {

				$result .= '<tr bgcolor="F3FAF5">';
				$result .= '<td colspan="4" align="right">';
				$result .= '<button name="action" type="button" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>';
				$result .= '</tr>';
			}

		}

		$result .= '</table>';

		return $result;

    	}

  	function formSegres(){
    		$form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TARJMAG'));
    		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARJMAG'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','TEXTO',espacios(3)));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','PDF',espacios(3)));
    		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('boton','Regresar',espacios(0)));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="resultados_grid" class="grid" align="center"></div>'));
		
    		return $form->getForm();
 	}

  	function SegresPDF($datos){
  		$columnas = array('SERVICIO'=>'SERVICIO',
					  'PLACA'=>'PLACA',
					  'USUARIO'=>'USUARIO',
					  'TARJETA'=>'TARJETA',
					  'VENCIMIENTO'=>'VENCIMIENTO',
					  'BANDA'=>'BANDA');
		$fontsize = 8;
    		$reporte = new CReportes2();
    		$reporte->SetMargins(5, 5, 5);
    		$reporte->SetFont("courier", "", $fontsize);
    		$reporte->definirCabecera(1, "L", "OFICINA CENTRAL");
    		$reporte->definirCabecera(1, "C", "REPORTE DE TARJETAS MAGNETICAS SEGRES");
    		$reporte->definirCabecera(2, "C", "");
    		$reporte->definirColumna("SERVICIO", $reporte->TIPO_TEXTO, 20, "L");
    		$reporte->definirColumna("PLACA", $reporte->TIPO_TEXTO, 9, "L");
    		$reporte->definirColumna("USUARIO",$reporte->TIPO_TEXTO, 26, "L");
    		$reporte->definirColumna("TARJETA",$reporte->TIPO_TEXTO, 11, "C");
    		$reporte->definirColumna("VENCIMIENTO",$reporte->TIPO_TEXTO, 6, "L");
    		$reporte->definirColumna("BANDA",$reporte->TIPO_TEXTO, 22, "L");
	
		//$reporte->definirCabecera(3, "L", "CODIGO DE CLIENTE : ".$codigo);
		//$reporte->definirCabecera(3, "C", "RAZON SOCIAL : ".$raz_social);
		$reporte->AddPage();
		print_r($datos);
   
		//$reporte->lineaH();
    		$datos2=array();
    		
    		for($j=0;$j<count($datos['datos']);$j++){
    			$datos2['SERVICIO']=$datos['datos'][$j]['servicio'];
    			$datos2['PLACA']=$datos['datos'][$j]['placa'];
    			$datos2['USUARIO']=$datos['datos'][$j]['usuario'];
    			$datos2['TARJETA']=$datos['datos'][$j]['tarjeta'];
    			$datos2['VENCIMIENTO']=$datos['datos'][$j]['vence'];
    		$datos2['BANDA']=$datos['datos'][$j]['banda'];
		$reporte->nuevaFila($datos2);
		$reporte->Ln();
	}
	$reporte->Output("/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf", "F");
	return '<center><iframe src="/sistemaweb/ventas_clientes/reportes/pdf/reporte_tarj_magneticas_cli.pdf" width="1000" height="500"></iframe></center>';
 
  }
  
  function setBandera_num_tarjeta($valor){
      $TarjetasMagneticasTemplate->bandera_num_tarjeta=$valor;
  }
  
  function setBandera_num_placa($valor){
      $TarjetasMagneticasTemplate->bandera_num_placa=$valor;
  }

  function formTarjetasMagneticas($tarjeta){
 
    //print_r($tarjeta);
      
    $siNo = array('N'=>'No',
                  'S'=>'Si');
                
    $TipoDespacho = array('C' => 'Combustible',
                          'M' => 'Market', 
                          '' => 'Ambos');

    $ConLimite = array(
    	'D'=>'Diario',
	    'S'=>'Semanal',
		'M'=>'Mensual'
	);
    
    $Estado       = array('1'=>'EMITIDA',
                           ''=>'NO EMITIDA');
                          
    $Dias = array(
		0 => 'Domingo',
		1 => 'Lunes',
		2 => 'Martes',
		3 => 'Miercoles',
		4 => 'Jueves',
		5 => 'Viernes',
		6 => 'Sabado'
	);

   $mensual = array();
   for($i = 1; $i <= 28; $i++){  
     $mensual[str_pad(trim($i),2,'0', STR_PAD_LEFT)]=str_pad(trim($i),2,'0', STR_PAD_LEFT);
	}

	   if(trim($tarjeta["ch_tipo_periodo_acumular"])=='M'){
	      $ValoresMD = $mensual;
	   }else{
	      $ValoresMD = $Dias;
	   }
     
    $form = new form2('', 'form_tarjetamagnetica', FORM_METHOD_POST, 'control.php', '', 'control','onSubmit="return validar_registro_tarjetas();"');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TARJMAG'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TARJMAG'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$tarjeta["numtar"]));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="0" cellpadding="0"> <tr><td bgcolor="#FFFFCD">'));
    
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('DATOS DE TARJETA</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[codcli]','C&oacute;digo de Cliente </td><td>: ', @$tarjeta["codcli"], '', 13, 12, array("onKeyUp"=>"getRegistro(this.value);this.value=this.value.toUpperCase();", "class"=>"form_input_numeric"),($_REQUEST['action']=='Modificar'?array('disabled'):array())));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(''.espacios(2).'<div id="desc_cliente" style="display:inline;">'.@$tarjeta["cli_razsocial"].'</div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[codcue]','N&uacute;mero de Cuenta</td><td>: ', @$tarjeta["codcue"], '', 13, 12, array("class"=>"form_input_numeric"),array("readonly")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));
    $array_activacion=(!$TarjetasMagneticasTemplate->bandera_num_tarjeta)?array():array("readonly");
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[numtar]','N&uacute;mero de Tarjeta Local </td><td>: ', @$tarjeta["numtar"], '', 11, 10, array("onChange"=>"javascript:checkNuevaTarjeta(this)", "class"=>"form_input_numeric"),$array_activacion));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;<div id="MensajeValidacion" style="display:inline;" class="MsgError"></div>'));
    if ($_REQUEST['action']=='Modificar') $arvales = array('disabled'); else $arvales=array();
    
    if ( substr($tarjeta['numtar'],7,3) == '000' && $_REQUEST['action'] == 'Modificar') {
    	array_push($arvales,'checked');	
    	$arrvisiblelimites = 'display:none';
    } else {
    	$arrvisiblelimites = 'display:inline';
    }

    $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox('vales', ' asignar vales ', 'S', '', array('onclick'=>'cambiar_vales();'),$arvales));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[nomusu]','Usuario </td><td>: ', @$tarjeta["nomusu"], '', 25, 25));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));


	if ( $_REQUEST['action'] == 'Modificar' ) {
		$array_activacion2=(!$TarjetasMagneticasTemplate->bandera_num_placa)?1:2;
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('validar', @$array_activacion2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[numpla]','Placa Vehiculo </td><td>: ', @$tarjeta["numpla"], '', 9, 8,array('onKeyUp'=>'this.value=this.value.toUpperCase();'),""));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
    	$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('<tr><td colspan="2"><div><b>&nbsp;</b></div>'));
	}else{
		$array_activacion2=(!$TarjetasMagneticasTemplate->bandera_num_placa)?1:2;
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('validar', @$array_activacion2));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[numpla]','Placa Vehiculo </td><td>: ', @$tarjeta["numpla"], '', 9, 8,array('onChange'=>'javascript:checkNuevaPlaca(this)', 'onKeyUp'=>'this.value=this.value.toUpperCase();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr>'));
    	$form->addElement(FORM_GROUP_MAIN,new form_element_anytext('<tr><td colspan="2"><div id="MensajeValidacion2"><b>&nbsp;</b></div>'));
	}

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[ventar]','Vencimiento </td><td>: ', @$tarjeta["ventar"], '', 6, 5));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[estblo]','T. Bloqueada </td><td>: ', trim(@$tarjeta["estblo"]), $siNo, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<hr>'));
    if($tarjeta["numtar"]){
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
       	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Grupo Tarj. Shell</td><td>: '.@$tarjeta["cli_grupo"].''));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
       	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Fecha de Registro</td><td>: '.@$tarjeta["to_char"].''));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
       	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Estado</td><td>: '.@$Estado[$tarjeta["estarj"]].''));
    }
    if ($_REQUEST['action']=='Modificar'){
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[segres]','Genero a SEGRES </td><td>: ', trim(@$tarjeta["segres"]), $siNo, espacios(3)));    	
    }
       
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    // Fin Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top" width="25">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td bgcolor="#FFFFCD" valign="top">'));
    //Inicio Contenido TD 2
    
   		$arrayDeshabilitaimporte=array('style'=>$arrvisiblelimites);
    

    	$arrayDeshabilitagalon=array('style'=>$arrvisiblelimites);
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="2"> <tr><td colspan="2" align="center" class="form_td_title">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('RESTRICCIONES DE DESPACHO</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[ch_almacen]','Estacion </td><td>: ', trim(@$tarjeta["ch_almacen"]), TarjetasMagneticasModel::getEstaciones(), espacios(25)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td style="display:none;">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[ch_tipo_producto]','Solo para </td><td style="display:none;">: ', trim(@$tarjeta["ch_tipo_producto"]), $TipoDespacho, espacios(25),array('onchange'=>'ocultar_market(this.value);')));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[nu_limite_galones]','Limite de Galones</td><td>: ', @$tarjeta["nu_limite_galones"], '', 11, 10, array("id"=>"nu_limite_galones", "onKeyUp"=>"javascript:bloquea(this,document.getElementById('nu_limite_importe'))","class"=>"form_input_numeric","style"=>$arrvisiblelimites),$arrayDeshabilitagalon));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('tarjeta[nu_limite_importe]','Limite de Importe </td><td>: ', @$tarjeta["nu_limite_importe"], '', 11, 10, array("id"=>"nu_limite_importe", "onKeyUp"=>"javascript:bloquea(this,document.getElementById('nu_limite_galones'))","class"=>"form_input_numeric","style"=>$arrvisiblelimites),$arrayDeshabilitaimporte));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    if ($_REQUEST['action']=='Agregar' || $_REQUEST['action']=='Guardar') $arraydisplay = 'display:none';
    else $arraydisplay ='display:inline';
    if ($_REQUEST['action']=='Modificar' && $tarjeta['nu_limite_galones']==$tarjeta['nu_limite_importe']) $arraydisplay = 'display:none';
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[ch_tipo_periodo_acumular]','Control Limite </td><td>: ', trim(@$tarjeta["ch_tipo_periodo_acumular"]), $ConLimite, espacios(3),array("onChange"=>"javascript:cambiarDias(this)","style"=>$arraydisplay)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[ch_dia_de_corte]','Dia de Semana o Mes de Corte </td><td>: ', @$tarjeta["ch_dia_de_corte"], $ValoresMD, espacios(3),array("id"=>"ch_dia_de_corte","style"=>$arraydisplay)));
    

    $css_display_name_days = 'style="display: none;"';
    $css_display_select_name_days = "display: none;";
	if ( $_REQUEST['action'] == "Modificar" && $tarjeta["ch_tipo_periodo_acumular"] == "S" ) {
		$css_display_name_days = 'style="display: block;"';
		$css_display_select_name_days = "display: block;";
	}

    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('tarjeta[ch_dia_de_corte]','<span id="span-sNameDays" '.$css_display_name_days.'>Día de Semana </td><td>', @$tarjeta["ch_dia_de_corte"], $ValoresMD, espacios(3),array("id"=>"ch_dia_de_corte","style"=>$css_display_select_name_days)));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="2" height="68" valign="bottom">'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<hr>'));
    if($tarjeta["numtar"]){
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td style="display:none;">'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Galones Acumulado Anterior</td><td style="display:none;">: '.@$tarjeta["nu_ant_galones_acumulados"].''));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td style="display:none;">'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Galones Acumulado al Perido</td><td style="display:none;">: '.@$tarjeta["nu_galones_acumulados"].''));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Importe Acumulado Anterior</td><td>: '.@$tarjeta["nu_ant_importe_acumulado"].''));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));
    	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Importe Acumulado al Perido</td><td>: '.@$tarjeta["nu_importe_acumulado"].''));
    }
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    //Fin Contenido TD 2
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td colspan="3" align="center" height="30"><br>'));
//    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Guardar', espacios(15)));

		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button name="action" type="submit" value="Guardar"><img src="/sistemaweb/icons/gadd.png" align="right" />Guardar</button>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;&nbsp;<button name="action" type="button" value="Regresar" onclick="regresar()"><img src="/sistemaweb/icons/atra.gif" align="right" />Regresar</button>'));
		//$form->addElement(FORM_GROUP_MAIN, new f2element_button('button','Regresar', espacios(2),array('onclick'=>'regresar();')));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    
		return $form->getForm().'<div id="error_body" align="center"></div><hr>';

	}

  function setRegistros($codigo, $buscar_todos = 0){
	error_log($codigo);
	if($buscar_todos == 0){
		$RegistrosCB = TarjetasMagneticasModel::ClientesCBArray("cli_codigo like '%".pg_escape_string(TRIM($codigo))."%'");
	}else{
		$RegistrosCB = TarjetasMagneticasModel::ClientesCBArray("cli_codigo = '".pg_escape_string(TRIM($codigo))."'");
	}
    //$RegistrosCB = TarjetasMagneticasModel::ClientesCBArray("cli_codigo = '".pg_escape_string(TRIM($codigo))."'");
    $result = '<blink><span class="MsgError">Error: Cliente no existe</span></blink>'." <script language=\"javascript\">top.setRegistro('','NO_EXISTE','');</script>";
    if (count($RegistrosCB) == 1) {
      foreach($RegistrosCB as $codcli => $descricli){
      	/*TARJETA MAGNETICA*/
      	$tarjeta = TarjetasMagneticasModel::TarjetasMagneticas(TRIM($codcli));
      	print_r('tarjetas'.$tarjeta);
        $result = $descricli." <script language=\"javascript\">top.setRegistro('".trim($codcli)."','".trim($tarjeta)."','".substr($tarjeta,4,3)."');</script>";
      }
    }
    if (count($RegistrosCB) > 1){
      $att_opt = array();
      foreach($RegistrosCB as $codcli => $descricli){
        $att_opt[trim($codcli)] = array("onclick"=>"getRegistro('".trim($codcli)."', '1');");
      }
      $cb = new f2element_combo('cbClientes', '','', $RegistrosCB,'',array("size"=>"5"), array(), $att_opt);
      $result = '<div align="left" style="position: absolute; width:250px;  z-index:1000;">'.$cb->getTag().'</div>';
    }
    if ($codigo==''){
    	$result='';
    }
    return $result;
  }

  		function reporteExcel($res) {
		$workbook = new Workbook("tarjetas_magneticas.xls");
		$formato0 = & $workbook->add_format();
		$formato1 = & $workbook->add_format();
		$formato2 = & $workbook->add_format();
		$formato3 = & $workbook->add_format();
		$formato4 = & $workbook->add_format();
		$formato5 = & $workbook->add_format();

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato1->set_top(1);
		$formato1->set_left(1);
		$formato1->set_border(0);
		$formato1->set_bold(1);
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato3->set_num_format(2);
		$formato4->set_num_format(2);
		$formato4->set_bold(1);
		$formato5->set_size(11);
		$formato5->set_align('left');


		$worksheet1 = & $workbook->add_worksheet('Hoja de Resultados Items');
		$worksheet1->set_column(0, 0, 15);
		$worksheet1->set_column(1, 1, 40);
		$worksheet1->set_column(2, 2, 20);
		$worksheet1->set_column(3, 3, 20);
		$worksheet1->set_column(4, 4, 10);
		$worksheet1->set_column(5, 5, 10);


		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);

		$worksheet1->write_string(1, 0, "TARJETAS MAGNETICAS", $formato0);
		$worksheet1->write_string(2, 0, " ", $formato0);

		$a = 3;

		$worksheet1->write_string($a, 0, "COD. CLIENTE", $formato2);
		$worksheet1->write_string($a, 1, "RAZON SOCIAL", $formato2);
		$worksheet1->write_string($a, 2, "NUMERO TARJETA", $formato2);
		$worksheet1->write_string($a, 3, "NUMERO PLACA", $formato2);
		$worksheet1->write_string($a, 4, "ESTADO", $formato2);
		$worksheet1->write_string($a, 5, "SUCURSAL", $formato2);
		$a++;

		for ($j = 0; $j < count($res); $j++) {

	    	$worksheet1->write_string($a, 0, $res[$j]['codcli'], $formato5);
	 		$worksheet1->write_string($a, 1, $res[$j]['cli_razsocial'], $formato5);
	    	$worksheet1->write_string($a, 2, $res[$j]['numtar'], $formato5);
	    	$worksheet1->write_string($a, 3, $res[$j]['numpla'], $formato5);
	    	$worksheet1->write_string($a, 4, $res[$j]['estblo'], $formato5);
	    	$worksheet1->write_number($a, 5, $res[$j]['ch_nombre_breve_sucursal'], $formato5);
			$a++;
		}
		$workbook->close();
        header("Location: /sistemaweb/ventas_clientes/tarjetas_magneticas.xls");
	}



	/*function gridViewEXCEL($arrResponseTarjetasMagneticas){
		$chrFileName = "";

		$workbook = new Workbook($chrFileName);
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();

		//titulo
		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('center');

		//Sub titulo
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato2->set_bottom(1);
		$formato2->set_top(1);
		$formato2->set_right(1);
		$formato2->set_left(1);

		//Data
		$formato5->set_size(10);
		$formato5->set_align('left');

		$worksheet1 =& $workbook->add_worksheet('TarjetasMagneticas');

		$worksheet1->set_column(0, 0, 20);//COD. CLIENTE
		$worksheet1->set_column(1, 1, 80);//RAZON SOCIAL
		$worksheet1->set_column(2, 2, 40);//NUMERO DE TARJETA
		$worksheet1->set_column(3, 3, 15);//NUMERO DE PLACA
		$worksheet1->set_column(4, 4, 80);//ESTADO BLOQUEO
		$worksheet1->set_column(5, 5, 15);//NOMBRE BREVE SUCURSAL

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		
		/*$worksheet1->write_string(1, 0, "TARJETAS MAGNETICAS",$formato0);

		/*$fila = 3;
		$worksheet1->write_string($fila, 0, "COD. CLIENTE",$formato2);
		$worksheet1->write_string($fila, 1, "RAZON SOCIAL",$formato2);
		$worksheet1->write_string($fila, 2, "NUMERO DE TARJETA",$formato2);
		$worksheet1->write_string($fila, 3, "NUMERO DE PLACA",$formato2);
		$worksheet1->write_string($fila, 4, "ESTADO BLOQUEO",$formato2);
		$worksheet1->write_string($fila, 5, "NOMBRE BREVE SUCURSAL",$formato2);
		
		/*$fila = 4;
		for ($i=0; $i<count($arrResponseTarjetasMagneticas); $i++){
			$worksheet1->write_string($fila, 0, $arrResponseTarjetasMagneticas[$i]['codcli'],$formato5);
			$worksheet1->write_string($fila, 1, $arrResponseTarjetasMagneticas[$i]['cli.cli_razsocial'],$formato5);
			$worksheet1->write_string($fila, 2, $arrResponseTarjetasMagneticas[$i]['numtar'],$formato5);
			$worksheet1->write_string($fila, 3, $arrResponseTarjetasMagneticas[$i]['numpla'],$formato5);
			$worksheet1->write_string($fila, 4, $arrResponseTarjetasMagneticas[$i]['estblo'],$formato5);
			$worksheet1->write_string($fila, 5, $arrResponseTarjetasMagneticas[$i]['s.ch_nombre_breve_sucursal'],$formato5);
			$fila++;
		}

		$workbook->close();	
		$chrFileName = "TarjetasMagneticas";
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename = " . $chrFileName . ".xls");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header("Pragma: public");
	}*/
}