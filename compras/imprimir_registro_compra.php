<?php
include "../valida_sess.php";
include_once('/sistemaweb/include/dbsqlca.php');
include_once('/sistemaweb/include/reportes2.inc.php');

//define global variables
$sqlca = new pgsqlDB('localhost', 'postgres', 'postgres', 'integrado');

$empresa = "SELECT p1.par_valor, p2.par_valor, p3.par_valor FROM int_parametros p1, int_parametros p2, int_parametros p3 WHERE p1.par_nombre='razsocial' and p2.par_nombre='ruc' and p3.par_nombre='dires';";

if ($sqlca->query($empresa) < 0)
	return null;

$res				= Array();
$a					= $sqlca->fetchRow();
$res['razsocial'] 	= $a[0];
$res['ruc'] 		= $a[1];
$res['direccion'] 	= $a[2];

//Variables GET
$sTipoSerieNumeroProveedorDocumento = trim($_GET['documento']);
$sTipoSerieNumeroProveedorDocumento = strip_tags($sTipoSerieNumeroProveedorDocumento);

$sqlc = "
SELECT
 to_char(c.pro_cab_fecharegistro, 'DD/MM/YYYY') fregistro,
 to_char(c.pro_cab_fechaemision, 'DD/MM/YYYY') femision,
 CASE WHEN
  gen.tab_car_03 = '14' THEN to_char(c.pro_cab_fechavencimiento, 'DD/MM/YYYY') 
 ELSE
  ''
 END as fvencimiento,
 gen.tab_desc_breve||' - '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento documento,
 p.pro_codigo ruc,
 p.pro_razsocial razsocial,
 p.pro_direccion direccion,
 rubro.ch_descripcion rubro,
 c.pro_cab_impafecto imponible,
 c.pro_cab_impto1 impuesto,
 c.pro_cab_impinafecto inafecto,
 (CASE WHEN c.pro_cab_impinafecto > 0 THEN (c.pro_cab_imptotal + c.pro_cab_impinafecto) ELSE c.pro_cab_imptotal END) total,
 c.pro_cab_impsaldo saldo,
 c.regc_sunat_percepcion perce,
 rubro.ch_tipo_item codrubro,
 MONE.tab_desc_breve AS moneda,
 MONE.tab_descripcion AS nmoneda,
 a.ch_sigla_almacen AS cc,
 LPAD(CAST(c.pro_cab_numreg AS bpchar), 10, '0') correlativo,
 c.pro_cab_glosa AS txt_glosa
FROM
cpag_ta_cabecera AS c
JOIN inv_ta_almacenes AS a
 ON(c.pro_cab_almacen = a.ch_almacen)
JOIN int_tabla_general AS gen
 ON(c.pro_cab_tipdocumento = substring(TRIM(tab_elemento) for 2 from length(TRIM(tab_elemento))-1) and tab_tabla ='08')
JOIN int_proveedores AS p
 USING(pro_codigo)
LEFT JOIN cpag_ta_rubros AS rubro
 ON(rubro.ch_codigo_rubro = c.pro_cab_rubrodoc)
JOIN int_tabla_general AS MONE
 ON(SUBSTRING(MONE.tab_elemento, 5) = c.pro_cab_moneda AND MONE.tab_elemento != '000000' AND MONE.tab_tabla='04')
WHERE
	c.pro_cab_tipdocumento||' '||c.pro_cab_seriedocumento||' - '||c.pro_cab_numdocumento|| ' - ' ||c.pro_codigo = '" . $sTipoSerieNumeroProveedorDocumento . "';
";

