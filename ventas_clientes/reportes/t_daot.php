<?php

class DaotTemplate extends Template {
 
	function Titulo(){
		$titulo = '<div align="center"><h2>Generar DAOT</h2></div><hr>';
		return $titulo;
	}
	
	function errorResultado($errormsg){
		return '<blink>'.$errormsg.'</blink>';
	}

	// Solo Formularios y otros

	function formBuscar($datos){	
		$form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.DAOT'));
		$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DAOT'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('anio','A&#209;O :', @$datos['anio'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('base','MONTO BASE :', @$datos['base'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_text ('ruc','RUC (opcional):', @$datos['ruc'], espacios(2), 20, 18, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
		$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Descargar DAOT',espacios(3)));
		return $form->getForm();
	}

	function hipervinculoDaot($anio, $ruc){
		$hipervinculo.='<p align="center"><a href="/sistemaweb/ventas_clientes/daot/daot' . $anio . 'detalle.csv">DAOT detallado</a></p>';
		if(strlen(trim($ruc)) == '11')
			$hipervinculo.='<p align="center"><a href="/sistemaweb/ventas_clientes/daot/daot' . $anio . 'detalleruc.csv">DAOT Detalle por RUC</a></p>';
		$hipervinculo.='<p align="center"><a href="/sistemaweb/ventas_clientes/daot/reporteDaotxRuc.pdf">PDF Reporte DAOT por RUC</a></p>';
		$hipervinculo.='<p align="center"><a href="/sistemaweb/ventas_clientes/daot/daot' . $anio . 'resumen.csv">DAOT resumido</a></p>';
		$hipervinculo.='<p align="center"><a href="/sistemaweb/ventas_clientes/daot/daot' . $anio . 'resumenfiltrado.csv">DAOT resumido filtrado</a></p>';
        return $hipervinculo;
	}

	function listado($resultados)
	{
	
		$result = '<p align="center">';
		$result .= '<table border="1">';
		$result .= '<tr>';
		$result .= '<th>RUC</th>';
		$result .= '<th>Razon Social</th>';
		$result .= '<th>Venta</th>';
		$result .= '<th>IGV</th>';
		$result .= '<th>Total</th>';
		$result .= '</tr>';
	
		for ($i = 0; $i < count($resultados); $i++) {
		$a = $resultados[$i];

		$result .= '<tr>';

		$result .= '<td>' . htmlentities($a['ruc']) . '</td>';
		$result .= '<td>' . htmlentities($a['razsocial']) . '</td>';
		$result .= '<td><p align="right">' . htmlentities($a['vventa']) . '</p></td>';
		$result .= '<td><p align="right">' . htmlentities($a['igv']) . '</p></td>';
		$result .= '<td><p align="right">' . htmlentities($a['total']) . '</p></td>';
		$result .= '</tr>';
		}
		$result .= '</p>';
		return $result;
	}

	function reportePDF($daotxruc, $cabecera) {

		$cab_items = Array(
			"ruc"		=>	"RUC",
			"razsocial"	=>	"Razon Social",
			"caja"		=>	"Caja",
			"trans"		=>	"No. Doc.",
			"dia"		=>	"Dia",
			"vventa"	=>	"Valor Venta",
			"igv"		=>	"IGV",
			"total"		=>	"Total"

		);

		$reporte = new CReportes2("P","pt","A4");
		$reporte->definirCabecera(1, "C", $cabecera);
		$reporte->definirCabecera(2, "R", "Pagina %p");
		$reporte->definirCabecera(2, "L", "Fecha: ".date("d/m/Y"));
		$reporte->definirCabecera(3, "C", "DAOT - Detalle por Cliente");

		$reporte->SetMargins(10,10,10);
		$reporte->SetFont("courier", "", 9.5);

		$reporte->definirColumna("caja", $reporte->TIPO_ENTERO, 5, "L");
		$reporte->definirColumna("trans", $reporte->TIPO_TEXTO, 10, "R");
		$reporte->definirColumna("dia", $reporte->TIPO_TEXTO, 10, "L");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 15, "R");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 15, "R"); 
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 15, "R");

		$reporte->definirColumna("totales", $reporte->TIPO_TEXTO, 27, "R", "_totales", "B");
		$reporte->definirColumna("vventa", $reporte->TIPO_IMPORTE, 15, "R", "_totales");
		$reporte->definirColumna("igv", $reporte->TIPO_IMPORTE, 15, "R", "_totales"); 
		$reporte->definirColumna("total", $reporte->TIPO_IMPORTE, 15, "R", "_totales");

		$reporte->definirCabeceraPredeterminada($cab_items);


		foreach ($daotxruc['ruc'] as $ruc) {
			$reporte->definirCabecera(4, "C", "RUC: " . $ruc['ruc'] . " - Razon Social: " . $ruc['razsocial']);
			$reporte->addPage();

			foreach($ruc['documentos'] as $item)
				$reporte->nuevaFila($item);
			$reporte->lineaH();
			$ruc['totales']['totales'] = "Total";
			$reporte->nuevaFila($ruc['totales'], "_totales");
		}

		$reporte->Output("/sistemaweb/ventas_clientes/daot/reporteDaotxRuc.pdf","F");

	}
}

