<?php
// duplica em el cierres.php
//include ('/sistemaweb/inventarios/js/inv_addmov_support_navsat.php');

// Debes generar transferencias de sistema en formulario

function Generar_Transf($coneccion, $serie_docu_ref, $num_docu_ref, $dia, $nro_lote, $dia_des)
{
	$sql="select trim(p1.par_valor), trim(p2.par_valor), trim(p3.par_valor)
			FROM int_parametros p1, int_parametros p2, int_parametros p3
			WHERE p1.par_nombre='cafe_alma_ing'
				and p2.par_nombre= 'cafe_alma_sal'
				and p3.par_nombre= 'cafe_linea'";

	echo $sql;
	$xsql=pg_query($coneccion, $sql);
	$alma_ingres = pg_result( $xsql,0,0);
	$alma_salida = pg_result( $xsql,0,1);
	$linea = pg_result( $xsql,0,2);
	echo $linea;

	// gaseosas es 35
	// $linea = '35';
	$tipo_documento = '45';

	/* es variable dependiendo si es la entrada o la salida solo puede ser 014 en el caso transf salida
	   y 030 en el caso transf entrada */
	// carga primero 030 porque saco venta de ese punto

	// debe ser alma_ingres
	$almacen=$alma_ingres;


	// 07 transferencia ingreso del 014 al 030 tipo 4
	// 08 transferencia salida  del 030 al 014 tipo 1

	/* $alma_salida='014';
	   $alma_ingres='030'; */
	$transf_ingres="07";
	$transf_salida="08";

	$fecha = substr($dia,3,1).substr($dia,5,2).substr($dia,8,2);

	// aqui cargamos el total de las ventas en el almacen cafeteria para ver cuanto hacemos transferencia
	$sql = "SELECT mov.art_codigo, sum( mov.mov_cantidad ) , art_costoreposicion
			FROM inv_movialma mov, int_articulos art
			WHERE  mov_numero like '".$almacen.$fecha."%' 
				and tran_codigo='$tipo_documento'
				and mov.art_codigo=art.art_codigo
				and substr(mov_numero,9,2) not in (
				select substr(trim(tab_elemento),5,2) 
				from int_tabla_general where tab_tabla='20' and trim(tab_car_01)='08' ) 
				group by mov.art_codigo, art.art_costoreposicion ";


	echo $sql;
	$xsql = pg_query($coneccion, $sql);

	/* aqui comenzamos con las transf salida
	   que consiste en */
	$RS_natu = inicializarVariables( $transf_salida , $alma_salida );

	$i=0;

	$tipo_documento = $transf_salida;

	$alma_orig = $alma_salida;
	$alma_dest = "730";
	$almacen   = $alma_salida;

	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);

		$ITEMS[$i][0] = $tipo_documento;
		$ITEMS[$i][1] = $almacen;
		$ITEMS[$i][2] = $alma_orig;
		$ITEMS[$i][3] = $alma_dest;

		$ITEMS[$i][4] = "";
		$ITEMS[$i][5] = $tipo_documento;   //inv_movialma.mov_tipdocuref   ???

		$ITEMS[$i][6] = $serie_docu_ref;
		$ITEMS[$i][7] = $num_docu_ref;

		$ITEMS[$i][8] = $rs[0];
		$ITEMS[$i][9] = "";
		$ITEMS[$i][10] = $rs[1];
		$ITEMS[$i][11] = $rs[2];

		$ITEMS[$i][13] = $RS_natu['valor'];
		$ITEMS[$i][14] = $RS_natu['natu'];
		$ITEMS[$i][30] = $dia_des;
		$ITEMS[$i][31] = $nro_lote;
		$i++;
	}

	$i=0;

	insertarRegistros($ITEMS);

	echo "<br>";
	while($i<pg_num_rows($xsql))
	{
		echo " ".$ITEMS[$i][0];
		echo " ".$ITEMS[$i][1];
		echo " ".$ITEMS[$i][2];
		echo " ".$ITEMS[$i][3];
		echo " ".$ITEMS[$i][4];
		echo " ".$ITEMS[$i][5];
		echo " ".$ITEMS[$i][6];
		echo " ".$ITEMS[$i][7];
		echo " ".$ITEMS[$i][8];
		echo " ".$ITEMS[$i][9];
		echo " ".$ITEMS[$i][10];
		echo " ".$ITEMS[$i][11];
		echo " ".$ITEMS[$i][12];
		echo " ".$ITEMS[$i][13];
		echo " ".$ITEMS[$i][14];
		echo " ".$ITEMS[$i][30]."\n";
		echo "<br>";
		$i++;
	}


	/* aqui comenzamos con las transf entrada
	   que consiste en */
	$RS_natu = inicializarVariables( $transf_ingres , $alma_ingres );

	$i=0;

	$tipo_documento = $transf_ingres;

	$alma_orig = "614";
	$alma_dest = $alma_ingres;
	$almacen   = $alma_ingres;

	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);

		$ITEMS[$i][0] = $tipo_documento;
		$ITEMS[$i][1] = $almacen;
		$ITEMS[$i][2] = $alma_orig;
		$ITEMS[$i][3] = $alma_dest;

		$ITEMS[$i][4] = "";
		$ITEMS[$i][5] = $tipo_documento;   //inv_movialma.mov_tipdocuref   ???

		$ITEMS[$i][6] = $serie_docu_ref;
		$ITEMS[$i][7] = $num_docu_ref;

		$ITEMS[$i][8] = $rs[0];
		$ITEMS[$i][9] = "";
		$ITEMS[$i][10] = $rs[1];
		$ITEMS[$i][11] = $rs[2];

		$ITEMS[$i][13] = $RS_natu['valor'];
		$ITEMS[$i][14] = $RS_natu['natu'];
		$ITEMS[$i][30] = $dia_des;
		$ITEMS[$i][31] = $nro_lote;
		$i++;
	}

	$i=0;

	insertarRegistros($ITEMS);

	echo "<br>";
	while($i<pg_num_rows($xsql))
	{
		echo " ".$ITEMS[$i][0];
		echo " ".$ITEMS[$i][1];
		echo " ".$ITEMS[$i][2];
		echo " ".$ITEMS[$i][3];
		echo " ".$ITEMS[$i][4];
		echo " ".$ITEMS[$i][5];
		echo " ".$ITEMS[$i][6];
		echo " ".$ITEMS[$i][7];
		echo " ".$ITEMS[$i][8];
		echo " ".$ITEMS[$i][9];
		echo " ".$ITEMS[$i][10];
		echo " ".$ITEMS[$i][11];
		echo " ".$ITEMS[$i][12];
		echo " ".$ITEMS[$i][13];
		echo " ".$ITEMS[$i][14];
		echo " ".$ITEMS[$i][30]."\n";
		echo "<br>";
		$i++;
	}

}

