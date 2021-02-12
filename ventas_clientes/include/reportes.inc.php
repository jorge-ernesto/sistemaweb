<?php
/*
 * Framework para reportes en forma de lista
 * Escrito por Sergio Aguayo, Abril 2006
 */

require_once("fpdf.php");

class CReportes extends FPDF {
    /**
     * @internal
     * Variables internas, no tocar de preferencia.
     */
    var $nColumnas = 0;		// Cantidad de columnas en el reporte
    var $aColumnas;		// Array que contiene las columnas y sus opciones/formatos
    var $aCut;			// Array que contiene TRUE si debe cortar la cadena, o FALSE si no debe hacerlo.
    var $pdf;			// Puntero a la clase de FPDF
    var $cabecera = "";		// Cache de la cabecera (cabecera de las columnas)
    var $custCab;		// Header del informe, definido por el usuario
    var $fontsize = 10;		// Tamanho de la fuente utilizada
    var $nFilas = 0;		// Cantidad de filas (data)
    var $fila = 0;		// Fila actual
    var $aData;			// Array que contiene los datos del informe

    /**
     * Especifica que la columna sea una simple cadena. No se hace ningun tipo de transformacion
     * en ella. Solo se recorta el tramanho de ser necesario.
     */
    var $TIPO_STRING = 0;
    
    /**
     * Especifica que el valor se trata de un entero. Si contiene decimales, se eliminan.
     */
    var $TIPO_INTEGER = 1;
    
    /**
     * Especifica que el valor se trata de un decimal.
     */
    var $TIPO_FLOAT = 2;

    /**
     * Inicializa la instancia actual de la clase.
     *
     * 	@param $columnas	la cantidad de columnas que debe tener el informe.
     *	@param $orientation	orientacion del papel: L=echado, P=de pie
     *	@param $fontsize	el tamanho de la fuenta en puntos (0.35mm).
     *	@param $papersize	tamano del papel. Puede ser:
     *				A3
     *				A4
     *				A5
     *				Legal
     *				Letter
     *				o un array de 2 elementos que contengan el ancho y el
     *				alto del papel expresados en puntos.
     */    
    function __construct($columnas=1, $orientation="L", $fontsize=10, $papersize="A4")
    {
	$this->pdf = new FPDF($orientation, "pt", $papersize);
	$this->nColumnas = $columnas;
	$this->fontsize = $fontsize;
	$this->pdf->SetFont("Courier", "", $this->fontsize);
    }

    /**
     * Reinicializa la instancia para la generacion de un nuevo informe en el PDF
     * actual. Deben volver a definirse las cabeceras y las columnas.
     *	@param $columnas	La cantidad de columnas que debe tener el informe.
     */    
    function nuevoInforme($columnas=1)
    {
	$this->nColumnas = $columnas;
	$this->aColumnas = Array();
	$this->cabecera = "";
	$this->custCab = Array();
	$this->nFilas = 0;
	$this->fila = 0;
	$this->aData = Array();
    }

    /**
     * Configura la columna especificada.
     *	@param $num	El numero de columna a configurar. Empieza desde cero.
     *	@param $label	La etiqueta (texto) que debe tener la columna en la parte superior.
     *	@param $tipo	El tipo de datos de la columna. Esto influye en el formato que se
     *			le da a la columna.
     *	@param $ancho	El ancho en caracteres de la columna.
     *	@param $estilo	Especifica la alineacion del texto: "C"=Centro, "R"=Derecha, "L"=izquierda
     */
    function definirColumna ($num=0, $label="", $tipo=0, $ancho=10, $estilo="C")
    {
	if ($num >= $this->nColumnas) return;	// columna invalida
	if (!is_numeric($num) || !is_numeric($ancho)) return;
	
	$this->aColumnas[$num]['etiqueta'] = $label;
	$this->aColumnas[$num]['tipo'] = $tipo;
	$this->aColumnas[$num]['ancho'] = $ancho;
	
	if ($estilo == 'R' || $estilo == 'r') $this->aColumnas[$num]['estilo'] = STR_PAD_LEFT;
	else if ($estilo == 'L' || $estilo == 'l') $this->aColumnas[$num]['estilo'] = STR_PAD_RIGHT;
	else $this->aColumnas[$num]['estilo'] = STR_PAD_BOTH;
    }
    
    /**
     * Configura la cabecera que presentara el informe. Se aceptan los siguientes comodines:
     * - %p: Se reemplazara por el numero de pagina actual.
     * - %f: Se reemplazara por la fecha al momento de generar.
     *
     * @param $array	Un array bidimensional, cuyo primer indice es cada linea, y el segundo
     *			debe contener:
     *				- 'texto': asociado con el texto de la linea.
     *				- 'estilo': alineacion de la linea: "R"=derecha, "L"=izquierda, "C"=centro
     */
    function ponerCabecera($array)
    {
	$this->custCab = $array;
    }
    
