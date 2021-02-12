<?php
//include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

// require("../clases/funciones.php");
$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;

// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

$m_fechaofrecida=$funcion->date_format($m_fechaofrecida,'DD/MM/YYYY');
$m_fecharecibida=$funcion->date_format( $m_fecharecibida,'DD/MM/YYYY');
$m_fecha=date("d/m/Y");
foreach($_REQUEST as $llave => $valor)
{
    echo "<!--LLAVE : $llave => VALOR : $valor -->\n";
}
if(trim($_REQUEST['chglosa'])=='S'){
    $checked='checked';
    $display='block';
    $m_glosa = $_REQUEST['glosa'];
}

//echo "ALMACEN : $almacen\n";		  
if (is_null($almacen) or trim($almacen)==""){
    $almacen="001";
}

if(is_null($almacen) or trim($almacen)==""){
    $almacendocs="001";
}else{
    $query="SELECT par_valor FROM int_parametros WHERE trim(par_nombre)='codes'";
    $result=pg_query($conector_id, $query);
    $almacendocs=pg_result($result, 0, 0);
}


$query="SELECT util_fn_corre_docs('01','".$almacendocs."', 'select' )";
$result=pg_query( $conector_id, $query);
$n_orden=pg_result($result, 0, 0 );
// carga los almacenes en un dropdown 
// $v_xsqlalma=pg_exec("select trim(tab_elemento) as cod,tab_descripcion from int_tabla_general  where tab_tabla='ALMA' and tab_elemento!='000000' and tab_car_02='1' order by cod");

$v_xsqlalma=pg_exec("select trim(ch_almacen) as cod,ch_nombre_almacen from inv_ta_almacenes  where ch_clase_almacen='1' order by cod");
$k_var = pg_fetch_row($v_xsqlalma,0);
$k_almacen=trim($k_var[0]);
$k_almacen_desc=$k_var[1];

/*$sql="select int_sp_numero_documento_ins( '$m_tipdoc', '$m_serie' )";
	echo $sql;*/
if($boton=="Ins" or $boton=="Agregar") 
	{
	// carga el ultimo numero orden de compra en que se quedo la tabla de numeradores de documentos
//	echo $m_tipdoc;
//	echo $m_serie;
	$sql="select int_sp_numero_documento_ins( '$m_tipdoc', '$m_serie' )";
	echo $sql;
	$xsql=pg_query( $conector_id,  $sql );
	$m_orden=pg_result($xsql, 0, 0 );
	echo $m_orden;
	$m_proveedor=trim($m_proveedor);
	$m_tipdoc=trim($m_tipdoc);
	$m_serie=trim($m_serie);
	$m_almacen=trim($m_almacen);
	$m_fecha=trim($m_fecha);
	$m_tcambio=round($m_tcambio,2);
	$m_recargo1=round($m_recargo1,2);

	$okgraba=true;

	if($m_cantidadpedida<=0 or $m_precio<=0) 
		{
		$v_mensaje=" No se puede Agregar \\n Cantidad o Precio en 0 o negativo !!! ";	
		$okgraba=false;
		}

	if(strlen($m_proveedor)>0){
		$xsqlprov=pg_query($conector_id,"select PRO_CODIGO,PRO_RAZSOCIAL from INT_PROVEEDORES where PRO_CODIGO='".$m_proveedor."' ");
		if(pg_numrows($xsqlprov)>0){ 
			$m_proveedor=pg_result($xsqlprov,0,0); 
			$m_descprov=pg_result($xsqlprov,0,1); 
			}
		else{
			$v_mensaje=" No se puede Agregar \\n No Existe Proveedor !!! ";	$okgraba=false;
			}
	}else
		{
		$v_mensaje=" No se puede Agregar \\n Proveedor Vacio !!! ";	$okgraba=false;
		}

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
			$v_mensaje=" No se puede Agregar \\n No Existe Articulo !!! ";	$okgraba=false;
			}
		}
	else
		{
		$v_mensaje=" No se puede Agregar \\n Articulo Vacio !!! ";	$okgraba=false;
		}

	if ($okgraba)
		{
/*		if (strlen($m_fecha) > 0) 
			{ 
			$m_fecha=$funcion->date_format($m_fecha,'YYYY-MM-DD');
			$v_ins_fecha="COM_CAB_FECHAORDEN,"; 
			$v_val_fecha="'$m_fecha',"; 
			} 
		else 
			{ 
			$m_fecha=" "; 
			$v_ins_fecha=" "; 
			$v_val_fecha=" "; 
			}*/
		$m_moneda=trim($m_moneda);
		$m_tcambio=trim($m_tcambio);
		$m_formapago=trim($m_formapago);
		$m_recargo1=trim($m_recargo1);
		$m_comentario=trim($m_comentario);
		if($_REQUEST['glosa'] != ''){
		  $glosa = "'".$_REQUEST['glosa']."'";
		}else{
		  $glosa = 'NULL';
		}
		$sql="INSERT INTO com_cabecera ( pro_codigo, 
		                                 num_tipdocumento, 
		                                 num_seriedocumento, 
		                                 com_cab_numorden, 
		                                 com_cab_almacen,
					         ".$v_ins_fecha."com_cab_moneda, 
					         com_cab_tipcambio, 
					         com_cab_formapago, 
					         com_cab_imporden, 
					         com_cab_recargo1,
					         com_cab_observacion, 
					         com_cab_estado,
					         com_cab_credito,
					         com_cab_det_glosa,
						com_cab_fechaofrecida,
						com_cab_fecharecibida
					       )
				VALUES( '$m_proveedor',
				        '$m_tipdoc', 
				        '$m_serie',
				        '$m_orden',
				        '$m_almacen',
					".$v_val_fecha."'$m_moneda',
					'$m_tcambio',
					'$m_formapago', 
					0,
					'$m_recargo1', 
					'$m_comentario', 
					'1' ,
					'$m_credito',
					$glosa,to_date('$m_fechaofrecida','DD/MM/YYYY'),to_date('$m_fecharecibida','DD/MM/YYYY')
				      )";
