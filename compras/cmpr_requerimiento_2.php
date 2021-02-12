<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

//require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

// Vuelve al Programa de seleccion;
if($boton=="Modificar cabecera") 
	{
	$v_modificar_cabecera="Grabar cabecera";
	$v_estado_cabecera=" ";
	$boton=" ";
	}
else
	{
	if ($boton=="Grabar cabecera" and strlen($v_almacen)>0 and strlen($v_fecha_requerida)>0 )
		{
		if (strlen($v_fecha_requerida) > 0) 
			{
			$v_fecha_requerida=$funcion->date_format($v_fecha_requerida,'YYYY-MM-DD');
			$v_act_fecha_requerida="DT_REQ_FECHA_REQUERIDA='$v_fecha_requerida',"; 
			if (strlen($v_fecha_requerida)>0) {$v_act_fecha_requerida= "DT_REQ_FECHA_REQUERIDA='$v_fecha_requerida',"; } else { $v_act_fecha_requerida= " "; }
			} 
		else 
			{ $v_act_fecha_requerida=" "; }
			
		if (strlen($v_fecha_atencion)  > 0) 
			{ 
			$v_fecha_atencion=$funcion->date_format($v_fecha_atencion,'YYYY-MM-DD');
			if (strlen($v_fecha_atencion)>0) {$v_act_fecha_atencion= "DT_REQ_FECHA_ATENCION='$v_fecha_atencion',"; } else { $v_act_fecha_atencion= " "; }
			} 
		else { $v_act_fecha_atencion= " "; }
								
		$v_sql="update COM_TA_REQUERIMIENTOS set
						CH_REQ_ALMACEN='$v_almacen',
						".$v_act_fecha_requerida."
						".$v_act_fecha_atencion."
						CH_REQ_ESTADO='1' 
					where NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||CH_REQ_NUMREQUERIMIENTO='$v_clave'";
					
		$v_xsql=pg_query($conector_id, $v_sql);
		$boton=" ";
		$v_modificar_cabecera="Modificar cabecera";
		$v_estado_cabecera="disabled";
		}
	if (is_null($v_modificar_cabecera))
		{
		$v_modificar_cabecera="Modificar cabecera";
		$v_estado_cabecera="disabled";
		}
	}


if($boton=="Regresar") 
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_requerimiento.php?'; ");
	echo('</script>');
	}

if($boton=="Eliminar") 
	{
	$v_sql="delete from COM_TA_REQUERIMIENTOS 
					where NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||CH_REQ_NUMREQUERIMIENTO||ART_CODIGO='$v_clavedet' ";
	$v_xsql=pg_query( $conector_id, $v_sql );
	$boton=" ";
	}

if($boton=="Ins" or $boton=="Agregar") 
	{
	if(strlen($v_art_codigo)>0) 
		{
		$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
		if(pg_numrows($v_xsql)==0) 
			{
			echo('<script languaje="JavaScript"> ');
			echo('alert(" No Existe Articulo !!! "); ');
			echo('</script>');
			}
		}
	else {
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Ingrese Articulo !!! "); ');
		echo('</script>');
		}

	if($v_cantidad_requerida<=0) 
		{
		echo('<script languaje="JavaScript"> ');
		echo('alert(" Cantidad en Cero o Negativo !!! "); ');
		echo('</script>');
		$v_art_codigo='';
		}
		
	
	if (strlen($v_fecha_requerida) > 0) 
		{
		$v_fecha_requerida=$funcion->date_format($v_fecha_requerida,'YYYY-MM-DD');
		$v_ins_fecha_requerida="DT_REQ_FECHA_REQUERIDA,"; 
		$v_val_fecha_requerida="'$v_fecha_requerida',"; 
		} 
	else { $v_ins_fecha_requerida=" "; $v_val_fecha_requerida=" " ; }
	
	if (strlen($v_fecha_atencion)  > 0) 
		{ 
		$v_fecha_atencion=$funcion->date_format($v_fecha_atencion,'YYYY-MM-DD');
		$v_ins_fecha_atencion= "DT_REQ_FECHA_ATENCION," ; 
		$v_val_fecha_atencion ="'$v_fecha_atencion'," ; 
		} 
	else { $v_ins_fecha_atencion=" "; $v_val_fecha_atencion=" " ; }

	$v_sql="insert into COM_TA_REQUERIMIENTOS ( 
					NUM_TIPDOCUMENTO, 
					NUM_SERIEDOCUMENTO, 
					CH_REQ_NUMREQUERIMIENTO, 
					CH_REQ_ALMACEN,
					".$v_ins_fecha_requerida."
					".$v_ins_fecha_atencion."
					ART_CODIGO,
					NU_REQ_CANTIDAD_REQUERIDA,
					NU_REQ_CANTIDAD_ATENDIDA,
					NU_REQ_VENTA_FECHA,
					NU_REQ_VENTA_MES_ACTUAL,
					NU_REQ_VENTA_MES_ANTERIOR,
					NU_REQ_CANTIDAD_STOCK,
					CH_REQ_ESTADO )
				values (
					'$v_tipdocumento',
					'$v_seriedocumento',
					'$v_numrequerimiento',
					'$v_almacen',
					".$v_val_fecha_requerida."
					".$v_val_fecha_atencion."
					'$v_art_codigo',
					$v_cantidad_requerida,
					$v_cantidad_atendida,
					$v_venta_fecha,
					$v_venta_mes_actual,
					$v_venta_mes_anterior,
					$v_cantidad_stock,
					'1' 
					)";
