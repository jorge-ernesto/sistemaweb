<?php

include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
include("/sistemaweb/utils/funcion-texto.php");
include("../funcjch.php");


require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
$clase_error->_error();

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

if ( is_null($almacen) or trim($almacen)=="")
        {
        $almacen="001";
        }


// carga los almacenes en un dropdown
$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod, ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");

if (is_null($v_fecha_desde) or is_null($v_fecha_hasta) )
        {
        $v_fecha_desde=date("d/m/Y");
        $v_fecha_hasta=date("d/m/Y");
        $v_turno_desde=0;
        $v_turno_hasta=0;
        }

?>

<html>






<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form name="f_actua" method="post">

<div align="center"><font face="Arial, Helvetica, sans-serif">

ACTUALIZACION DE ENLACES DE CODIGOS
<br>
Fecha <?php echo $v_fecha_desde; ?> Turno: <?php echo $v_turno_desde; ?>
<br>
<?php

$v_sql="select trim(ch_almacen), ch_nombre_almacen from inv_ta_almacenes  where ch_almacen like '%".trim($almacen)."%' and
ch_clase_almacen='1'";

$v_xsql=pg_query($conector_id,$v_sql);
if(pg_numrows($v_xsql)>0)       {       $v_descalma=pg_result($v_xsql,0,1);     }

?>

ALMACEN ACTUAL <?php echo $almacen; ?>  <?php echo $v_descalma; ?>
<br>
</div>
<br>

<table border="1" name="t1"º>
<tr>
<td><input type="submit" name="bt" value="Actualizar"></td><td><b>Ultima Importacion de  Enlaces de Codigo</b></td>
</tr>
<tr>
<td><input type="submit" name="bt" value="Comparar"></td><td><b>Comparar Enlaces de Codigo CONSULTA - WEB</b></td>
</tr>
</table>
<br>

<table border="1" name="t2">
<tr>

<td>CODIGO</td><td>DESCRIPCION ENLACE</td><td>COMPONENTE</td><td>DESCRIPCION DESCARGA</td><td>CANT.CONSL</td><td>CANT.WEB</td><td>Plu</td><td>Plu</td>









<?php


