<?php
//load libraries
include "../valida_sess.php";
include_once('/sistemaweb/include/dbsqlca.php');
//include_once('/sistemaweb/include/reportes2.inc.php');
include_once('/sistemaweb/include/fpdf.php');
//include_once('/sistemaweb/include/NumberToLetterConverter.class.php');

//define global variables

/*$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

class reporte_lv extends FPDF
{
	protected $B = 0;
	protected $I = 0;
	protected $U = 0;
	protected $HREF = '';

	protected $valorBruto = 0;
	protected $impuesto = 0;
	protected $valorTotal = 0;

	function printText($text) {
		return utf8_decode($text);
	}

	function cabecera($data) {
		$result = $this->getDataCabecera($data);
		$resutl_ = $this->getDataEmpresa();

		//titulo
		$this->SetFont('Arial', 'B', 10);
		$this->Cell(10,10,$resutl_[0],0,0,'L');
		$this->Cell(135,10,'FACTURA ELECTRONICA',0,0,'R');
		$this->Ln(5);
		$this->Cell(10,10,'RUC: '.$resutl_[1],0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,$resutl_[2],0,0,'L');
		$this->Cell(135,10,$data['serie'].' - '.$data['documento'],0,0,'R');
		$this->SetFont('Arial', '', 10);
		$this->Ln(8);

		$this->Cell(10,10,'Fecha: '.$result[2],0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,'Moneda: '.$result[4],0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,'RUC Cliente: '.$result[1],0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,'Razon Social: '.$result[5],0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,$this->printText('Dirrección: '.$result[6]),0,0,'L');
		$this->Ln(8);

		//$result[10] = '00000007*F001*35';//deberia estar comentado
		$dataDoc = explode("*", $result[10]);
		$serieDoc = $dataDoc[1];
		$numDoc = $dataDoc[0];
		$typeDoc = $dataDoc[2];
		$doc = $serieDoc.' - '.$numDoc;
		$this->Cell(10,10,'Referencia: '.$doc,0,0,'L');
		$this->Ln(5);
		$this->Cell(10,10,'Obs: ',0,0,'L'.$result[11]);

		$this->valorBruto = $result[7];
		$this->impuesto = $result[8];
		$this->valorTotal = $result[9];
	}

	function detalle($data) {
		$w = array(20,60,25,25,30,30);
		$this->SetFont('Arial', 'B', 10);
		foreach($data[0] as $key => $col) {
			$this->Cell($w[$key],7,$this->printText($col),1);
		}
		$this->SetFont('Arial', '', 10);
		$this->Ln();
		$results = $this->getDetalleFactura($data[1]);
		foreach ($results as $key => $result) {
			//$this->Cell($w[$key],7,$this->printText($result),1);
			$this->Cell($w[0],6,$this->printText($result[0]),1);
			$this->Cell($w[1],6,$this->printText($result[1]),1);
			$this->Cell($w[2],6,$this->printText($result[2]),1);
			$this->Cell($w[3],6,$this->printText($result[3]),1);
			$this->Cell($w[4],6,$this->printText($result[4]),1);
			$this->Cell($w[5],6,$this->printText($result[5]),1);			
			$this->Ln();
		}
	}

	function pie($data) {
		//$result = $this->getDataPie($data);
		$this->Cell(10,10,'SON: '.$this->MontoMonetarioEnLetras($this->valorTotal,'SOLES'),0,0,'L');
		$this->Ln(10);
		$this->Cell(110,10,'OPERACIONES GRAVADAS: ',0,0,'R');
		$this->Ln(5);
		$this->Cell(110,10,'I.G.V. (18.00%): '.$this->impuesto,0,0,'R');
		$this->Ln(5);
		$this->SetFont('Arial', 'B', 10);
		$this->Cell(110,10,'IMPORTE TOTAL: '.$this->valorTotal,0,0,'R');
		$this->SetFont('Arial', '', 10);
		$this->Ln(10);
		$this->Cell(10,10,'DESCARGAR EL DOCUMENTO ELECTRONICO EN: https://consulta.efacturas.pe/ocs',0,0,'L');
		//$this->writeHTML(110,10,'DESCARGAR EL DOCUMENTO ELECTRONICO EN: <a>https://consulta.efacturas.pe/ocs</a>',0,0,'C');
	}

	function getDataEmpresa() {
		global $sqlca;
		$sql = "
			SELECT
				p1.par_valor,
				p2.par_valor,
				p3.par_valor
			FROM
				int_parametros p1,
				int_parametros p2,
				int_parametros p3
			WHERE
				p1.par_nombre = 'razsocial'
				AND p2.par_nombre = 'ruc'
				AND p3.par_nombre = 'dires';
		";
		if($sqlca->query($sql) < 0) {
            return null;
        } else {
            return $sqlca->fetchRow();
        }
        //1: razon social, 2: ruc, 3: direccion
	}

	function getDataCabecera($data) {
		global $sqlca;
        $sql = "SELECT
    complemento.ch_fac_tipodocumento,
    complemento.cli_codigo,
    complemento.dt_fac_fecha,
    cabecera.ch_fac_moneda,
    moneda.tab_descripcion,
    clientes.cli_razsocial,
    clientes.cli_direccion,
    cabecera.nu_fac_valorbruto,
	cabecera.nu_fac_impuesto1,
	cabecera.nu_fac_valortotal,
	complemento.ch_fac_observacion2,--ref
	''--11: observacion
FROM
    fac_ta_factura_complemento complemento
JOIN fac_ta_factura_cabecera cabecera ON complemento.ch_fac_seriedocumento = cabecera.ch_fac_seriedocumento
AND complemento.ch_fac_numerodocumento = cabecera.ch_fac_numerodocumento
JOIN int_tabla_general AS moneda ON (
    cabecera.ch_fac_moneda = (
        SUBSTRING (
            TRIM (moneda.tab_elemento) FOR 2
            FROM
                LENGTH (TRIM(moneda.tab_elemento)) - 1
        )
    )
    AND moneda.tab_tabla = '04'
    AND moneda.tab_elemento != '000000'
)
JOIN int_clientes clientes ON complemento.cli_codigo = clientes.cli_codigo
WHERE
    complemento.ch_fac_seriedocumento = '".$data['serie']."'
AND complemento.ch_fac_numerodocumento = '".$data['documento']."';";

        if($sqlca->query($sql) < 0) {
            return null;
        } else {
            return $sqlca->fetchRow();
        }
	}

	function getDetalleFactura($data) {
        global $sqlca;
        $result = array();
        $sql = "SELECT articulo.art_codigo, articulo.art_descripcion,articulo.art_unidad, detalle.nu_fac_cantidad, detalle.nu_fac_precio, detalle.nu_fac_valortotal from fac_ta_factura_detalle detalle JOIN int_articulos articulo on detalle.art_codigo = articulo.art_codigo where detalle.ch_fac_seriedocumento = '".$data['serie']."' and detalle.ch_fac_numerodocumento = '".$data['documento']."';";
        if($sqlca->query($sql) > 0) {
            while ($reg = $sqlca->fetchRow()) {
                $result[] = array($reg[0], $reg[1], $reg[2], $reg[3], $reg[4], $reg[5], $reg[6], $reg[7], $reg[8]);
            }
        }
		return $result;
    }

    function getDataPie($data) {
		global $sqlca;
        $sql = "SELECT
    cabecera.nu_fac_valorbruto,
	cabecera.nu_fac_impuesto1,
	cabecera.nu_fac_valortotal
FROM
    fac_ta_factura_cabecera cabecera
WHERE
    cabecera.ch_fac_seriedocumento = '".$data['serie']."'
AND cabecera.ch_fac_numerodocumento = '".$data['documento']."';";

        if($sqlca->query($sql) < 0) {
            return null;
        } else {
            return $sqlca->fetchRow();
        }
	}

	function NumerosALetras($monto){
	    $maximo				= pow(10,9);
		$unidad				= array(1=>"UNO", 2=>"DOS", 3=>"TRES", 4=>"CUATRO", 5=>"CINCO", 6=>"SEIS", 7=>"SIETE", 8=>"OCHO", 9=>"NUEVE" );
		$decena				= array(10=>"DIEZ", 11=>"ONCE", 12=>"DOCE", 13=>"TRECE", 14=>"CATORCE", 15=>"QUINCE", 20=>"VEINTE", 30=>"TREINTA", 40=>"CUARENTA", 50=>"CINCUENTA", 60=>"SESENTA", 70=>"SETENTA", 80=>"OCHENTA", 90=>"NOVENTA");
		$prefijoDecena		= array(10=>"DIECI", 20=>"VEINTI", 30=>"TREINTA Y ", 40=>"CUARENTA Y ", 50=>"CINCUENTA Y ", 60=>"SESENTA Y ", 70=>"SETENTA Y ", 80=>"OCHENTA Y ", 90=>"NOVENTA Y ");
		$centena			= array(100=>"CIEN", 200=>"DOSCIENTOS", 300=>"TRESCIENTOS", 400=>"CUATROCIENTOS", 500=>"QUINIENTOS", 600=>"SEISCIENTOS", 700=>"SETECIENTOS", 800=>"OCHOCIENTOS", 900=>"NOVECIENTOS");	
		$prefijoCentena		= array(100=>"CIENTO ", 200=>"DOSCIENTOS ", 300=>"TRESCIENTOS ", 400=>"CUATROCIENTOS ", 500=>"QUINIENTOS ", 600=>"SEISCIENTOS ", 700=>"SETECIENTOS ", 800=>"OCHOCIENTOS ", 900=>"NOVECIENTOS ");
		$sufijoMiles		= "MIL";
		$sufijoMillon		= "UN MILLON";
		$sufijoMillones		= "MILLONES";
		$base         		= strlen(strval($monto));
		$pren         		= intval(floor($monto/pow(10,$base-1)));
		$prencentena  		= intval(floor($monto/pow(10,3)));
		$prenmillar   		= intval(floor($monto/pow(10,6)));
		$resto        		= $monto%pow(10,$base-1);
		$restocentena 		= $monto%pow(10,3);
		$restomillar  		= $monto%pow(10,6);
		
		if (!$monto) return "";
		
	    if (is_int($monto) && $monto > 0 && $monto < abs($maximo)) {
			switch ($base) {
				case 1: return $unidad[$monto]; 
				case 2: return array_key_exists($monto, $decena)  ? $decena[$monto]  : $prefijoDecena[$pren*10]   . $this->NumerosALetras($resto);
				case 3: return array_key_exists($monto, $centena) ? $centena[$monto] : $prefijoCentena[$pren*100] . $this->NumerosALetras($resto);
				case 4: case 5: case 6: return ($prencentena>1) ? $this->NumerosALetras($prencentena). " ". $sufijoMiles . " " . $this->NumerosALetras($restocentena) : $sufijoMiles. " " . $this->NumerosALetras($restocentena);
				case 7: case 8: case 9: return ($prenmillar>1)  ? $this->NumerosALetras($prenmillar). " ". $sufijoMillones . " " . $this->NumerosALetras($restomillar)  : $sufijoMillon. " " . $this->NumerosALetras($restomillar);
			}
	    } else return false;
	}

	function MontoMonetarioEnLetras($monto,$text) {
		$monto = str_replace(',', '', $monto);
		$pos = strpos($monto, '.');
			
		if ($pos == false) {
			$monto_entero = $monto;
			$monto_decimal = '00';
		} else {
			$monto_entero = substr($monto,0,$pos);
			$monto_decimal = substr($monto,$pos,strlen($monto)-$pos);
			$monto_decimal = $monto_decimal * 100;
		}
		$monto = (int)($monto_entero);
		$texto_con = " Y $monto_decimal/100 ".$text;
		//echo NumerosALetras($monto).$texto_con;
		return $this->NumerosALetras($monto).$texto_con;
	}



	// Tabla simple
	function BasicTable($header, $data)
	{
	    // Cabecera
	    foreach($header as $col)
	        $this->Cell(40,7,$col,1);
	    $this->Ln();
	    // Datos
	    foreach($data as $row)
	    {
	        foreach($row as $col)
	            $this->Cell(40,6,$col,1);
	        $this->Ln();
	    }
	}

	function WriteHTML($html)
	{
	    // Intérprete de HTML
	    $html = str_replace("\n",' ',$html);
	    $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	    foreach($a as $i=>$e)
	    {
	        if($i%2==0)
	        {
	            // Text
	            if($this->HREF)
	                $this->PutLink($this->HREF,$e);
	            else
	                $this->Write(5,$e);
	        }
	        else
	        {
	            // Etiqueta
	            if($e[0]=='/')
	                $this->CloseTag(strtoupper(substr($e,1)));
	            else
	            {
	                // Extraer atributos
	                $a2 = explode(' ',$e);
	                $tag = strtoupper(array_shift($a2));
	                $attr = array();
	                foreach($a2 as $v)
	                {
	                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
	                        $attr[strtoupper($a3[1])] = $a3[2];
	                }
	                $this->OpenTag($tag,$attr);
	            }
	        }
	    }
	}

	function OpenTag($tag, $attr)
	{
	    // Etiqueta de apertura
	    if($tag=='B' || $tag=='I' || $tag=='U')
	        $this->SetStyle($tag,true);
	    if($tag=='A')
	        $this->HREF = $attr['HREF'];
	    if($tag=='BR')
	        $this->Ln(5);
	}

	function CloseTag($tag)
	{
	    // Etiqueta de cierre
	    if($tag=='B' || $tag=='I' || $tag=='U')
	        $this->SetStyle($tag,false);
	    if($tag=='A')
	        $this->HREF = '';
	}

	function SetStyle($tag, $enable)
	{
	    // Modificar estilo y escoger la fuente correspondiente
	    $this->$tag += ($enable ? 1 : -1);
	    $style = '';
	    foreach(array('B', 'I', 'U') as $s)
	    {
	        if($this->$s>0)
	            $style .= $s;
	    }
	    $this->SetFont('',$style);
	}

	function PutLink($URL, $txt)
	{
	    // Escribir un hiper-enlace
	    $this->SetTextColor(0,0,255);
	    $this->SetStyle('U',true);
	    $this->Write(5,$txt,$URL);
	    $this->SetStyle('U',false);
	    $this->SetTextColor(0);
	}
}


$pdf = new reporte_lv();

$html = $pdf->printText('Ahora puede imprimir fácilmente texto mezclando diferentes estilos: <b>negrita</b>, <i>itálica</i>,
<u>subrayado</u>, o ¡ <b><i><u>todos a la vez</u></i></b>!<br><br>También puede incluir enlaces en el
texto, como <a href="http://www.fpdf.org">www.fpdf.org</a>, o en una imagen: pulse en el logotipo.');

$pdf->SetFont('Arial','',10);
$pdf->AddPage();
$params = array('serie' => '001', 'documento' => '0001010');
$pdf->cabecera($params);
$pdf->Ln(10);
$header = array('Código', 'Descripción', 'Unidad', 'Cantidad', 'Precio Unitario', 'Importe');
// Carga de datos
$pdf->detalle(array($header,$params));
$pdf->Ln(10);
$pdf->pie($params);*/



