<?php

include_once('/sistemaweb/include/mvc_sistemaweb.php');
include_once('/sistemaweb/include/dbsqlca.php');

$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'integrado');

$accion = NULL;

if (isset($_POST['accion']))
	$accion = $_POST['accion'];

if ($accion == 'getProveedores') {
	$sql = "
	SELECT
		*
	FROM
		int_proveedores
	WHERE
		(pro_codigo LIKE '%" . $_POST['criterio'] . "%'
		OR pro_razsocial LIKE '%" . $_POST['criterio'] . "%')
	LIMIT
		20;
	";

	if ($sqlca->query($sql) < 0)
		return false;

	print_r(json_encode($sqlca->fetchAll()));
} else {

	if ($accion == 'getLineas') {
		$sql = "
		SELECT
			*
		FROM
			int_tabla_general
		WHERE
			tab_tabla = '20'
			AND (tab_elemento LIKE '%" . $_POST['criterio'] . "%'
			OR tab_descripcion LIKE '%" . $_POST['criterio'] . "%')
		LIMIT
			20;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		print_r(json_encode($sqlca->fetchAll()));

	} else if ($accion == 'getPartnersByRucOrName') {
		$sql = "
		SELECT
			*, pro_codigo AS _id,
			pro_razsocial AS _value
		FROM
			int_proveedores
		WHERE
			(pro_codigo LIKE '%" . $_POST['criterio'] . "%'
			OR pro_razsocial LIKE '%" . $_POST['criterio'] . "%')
		LIMIT
			20;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		print_r(json_encode($sqlca->fetchAll()));
	} else if ($accion == 'getEmployees') {
		$sql = "
		SELECT
			*,
			ch_codigo_trabajador AS _id,
			ch_nombre1||' '||ch_apellido_paterno||' '||ch_apellido_materno AS _value
		FROM
			pla_ta_trabajadores
		WHERE
			(
			ch_codigo_trabajador LIKE '%" . $_POST['criterio'] . "%'
			OR ch_nombre1||' '||ch_apellido_paterno||' '||ch_apellido_materno LIKE '%" . $_POST['criterio'] . "%'
			)
		LIMIT
			20;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		echo json_encode($sqlca->fetchAll());
	} else if ($accion == 'getCustomers') {
		$sql = "
		SELECT
			*,
			cli_codigo AS _id,
			cli_razsocial AS _value
		FROM
			int_clientes
		WHERE
			(cli_codigo LIKE '%" . $_POST['criterio'] . "%'
			OR cli_razsocial LIKE '%" . $_POST['criterio'] . "%')
		LIMIT
			20;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		print_r(json_encode($sqlca->fetchAll()));
	} else if($accion == 'getLineByCodeOrName') {
		$sql = "
		SELECT
			*, tab_elemento AS _id,
			tab_descripcion AS _value
		FROM
			int_tabla_general
		WHERE
			tab_tabla = '20'
			AND (tab_elemento LIKE '%" . $_POST['criterio'] . "%'
			OR tab_descripcion LIKE '%" . $_POST['criterio'] . "%')
		LIMIT
			20;
		";

		if ($sqlca->query($sql) < 0)
			return false;

		print_r(json_encode($sqlca->fetchAll()));
	} else if($accion == 'getProductXByCodeOrName') {
		$sCriterio = strtoupper($_POST['criterio']);
		$sql = "
SELECT
 *, art_codigo AS _id,
 art_descripcion AS _value
FROM
 int_articulos
WHERE
 (art_codigo LIKE '%" . $sCriterio . "%' OR art_descripcion LIKE '%" . $sCriterio . "%')
 AND art_estado='0'
ORDER BY
 art_descripcion
LIMIT 15;
		";
		error_log(json_encode($sql));
        $iStatusSQL = $sqlca->query($sql);
        if ( (int)$iStatusSQL < 0 ) {
	        $arrResponse = array(
	            'status_sql' => $iStatusSQL,
	            'message_sql' => $sqlca->get_error(),
	            'sStatus' => 'danger',
	            'sMessage' => 'problemas con el modulo ' . $accion,
	        );
        	echo json_encode($arrResponse);
        } else if ( (int)$iStatusSQL == 0 ) {
            $arrResponse = array(
                'sStatus' => 'warning',
                'arrData' => 0,
                'sMessage' => 'No hay registros'
            );
        	echo json_encode($arrResponse);
        } else if ( (int)$iStatusSQL > 0 ) {
            $arrDataSQL = $sqlca->fetchAll();

            $arrItems=array();
            $i=0; 
            foreach ($arrDataSQL as $row) {
			    $arrItems[$i]["art_codigo"] = $row["art_codigo"];
			    $arrItems[$i]["art_descripcion"] = utf8_encode($row["art_descripcion"]);
			    $arrItems[$i]["art_descbreve"] = utf8_encode($row["art_descbreve"]);
			    $arrItems[$i]["art_clase"] = $row["art_clase"];
			    $arrItems[$i]["art_tipo"] = $row["art_tipo"];
			    $arrItems[$i]["art_linea"] = $row["art_linea"];
			    $arrItems[$i]["art_unidad"] = $row["art_unidad"];
			    $arrItems[$i]["art_presentacion"] = $row["art_presentacion"];
			    $arrItems[$i]["art_costoinicial"] = $row["art_costoinicial"];
			    $arrItems[$i]["art_stockinicial"] = $row["art_stockinicial"];
			    $arrItems[$i]["art_stockactual"] = $row["art_stockactual"];
			    $arrItems[$i]["art_costoactual"] = $row["art_costoactual"];
			    $arrItems[$i]["art_costoreposicion"] = $row["art_costoreposicion"];
			    $arrItems[$i]["art_margenutilidad"] = $row["art_margenutilidad"];
			    $arrItems[$i]["art_fecucompra"] = $row["art_fecucompra"];
			    $arrItems[$i]["art_fecuventa"] = $row["art_fecuventa"];
			    $arrItems[$i]["art_fecactuliz"] = $row["art_fecactuliz"];
			    $arrItems[$i]["art_estado"] = $row["art_estado"];
			    $arrItems[$i]["art_trasmision"] = $row["art_trasmision"];
			    $arrItems[$i]["art_stkgnrlmin"] = $row["art_stkgnrlmin"];
			    $arrItems[$i]["art_stkgnrlmax"] = $row["art_stkgnrlmax"];
			    $arrItems[$i]["art_promconsumo"] = $row["art_promconsumo"];
			    $arrItems[$i]["art_plazoreposicprom"] = $row["art_plazoreposicprom"];
			    $arrItems[$i]["art_diasreposic"] = $row["art_diasreposic"];
			    $arrItems[$i]["art_feccostorep"] = $row["art_feccostorep"];
			    $arrItems[$i]["art_usuario"] = $row["art_usuario"];
			    $arrItems[$i]["art_costopromedio"] = $row["art_costopromedio"];
			    $arrItems[$i]["art_cod_ubicac"] = $row["art_cod_ubicac"];
			    $arrItems[$i]["art_cod_sku"] = $row["art_cod_sku"];
			    $arrItems[$i]["art_plutipo"] = $row["art_plutipo"];
			    $arrItems[$i]["nu_dias_minimo"] = $row["nu_dias_minimo"];
			    $arrItems[$i]["nu_dias_maximo"] = $row["nu_dias_maximo"];
			    $arrItems[$i]["art_acuenta"] = $row["art_acuenta"];
			    $arrItems[$i]["flg_replicacion"] = $row["flg_replicacion"];
			    $arrItems[$i]["fecha_replicacion"] = $row["fecha_replicacion"];
			    $arrItems[$i]["art_impuesto1"] = $row["art_impuesto1"];
			    $arrItems[$i]["art_modifica_articulo"] = $row["art_modifica_articulo"];

			    $arrItems[$i]["_id"] = $row["_id"];
			    $arrItems[$i]["_value"] = utf8_encode($row["_value"]);
			    $i++;
            }

            $arrResponse = array(
                'sStatus' => 'success',
                'arrData' => $arrItems
            );

        	echo json_encode($arrItems);
        }
	} else {

		$tipo_plu = $_POST['criterio2'];

		if ($tipo_plu == '1')
			$cond = " AND art_plutipo = '1'";
		else
			$cond = " AND art_plutipo = '2'";
		
		$sql = "
		SELECT
			*
		FROM
			int_articulos
		WHERE
			(art_codigo LIKE '" . $_POST['criterio'] . "%' OR art_descripcion LIKE '" . $_POST['criterio'] . "%')
			" . $cond . "
		ORDER BY
			art_descripcion
		LIMIT
			15;
		";

		error_log($sql);

		if ($sqlca->query($sql) < 0)
			return false;+
		
		print_r(json_encode($sqlca->fetchAll()));
	}
}