//		echo $sql;
		$xsql=pg_query( $conector_id,  $sql );
		$sql="insert into COM_DETALLE ( PRO_CODIGO, NUM_TIPDOCUMENTO, NUM_SERIEDOCUMENTO, COM_CAB_NUMORDEN, ART_CODIGO,
					COM_DET_CANTIDADPEDIDA, COM_DET_PRECIO, COM_DET_DESCUENTO1, COM_DET_ESTADO )
					values ('$m_proveedor','$m_tipdoc', '$m_serie','$m_orden', '$v_art_codigo',
					".$m_cantidadpedida.",".$m_precio.", ".$m_descuento1.", '1'  ) ";
		//echo $sql;
		$xsql=pg_query( $conector_id,  $sql );
		
	
		$sql="select PRO_CODIGO||NUM_TIPDOCUMENTO||NUM_SERIEDOCUMENTO||COM_CAB_NUMORDEN as CLAVE 
						from COM_CABECERA
						where PRO_CODIGO='$m_proveedor' and NUM_TIPDOCUMENTO='$m_tipdoc' and NUM_SERIEDOCUMENTO='$m_serie' and COM_CAB_NUMORDEN='$m_orden' ";
		//echo $sql;
		$xsql=pg_query( $conector_id,  $sql );	
		$m_clave=pg_result($xsql,0,0);
		
		$query = "SELECT art_codigo FROM com_rec_pre_proveedor 
                          WHERE pro_codigo = '".$m_proveedor."' AND art_codigo = '".$v_art_codigo."'";
		$result_select=pg_query($conector_id,$query);
		$numrows=pg_numrows($result_select);
		
                if($numrows>0)
                {
                $query = "UPDATE com_rec_pre_proveedor SET
				art_codigo = '$v_art_codigo',
				rec_moneda = '".$_REQUEST['m_moneda']."',
				rec_precio = '$m_precio',
				rec_fecha_precio = now(),
				rec_fecha_ultima_compra = now() 
			  WHERE pro_codigo = '$m_proveedor'
			  AND art_codigo = '$v_art_codigo'";
                }else{
                $query = "INSERT INTO com_rec_pre_proveedor (
                                                             pro_codigo,
                                                             art_codigo,
                                                             rec_moneda,
                                                             rec_precio,
                                                             rec_fecha_precio,
                                                             rec_fecha_ultima_compra
                                                             )
                            VALUES ('$m_proveedor',
                                    '$v_art_codigo',
                                    '".$_REQUEST['m_moneda']."',
                                    '$m_precio',
                                    now(),
                                    now()
                                    )";
                //echo "<!--QUERY : $query -->\n";
                
                }
                $result=pg_query( $conector_id,  $query );		
                
		echo("<script>");
		echo("	location.href='cmpr_ordencom_2.php?m_clave=".$m_clave."' " );
