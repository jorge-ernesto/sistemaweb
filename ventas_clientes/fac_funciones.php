<?php
function lineas_venesp($cod_almacen,$ordx,$cond,$AR,$fechad,$fechaa,$xdia){
	$fechad = "to_date('$fechad','dd/mm/yyyy')";
	$fechaa = "to_date('$fechaa','dd/mm/yyyy')";
	if($cond==""){
		if($xdia!="xdia"){
		$rs = pg_exec("select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2");
			for($i=0;$i<pg_numrows($rs);$i++){
				$A = pg_fetch_array($rs,$i);
				$lin = $A[0];
				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,null) ");
				echo "xdia "."select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,null)"." aaaaaaaa";
			}
		}else{
				$rs = pg_exec("select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2");
				for($i=0;$i<pg_numrows($rs);$i++){
				$A = pg_fetch_array($rs,$i);
				$lin = $A[0];
				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,true) ");
				echo "elsexdia "."select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,true)"." aaaaaaaa";
				}
		}

	}else{
		if($xdia!="xdia"){ $xdia ="null";}else{$xdia="true"; }
		llenar_tmp_ventas_especiales($cond , $AR, $fechad, $fechaa ,$xdia);
	
	}
	$q = "select distinct linea, des_linea from tmp_ventas_especiales order by des_linea";
	if($cod_almacen!=""){
	$q = "select distinct linea, des_linea,sum(importe) as cant  from tmp_ventas_especiales 
	where alma='$cod_almacen' group by linea, des_linea order by cant desc";
	}
	if($ordx=="total"){
	$q = "select linea,des_linea,sum(importe) as cant from tmp_ventas_especiales
	group by linea,des_linea order by cant desc";
	}
	$rs = pg_exec($q);
	return $rs;
}

function articulos_venesp($cod_linea,$cod_almacen,$ordx,$xdia){
	if($xdia=="xdia"){
		$campo = " , dt_fac_fecha";
		$campo2 = "dt_fac_fecha,";
		$cond = " and dt_fac_fecha is not null ";
		$order = " order by dt_fac_fecha ";
	}
//	if($cod_articulo!=""){ $w1 ="and art_codigo='$cod_articulo'"; }

	$q2 = "select distinct art_codigo,art_descripcion from VEN_VS_VENTAS_ESPECIALES2
	where linea='$cod_linea' order by art_descripcion";
	//echo $q;
	$q = "select distinct art_codigo,art_descripcion $campo from tmp_ventas_especiales
	where linea='$cod_linea' $cond $order";
	if($cod_almacen!=""){
	$q= "select art_codigo,art_descripcion,sum(importe) $campo as cant from tmp_ventas_especiales
	where alma='$cod_almacen' and linea='$cod_linea' $cond group by art_codigo,art_descripcion $campo
	order by cant desc";
	}
	if($ordx=="total"){
	$q= "select art_codigo,art_descripcion,sum(importe) as cant $campo from tmp_ventas_especiales  
	where linea='$cod_linea' $cond group by art_codigo,art_descripcion $campo order by $campo2 cant desc ";
	}
	echo "articulos_venesp -> ".$q;
	$rs = pg_exec($q);
	
	return $rs;
}

function valor_venesp($cod_linea,$cod_articulo,$alma_cod,$campo_valor,$xdia,$art_fecha){
	$q = "select sum(cantidad) as cant,sum(importe) as imp from VEN_VS_VENTAS_ESPECIALES2
	where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod'";
	$q = "select sum(cantidad) as cant,sum($campo_valor) as imp from tmp_ventas_especiales
	where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod'";
	if($xdia=="xdia"){
		$q = "select sum(cantidad) as cant,sum($campo_valor) as imp,dt_fac_fecha from tmp_ventas_especiales
		where linea='$cod_linea' and art_codigo='$cod_articulo' and alma='$alma_cod' 
		and dt_fac_fecha='$art_fecha' group by dt_fac_fecha order by dt_fac_fecha";
	}
	//echo "valor_venesp ->".$q;
	$rs = pg_exec($q);
	return $rs;
}

