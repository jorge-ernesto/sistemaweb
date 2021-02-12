<?php
// Descomentar estas líneas, cuando estamos en modo - development
/*
error_reporting(-1);
ini_set('display_errors', 1);
*/
// Descomentar estas líneas, cuando estamos en modo - production

ini_set('display_errors', 0);
if (version_compare(PHP_VERSION, '5.3', '>='))
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}
else
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
}


class TransTrabajadorModel extends Model {

	function busqueda($periodo, $mes, $diadesde, $diahasta, $tipo, $tiporeporte, $column_caja, $arrParams){
		global $sqlca;
		$fechaini	= $periodo."-".$mes."-".$diadesde;
		$fechafin	= $periodo."-".$mes."-".$diahasta;
		$postrans	= "pos_trans".$periodo.$mes;
		$cond		= "";

		if ($tipo != "T")
			$cond = "AND t.tipo = '".$tipo."'";

		$sql="
SELECT
 h.ch_codigo_trabajador AS cod_trab,
 FIRST(w.ch_nombre1) || ' ' || FIRST(ch_nombre2) || ' ' || FIRST(ch_apellido_paterno) || ' ' || FIRST(ch_apellido_materno) AS nom_trab,
 SUM(t.cantidad) AS cant_art,
 t.dia,
 t.turno,
 ".$column_caja."
 ".$arrParams['column_product']."
 ".$arrParams['column_quantity']."
 SUM(t.importe) AS ventatotal,
 COUNT(t.trans) AS num_trans,
 FIRST(art.art_descbreve) AS no_producto
FROM
 ".$postrans." AS t
 JOIN pos_historia_ladosxtrabajador AS h ON (t.dia = h.dt_dia AND t.turno = h.ch_posturno::text AND t.caja = h.ch_lado)
 LEFT JOIN pla_ta_trabajadores AS w ON (h.ch_codigo_trabajador = w.ch_codigo_trabajador)
 LEFT JOIN int_articulos AS art ON (t.codigo = art.art_codigo) 
WHERE
 t.dia BETWEEN '".$fechaini."' AND '".$fechafin."'
 ".$cond."
GROUP BY
 t.dia,
 ".$column_caja."
 ".$arrParams['column_product']."
 t.turno,
 h.ch_codigo_trabajador
ORDER BY
 cod_trab,
 t.dia,
 ".$column_caja."
 ".$arrParams['column_product']."
 t.turno;
		";

        echo "<pre>";
        var_dump($sql);
        echo "</pre>";

        $iStatusSQL = $sqlca->query($sql);
        $arrResponse = array(
            'status_sql' => $iStatusSQL,
            'message_sql' => $sqlca->get_error(),
            'sStatus' => 'danger',
            'sMessage' => 'problemas al generar reporte',
        );
        if ( $iStatusSQL == 0 ) {
            $arrResponse = array(
                'sStatus' => 'warning',
                'sMessage' => 'No hay registros'
            );
        } else if ( $iStatusSQL > 0 ) {
            $arrDataSQL = $sqlca->fetchAll();
            $arrResponse = array(
                'sStatus' => 'success',
                'arrData' => $arrDataSQL
            );
        }
		return $arrResponse;
  	}
  	
  	function busquedaMD($periodo, $mes, $diadesde, $diahasta, $t, $column_caja){//busqueda market detallado
		global $sqlca;
		
		$fechaini	= $periodo."-".$mes."-".$diadesde;
		$fechafin	= $periodo."-".$mes."-".$diahasta;
		$postrans	= "pos_trans".$periodo.$mes;
		$cond		= "";

		$sql="
SELECT
		";

		if( $t == "S" ){
			$sql.="
 COALESCE(t.cajero,h.ch_codigo_trabajador) AS cod_trab,
			";
		}else{
			$sql.="
 h.ch_codigo_trabajador AS cod_trab,
 			";
		}
		
		$sql.="
 FIRST(w.ch_nombre1) || ' ' || FIRST(ch_nombre2) || ' ' || FIRST(ch_apellido_paterno) || ' ' || FIRST(ch_apellido_materno) AS nom_trab,
 SUM(t.cantidad) AS cant_art,
 t.dia,
 t.turno,
 ".$column_caja."
 ".$arrParams['column_product']."
 ".$arrParams['column_quantity']."
 SUM(t.importe) AS ventatotal,
 COUNT(t.trans) AS num_trans,
 t.codigo AS articulo,
 art.art_descripcion as descripcion 
FROM
 ".$postrans." AS t
 JOIN pos_historia_ladosxtrabajador h ON (t.dia = h.dt_dia AND t.turno = h.ch_posturno::text AND t.caja = h.ch_lado AND t.es = h.ch_sucursal)
		";

		if( $t=="S" ){
			$sql.="
 LEFT JOIN pla_ta_trabajadores w ON (COALESCE(t.cajero,h.ch_codigo_trabajador) = w.ch_codigo_trabajador)
			";
		}else{
			$sql.="
 LEFT JOIN pla_ta_trabajadores w ON (h.ch_codigo_trabajador = w.ch_codigo_trabajador)
			";
		}

		$sql.="
 LEFT JOIN int_articulos art ON (t.codigo = art.art_codigo) 
WHERE
 t.dia BETWEEN '".$fechaini."' AND '".$fechafin."'
 AND t.tipo='M'
 AND t.tm='V'
GROUP BY
 cod_trab,
 t.dia,
 ".$column_caja."
 ".$arrParams['column_product']."
 t.turno,
 t.codigo,
 art.art_descripcion
ORDER BY
 cod_trab,
 t.dia,
 ".$column_caja."
 ".$arrParams['column_product']."
 t.turno,
 art.art_descripcion;
		";

        $iStatusSQL = $sqlca->query($sql);
        $arrResponse = array(
            'status_sql' => $iStatusSQL,
            'message_sql' => $sqlca->get_error(),
            'sStatus' => 'danger',
            'sMessage' => 'problemas al generar reporte',
        );
        if ( $iStatusSQL == 0 ) {
            $arrResponse = array(
                'sStatus' => 'warning',
                'sMessage' => 'No hay registros'
            );
        } else if ( $iStatusSQL > 0 ) {
            $arrDataSQL = $sqlca->fetchAll();
            $arrResponse = array(
                'sStatus' => 'success',
                'arrData' => $arrDataSQL
            );
        }
		return $arrResponse;
  	}
}