/*// Primera página
$pdf->AddPage();
$pdf->SetFont('Arial','',20);
$pdf->Write(5,$pdf->printText('Para saber qué hay de nuevo en este tutorial, pulse '));
$pdf->SetFont('','U');
$link = $pdf->AddLink();
$pdf->Write(5,$pdf->printText('aquí'),$link);
$pdf->SetFont('');
// Segunda página
$pdf->AddPage();
$pdf->SetLink($link);
//$pdf->Image('logo.png',10,12,30,0,'','http://www.fpdf.org');
$pdf->SetLeftMargin(45);
$pdf->SetFontSize(14);
$pdf->WriteHTML($html);*/


/*// Títulos de las columnas
$header = array('País', 'Capital', 'Superficie (km2)', 'Pobl. (en miles)');
// Carga de datos
$data = array(array('Peru', 'Lima', '', ''), array('Inglaterra', 'Londres', '',''));
$pdf->BasicTable($header,$data);*/
/*$pdf->AddPage();
$pdf->ImprovedTable($header,$data);
$pdf->AddPage();
$pdf->FancyTable($header,$data);*/

//$pdf->Output();

class ConductPDF extends FPDF {
function vcell($c_width,$c_height,$x_axis,$text){
	$w_w=$c_height/3;
	$w_w_1=$w_w+2;
	$w_w1=$w_w+$w_w+$w_w+3;
	$len=strlen($text);// check the length of the cell and splits the text into 7 character each and saves in a array 
	if($len>7){
		$w_text=str_split($text,7);
		$this->SetX($x_axis);
		$this->Cell($c_width,$w_w_1,$w_text[0],'','','');
		$this->SetX($x_axis);
		$this->Cell($c_width,$w_w1,$w_text[1],'','','');
		$this->SetX($x_axis);
		$this->Cell($c_width,$c_height,'','LTRB',0,'L',0);
	}
	else{
	    $this->SetX($x_axis);
	    $this->Cell($c_width,$c_height,$text,'LTRB',0,'L',0);}
	}
}