function llenar_tmp_ventas_especiales($cond , $A , $fechad , $fechaa, $xdia){
	switch($cond){
		case "linea":
			
			for($i=0;$i<count($A);$i++){
				$lin = $A[$i];

				pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,$xdia) ");
				echo "select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,$xdia) ";
			}
		break;
		case "tipo":
			for($k=0;$k<count($A);$k++){		
				$tipo = $A[$k];
				$rs = pg_exec("select distinct linea, des_linea,tipo from  VEN_VS_VENTAS_ESPECIALES2
				where tipo='$tipo'");
				for($i=0;$i<pg_numrows($rs);$i++){
					$A = pg_fetch_array($rs,$i);
					$lin = $A[0];
					pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,null,$xdia) ");
				}
			}
		break;
		case "codigo":

				for($k=0;$k<count($A);$k++){
				$articulo = $A[$k];

				$rs = pg_exec(" select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2
				where art_codigo='$articulo' ");
				$L = pg_fetch_array($rs,0);
				$linea = $L[0];
				echo "jjjjjjjjj select distinct linea, des_linea from  VEN_VS_VENTAS_ESPECIALES2 
				where art_codigo='$articulo'";

				$R[0]="nadannn";
				$enc = array_search($linea,$R);
					if($enc==""){   $R[$k+1] = $linea;  }
				}

				/*Ahora con las lineas definidas ejecutamos el procedimiento*/

				for($i=1;$i<=count($R);$i++){
					$lin = $R[$i];
					$articulo = $A[$k-1];
					pg_exec("select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,'$articulo',$xdia) ");
					echo "llenar_tmp_ventas_especiales select VEN_FN_REP_VENTAS_ESPECIALES('$lin',$fechad,$fechaa,'$articulo',$xdia) ";
				}

		break;

	}
}



function comboSerieDocumentos($conector_id, $variable, $nombre_variable, $tipo_documento, $javascript)
{
	$sql = "SELECT num_serieDocumento, num_numActual
			FROM int_num_documentos
			WHERE num_tipDocumento='$tipo_documento' order by num_serieDocumento";

	//echo "<br>".$sql."<br>";
	$xsql = pg_query($conector_id, $sql);
	//echo "<select name='$nombre_variable' onChange='submit();'>";
	echo "<select name='$nombre_variable' $javascript>";

	$i=0;
	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql, $i);

		if(trim($variable)==trim($rs[0])) {
			echo "<option value='$rs[0]' selected>$rs[0]</option>";
			$numeroActual = $rs[1];
		}
		else {
			echo '<option value="'.$rs[0].'">'.$rs[0].'</option>';
		}
		$i++;
	}
	echo "</select>";
	return trim($numeroActual);
}

function comboTablaGeneral($conector_id, $name ,$codigo_tab_tabla,$defecto, $javascript)
{
	$sql = "select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='".$codigo_tab_tabla."' ORDER BY TAB_DESCRIPCION";
	$xsql = pg_query($conector_id, $sql);

	echo "<select name='$name' $javascript>";

	$i=0;
	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql,$i);

		if($rs[0]!='000000')
		{
			if(trim($defecto)==trim($rs[0]))
			{
				echo "<option value=".$rs[0]." selected>".$rs[1]."</option>";
			}
			else
			{
				echo "<option value=".$rs[0].">".$rs[1]."</option>";
			}
//		echo $rs[$i];
		}
		$i++;
	}
	echo '</select>';
}


function combosino($variable, $name_variable,$javascript)
{
    echo "<select name='$name_variable' $javascript>";

    if($variable=="1")
    {
        echo "<option value='1' selected>SI</option>";
	echo "<option value='0'>NO</option>";
    }
    else
    {
	echo "<option value='1'>SI</option>";
        echo "<option value='0' selected>NO</option>";
    }
    echo "</select>";
}


function comboFormaPago($conector_id, $variable, $name_variable, $variable_credito,$javascript)
{
    echo "<select name='$name_variable' $javascript>";
    if(trim($variable_credito)=='1') {
	$tab_tabla="95"; //Credito S
    }else {
	$tab_tabla="05"; //Credito N
    }

    $query = "select tab_elemento, tab_desc_breve from int_tabla_general where trim(tab_tabla)='$tab_tabla'";
    $xquery = pg_query($conector_id, $query);

    $i=0;
    while($i<pg_num_rows($xquery))
    {
	$rs = pg_fetch_array($xquery, $i);
	if($rs[0]==$variable) {
	    echo "<option value='$rs[0]' selected>$rs[1]</option>";
	} else {
	    echo "<option value='$rs[0]'>$rs[1]</option>";
	}
	$i++;
    }
    echo "</select>";
}