//		echo("	location.href='cmpr_ordencom_2.php?m_proveedor=".$m_proveedor."&m_tipdoc=".$m_tipdoc."&m_serie=".$m_serie."&m_orden=".$m_orden."' " );
		echo("</script>");
		}
	else {
		echo('<script languaje="JavaScript"> ');
		echo('alert("'.$v_mensaje.'"); ');
		echo('</script>');
		}
	
	}

if($boton=="Eliminar") 
	{
	$sqleli="delete from COM_DETALLE where 
				PRO_CODIGO='$m_proveedor',
				NUM_TIPDOCUMENTO='$m_tipdoc',
				NUM_SERIEDOCUMENTO='$m_serie',
				COM_CAB_NUMORDEN='$m_orden',
				COM_CAB_ALMACEN='$m_almacen',
				ART_CODIGO='$r_articulo' ";
	$xsqleli=pg_query($conector_id,$sqleli);
	}
	
if($boton=="Regresar") 
	{
	echo('<script languaje="JavaScript">');
	echo("	location.href='cmpr_ordencom.php' ");
	echo('</script>');
	}

if (!isset($m_fecha) || is_null($m_fecha)) {
	$m_almacen=$almacen;
	$m_fecha=date("d/m/Y");
	$m_recargo1=0;
	$m_tcambio=0;
	$m_cantidadpedida=0;
	$m_precio=0;
	$m_descuento1=0;
}

if($boton=="Modificar cabecera") 
	{
	
	$sqlupdc="update COM_CABECERA set
				PRO_CODIGO='$m_proveedor',
				NUM_TIPDOCUMENTO='$m_tipdoc',
				NUM_SERIEDOCUMENTO='$m_serie',
				COM_CAB_NUMORDEN='$m_orden',
				COM_CAB_ALMACEN='$m_almacen',
				COM_CAB_FECHAORDEN=to_date('$m_fecha','DD/MM/YYYY'),
				COM_CAB_MONEDA='$m_moneda',
				COM_CAB_TIPCAMBIO='$m_tcambio',
				COM_CAB_FORMAPAGO='$m_formapago',
				COM_CAB_IMPORDEN=0,
				COM_CAB_RECARGO1='$m_recargo1', 
				COM_CAB_OBSERVACION='$m_comentario', 
				COM_CAB_ESTADO='1' 
				where PRO_CODIGO='$m_proveedor' and NUM_TIPDOCUMENTO='$m_tipdoc' and NUM_SERIEDOCUMENTO='$m_serie' and COM_CAB_NUMORDEN='$m_orden' ";
	$xsqlupdc=pg_query($conector_id,$sqlupdc);
	}

?>



<script language="JavaScript1.2"> 


var digitos=10 //cantidad de digitos buscados 
var puntero=0 
var buffer=new Array(digitos) //declaraci� del array Buffer 
var cadena="" 

function buscar_op(obj,objfoco){ 
	var letra = String.fromCharCode(event.keyCode) 
	if(puntero >= digitos){ 
		cadena=""; 
		puntero=0; 
		} 
	//si se presiona la tecla ENTER, borro el array de teclas presionadas y salto a otro objeto... 
	if (event.keyCode == 13){ 
		borrar_buffer(); 
		if(objfoco!=0) objfoco.focus(); //evita foco a otro objeto si objfoco=0 
		} 
	//sino busco la cadena tipeada dentro del combo... 
	else
		{ 
		buffer[puntero]=letra; 
		//guardo en la posicion puntero la letra tipeada 
		cadena=cadena+buffer[puntero]; //armo una cadena con los datos que van ingresando al array 
		puntero++; 
		//barro todas las opciones que contiene el combo y las comparo la cadena... 
		for (var opcombo=0;opcombo < obj.length;opcombo++){ 
			if(obj[opcombo].text.substr(0,puntero).toLowerCase()==cadena.toLowerCase())
				{ 
				obj.selectedIndex=opcombo; 
				} 
			} 
		} 
		event.returnValue = false; //invalida la acci� de pulsado de tecla para evitar busqueda del primer caracter 
	} 

