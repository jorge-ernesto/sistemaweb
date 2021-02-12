<?php
include ('/sistemaweb/inventarios/js/inv_addmov_support_navsat.php');





function GenerarNavsat($coneccion, $serie_docu_ref, $num_docu_ref, $dia, $nro_lote)
{ global $logbuffer ;

	$sql="select trim(p1.par_valor), trim(p2.par_valor)
			FROM int_parametros p1, int_parametros p2
			WHERE p1.par_nombre='codes'
			and p2.par_nombre= 'prov_tarj_virtual'";

	echo $sql;
	$almacen = pg_result(pg_query($coneccion, $sql),0,0);
	$pro_navsat = pg_result(pg_query($coneccion, $sql),0,1);

	$linea = '72';
	$tipo_documento = '45';

   	$xfechalog=date("Y/m/d H:i:s");
    $logbuffer= $logbuffer."CD: ".$xfechalog." 3.1. SELECCIONA REGISTROS LINEA 72 DE MOVIALMA  \n";   
	
	$fecha = substr($dia,3,1).substr($dia,5,2).substr($dia,8,2);
	$RS_natu = inicializarVariables("01",$almacen);
    $varmovi = $almacen.$fecha.$linea ;
	$sql = "SELECT
			  mov.art_codigo, mov.mov_cantidad, art_costoreposicion
			FROM inv_movialma mov, int_articulos art
			WHERE
			    mov_numero = '$varmovi' and
				tran_codigo='$tipo_documento' and
				mov.art_codigo=art.art_codigo " ;
			 
			 // and substr(trim(mov_numero),0,4)='$almacen'
			 // and substr(trim(mov_numero),4,5)='$fecha'
			 // and substr(trim(mov_numero),9,2)='$linea'";

	echo $sql;
	$xsql = pg_query($coneccion, $sql);
	$i=0;

	$tipo_documento = '01';
	$tipo_documento_ref='09';


   	
	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);
	
		$ITEMS[$i][0] = $tipo_documento;
		$ITEMS[$i][1] = $almacen;	
		$ITEMS[$i][2] = "421";
		$ITEMS[$i][3] = $almacen;

		$ITEMS[$i][4] = $pro_navsat;   
		$ITEMS[$i][5] = $tipo_documento_ref;   //inv_movialma.mov_tipdocuref   ???

		$ITEMS[$i][6] = $serie_docu_ref;
		$ITEMS[$i][7] = $num_docu_ref;

		$ITEMS[$i][8] = $rs[0];
		$ITEMS[$i][9] = "";
		$ITEMS[$i][10] = $rs[1];
		$ITEMS[$i][11] = $rs[2];

//		$ITEMS[$i][12] = $tipo_documento;
		$ITEMS[$i][13] = $RS_natu['valor'];
		$ITEMS[$i][14] = $RS_natu['natu'];
		$ITEMS[$i][30] = $dia;
		$ITEMS[$i][31] = $nro_lote;
		$i++;
	}

	$i=0;

	//echo count($ITEMS);
	$xfechalog=date("Y/m/d H:i:s");
    $logbuffer= $logbuffer."CD: ".$xfechalog." 3.2. INICIA INSERCION DE REGISTROS 01 LINEA 72  \n";   
	
	insertarRegistros($ITEMS);
	
	$xfechalog=date("Y/m/d H:i:s");
    $logbuffer= $logbuffer."CD: ".$xfechalog." 3.3. FINALIZA INSERCION DE REGISTROS 01 LINEA 72  \n";   
	

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
	/*
		$A = $ITEMS[$i];

		$fm = $A[0]; //inv_movialma.tran_codigo

		$almacen_interno = $A[1]; //inv_movialma.mov_almacen
		$alma_ori = $A[2]; //inv_movialma.mov_almaorigen
		$alma_des = $A[3]; //inv_movialma.mov_almadestino

		$cod_proveedor = $A[4]; //inv_movialma.mov_entidad
		$tipo_doc = $A[5]; //inv_movialma.mov_tipdocuref


		$serie_doc = $A[6]; //inv_movialma.mov_docurefe(primeros3 digitos)
		$num_doc = $A[7]; //inv_movialma.mov_docurefe(siguientes 7 digitos)

		$cod_articulo = $A[8]; //inv_movialma.art_codigo
		$des_art_campo = $A[9]; //DESCRIPCIN SOLO PARA MOSTRARLA
		$art_cantidad = $A[10]; //inv_movialma.mov_cantidad
		$art_costo_uni = $A[11]; //inv_movialma.mov_costounitario
		//$nro_mov = $A[12]; //inv_movialma.mov_numero
		$val = $A[13]; //estos 2 campos valor y naturaleza los uso para decidir si pintar o no
		$na  = $A[14]; // y ademas para saber si actualizo o no articulos.costo_reposicion
		$mov_fecha = "now()"; //inv_movialma.mov_fecha	
		/* nro_mov -->Se crea el numero con serie y numero y se hace el insert */	
	//	$docu_refe = $serie_doc.$num_doc;

}