function comboTipoDocumento($conector_id, $variable, $nombre_variable, $javascript)
{
	echo '<select name="'.$nombre_variable.'" '.$javascript.' >';
	switch($variable)
	{
		case 10:
			$v_10 = "selected"; break;
		case 11:
			$v_11 = "selected"; break;
		case 20:
			$v_20 = "selected"; break;
		case 35:
			$v_35 = "selected"; break;
	}
	echo "<option value='10' $v_10>10</option>
		<option value='11' $v_11>11</option>
		<option value='20' $v_20>20</option>
		<option value='35' $v_35>35</option>
		";
	echo '</select>';
}

function obtenerDescripcion($conector_id, $codigo)
{
	$query = "select art_descripcion from int_articulos where art_codigo='$codigo'";
	$xquery = pg_query($conector_id, $query);
	if(pg_num_rows($xquery)>0)
	{
		$resultado = pg_result($xquery,0,0);
	}
	return $resultado;
}


function obtenerNumeroActual($conector_id, $new_tipo_documento, $new_serie_documento)
{
	if(strlen(trim($new_tipo_documento))==6)
	{
		$new_tipo_documento = substr($new_tipo_documento,4,6);
	}
	$query = "select num_numActual from int_num_documentos where trim(num_tipDocumento)='$new_tipo_documento' and trim(num_SerieDocumento)='".trim($new_serie_documento)."'";
	echo $query;
	$xquery  = pg_query($conector_id, $query);

	if(pg_num_rows($xquery)>0)
	{
		$resultado = pg_result($xquery,0,0);
		$query="update int_num_documentos set num_numActual=".($resultado+1)." where num_tipDocumento='$new_tipo_documento' and trim(num_SerieDocumento)='".trim($new_serie_documento)."'";
		pg_exec($conector_id, $query);
		return $resultado;
	}else {
		$resultado = "ERROR: Nro de Documento no Inicializado";
		return $resultado;
		exit;
	}
}


function combo_Almacenes($conector_id, $variable, $nombre_variable, $discriminar_query, $efectos_scripts)
{
    echo "<select name='$nombre_variable' $efectos_scripts>";

    $query = "select ch_almacen, ch_nombre_almacen from inv_ta_almacenes $discriminar_query ORDER BY CH_NOMBRE_ALMACEN";
    $xquery  = pg_query($conector_id, $query);

    $i=0;
    while($i<pg_num_rows($xquery))
    {
	$rs = pg_fetch_array($xquery, $i);
	if(trim($variable)==trim($rs[0])) {
	    echo "<option value='$rs[0]' selected>$rs[0] -- $rs[1]</option>";
	} else {
	    echo "<option value='$rs[0]'>$rs[0] -- $rs[1]</option>";
	}
	$i++;
	}
    echo "</select>";
}


function calcularImpuestos($conector_id, $codigo_articulo, $cantidad)
{
	$m_precio = pg_result(pg_query($conector_id, "select util_fn_precio_articulo('".$codigo_articulo."')" ),0,0);
	$m_importe = round($m_precio,2)*$cantidad;

	$query = "select tab_num_01 from int_tabla_general where tab_tabla='17' and tab_elemento='000009'";
	$xsql = pg_query($conector_id, $query);

	$igv="1.".round(pg_result($xsql, 0, 0),0);

	$neto = round($m_importe/$igv,2);
	$impuesto = $m_importe-$neto;

	$resultado[0]=round($m_precio,2);
	$resultado[1]=$neto;
	$resultado[2]=$impuesto;
	$resultado[3]=$m_importe;

	return $resultado;
}

function combitoTablaGeneral($conector_id, $name ,$codigo_tab_tabla,$defecto, $script)
{
	$sql = "select trim(tab_elemento), tab_desc_breve from int_tabla_general where tab_tabla='".$codigo_tab_tabla."' ORDER BY TAB_DESCRIPCION";
	$xsql = pg_query($conector_id, $sql);

	echo '<select name="'.$name.'" '.$script.'>';

	$i=0;
	while($i<pg_num_rows($xsql))
	{
		$rs = pg_fetch_array($xsql,$i);

		if($rs[0]!='000000')
		{
			if(trim($defecto)==trim($rs[0]))
			{
				echo "<option value=".$rs[0]." selected>".$rs[0]." -".trim($rs[1])."</option>";
			}
			else
			{
				echo "<option value=".$rs[0].">".$rs[0]."-".$rs[1]."</option>";
			}
//		echo $rs[$i];
		}
		$i++;
	}
	echo '</select>';
}