/*
echo "<pre>";
print_r($sqlc);
echo "</pre>";
*/

		$sqlca->query($sqlc);

		$data = $sqlca->fetchRow();

		$reporte = new CReportes2("P","pt","A4");

		$fontsize = 7.5;
		$reporte->SetMargins(5, 12, 5);
		$reporte->SetFont("courier", "", $fontsize);

		$reporte->Ln();	 
		$reporte->definirCabecera(1, "L", "");
		$reporte->definirCabeceraSize(2, "L", "courier,B,14", "                            REGISTRO DE COMPRAS");
		$reporte->definirCabecera(13, "L", "         Centro Costo: ".$data["cc"]);
		$reporte->definirCabecera(3, "L", "Nombre o Razon Social: ".$data["razsocial"]);
		$reporte->definirCabecera(4, "L", "            Domicilio: ".$data["direccion"]);
		$reporte->definirCabecera(5, "L", "                  RUC: ".$data["ruc"]);
		$reporte->definirCabecera(6, "L", "     Fecha de Emision: ".$data["femision"]);
		$reporte->definirCabecera(7, "L", "    Fecha de Registro: ".$data["fregistro"]);
		$reporte->definirCabecera(8, "L", " Fecha de Vencimiento: ".$data["fvencimiento"]);
		$reporte->definirCabecera(9, "L", "            Documento: ".$data["documento"]);
		$reporte->definirCabecera(10, "L", "               Moneda: ".$data["nmoneda"]);
		$reporte->definirCabecera(11, "L", "                Rubro: ".$data["rubro"]);
		$reporte->definirCabecera(12, "L", "        Nro. Registro: ".$data["correlativo"]);
		$reporte->definirCabecera(14, "L", "                Glosa: ".$data["txt_glosa"]);
		
		if(empty($data["codrubro"]))
			$reporte->addPage();

		if(!empty($data["codrubro"])){

			$cab = Array(
				"CODIGO"		=>	"CODIGO",
				"DESCRIPCION"		=>	"DESCRIPCION",
				"CANTIDAD"		=>	"CANTIDAD",
				"COSTO UNITARIO"	=>	"COSTO UNITARIO",
				"TOTAL"			=>	"TOTAL",
			);

			$reporte->definirCabecera(12, "R", " ");
			$reporte->definirColumna("CODIGO", $tipo->TIPO_TEXT, 13, "L");
			$reporte->definirColumna("DESCRIPCION", $tipo->TIPO_TEXT, 60, "L");
			$reporte->definirColumna("CANTIDAD", $tipo->TIPO_TEXT, 15, "L");
			$reporte->definirColumna("COSTO UNITARIO", $tipo->TIPO_TEXT, 15, "R");
			$reporte->definirColumna("TOTAL", $tipo->TIPO_TEXT, 15, "R");
			$reporte->definirCabeceraPredeterminada($cab);
			$reporte->addPage();

			$sqld = "
			SELECT
				i.art_codigo codigo,
				trim(a.art_descripcion) descripcion,
				round(i.mov_cantidad,3) cantidad,
				round(i.mov_costounitario,4) as costo,
				round(i.mov_costototal,2) as itotal
			FROM
				inv_ta_compras_devoluciones i
				LEFT JOIN int_articulos a ON (a.art_codigo = i.art_codigo)
			WHERE
				i.cpag_tipo_pago||' '||i.cpag_serie_pago||' - '||i.cpag_num_pago|| ' - ' ||i.mov_entidad = '$documento';
			";

			$sqlca->query($sqld);
			$num = $sqlca->numrows();

			if($num > 0){

				while ($row = $sqlca->fetchRow()){

				extract($row);

				$arr = array(

						"CODIGO"		=>$codigo,
						"DESCRIPCION"		=>$descripcion,
						"CANTIDAD"		=>$cantidad,
						"COSTO UNITARIO"	=>$costo,
						"TOTAL"			=>$itotal,
					);

				$reporte->nuevaFila($arr);

				}
			}

		}

		$reporte->Ln();
		$reporte->Ln();
		$reporte->cell(0,10,'BASE IMPONIBLE: '.$data["moneda"].' '.$data["imponible"].'  IMPUESTO : '.$data["moneda"].' '.$data["impuesto"].'  INAFECTO IGV : '.$data["moneda"].' '.$data["inafecto"].'  TOTAL : '.$data["moneda"].'  '.$data["total"].'  PERCEPCION : '.$data["moneda"].' '.$data["perce"].'',1,0,'L');
		$reporte->Ln();
		$reporte->Ln();

		$reporte->borrarCabecera();
		$reporte->borrarCabeceraPredeterminada();
		$reporte->Lnew();$reporte->Lnew();

	
		return $reporte->Output("/sistemaweb/compras/movimientos/pdf/imprimir_registro_compras.pdf", "I");

?>
