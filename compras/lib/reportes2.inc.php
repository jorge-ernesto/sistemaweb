<?php
include 'lib/fpdf.php';

class CReportes2 extends FPDF
{
    /*
     * Array que contiene los distintos tipos de filas
     */
    var $templates = Array();
    
    var $cabecera = Array();
    
    var $cabeceraImagen = Array();
    
    var $cabeceraSize = Array();
    
    /*
     * Tipos de columnas
     */
    var $TIPO_TEXTO = 0;		/* ningun tipo de procesamiento */
    var $TIPO_IMPORTE = 1;		/* comas como separadores de miles, punto decimal, 2 digitos decimales */
    var $TIPO_CANTIDAD = 2;		/* punto decimal, 3 digitos decimales */
    var $TIPO_COSTO = 3;		/* punto decimal, 4 digitos decimales */

    var $separador = " ";		/* Caracter que separa las columnas */

    var $cab_default = Array();

    function __construct($orientation="P", $unit="pt", $format="A4")
    {
		parent::__construct($orientation, $unit, $format);
    }

    function definirColumna($nombre, $tipo, $ancho, $align, $template="_default", $estilo="")
    {
	$this->templates[$template][$nombre]['tipo'] = $tipo;
	$this->templates[$template][$nombre]['ancho'] = $ancho;
	$this->templates[$template][$nombre]['align'] = $align;
	$this->templates[$template]['estilo'] = $estilo;
    }

    function definirCabecera($linea, $align, $texto)
    {
	$this->cabecera[$linea][$align] = $texto;
    }
    
    function definirCabeceraSize($linea, $align, $style, $texto)
    {
	$this->cabeceraSize[$linea][$align][$style] = $texto;
    }

    function definirCabeceraImagen($lini, $lfin, $url, $alto, $ancho)
    {
	$this->cabeceraImagen['linea_ini'] = $lini;
	$this->cabeceraImagen['linea_fin'] = $lfin;
	$this->cabeceraImagen['url'] = $url;
	$this->cabeceraImagen['alto'] = $alto;
	$this->cabeceraImagen['ancho'] = $ancho;
    }
    
    function definirCabeceraPredeterminada($valores, $template="_default")
    {
	$this->cab_default[$template] = $valores;
    }
    
    function nuevaFila($valores=Array(), $template="_default", $bCabecera=false)
    {
	$texto = $this->formatColumn($valores, $template, $bCabecera);
	
	$old_FontStyle = $this->FontStyle;
	$old_FontSizePt = $this->FontSizePt;
	
	$this->SetFont($this->FontFamily, $this->templates[$template]['estilo'], $this->FontSizePt);
	$this->Cell(0, $this->FontSizePt, $texto, 0, 1);
	$this->SetFont($this->FontFamily, $old_FontStyle, $old_FontSizePt);
    }
    
    function SumGetX($texto)
    {
      $GetX    = $this->GetX();
      $GetSW   = $this->GetStringWidth($texto);
      return ($GetX + $GetSW);
    }
    
    function Lnew()
    {
      return $this->SetY($this->GetY()+12);
    }
    
    function formatColumn($valores, $template="_default", $bCabecera=false)
    {
	$result = "";
	foreach($this->templates[$template] as $nombre=>$config) {
	    if ($nombre == "estilo") continue;
	    if (!$bCabecera) {
		switch ($config['tipo']) {
		    case $this->TIPO_IMPORTE:
			if ($valores[$nombre] != "") $value = number_format($valores[$nombre], 2, ".", ",");
			else $value = "";
			break;
		    case $this->TIPO_CANTIDAD:
			if ($valores[$nombre] != "") $value = number_format($valores[$nombre], 3, ".", "");
			else $value = "";
			break;
		    case $this->TIPO_COSTO:
			if ($valores[$nombre] != "") $value = number_format($valores[$nombre], 4, ".", ",");
			else $value = "";
			break;
		    default:
			$value = $valores[$nombre];
			break;
		}
	    }
	    else $value = $valores[$nombre];

	    $value = substr($value, 0, $config['ancho']);
	    
	    switch ($config['align']) {
		case "L":
		    $pad = STR_PAD_RIGHT;
		    break;
		case "C":
		    $pad = STR_PAD_BOTH;
		    break;
		case "R":
		    $pad = STR_PAD_LEFT;
		    break;
		default:
		    echo "align no valido en $nombre de $template: " . $config['align'];
		    $pad = STR_PAD_LEFT;
		    break;
	    }
	    
	    $result .= str_pad($value, $config['ancho'], " ", $pad) . $this->separador;
	}
	
	return $result;
    }