    /**
     * @internal
     * Funcion interna. No llamar.
     */
    function imprimirCabecera($pagina)
    {
	if ($this->cabecera == "") {
	    /* No hay cache, general directamente */	    
	    $this->cabecera = " ";
	    for ($c = 0; $c < $this->nColumnas; $c++) {
		$this->cabecera .= str_pad(substr($this->aColumnas[$c]['etiqueta'], 0, $this->aColumnas[$c]['ancho']), $this->aColumnas[$c]['ancho'], " ", $this->aColumnas[$c]['estilo']) . " ";
	    }

	}
	
	for ($i = 0; $i < count($this->custCab); $i++) {
	    $linea = $this->custCab[$i]['texto'];
	    $linea = str_replace("%p", $pagina, $linea);
	    $linea = str_replace("%f", date("d/m/Y"), $linea);
	    
	    $this->pdf->Cell(0, $this->fontsize, $linea, 0, 1, $this->custCab[$i]['estilo']);
	}
	
        $this->pdf->Cell(0, $this->fontsize, $this->cabecera, 0, 1);
	$this->lineaH();
    }

    /**
     * Agrega una fila de datos al informe actual.
     * @return	Regresa el indice de la fila agregada, como para usarse con irFila.
     */
    function agregarFila()
    {
	$this->nFilas++;
	return $this->nFilas-1;
    }
    
    /**
     * Especifica la fila de datos con la cual se va a trabajar.
     * @param $fila	Especifica la fila. Empieza en cero.
     */
    function irFila($fila)
    {
	if ($fila > 0 && $fila < $this->nFilas) $this->fila = $fila;
    }
    
    /**
     * Establece el valor para la columna especificada, en la fila actual.
     * @param $valor	El valor a poner en la columna.
     * @param $columna	Especifica la columna en la que se almacenara el valor
     *			dentro de la fila actual.
     * @param $bNoCut	Especifica que no debe restringir la longitud del valor
     *			a la especificada por la definicion de la columna. De
     *			lo contrario, se expande hasta donde sea necesario.
     */
    function poneValor($valor, $columna, $bNoCut=false)
    {
//	echo "fila: " . $this->fila . " columna: $columna valor: $valor\n";
	switch ($this->aColumnas[$columna]['tipo']) {
	    case $this->TIPO_STRING:
		break;
	    case $this->TIPO_INTEGER:
		$valor = intval($valor);
		break;
	    case $this->TIPO_FLOAT:
		$valor = floatval($valor);
		break;
	}
	$valor = strval($valor);
	if (!$bNoCut) $valor = substr($valor, 0, $this->aColumnas[$columna]['ancho']);
	$this->aData[$this->fila][$columna] = $valor;
	$this->aCut[$this->fila][$columna] = $bNoCut;
    }

    /*
     * Procesa los datos ingresados y lo envia a un documento PDF, pero todavia no
     * lo envia al navegador.
     */
    function generar($nueva_pagina=true)
    {
	if ($nueva_pagina) $this->pdf->AddPage();
	$pagina = $this->pdf->PageNo()-1;

	for ($i = 0; $i < $this->nFilas; $i++) {
	    if ($pagina != $this->pdf->PageNo()) {
		$pagina++;
		$this->imprimirCabecera($pagina);
	    }
	    
	    $linea = " ";
	    
	    for ($c = 0; $c < $this->nColumnas; $c++) {
		if (!$this->aCut[$i][$c])	// linea cortada
		    $linea .= str_pad($this->aData[$i][$c], $this->aColumnas[$c]['ancho'], " ", $this->aColumnas[$c]['estilo']) . " ";
		else {	// linea expandida
		    $len = strlen(trim($this->aData[$i][$c]));
		    $clen = 0;
		    $hasta = $c;
		    while ($clen <= $len && $hasta < $this->nColumnas) {
			$clen += $this->aColumnas[$hasta]['ancho']+1;	// ancho columna + separador (cuyo ancho es 1)
			$hasta++;
		    }
		    $linea .= str_pad(trim($this->aData[$i][$c]), $clen-1) . " ";
		    $c = $hasta-1;
		}
	    }
	    
	    $this->pdf->Cell(0, $this->fontsize, $linea, 0, 1);
	}
    }

    /*
     * Envia el documento al navegador. No se recomienda utilizar esta funcion
     * mas de una vez por peticion del usuario.
     */
    function mostrar()
    {
	$this->pdf->Output();
    }
    /**************************** FUNCIONES PRIVADAS - NO LLAMAR ****************************/

    /*
     * @internal
     */
    function lineaH()
    {
	$this->pdf->Line($this->pdf->lMargin, $this->pdf->GetY(),
			 $this->pdf->w-$this->pdf->rMargin, $this->pdf->GetY());
    }
    
    /*
     * @internal
     */
    function lineaV($x)
    {
	$this->pdf->Line($x, $this->pdf->GetY()-$this->fontsize, $x, $this->pdf->h-$this->pdf->bMargin);
    }

}