function borrar_buffer()
	{ 
	//inicializa la cadena buscada 
	cadena=""; 
	puntero=0; 
	}
	
function agrega_ceros(campo)
{
   var campo_long = campo.value.length;
   var campo_value = campo.value;
   if(campo_long > 0 && campo_long<=13)
   {
      switch (campo_long)
      {
         case 1:
         ceros = '000000000000';
         break;
         case 2:
         ceros = '00000000000';
         break;

         case 3:
         ceros = '0000000000';
         break;

         case 4:
         ceros = '000000000';
         break;

         case 5:
         ceros = '00000000';
         break;

         case 6:
         ceros = '0000000';
         break;
         
         case 7:
         ceros = '000000';
         break;

         case 8:
         ceros = '00000';
         break;

         case 9:
         ceros = '0000';
         break;

         case 10:
         ceros = '000';
         break;

         case 11:
         ceros = '00';
         break;

         case 12:
         ceros = '0';
         break;

         default:
         ceros = '';
         break;
               
      }
      
   }else if(campo_long > 13)
   {
	    ceros = '';
   }
   campo.value = ceros + campo_value;
}

function activaGlosa(campo, glosa, glosatext)
{
    if(campo.checked == true)
    {
        glosa.style.display = 'block';
        glosatext.disabled = false; 
    }else{
        glosa.style.display = 'none';
        glosatext.disabled = true; 
    }
}
function agrega_ceros_prov(campo)
{
   var campo_long = campo.value.length;
   var campo_value = campo.value;
   if(campo_long > 0 && campo_long<=6)
   {
      switch (campo_long)
      {
         case 1:
         ceros = '00000';
         break;
         case 2:
         ceros = '0000';
         break;

         case 3:
         ceros = '000';
         break;

         case 4:
         ceros = '00';
         break;

         case 5:
         ceros = '0';
         break;

         default:
         ceros = '';
         break;
               
      }
      
   }else if(campo_long > 0 && campo_long > 6)
   {
	    ceros = '';
   }
   campo.value = ceros + campo_value;
}	
</script> 
<html><head>
<script language="JavaScript" src="/sistemaweb/maestros/js/jaime.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>


