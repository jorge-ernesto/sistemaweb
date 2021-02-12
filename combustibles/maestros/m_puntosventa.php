<?php
  // Modelo para Tarjetas Magneticas

Class PuntoVentaModel extends Model{

	function guardarRegistro($puntoventa, $prod1, $prod2, $prod3, $prod4, $ndec_cantidad, $ndec_precio, $ndec_importe, $ndec_contometro_cantidad, $ndec_contometro_importe, $idinterfase, $puntoventainterfase){
	
		global $sqlca;
		if(strlen($puntoventa)>0)
		{
			$okgraba=true;

			$sql_busc = "select lado from pos_cmblados where trim(lado)='$puntoventa'";
	
			$sqlca->query($sql_busc);
			if($sqlca->fetchRow()>0)
			{
				$okgraba=false; $mensaje="Error!! \\n Codigo ya Existe";
			}

			if($okgraba==true)
			{
				$sql_insert = "Insert into pos_cmblados (lado, prod1, prod2, prod3, prod4, ndec_cantidad, ndec_precio, ndec_importe, ndec_contometro_cantidad, ndec_contometro_importe, idinterfase,ladointerfase) values
				('".pg_escape_string($puntoventa)."','".
				pg_escape_string($prod1)."','".
				pg_escape_string($prod2)."','".
				pg_escape_string($prod3)."','".
				pg_escape_string($prod4)."','".
				pg_escape_string($ndec_cantidad)."','".
				pg_escape_string($ndec_precio)."','".
				pg_escape_string($ndec_importe)."','".
				pg_escape_string($ndec_contometro_cantidad)."','".
				pg_escape_string($ndec_contometro_importe)."','".
				pg_escape_string($idinterfase)."','".
				pg_escape_string($puntoventainterfase)."')";

				$sqlca->query($sql_insert);
				return '';	

			}else{
				return '0';
			}
		}else{
			return '0';
		}
	
	}
	
	function actualizarRegistro($POS,$INTERF,$LD01,$LD02,$LD03,$LD04,$LD05,$LD06,$LD07,$LD08,$LD09,$LD10,$LD11,$LD12,$LD13,$LD14,$LD15,$LD16,$IP,$SERIE,$SUNAT,$NOMBREPOS,$IMPRESORA,$DISPOSITIVO,$EJECT,$LINES,$MENSAJE){

?><script>//alert('<?php echo $POS.",".$INTERF.",".$LD01.",".$LD02.",".$LD03.",".$LD04.",".$LD05.",".$LD06.",".$LD07.",".$LD08.",".$LD09.",".$LD10.",".$LD11.",".$LD12.",".$LD13.",".$LD14.",".$LD15.",".$LD16.",".$IP.",".$SERIE.",".$SUNAT.",".$NOMBREPOS.",".$IMPRESORA.",".$DISPOSITIVO.",".$EJECT.",".$LINES.",".$MENSAJE ?>');</script><?php

		global $sqlca;
		
		$query = "Update pos_cfg set

		interf ='".pg_escape_string($INTERF)."', 
		ld01 ='".pg_escape_string($LD01)."', 
		ld02 ='".pg_escape_string($LD02)."', 
		ld03 ='".pg_escape_string($LD03)."', 
		ld04 = '".pg_escape_string($LD04)."', 
		ld05 ='".pg_escape_string($LD05)."', 
		ld06 ='".pg_escape_string($LD06)."', 
		ld07 = '".pg_escape_string($LD07)."', 
		ld08 ='".pg_escape_string($LD08)."' , 
		ld09 ='".pg_escape_string($LD09)."', 
		ld10 ='".pg_escape_string($LD10)."', 
		ld11 ='".pg_escape_string($LD11)."', 
		ld12 = '".pg_escape_string($LD12)."', 
		ld13 ='".pg_escape_string($LD13)."', 
		ld14 ='".pg_escape_string($LD14)."', 
		ld15 = '".pg_escape_string($LD15)."', 
		ld16 ='".pg_escape_string($LD16)."' , 
		ip ='".pg_escape_string($IP)."' , 
		nroserie ='".pg_escape_string($SERIE)."' , 
		autsunat ='".pg_escape_string($SUNAT)."' , 
		PC_SAMBA ='".pg_escape_string($NOMBREPOS)."' , 
		PRN_SAMBA ='".pg_escape_string($IMPRESORA)."' , 
		dispositivo ='".pg_escape_string($DISPOSITIVO)."' , 
		eject ='".pg_escape_string($EJECT)."' , 
		lines ='".pg_escape_string($LINES)."' , 
		mensaje_cab ='".pg_escape_string($MENSAJE)."'

		where pos ='".pg_escape_string($POS)."'";
	
		$result = $sqlca->query($query);


		$query = "Update pos_cfg set 
		ld01 = Case When '".$LD01."' = 'S' Then 'N' Else ld01 End,
		ld02 = Case When '".$LD02."' = 'S' Then 'N' Else ld02 End,
		ld03 = Case When '".$LD03."' = 'S' Then 'N' Else ld03 End,
		ld04 = Case When '".$LD04."' = 'S' Then 'N' Else ld04 End,
		ld05 = Case When '".$LD05."' = 'S' Then 'N' Else ld05 End,
		ld06 = Case When '".$LD06."' = 'S' Then 'N' Else ld06 End,
		ld07 = Case When '".$LD07."' = 'S' Then 'N' Else ld07 End,
		ld08 = Case When '".$LD08."' = 'S' Then 'N' Else ld08 End,
		ld09 = Case When '".$LD09."' = 'S' Then 'N' Else ld09 End,
		ld10 = Case When '".$LD10."' = 'S' Then 'N' Else ld10 End,
		ld11 = Case When '".$LD11."' = 'S' Then 'N' Else ld11 End,
		ld12 = Case When '".$LD12."' = 'S' Then 'N' Else ld12 End,
		ld13 = Case When '".$LD13."' = 'S' Then 'N' Else ld13 End,
		ld14 = Case When '".$LD14."' = 'S' Then 'N' Else ld14 End,
		ld15 = Case When '".$LD15."' = 'S' Then 'N' Else ld15 End,
		ld16 = Case When '".$LD16."' = 'S' Then 'N' Else ld16 End 
		where pos <>'".pg_escape_string($POS)."'";
	
		echo $query;
		$result = $sqlca->query($query);
		return '';

	}

	function actualizarRegistroLados($POS,$LD01,$LD02,$LD03,$LD04,$LD05,$LD06,$LD07,$LD08,$LD09,$LD10,$LD11,$LD12,$LD13,$LD14,$LD15,$LD16){

		global $sqlca;
		
		$query = "Update pos_cfg set
 
		ld01 ='".pg_escape_string($LD01)."', 
		ld02 ='".pg_escape_string($LD02)."', 
		ld03 ='".pg_escape_string($LD03)."', 
		ld04 = '".pg_escape_string($LD04)."', 
		ld05 ='".pg_escape_string($LD05)."', 
		ld06 ='".pg_escape_string($LD06)."', 
		ld07 = '".pg_escape_string($LD07)."', 
		ld08 ='".pg_escape_string($LD08)."' , 
		ld09 ='".pg_escape_string($LD09)."', 
		ld10 ='".pg_escape_string($LD10)."', 
		ld11 ='".pg_escape_string($LD11)."', 
		ld12 = '".pg_escape_string($LD12)."', 
		ld13 ='".pg_escape_string($LD13)."', 
		ld14 ='".pg_escape_string($LD14)."', 
		ld15 = '".pg_escape_string($LD15)."', 
		ld16 ='".pg_escape_string($LD16)."'

		where pos ='".pg_escape_string($POS)."'";
	
		$result = $sqlca->query($query);

		$query = "Update pos_cfg set 
		ld01 = Case When '".$LD01."' = 'S' Then 'N' Else ld01 End,
		ld02 = Case When '".$LD02."' = 'S' Then 'N' Else ld02 End,
		ld03 = Case When '".$LD03."' = 'S' Then 'N' Else ld03 End,
		ld04 = Case When '".$LD04."' = 'S' Then 'N' Else ld04 End,
		ld05 = Case When '".$LD05."' = 'S' Then 'N' Else ld05 End,
		ld06 = Case When '".$LD06."' = 'S' Then 'N' Else ld06 End,
		ld07 = Case When '".$LD07."' = 'S' Then 'N' Else ld07 End,
		ld08 = Case When '".$LD08."' = 'S' Then 'N' Else ld08 End,
		ld09 = Case When '".$LD09."' = 'S' Then 'N' Else ld09 End,
		ld10 = Case When '".$LD10."' = 'S' Then 'N' Else ld10 End,
		ld11 = Case When '".$LD11."' = 'S' Then 'N' Else ld11 End,
		ld12 = Case When '".$LD12."' = 'S' Then 'N' Else ld12 End,
		ld13 = Case When '".$LD13."' = 'S' Then 'N' Else ld13 End,
		ld14 = Case When '".$LD14."' = 'S' Then 'N' Else ld14 End,
		ld15 = Case When '".$LD15."' = 'S' Then 'N' Else ld15 End,
		ld16 = Case When '".$LD16."' = 'S' Then 'N' Else ld16 End 
		where pos <>'".pg_escape_string($POS)."'";
	
		echo $query;
		$result = $sqlca->query($query);
		return '';

	}

	function recuperarRegistroArray($registroid){

		global $sqlca;	
		$registro = array();
		$query = "SELECT lado, prod1, prod2, prod3, prod4, ndec_cantidad, ndec_precio, ndec_importe, ndec_contometro_cantidad, ndec_contometro_importe, idinterfase, ladointerfase
		FROM pos_cmblados
		WHERE lado= '". pg_escape_string($registroid) . "'";
	
		$sqlca->query($query);
	
		while( $reg = $sqlca->fetchRow()){
			$registro = $reg;
		}
	
		return $registro;

	}

	function eliminarRegistro($idregistro){
		global $sqlca;
		$query = "DELETE FROM pos_cmblados WHERE lado = '" . pg_escape_string($idregistro) . "';";
		$sqlca->query($query);
		return OK;
	}

  //Otras funciones para consultar la DB

	function tmListado(){

		global $sqlca;
		
		$query = "select CH_SUCURSAL,ES,POS, CASE WHEN TIPO= 'M' THEN 'Market' WHEN TIPO='C' THEN 'Combustible' END AS TIPO , ACTINV, 
			 PRE_LISTA_PRECIO, INTERF
			,LD01, LD02, LD03, LD04, LD05, LD06, LD07
			,LD08, LD09, LD10, LD11, LD12, LD13, LD14, LD15, LD16
			,IP, NROSERIE, AUTSUNAT, PC_SAMBA, PRN_SAMBA, RUTAPRINT
			,DISPOSITIVO, EJECT, LINES,  MENSAJE_CAB,NUME
			from POS_CFG
			order by ch_sucursal, es, pos";

		$resultado_1 = $sqlca->query($query);
		$numrows = $sqlca->numrows();
		if ($sqlca->query($query)<=0){return $sqlca->get_error();}
	
		$listado[] = array();
		while( $reg = $sqlca->fetchRow()){
			$listado['datos'][] = $reg;
		}
		return $listado;

	}

//PARA COMBO ID INTERFASES
  function ListadoInterfases(){
    global $sqlca;
    $sqlca->query("SELECT id, dispositivo, tipo FROM comb_ta_interfases ORDER BY dispositivo;");
    $cbArray = array();
    $x=0;
    while($reg = $sqlca->fetchRow())
    {
       $cbArray[$reg[0]] = $reg[1] . " - " . $reg[2];
    }    
    return $cbArray;
  }

//PARA COMBO PRODUCTOS
  function ListadoProductos(){
    global $sqlca;
    $sqlca->query("SELECT ch_codigocombex FROM comb_ta_combustibles ORDER BY ch_codigocombex;");
    $cbArray = array();
    $cbArray[''] = '';
    $x=0;
    while($reg = $sqlca->fetchRow())
    {
       $cbArray[$reg[0]] = $reg[0];
    }    
    return $cbArray;
  }

}