$pdf = new ConductPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',16);
$pdf->Ln();
$x_axis=$pdf->getx();
$c_width=20;// cell width 
$c_height=6;// cell height
$text="aim success ";// content 
$pdf->vcell($c_width,$c_height,$x_axis,'Hi1');// pass all values inside the cell 
$x_axis=$pdf->getx();// now get current pdf x axis value
$pdf->vcell($c_width,$c_height,$x_axis,'Hi2');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,'Hi3');
$pdf->Ln();
$x_axis=$pdf->getx();
$c_width=20;
$c_height=12;
$text="aim success ";
$pdf->vcell($c_width,$c_height,$x_axis,'Hi4');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,'Hi5(xtra) 72326 434');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,'Hi5');
$pdf->Ln();
$x_axis=$pdf->getx();
$c_width=20;
$c_height=12;
$text="All the best";
$pdf->vcell($c_width,$c_height,$x_axis,'Hai');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,'VICKY');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,$text);
$pdf->Ln();
$x_axis=$pdf->getx();
$c_width=20;
$c_height=6;
$text="Good";
$pdf->vcell($c_width,$c_height,$x_axis,'Hai');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,'vignesh');
$x_axis=$pdf->getx();
$pdf->vcell($c_width,$c_height,$x_axis,$text);
$pdf->Output();