    function Header()
    {

	$old_FontStyle = $this->FontStyle;
	$old_FontSizePt = $this->FontSizePt;

	$this->SetFont("", "", $this->FontSizePt);
         if($this->cabeceraImagen)
         {
	       $temp = $this->ParseHeaderImagen($this->cabeceraImagen['url']);
               $this->Image($temp, $this->GetX(), $this->GetY(), $this->cabeceraImagen['alto'], $this->cabeceraImagen['ancho']);

               for($i=$this->cabeceraImagen['linea_ini']; $i<=$this->cabeceraImagen['linea_fin']; $i++)
               {
		  $x = $this->GetX();
		  $y = $this->GetY();
		  $temp = $this->ParseHeaderString(" ");
		  $this->SetX($x);
		  $this->SetY($y);
		  $this->Cell(0, $this->FontSize, $temp, 0, 0, "R"); 
		  $this->Ln();
               }
         }
         
	foreach($this->cabeceraSize as $i => $cabecera) 
	{
	    $x = $this->GetX();
	    $y = $this->GetY();
            //$this->SetFont('Courier','B','15');
	    foreach($cabecera as $align => $cabecera2) 
	    {
	     foreach($cabecera2 as $style => $texto) 
	     {
	        if($style !=" ")
	        {
		  $font = explode(',', $style);
		  $family = $font[0];
		  $estilo = $font[1];
		  if($estilo == "N")
		  {
		    $estilo = "";
		  }
		  //$font[0]!="N"?$style=" ":$style=$font[0];
		  $size = $font[2];
		  $this->SetFont("".$family."","".$estilo."","".$size."");
	        }
		$temp = $this->ParseHeaderString($texto);
		$this->SetX($x);
		$this->SetY($y);
		$this->Cell(0, $this->FontSize, $temp, 0, 0, $align);
	     }
	    }
	    $this->Ln();
	}	
	
	$this->SetFont("", "", $old_FontSizePt);
	
	foreach($this->cabecera as $i => $cabecera) {
	    $x = $this->GetX();
	    $y = $this->GetY();

	    foreach($cabecera as $align => $texto) {	    
		$temp = $this->ParseHeaderString($texto);
		$this->SetX($x);
		$this->SetY($y);
		$this->Cell(0, $this->FontSize, $temp, 0, 0, $align);
	    }	    
	    $this->Ln();
	}

	
	if (count($this->cab_default) > 0) $this->lineaH();

	foreach($this->cab_default as $template => $valores) {	
	    $this->nuevaFila($valores, $template, true);
	}

	if (count($this->cab_default) > 0) $this->lineaH();
	$this->SetFont("", $old_FontStyle, $old_FontSizePt);
	
    }
    
    function ParseHeaderString($string)
    {
	$str = str_replace("%p", $this->PageNo(), $string);
	$str = str_replace("%f", date("d/m/Y"), $str);
	return $str;
    }
    
    function ParseHeaderImagen($imagen)
    {
	return $imagen;
    }    
    
    function lineaH()
    {
	$this->Line($this->lMargin, $this->GetY(), $this->w-$this->rMargin, $this->GetY());
    }
}