switch($bt){

case Actualizar:
			

		$delet="delete from inf_enlace_items";
		$rs_1=pg_exec($delet);

	$copi_data="copy inf_enlace_items from '/sistemaweb/interface/enlaces.txt' with delimiter as ',' null as 'null'";
		echo "Se actualizaron Maestros de consulta";
		$rs_2=pg_exec($copi_data);
		
		break;

case Comparar:
		
		//  -------> WEB - a - CONSULTA	
		//Consulta a WEB
		$sql1="select art_codigo, ch_item_estandar, nu_cantidad_descarga from int_ta_enlace_items ORDER BY art_codigo";
//		echo $sql1;
		$r_sql1=pg_exec($sql1);
		
		$n_r_sql1=pg_numrows($r_sql1);
//		echo "<br>".$n_r_sql1;
		
		$cont1=0;
		
		while ( $cont1 < $n_r_sql1 ){
		
		// descarga todos los campos requeridos de la consulta al servidor

		$codigo_1=pg_result($r_sql1,$cont1,0);
		$descarga_1=pg_result($r_sql1,$cont1,1);
		$cant_a_1=pg_result($r_sql1,$cont1,2);
	
		//--------------echo "descarga ".$descarga_1; 


//descripcion de codigo enlace ------------------------------------------------------------------------

		$sql_descrip_cod="select art_codigo, art_descripcion, art_plutipo from int_articulos where art_codigo='".$codigo_1."'";
//echo "codigo ".$sql_descrip_cod."<br>";

		$r_sql_descrip_cod=pg_exec($sql_descrip_cod);

//capturando valores
		$descrip_cod_1=pg_result($r_sql_descrip_cod,0,1);
		$tip_plu1_1=pg_result($r_sql_descrip_cod,0,2);




//descripcion de codigo de descarga -----------------------------------------------------------------

		$sql_descrip_enlace="select art_codigo, art_descripcion, art_plutipo from int_articulos where art_codigo='".$descarga_1."'";

//echo "cod_enlace ".$sql_descrip_enlace."<br>";

		$r_sql_descrip_enlace=pg_exec($sql_descrip_enlace);
		
//capturando valores
		$descrip_enlace_1=pg_result($r_sql_descrip_enlace,0,1);
		$tip_plu2_1=pg_result($r_sql_descrip_enlace,0,2);



// Consulta para mostrar los codigos con ERROR

		
			$sql2="select i_serv, i_item, i_cant from inf_enlace_items where i_serv='".$codigo_1."' and i_item='".$descarga_1."'";

//echo $sql2."<br>";
			
			$r_sql2=pg_exec($sql2);
			$n_r_sql2=pg_numrows($r_sql2);

//echo $n_r_sql2."<br>";

			
		if($n_r_sql2 > 0){
		

		$cant_a_2=pg_result($r_sql2,0,2);
		
			if($cant_a_1!=$cant_a_2){
			
echo "<tr><td>".$codigo_1."</td><td>".$descrip_cod_1."</td><td>".$descarga_1."</td><td>".$descrip_enlace_1."</td><td>".$cant_a_1."</td><td>".$cant_a_2."</td><td>".$tip_plu1_1."</td><td>".$tip_plu2_1."</td></tr>";
			


			}
			else {
			//echo "goodddddd";
			}
	
		}

		if($n_r_sql2==0){
		

		$can="NO EXISTE";

		echo "<tr><td>".$codigo_1."</td><td>".$descrip_cod_1."</td><td>".$descarga_1."</td><td>".$descrip_enlace_1."</td><td>".$can."</td><td>".$cant_a_1."</td><td>".$tip_plu1."</td><td>  </td></tr>";
		

		}

       $cont1++;	
		}
		 // fin del while
		
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

		//  ------> CONSULTA - A - WEB

		//consulta a CONSULTA


		$sql1_2="select i_serv, i_item, i_cant from inf_enlace_items ORDER BY i_serv ";

//		echo $sql1_2;
		$r_sql1_2=pg_exec($sql1_2);
		
		$n_r_sql1_2=pg_numrows($r_sql1_2);
//		echo "<br>".$n_r_sql1_2;
		
		$cont2=0;


		
		while ( $cont2 < $n_r_sql1_2 ){
		
		// descarga todos los campos requeridos de la consulta al servidor

		$codigo_2=pg_result($r_sql1_2,$cont2,0);
		$descarga_2=pg_result($r_sql1_2,$cont2,1);
		$cant_a_1_2=pg_result($r_sql1_2,$cont2,2);
	
		//echo "descarga ".$descarga_1; 


//descripcion de codigo enlace ------------------------------------------------------------------------



		$sql_descrip_cod_2="select art_codigo, art_descripcion, art_plutipo from int_articulos where art_codigo='".$codigo_2."'";
//echo "codigo ".$sql_descrip_cod_2."<br>";

		$r_sql_descrip_cod_2=pg_exec($sql_descrip_cod_2);

//capturando valores
		$descrip_cod_2=pg_result($r_sql_descrip_cod_2,0,1);
		$tip_plu1_2=pg_result($r_sql_descrip_cod_2,0,2);




//descripcion de codigo de descarga -----------------------------------------------------------------

		$sql_descrip_enlace_2="select art_codigo, art_descripcion, art_plutipo from int_articulos where art_codigo='".$descarga_2."'";

//echo "cod_enlace ".$sql_descrip_enlace."<br>";

		$r_sql_descrip_enlace_2=pg_exec($sql_descrip_enlace_2);
		
//capturando valores
		$descrip_enlace_2=pg_result($r_sql_descrip_enlace_2,0,1);
		$tip_plu2_2=pg_result($r_sql_descrip_enlace_2,0,2);



// Consulta para mostrar los codigos con ERROR

		$sql2_2="select art_codigo, ch_item_estandar, nu_cantidad_descarga from int_ta_enlace_items where art_codigo='".$codigo_2."' and ch_item_estandar = '".$descarga_2."'";
		

//echo $sql2_2."<br>";
			
			$r_sql2_2=pg_exec($sql2_2);
			$n_r_sql2_2=pg_numrows($r_sql2_2);

//echo $n_r_sql2_2."<br>";

			
		if($n_r_sql2_2>0) {
		

		$cant_a_2_2=pg_result($r_sql2_2,0,2);
		
			if($cant_a_1_2!=$cant_a_2_2){
			
echo "<tr><td>".$codigo_2."</td><td>".$descrip_cod_2."</td><td>".$descarga_2."</td><td>".$descrip_enlace_2."</td><td>".$cant_a_1_2."</td><td>".$cant_a_2_2."</td><td>".$tip_plu1_2."</td><td>".$tip_plu2_2."</td></tr>";
			


			}
			else {
			//echo "goodddddd";
			}
	
		}
	
		if($n_r_sql2_2==0){
		
		$can="NO EXISTE";

		echo "<tr><td>".$codigo_2."</td><td>".$descrip_cod_2."</td><td>".$descarga_2."</td><td>".$descrip_enlace_2."</td><td>".$can_a_1_2."</td><td>".$can."</td><td>".$tip_plu2_2."</td><td>".$tip_plu1_2."</td></tr>";


		}

       $cont2++;	
		}
		 // fin del while
		


		break;


} // fin del switch 


?>




<tr>
</table>
</body>
</html>