<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="javascript"> 
var miPopup 
function abrealma(){
	miPopup = window.open("../maestros/escogealmacen.php?k_variable=formular.m_almacen","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}
	
function abreprov() {
	miPopup = window.open("../maestros/escogeproveedor.php?k_variable=formular.m_proveedor","miwin","width=500,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

function abrearti() {
	miPopup = window.open("../maestros/escogearticulo.php?k_variable=formular.v_art_codigo","miwin","width=550,height=400,scrollbars=yes") 
	miPopup.focus() 
	}

function abretabla( tabla ,k_var ){ 
	miPopup = window.open("../maestros/escogetabla.php?m_tabla="+tabla+"&k_variable="+k_var+" ","miwin","width=600,height=350,scrollbars=yes") 
	miPopup.focus() 
	}

function enviadatos(){
	document.formular.submit()
	}


function activa(){
	// carga de frente el formulario con el foco en diad
	document.formular.m_fecha.select()
	document.formular.m_fecha.focus()
}


</script> 
</head>
<BODY>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<form name='formular'  method="post">

<input type="hidden" name="m_tipdoc" value="01">
<input type="hidden" name="m_serie" value="<?php echo $almacendocs; ?>">  
<input type="hidden" name="m_orden" value="<?php echo $m_orden; ?>">

<input type="hidden" name="m_tcambio_new" value="<?php echo $m_tcambio; ?>">
<input type="hidden" name="m_recargo1" value="<?php echo $m_recargo1; ?>">

<?php 

if (!isset($m_fecha) || is_null($m_fecha)) {
	$m_almacen=$almacen;
	$m_fecha=date("d/m/Y");
	$m_recargo1=0;
	$m_tcambio=0;
	$m_cantidadpedida=0;
	$m_precio=0;
	$m_descuento1=0;
}
?>


<table border="1">
	<tr> 
		<th width="500">ORDENES DE COMPRA</th>
	</tr>
</table>
<table border="1">
	<tr> 
		<th width="100" >N&deg; NUMERO </th>      
		<td>:</td>      
		<td>&nbsp;<?php
		    if($m_orden)
		    {
		       echo "<span style='font-size:14;font-weight:bold;'>".$m_orden."</span>";
		    }else{
		       echo "<span style='font-size:14;font-weight:bold;'>".$n_orden."</span>";
		    }
		     
		    ?>
		</td>

		<th>FECHA</th>
		<td>:</td>
		<td>		

		<p>
		<input type="text" name="m_fecha" size="16" maxlength="10" value='<?php echo $m_fecha ; ?>'  tabindex="1" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
		<a href="javascript:show_calendar('formular.m_fecha');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>


		</td>
		
	</tr>


	<tr> 
		<th>ALMACEN</th>
		<td>:</td>

		<td width="118" valign="top">  
		<select name="m_almacen" tabindex="2">
			<?php 
			for($i=0;$i<pg_numrows($v_xsqlalma);$i++){		
				$k_alma1 = pg_result($v_xsqlalma,$i,0);	
				$k_alma2 = pg_result($v_xsqlalma,$i,1);
				if (trim($k_alma1)==trim($m_almacen)) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>

		
        </select>
		</td>


		<th>PROVEEDOR</th>
		<td>:</td>
		<?php
		if(strlen($m_proveedor)>0) 
			{
			$sqlprov="select PRO_CODIGO,PRO_RAZSOCIAL 
						from INT_PROVEEDORES 
						where PRO_CODIGO='".$m_proveedor."' ";
			$xsqlprov=pg_query($conector_id,$sqlprov);
			if(pg_numrows($xsqlprov)>0) 
				{ 
				$m_proveedor=pg_result($xsqlprov,0,0); 
				$m_descprov=pg_result($xsqlprov,0,1); 
				}
			else
				{
				echo('<script languaje="JavaScript"> ');
				echo('alert(" No Existe Proveedor !!! "); ');
				echo('</script>');
				}
			}
				
		?>

		<td width="300" valign="top">  
		<input name="m_proveedor" type="text" size="15" maxlength="12" value="<?php echo $m_proveedor;?>" tabindex="3" onblur="javascript:agrega_ceros_prov(this);mostrarProcesar('procesando.php',this.value,'formular.m_descprov','proveedores')" >
		<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.m_proveedor','formular.m_descprov','proveedores')">
		<input type="text" name="m_descprov" tabindex=0 size="46" readonly="true" value='<?php echo $m_descprov; ?>' >

		</td>
	</tr>
	<tr> 
		<th>MONEDA</th>
		<td>:</td>

		<td width="118" valign="top">  
		<select name="m_moneda" onchange='submit()'>
			<?php
			$v_xsqlmone=pg_exec("select tab_elemento as cod,tab_descripcion from int_tabla_general  where tab_tabla='MONE' and tab_elemento!='000000' order by cod");
			for($i=0;$i<pg_numrows($v_xsqlmone);$i++){		
				$k_alma1 = pg_result($v_xsqlmone,$i,0);	
				$k_alma2 = pg_result($v_xsqlmone,$i,1);
				if ($k_alma1==$m_moneda) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>

		
        </select>
		</td>


		<?php

if (!isset($m_fecha) || is_null($m_fecha)) {
	$m_fecha=date("d/m/Y");
}
		if(strlen($m_moneda)>0) 
		{
		  $fecha = explode('/',$m_fecha);
		  $m_fecha = $fecha[2]."-".$fecha[1]."-".$fecha[0];
		  $sqltcam="SELECT tca_compra_oficial FROM int_tipo_cambio WHERE tca_moneda='02' AND tca_fecha='".$m_fecha."' ";
		  //echo "<!--QUERY : $sqltcam -->\n";
		  $xsqltcam=pg_query($conector_id,$sqltcam);
		  if(pg_numrows($xsqltcam)>0) 
		  { 
		     $m_tcambio=pg_result($xsqltcam,0,0); 
		  }else{
		     $m_tcambio="0.00"; 
		  }
		}
		?>

		<th>T.Cambio</th>
		<td>:</td>
		<td><input name="m_tcambio" type="text" value="<?php echo $m_tcambio;?>" size="5" maxlength="6" onkeyup='validarNumeroDecimales(this)'  tabindex="5">
		<?php
		if(strlen($m_tcambio)>0) 
			{
			}
		else
			{
			$m_tcambio=0;
			}
		?>
		</td>
	</tr>

	<tr> 
		<th>CANON</th>
		<td>:</td>
		<td><input name="m_recargo1" type="text" value="<?php echo $m_recargo1;?>" size="16" maxlength="10" onkeyup='validarNumeroDecimales(this)'  tabindex="6">
		</td>

		
		<th>COMENTARIO</th>
		<td>:</td>
		<td><input name="m_comentario" type="text" value="<?php echo $m_comentario;?>" size="20" maxlength="20" tabindex="7">
		</td>
	</tr>
	<tr> 
		<th>CREDITO</th>
		<td>:</td>
		
		<td>
			<?php 
			$checksi=" ";
			$checkno=" ";
			if ($m_credito=='S') 
				{
				$checksi="checked"; 
				}  
			else 
				{
				$checkno="checked"; 
				}
			echo ("<input type='radio' name='m_credito' value='S' ".$checksi."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8'>SI");
			echo ("<input type='radio' name='m_credito' value='N' ".$checkno."  onclick='javascript:m_formapago.value=\" \";submit();' tabindex='8'>NO");
			?>
		</td>

		<th>FORMA PAGO</th>
		<td>:</td>
		<?php 
		if ($m_credito=='S') 
			{
			$m_tab="96";
			}
		else 
			{
			$m_tab="05";
			}
		?>

		<td width="118" valign="top">  
		<select name="m_formapago" tabindex="2">
			<?php 
			$v_sqlfpag="select substr(TAB_ELEMENTO,5,2) ,TAB_DESCRIPCION from INT_TABLA_GENERAL where TAB_TABLA='".$m_tab."' and tab_elemento!='000000' order by TAB_ELEMENTO ";
			$v_xsqlfpag=pg_query($conector_id,$v_sqlfpag);
			for($i=0;$i<pg_numrows($v_xsqlfpag);$i++){	
				$k_alma1 = pg_result($v_xsqlfpag,$i,0);	
				$k_alma2 = pg_result($v_xsqlfpag,$i,1);
				if ($k_alma1==$m_formapago) { 
					echo "<option value='".$k_alma1."' selected >".$k_alma1." -- ".$k_alma2." </option>";	
					} 
				else {
					echo "<option value='".$k_alma1."' >".$k_alma1." -- ".$k_alma2." </option>";	
					}
		  		}
			?>
		
        </select>
	&nbsp;&nbsp;&nbsp;Glosa<input type="checkbox" name="chglosa" value="S" onclick="javascript:activaGlosa(this,document.getElementById('glosa'),forms[0].glosa);" <?php echo $checked?> <?php echo $v_estado_cabecera?>>
		</td>

	</tr>
<tr> 
		<th>Fecha Ofrecida</th>
		<td>:</td>
		<td>		
		<p>
		<input type="text" name="m_fechaofrecida" size="16" maxlength="10" value='<?php echo $m_fechaofrecida ; ?>'  tabindex="1" <?php echo $v_estado_cabecera; ?> onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('formular.m_fechaofrecida');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		</p>
		</td>

		<th>Fecha Recibida</th>
		<td>:</td>
		<td>		
		<p>
		<input type="text" name="m_fecharecibida" size="16" maxlength="10" value='<?php echo $m_fecharecibida ; ?>'  tabindex="1" <?php echo $v_estado_cabecera; ?> onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)"  >
		<a href="javascript:show_calendar('formular.m_fecharecibida');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
	    <img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>
		&nbsp;Glosa<input type="checkbox" name="chglosa" value="S" onclick="javascript:activaGlosa(this,document.getElementById('glosa'),forms[0].glosa);" <?php echo $checked?> <?php echo $v_estado_cabecera?>>
		</p>
		
		</td>

		<?php
		if(strlen($m_fechaofrecida)==0) 
			{
			$m_fechaofrecida=" ";
			}
		if(strlen($m_fecharecibida)==0) 
			{
			$m_fecharecibida=" ";
			}

		?>
	
	</tr>
</table>



<table border="1" cellpadding="0" cellspacing="0">
	<tr> 
		<th>&nbsp;</th>
		<th>CODIGO</th>
		<th>DESCRIPCION</th>
		<th>CANTIDAD</th>
		<th>COSTO UNITARIO</th>
		<th>DESCUENTO</th>
		<th>SUBTOTAL</th>
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

		<td>
		<input name="v_art_codigo" type="text" size="19" maxlength="13" value="<?php echo $v_art_codigo;?>" tabindex="10" onkeyup='validarNumeroEntero(this)' onblur="javascript:agrega_ceros(this);mostrarProcesarCos('procesando.php',this.value,'formular.v_art_descripcion','formular.m_precio','articulos',formular.m_proveedor.value,formular.m_moneda.value,formular.m_tcambio.value)" >
		<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyudaCos('/sistemaweb/compras/lista_ayuda.php','formular.v_art_codigo','formular.v_art_descripcion','formular.m_precio','articulos',formular.m_proveedor.value,formular.m_moneda.value,formular.m_tcambio.value)"></td>
		<td><input type="text" name="v_art_descripcion" tabindex=0 size="46" readonly="true" value='<?php echo $v_art_descripcion; ?>' ></td>
		
		<th><input name="m_cantidadpedida" type="text" size='15'  maxlength="15" value="<?php echo $m_cantidadpedida;?>"  onkeyup='validarNumeroDecimales(this)'  tabindex="11"  ></th>
		
		<th><input name="m_precio" type="text" size='15' maxlength="15" value="<?php echo $m_precio;?>"  onkeyup='validarNumeroDecimales(this)'  tabindex="12" ></th>
		<th><input name="m_descuento1" type="text" size='15' maxlength="15" value="<?php echo $m_descuento1;?>" onkeyup='validarNumeroDecimales(this)' tabindex="13" ></th>
		
		<th><input type="submit" name="boton" value="Agregar" tabindex="14"></th>
	</tr>
	
    <?php
	$sql3="select   DET.ART_CODIGO, 
					ART.ART_DESCRIPCION, 
					DET.COM_DET_CANTIDADPEDIDA, 
					DET.COM_DET_PRECIO, 
					DET.COM_DET_DESCUENTO1, 
					DET.COM_DET_IMPARTICULO 
					from COM_DETALLE DET, INT_ARTICULOS ART 
					where DET.ART_CODIGO=ART.ART_CODIGO and DET.PRO_CODIGO='".$m_proveedor."' and DET.NUM_TIPDOCUMENTO='".$m_tipdoc."' and DET.NUM_SERIEDOCUMENTO='".$m_serie."' and DET.COM_CAB_NUMORDEN='".$m_orden."' " ;
	$xsql3=pg_query($conector_id,$sql3);
	$ilimit3=pg_numrows($xsql3);
	while($irow3<$ilimit3) 
		{
		$ad0=pg_result($xsql3,$irow3,0);
		$ad1=pg_result($xsql3,$irow3,1);
		$ad2=pg_result($xsql3,$irow3,2);
		$ad3=pg_result($xsql3,$irow3,3);
		$ad4=pg_result($xsql3,$irow3,4);
		$ad5=pg_result($xsql3,$irow3,5);
		echo "<tr>";
		echo "<td><input type='radio' name='r_articulo' value='".$ad0."'></td>";
		echo "<td>".$ad0."</td>";
		echo "<td>".$ad1."</td>";
		echo "<td><p align='right'>".$ad2."</p></td>";
		echo "<td><p align='right'>".$ad3."</p></td>";
		echo "<td><p align='right'>".$ad4."</p></td>";
		echo "<td><p align='right'>".$ad5."</p></td>";
		echo "</tr>";
		$irow3++;
		}

	?>
	<tr> 
		<td>&nbsp;</td>
		<td><input type="submit" name="boton" value="Eliminar"></td>
		<td colspan="5"><input type="submit" name="boton" value="Modificar cabecera">
		&nbsp;&nbsp;
		<input type="submit" name="boton" value="Regresar"></td>
		<!--<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>-->
	</tr>
	<tr valign="top">
	 <td colspan="7" align="center" valign="top"><div  id="glosa" style="display:<?php echo !$checked?'none':$display?>;" >Descripci&oacute;n : <textarea  rows="10" cols="100" name="glosa"  <?php echo $display?$display:'disabled'?>><?php echo @$m_glosa?></textarea> </div></td>
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