//	echo $v_sql;						
	$v_xsql=pg_query( $conector_id, $v_sql );
	//en este momento ya cargo las variables ok
	$v_clave=$v_tipdocumento.$v_seriedocumento.$v_numrequerimiento;
	$boton=" ";
	echo("<script>");
	echo("	location.href='cmpr_requerimiento_2.php?v_clave=".$v_clave."' " );
	echo("</script>");
	}


?>
<html>
<head>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="javascript"> 
var miPopup 
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.v_almacen","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

function abrearti() {
	miPopup = window.open("../maestros/escogearticulo.php?k_variable=formular.v_art_codigo","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

function abretabla( tabla ,k_var ){ 
	miPopup = window.open("../maestros/escogetabla.php?m_tabla="+tabla+"&k_variable="+k_var+" ","miwin","width=600,height=350,scrollbars=yes") 
	miPopup.focus() 
	}

function enviadatos(){
	document.formular.submit()
	}


</script> 
</head>
<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular' action="../compras/cmpr_requerimiento_2.php?v_clave=<?php echo $v_clave;?>" method="post">
<?php 

$v_sql="select  NUM_TIPDOCUMENTO,
				NUM_SERIEDOCUMENTO,
				CH_REQ_NUMREQUERIMIENTO,
				CH_REQ_ALMACEN,
				DT_REQ_FECHA_REQUERIDA,
				DT_REQ_FECHA_ATENCION,
				ART_CODIGO,
				NU_REQ_CANTIDAD_REQUERIDA,
				NU_REQ_CANTIDAD_ATENDIDA,
				NU_REQ_VENTA_FECHA,
				NU_REQ_VENTA_MES_ACTUAL,
				NU_REQ_VENTA_MES_ANTERIOR,
				NU_REQ_CANTIDAD_STOCK,
				CH_REQ_ESTADO  
				from COM_TA_REQUERIMIENTOS 
				where NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||CH_REQ_NUMREQUERIMIENTO='".$v_clave."' " ;


$v_xsql=pg_query( $conector_id, $v_sql );
$v_ilimit=pg_numrows($v_xsql);

$v_tipdocumento=pg_result($v_xsql,0,'NUM_TIPDOCUMENTO'); 
$v_seriedocumento=pg_result($v_xsql,0,'NUM_SERIEDOCUMENTO'); 
$v_numrequerimiento=pg_result($v_xsql,0,'CH_REQ_NUMREQUERIMIENTO'); 

$v_almacen=pg_result($v_xsql,0,'CH_REQ_ALMACEN'); 
$v_fecha_requerida=$funcion->date_format( pg_result($v_xsql,0,'DT_REQ_FECHA_REQUERIDA') , 'DD/MM/YYYY') ;
$v_fecha_atencion=$funcion->date_format(pg_result($v_xsql,0,'DT_REQ_FECHA_ATENCION') , ',');

// $v_cantidad_requerida=0;
$v_cantidad_atendida=0;
$v_venta_fecha=0;
$v_venta_mes_actual=0;
$v_venta_mes_anterior=0;
$v_cantidad_stock=0;
	
?>

<input type="hidden" name="v_clavedet" value='<?php echo $v_clavedet; ?>'>
<input type="hidden" name="v_tipdocumento" value='<?php echo $v_tipdocumento; ?>'>
<input type="hidden" name="v_seriedocumento" value='<?php echo $v_seriedocumento; ?>'>
<input type="hidden" name="v_numrequerimiento" value='<?php echo $v_numrequerimiento; ?>'>
<input type="hidden" name="v_almacen" value='<?php echo $v_almacen; ?>'>
<input type="hidden" name="v_fecha_requerida" value='<?php echo $v_fecha_requerida; ?>'>
<!-- <input type="hidden" name="v_fecha_atencion" value='< ? echo $v_fecha_atencion; ? >'>  -->

<input type="hidden" name="v_modificar_cabecera" value='<?php echo $v_modificar_cabecera;?>'>
<input type="hidden" name="v_estado_cabecera" value='<?php echo $v_estado_cabecera;?>'>

<input type="hidden" name="v_cantidad_requerida" value="<?php echo $v_cantidad_requerida ?>">  
<input type="hidden" name="v_cantidad_atendida" value="<?php echo $v_cantidad_atendida ?>">  
<input type="hidden" name="v_venta_fecha" value="<?php echo $v_venta_fecha ?>">  
<input type="hidden" name="v_venta_mes_actual" value="<?php echo $v_venta_mes_actual ?>">  
<input type="hidden" name="v_venta_mes_anterior" value="<?php echo $v_venta_mes_anterior ?>">  
<input type="hidden" name="v_cantidad_stock" value="<?php echo $v_cantidad_stock ?>">  



<table border="1">
	<tr> 	
		<th width="500">REQUERIMIENTO DE COMPRA</th>
	</tr>
</table>
<table border="1">
	<tr> 
		<th width="100" >N&deg; NUMERO </th>      
		<td>:</td>      
		<td>&nbsp;<?php echo $v_numrequerimiento; ?></td>

		<th>FECHA</th>
		<td>:</td>
		<td>&nbsp;<?php echo $v_fecha_requerida; ?></td>
		
	</tr>
	<tr> 
		<th>ALMACEN</th>
		<td>:</td>
		<td>&nbsp;<?php echo $v_almacen; ?>
		<?php
		if( strlen($v_almacen)>0 )
			{
			// $v_sql="select TAB_ELEMENTO, TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='ALMA' and TAB_ELEMENTO='".$v_almacen."' ";
			$v_sql="select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_almacen='".$v_almacen."' and ch_clase_almacen='1' order by cod " ;
			$v_xsql=pg_query($conector_id,$v_sql);
			if(pg_numrows($v_xsql)>0)
				{
				$v_descripcion=pg_result($v_xsql,0,1);
				echo $v_descripcion;
				}
			}
		?>
		</td>

		<th>Fecha Atencion</th>
		<td>:</td>
		<td>
		<p>
		<input name="v_fecha_atencion" type="text" value="<?php echo $v_fecha_atencion; ?>" size="16" maxlength="10" <?php echo $v_estado_cabecera; ?>  >
		<a href="javascript:show_calendar('formular.v_fecha_atencion');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" >
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0 ></a>
		</p>
		</td>
		<?php
		if(strlen($v_fecha_atencion)==0) 
			{
			$v_fecha_atencion=" ";
			}
		?>
	
	</tr>
	
	
</table>



<table border="1" cellpadding="0" cellspacing="0">
	<tr> 
		<th>&nbsp;</th>
		<th>CODIGO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD REQ</th>
<!--		<th>CANTIDAD ATE</th> -->
		<th>VTA FECHA</th>
		<th>VTA MES ACT</th>
		<th>VTA MES ANT</th>
		<th>CANT STK</th>
		<th>ESTADO</th>
	</tr>
	<tr> 
		<th>&nbsp;</th>
		<?php
	
		if(strlen($v_art_codigo)>0) 
			{
			$v_xsql=pg_query($conector_id,"select ART_CODIGO, ART_DESCRIPCION from INT_ARTICULOS where ART_CODIGO='".$v_art_codigo."' ");
			if(pg_numrows($v_xsql)>0) 
				{ 
				$v_art_codigo=pg_result($v_xsql,0,0); 
				$v_art_descripcion=pg_result($v_xsql,0,1); 
				}
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Articulo !!! "); ');
				echo('</script>');
				}
			}
				
		?>

		<th><input type="text" name="v_art_codigo" size='19' maxlength="13" value="<?php echo $v_art_codigo;?>"  onblur='submit()' onkeyup='validarNumeroEntero(this)' tabindex="5" >
			<input name="imgarti" type="image" src="/sistemaweb/images/help.gif" width="16" height="15" border="0" onClick="abrearti()" ></th>
		<td>&nbsp; <?php echo $v_art_descripcion; ?>	</td>
		
		<th><input name="v_cantidad_requerida" type="text" size='15'  maxlength="15" value="<?php echo $v_cantidad_requerida;?>" align="right" onblur='submit()' onkeyup='validarNumeroDecimales(this)' tabindex="6"></th>

		<?php
		if(strlen($v_art_codigo)>0) 
			{
			//del mes actual a la fecha
			$v_ano_act=substr($v_fecha_requerida,6,4);
			$v_mes_act=substr($v_fecha_requerida,3,2);
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and CH_SUCURSAL='$v_almacen' and ART_CODIGO='$v_art_codigo'  ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_fecha=pg_result($v_xsql,0,0) ;} else { $v_venta_fecha=0; }

			// del ultimo mes finalizado
			if ($v_mes_act=='01') 
				{ 
				$v_mes_actual='12'; 
				$v_ano_act=$v_ano_act-1; 
				}
			else
				{
				$v_mes_act=$v_mes_act-1;
				$v_mes_act=str_pad(trim($v_mes_act),2,'0', STR_PAD_LEFT );
				}
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and CH_SUCURSAL='$v_almacen' and ART_CODIGO='$v_art_codigo'  ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_mes_actual=pg_result($v_xsql,0,0) ;} else { $v_venta_mes_actual=0; }

			// del mes anterior finalizado
			if ($v_mes_act=='01') 
				{ 
				$v_mes_act='12'; 
				$v_ano_act=$v_ano_act-1; 
				}
			else
				{
				$v_mes_act=$v_mes_act-1;
				$v_mes_act=str_pad(trim($v_mes_act),2,'0', STR_PAD_LEFT );
				}
			$v_sql="select NU_CAN".$v_mes_act."  
					from VEN_TA_VENTA_MENSUALXITEM
					where CH_PERIODO='$v_ano_act' and ART_CODIGO='$v_art_codigo' and CH_SUCURSAL='$v_almacen' ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { $v_venta_mes_anterior=pg_result($v_xsql,0,0) ;} else { $v_venta_mes_anterior=0; }

			// Stock Actual
			$v_ano_act=substr($v_fecha_requerida,6,4);
			$v_mes_act=substr($v_fecha_requerida,3,2);
			$v_sql="select STK_STOCKACTUAL  
					from INV_SALDOALMA 
					where STK_ALMACEN='$v_almacen' and STK_PERIODO='$v_ano_act' and ART_CODIGO='$v_art_codigo' ";
			// echo $v_sql;
			$v_xsql=pg_query($conector_id,$v_sql);
			$v_ilimit=pg_numrows($v_xsql);
			if ($v_ilimit>0) { 	$v_cantidad_stock=pg_result($v_xsql,0,0) ;} else { $v_cantidad_stock=0; }
			if (strlen($v_cantidad_stock)==0){$v_cantidad_stock=0; }
			}
			
		
		// <td><p align='right'>&nbsp;<?php echo $v_venta_fecha; ? ></p></td>
		// <td><p align='right'>&nbsp;<?php echo $v_venta_mes_actual; ? ></p></td>
		//<td><p align='right'>&nbsp;<?php echo $v_venta_mes_anterior; ? ></p></td>
		//<td><p align='right'>&nbsp;<?php echo $v_cantidad_stock; ? ></p></td>
		?>
		
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_fecha" value="<?php echo $v_venta_fecha ?>" readonly='true'>  </td>
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_mes_actual" value="<?php echo $v_venta_mes_actual ?>" readonly='true'> </td> 
		<td><input type="text" size='15'  maxlength="15"  name="v_venta_mes_anterior" value="<?php echo $v_venta_mes_anterior ?>" readonly='true'>  </td>
		<td><input type="text" size='15'  maxlength="15"  name="v_cantidad_stock" value="<?php echo $v_cantidad_stock ?>" readonly='true'>  </td>


		<th><input type="submit" name="boton" value="Agregar" ></th>
	</tr>
	
    <?php

	$v_sql="select  REQ.NUM_TIPDOCUMENTO||REQ.NUM_SERIEDOCUMENTO||REQ.CH_REQ_NUMREQUERIMIENTO||REQ.ART_CODIGO, 
					REQ.NUM_TIPDOCUMENTO,
					REQ.NUM_SERIEDOCUMENTO,
					REQ.CH_REQ_NUMREQUERIMIENTO,
					REQ.CH_REQ_ALMACEN,
					REQ.DT_REQ_FECHA_REQUERIDA,
					REQ.DT_REQ_FECHA_ATENCION,
					REQ.ART_CODIGO,
					ART.ART_DESCRIPCION,
					REQ.NU_REQ_CANTIDAD_REQUERIDA,
					REQ.NU_REQ_CANTIDAD_ATENDIDA,
					REQ.NU_REQ_VENTA_FECHA,
					REQ.NU_REQ_VENTA_MES_ACTUAL,
					REQ.NU_REQ_VENTA_MES_ANTERIOR,
					REQ.NU_REQ_CANTIDAD_STOCK,
					REQ.CH_REQ_ESTADO  
					from COM_TA_REQUERIMIENTOS REQ, INT_ARTICULOS ART 
					where REQ.ART_CODIGO=ART.ART_CODIGO and REQ.NUM_TIPDOCUMENTO||REQ.NUM_SERIEDOCUMENTO||REQ.CH_REQ_NUMREQUERIMIENTO='".$v_clave."' " ;


	$v_xsql=pg_query( $conector_id, $v_sql );
	$v_ilimit=pg_numrows( $v_xsql );
	while($v_irow<$v_ilimit) 
		{
		$ad0=pg_result($v_xsql,$v_irow,0);
		$ad1=pg_result($v_xsql,$v_irow,1);
		$ad2=pg_result($v_xsql,$v_irow,2);
		$ad3=pg_result($v_xsql,$v_irow,3);
		$ad4=pg_result($v_xsql,$v_irow,4);
		$ad5=pg_result($v_xsql,$v_irow,5);
		$ad6=pg_result($v_xsql,$v_irow,6);
		$ad7=pg_result($v_xsql,$v_irow,7);
		$ad8=pg_result($v_xsql,$v_irow,8);
		$ad9=pg_result($v_xsql,$v_irow,9);
//		$ad10=pg_result($v_xsql,$v_irow,10);
		$ad11=pg_result($v_xsql,$v_irow,11);
		$ad12=pg_result($v_xsql,$v_irow,12);
		$ad13=pg_result($v_xsql,$v_irow,13);
		$ad14=pg_result($v_xsql,$v_irow,14);
		$ad15=pg_result($v_xsql,$v_irow,15);

		echo "<tr>";
		echo "<td><input type='radio' name='v_clavedet' value='".$ad0."'></td>";
		echo "<td>".$ad7."</td>";
		echo "<td>".$ad8."</td>";
		echo "<td><p align='right'>".$ad9."</p></td>";
//		echo "<td><p align='right'>".$ad10."</p></td>";
		echo "<td><p align='right'>".$ad11."</p></td>";
		echo "<td><p align='right'>".$ad12."</p></td>";
		echo "<td><p align='right'>".$ad13."</p></td>";		
		echo "<td><p align='right'>".$ad14."</p></td>";

		$v_sql3="select  TAB_ELEMENTO,
						TAB_DESC_BREVE
						from INT_TABLA_GENERAL
						where TAB_TABLA='ESTR' and TAB_ELEMENTO='$ad15' ";
		$v_xsql3=pg_query($conector_id,$v_sql3);
		if(pg_numrows($v_xsql3)>0)	{	$v_descripcion=pg_result($v_xsql3,0,1);	}
		echo "<td>&nbsp;".$v_descripcion."</td>";

//		echo "<td><p align='right'>".$ad15."</p></td>";

		echo "</tr>";
		$v_irow++;
		}

	?>
	<tr> 
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td><input type="submit" name="boton" value="<?php echo $v_modificar_cabecera; ?>" >
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>



</form>
</body>
</html>
<?php 

 
// comprueba si la conexion existe y la cierra
if ($conector_id) pg_close($conector_id);

// restaura el control de errores original
$clase_error->_error();


