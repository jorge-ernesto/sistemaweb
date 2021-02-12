<?php
/*
 * Clase para preparar documentos en formato continuo (texto)
 *
 */
class CDocumentos
{
    /*
     * Constructor de la clase CDocumentos
     *
     * Carga los datos para generar el texto para la impresion del documento.
     * Ademas obtiene los parametros para la generacion del documento de la
     * base de datos.
     */
    function __construct($tipo_documento, $cabecera, $cuerpo, $pie)
    {
    }
    
    /*
     * generar()
     *
     * Genera el texto en base a los datos especificados al constructor y
     * devuelve un string conteniendo la informacion ya formateada y lista
     * para grabarla a un archivo de texto e imprimirla.
     */
    function generar()
    {
    }
    
    /*
     * obtenerDocumento($tipo_documento)
     * (funcion interna!!!)
     * 
     * Obtiene los parametros de impresion del documento solicitado. Devuelve
     * un array cuyo primer nivel son las claves: "cabecera", "cuerpo" y "pie".
     */
    function obtenerDocumento($tipo_documento)
    {
	global $sqlca;
	
	$sql = "SELECT
		    ch_parte_documento,
		    x,
		    y,
		    ch_valor,
		    nu_longitud,
		    ch_alineacion
		FROM
		    int_formatos_doc
		WHERE
		    ch_tipo_documento='" . pg_escape_string($tipo_documento) . "'
		ORDER BY
		    ch_parte_documento,
		    y
		;
		";
	if ($sqlca->query($sql) < 0) return false;
	
	$result = Array();

	for ($i = 0; $i < $sqlca->numrows(); $i++) {
	    $a = $sqlca->fetchRow();
	    
	    $ch_parte_documento = $a[0];
	    $x = $a[1];
	    $y = $a[2];
	    $ch_valor = $a[3];
	    $nu_longitud = $a[4];
	    $ch_alineacion = $a[5];
	    
	    switch ($ch_parte_documento) {
		case "C":	/* Cabecera */
		    $nombre = "cabecera";
		    break;
		case "D":	/* Cuerpo */
		    $nombre = "cuerpo";
		    break;
		case "P":	/* Pie */
		    $nombre = "pie";
		    break;
		default:	/* Desconocido - no deberia ocurrir */
		    $nombre = "desconocido";
		    break;
	    }
	    
	    $result[$nombre][$ch_valor]['x'] = $x;
	    $result[$nombre][$ch_valor]['y'] = $y;
	    $result[$nombre][$ch_valor]['longitud'] = $nu_longitud;
	    $result[$nombre][$ch_valor]['alineacion'] = $ch_alineacion;
	}
    }
}

