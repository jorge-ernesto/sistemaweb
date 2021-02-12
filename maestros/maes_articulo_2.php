<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

// $coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

if($fupd=="Z")
	{
	$sqlart="select art_descripcion,art_descbreve,art_clase,art_tipo,art_linea,art_unidad,art_presentacion,
			art_costoinicial,art_stockinicial,art_stockactual,art_costoactual,art_costoreposicion,art_margenutilidad,
			art_fecucompra,art_fecuventa,art_fecactuliz,art_estado,art_trasmision, 
			art_impuesto1,art_stkgnrlmin,art_stkgnrlmax,art_promconsumo,
			art_plazoreposicprom,art_diasreposic,art_feccostorep,art_cod_ubicac, art_cod_sku
			from int_articulos where art_codigo='".$idart."' ";

	$xsqlart=pg_exec($coneccion,$sqlart);
	$ilimitart=pg_numrows($xsqlart);
	if($ilimitart>0) 
		{
		$codarti=$idart;
		$desc=pg_result($xsqlart,0,0);
		$descbreve=pg_result($xsqlart,0,1);
		$clase=pg_result($xsqlart,0,2);
		$tipo=pg_result($xsqlart,0,3);  //echo $tipo."hola";
		$nrocaract=6; $cadena=$tipo; completaceros($nrocaract,$cadena); $tipo=$cadena;
		$linea=pg_result($xsqlart,0,4);
		$ubicac=pg_result($xsqlart,0,25);
		$cadena=$linea; completaceros($nrocaract,$cadena); $linea=$cadena; //echo "linea".$linea;
		$sqlitserv="select tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento='".$linea."' ";
		$xsqlitserv=pg_exec($coneccion,$sqlitserv);
		if(pg_numrows($xsqlitserv)>0) 
			{ 
			$itemserv=pg_result($xsqlitserv,0,0); 
			if(trim($itemserv)=="S") 
				{ 
				$itemserv="N"; 
				}
			elseif(trim($itemserv)=="N")
				{ 
				$itemserv="S"; 
				}  
			}
	
		$unidmanejo=pg_result($xsqlart,0,5);
		$unidpresent=pg_result($xsqlart,0,6);
		$costoinicompra=pg_result($xsqlart,0,7);
		$stkinicompra=pg_result($xsqlart,0,8);
		$stkactual=pg_result($xsqlart,0,9);
		$ctoactual=pg_result($xsqlart,0,10);
		$ctoreposic=pg_result($xsqlart,0,11);
		$imp1=pg_result($xsqlart,0,18);
		$stkgnrlmin=pg_result($xsqlart,0,19);
		$stkgnrlmax=pg_result($xsqlart,0,20);
		$promconsumo=pg_result($xsqlart,0,21);
		$plazorepprom=pg_result($xsqlart,0,22);
		$diareposic=pg_result($xsqlart,0,23);
		$feccostrepos=$funcion->date_format(pg_result($xsqlart,0,24),'DD/MM/YYYY');   
		$v_codigosku=pg_result($xsqlart,0,26);
		
		
		}
	} 
else 
	{
	if(strlen($escoglinea)>0) 
		{
		$xsqlitserv=pg_exec($coneccion,"select tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento='".$escoglinea."' ");
		if(pg_numrows($xsqlitserv)>0) 
			{ 
			$itemserv=pg_result($xsqlitserv,0,0); 
			if(trim($itemserv)=="S") 
				{ 
				$itemserv="N"; 
				} 
			elseif(trim($itemserv)=="N")
				{ 
				$itemserv="S"; 
				} 
			}
		}
	elseif(strlen($linea)>0) 
		{
		$xsqlitserv=pg_exec($coneccion,"select tab_car_03 from int_tabla_general where tab_tabla='20' and tab_elemento='".$linea."' ");
		if(pg_numrows($xsqlitserv)>0) 
			{ 
			$itemserv=pg_result($xsqlitserv,0,0); 
			if(trim($itemserv)=="S") 
				{ 
				$itemserv="N"; 
				} 
			elseif(trim($itemserv)=="N")
				{ 
				$itemserv="S"; 
				} 
			}
		}
	}

if($boton=="Regresar") 
	{
	?>
	<script>
	location.href='maes_articulo.php';
	</script>
	<?php
	}
elseif($boton=="Siguiente") 
	{
	}
elseif($boton=="Grabar") 
	{
	/*
	$sqlai="select art_codigo from int_articulos where art_codigo='".$newcod."' ";
	//  echo $sqlai;
	$xsqlai=pg_exec($coneccion,$sqlai);
	$ilimitai=pg_numrows($xsqlai);
	if($ilimitai==0) {
	*/
	//  (art_codigo,art_descripcion,art_costoactual,art_stockactual,art_linea)
	
	$fechoy=date("Y-m-d");
	$estado="1";
	$ftransmis="M";

	$okgraba=true;
	$addsql=" ";

	
	// $feccostrepos=$feccostreposa."/".$feccostreposm."/".$feccostreposd;
	if(strlen($ctoreposic)==0) { $ctoreposic=0; }
	if(strlen($stkactual)==0) { $stkactual=0; } 
	if(strlen($ctoactual)==0) { $ctoactual=0; }
	if(strlen($stkinicompra)==0) { $stkinicompra=0; }
	if(strlen($costoinicompra)==0) { $costoinicompra=0; }
	if(strlen($plazorepprom)==0) { $plazorepprom=0; }
	if(strlen($diareposic)==0) { $diareposic=0; }
	if(strlen($promconsumo)==0) { $promconsumo=0; }
	if(strlen($stkgnrlmax)==0) { $stkgnrlmax=0; }
	if(strlen($stkgnrlmin)==0) { $stkgnrlmin=0; }
	if(strlen($imp1)==0) { $imp1=0; }
	if(strlen($precio1)==0) { $precio1=0; }
	if(strlen($precio2)==0) { $precio2=0; }
	if(strlen($precio3)==0) { $precio3=0; }
	if(strlen($precio4)==0) { $precio4=0; }

	if (strlen($feccostrepos) > 0) 
		{ 
		$v_feccostrepos=$funcion->date_format($feccostrepos,'YYYY-MM-DD');
		$addsql=",art_feccostorep='".$v_feccostrepos."'"; 
		} 
	else 
		{ 
		$v_feccostrepos=" "; 
		$addsql=" "; 
		}
		
	if (strlen($v_codigosku) > 0) 
		{ 
		$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$v_codigosku."' ";
		$xsqlsku=pg_exec($coneccion,$sqlsku);
		if(pg_numrows($xsqlsku)==0)
			{
			echo('<script languaje="JavaScript"> ');
			echo('alert(" No Existe Codigo SKU !!! "); ');
			echo('</script>');
			$okgraba=true;
			}
		else
			{
			$addsql=$addsql.",art_cod_sku='".$v_codigosku."'"; 
			}
		}
	if ($okgraba)
		{
		$sqli="update int_articulos set art_descripcion='".$desc."',art_descbreve='".$descbreve."',
				art_clase='".$tipo."',art_tipo='".$tipo."',art_linea='".$linea."',art_unidad='".$unidmanejo."',art_presentacion='".$unidpresent."',
				art_costoinicial=".$costoinicompra.",art_costoreposicion=".$ctoreposic.",
				art_fecactuliz='".$fechoy."',art_estado='".$estado."',
				art_trasmision='".$ftransmis."',art_impuesto1='".$imp1."',art_stkgnrlmin=".$stkgnrlmin.",art_stkgnrlmax=".$stkgnrlmax.",
				art_promconsumo=".$promconsumo.",art_usuario='".$user."',art_cod_ubicac='".$ubicac."',
				art_plazoreposicprom=".$plazorepprom.",art_diasreposic=".$diareposic."  ".$addsql."
				where art_codigo='".$codarti."' ";
		//echo $sqli;
		$xsqli=pg_exec($coneccion,$sqli);
		//$xsql=pg_exec($coneccion,"update ");
		
		$lista=pg_result(pg_exec($coneccion,"select util_fn_cd_precio()"),0,0);
		$moneda='01';
		// echo $lista;
		$sql="select pre_precio_act1 from fac_lista_precios where art_codigo='".$codarti."' and pre_lista_precio='".$lista."'";
		if (pg_numrows(pg_query($coneccion,$sql))>0) 
			{
			$sql="update fac_lista_precios set pre_precio_act1=".$precio1." where art_codigo='".$codarti."' and pre_lista_precio='".$lista."'";
			}
		else
			{
			$sql="insert into fac_lista_precios(pre_lista_precio,art_codigo,pre_moneda,pre_precio_fec1,
												pre_usuario,pre_precio_act1,pre_estado,pre_fecactualiz,
												pre_transmision) 
					values('".$lista."','".$codarti."','".$moneda."','now',
												'".$user."',".$precio1.",'1','now',
												'1')";
			}
		echo $sql;
		$xsqlprecio=pg_exec($coneccion,$sql);
		// $xsqlprecio=pg_exec($coneccion,"update fac_lista_precios set pre_precio_act1=".$precio1." where art_codigo='".$codarti."' and pre_lista_precio='02'");
		}
	}
?>

<html> 
<head> 
    <title>Formulario prefijos</title>
<script language="JavaScript" src="/sistemaweb/maestros/js/jaime.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
	

<script language="javascript"> 
var miPopup 
function abrelinea(){ 
    miPopup = window.open("escogelinea.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
} 
function abretipo(){ 
    miPopup = window.open("escogetipo.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreunipres() {
    miPopup = window.open("escogeunipres.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreuniman() {
    miPopup = window.open("escogeuniman.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}
function abreubica(){ 
    miPopup = window.open("/sistemaweb/inventarios/ubicac.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
} 
function enviadatos(){
	document.formular.submit()
}
</script> 
</head> 
<body> 
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<p>MODIFICAR ART&Iacute;CULO</p>
<form name='formular' action="maes_articulo_2.php" method="post">
<!--<input type="hidden" name='fupd' value="">-->
<table border="0"><tr><td>
<table border="1" cellspacing="0" cellpadding="0">
          <tr> 
            <td>C&oacute;digo de Item</td>
            <td>:</td>
            <td><input name="codarti" type="text" value="<?php echo $codarti;?>" maxlength="13" readonly></td>
          </tr>
          <tr> 
            <td>Descripci&oacute;n</td>
            <td>:</td>
            <td><input name="desc" type="text" value="<?php echo $desc; ?>" size="45" maxlength="55"></td>
          </tr>
          <tr> 
            <td>Descripci&oacute;n Breve</td>
            <td>:</td>
            <td><input name="descbreve" type="text" value="<?php echo $descbreve;?>" size="40" maxlength="20"></td>
          </tr>
          <tr> 
            <td>Item de Servicios</td>
            <td>:</td>
            <td><input name="itemserv" type="text" value="<?php echo $itemserv; ?>" size="5" readonly> 
              <input type="submit" name="boton3" value="Ok" onClick="enviadatos()"></td>
          </tr>
          <tr> 
            <!--    <td>C&oacute;digo de Maq.Reg</td>
            <td>:</td>
            <td><input name="codmaqreg" type="text" value="<?php echo $codmaqreg; ?>"></td>
          </tr>-->
          <tr> 
            <td height="24">L&iacute;nea</td>
            <td>:</td>
                <?php
				$sqllin="select tab_elemento,tab_descripcion,tab_car_03 from int_tabla_general where tab_tabla='20' and (tab_elemento like '%".$linea."%' or tab_descripcion like '%".$linea."%')";
				$xsqllin=pg_exec($coneccion,$sqllin);
				$ilimitlin=pg_numrows($xsqllin);
				if($ilimitlin>0) 
					{
					$codlinea=pg_result($xsqllin,0,0);
					$desclinea=pg_result($xsqllin,0,1);	
					$flglinea=pg_result($xsqllin,0,2); 
					//echo $desclinea; //echo "<input type='hidden' name='flglinea' value='".$flglinea."'>";
					} 
				else 
					{
					?>
					<script>
					alert(" El código de la línea <?php echo $linea; ?> no existe !!!")
					</script>
					<?php  
					}
				?>
            <td>
			<input name="linea" type="text" value="<?php echo $linea; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.desclinea','lineas')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.linea','formular.desclinea','lineas')">
			<input type="text" name="desclinea" tabindex=0 size="46" readonly="true" value='<?php echo $desclinea; ?>' >
		    </td>
			
          </tr>
          <tr> 
            <td>Tipo</td>
            <td>:</td>
				<?php
				if(strlen($tipo)>0) 
					{
					$sqlai="select tab_elemento,tab_descripcion from int_tabla_general where tab_tabla='21' and tab_elemento='".$tipo."' ";
					$xsqlai=pg_exec($coneccion,$sqlai);
					$ilimitai=pg_numrows($xsqlai);
					if($ilimitai>0) 
						{
						$codtipo=pg_result($xsqlai,0,0);	
						$desctipo=pg_result($xsqlai,0,1);	
						//echo $desctipo; //echo "<input type='hidden' name='htipo' value='".$codtipo."'>";
						} 
					else 
						{
						?>
						<script>
						alert(" El código del tipo <?php echo $tipo;?> no existe !!! ")
						</script> 
						<?php
						}
					}
				?>
            <td>
			<input name="tipo" type="text" value="<?php echo $tipo; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.desctipo','tipos')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.tipo','formular.desctipo','tipos')">
			<input type="text" name="desctipo" tabindex=0 size="46" readonly="true" value='<?php echo $desctipo; ?>' >
		    </td>
		</tr>
          
		  
		  <!--          
		  <tr> 
            <td>Tipo de Estad&iacute;stica</td>
            <td>:</td>
            <td><input name="tipoestad" type="text" value="<?php echo $tipoestad; ?>"> 
            </td>
          </tr>
          <tr> 
            <td>Marca</td>
            <td>:</td>
            <td><input name="marca" type="text" value="<?php echo $marca; ?>"></td>
          </tr>
		  
		  -->
		  
		  
          <tr> 
            <td>Unidad de Manejo</td>
            <td>:</td>
			<?php
			if(strlen($unidmanejo)>0) 
				{
				$sqlai="select tab_descripcion from int_tabla_general where tab_tabla='34' and tab_elemento='".$unidmanejo."' ";
				//  $sqlai="select tab_descripcion from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidmanejo."' ";
				// echo $sqlai;
				$xsqlai=pg_exec($coneccion,$sqlai);
				$ilimitai=pg_numrows($xsqlai);
				if($ilimitai>0) 
					{
					$descunidmanejo=pg_result($xsqlai,0,0);		
					//echo $descunidmanejo;
					} 
				else 
					{
					$nrocaract=6;
					$cadena=$unidmanejo;
					completaceros($nrocaract,$cadena);
					//echo $cadena;
					$unidmanejo=$cadena;
					$sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='34' and tab_elemento='".$unidmanejo."' ";
					//  echo $sqlai;
					$xsqlai=pg_exec($coneccion,$sqlai);
					$ilimitai=pg_numrows($xsqlai);
					if($ilimitai>0) 
						{
						$descunidmanejo=pg_result($xsqlai,0,0);
						//echo $descunidmanejo;
						}
					else 
						{
						?>
						<script>
						alert(" El código de la unidad de manejo <?php echo $unidmanejo; ?>  no existe !!! ");
						</script> 
						<?php
						}
					}
				}
			?>
            <td>
			<input name="unidmanejo" type="text" value="<?php echo $unidmanejo; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.descunidmanejo','unimanejo')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.unidmanejo','formular.descunidmanejo','unimanejo')">
			<input type="text" name="descunidmanejo" tabindex=0 size="46" readonly="true" value='<?php echo $descunidmanejo; ?>' >
		    </td>
          </tr>
          <tr> 
            <td>Impuesto 1</td>
            <td>:</td>
            <td><input name="imp1" type="text" style="text-align:right" value="<?php echo $imp1; ?>" size="10" maxlength="1"></td>
          </tr>
          <!--          <tr> 
            <td>Impuesto 2</td>
            <td>:</td>
            <td><input name="imp2" type="text" value="<?php echo $imp2; ?>"></td>
          </tr>
          <tr> 
            <td>Impuesto 3</td>
            <td>&nbsp;</td>
            <td><input name="imp3" type="text" value="<?php echo $imp3; ?>"></td>
          </tr>
-->
          <tr> 
			<td>Unidad de Presenta.</td>
			<td>:</td>
				<?php
				if(strlen($unidpresent)>0) 
					{
					$sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidpresent."' ";
					//  echo $sqlai;
					$xsqlai=pg_exec($coneccion,$sqlai);
					$ilimitai=pg_numrows($xsqlai);
					if($ilimitai>0) {
						$descunidpresent=pg_result($xsqlai,0,0);		
						//echo $descunidpresent;
						} 
					else 
						{
						$nrocaract=6;
						$cadena=$unidpresent;
						completaceros($nrocaract,$cadena);
						//echo $cadena;
						$unidpresent=$cadena;
						$sqlai="select tab_desc_breve from int_tabla_general where tab_tabla='35' and tab_elemento='".$unidpresent."' ";
						//  echo $sqlai;
						$xsqlai=pg_exec($coneccion,$sqlai);
						$ilimitai=pg_numrows($xsqlai);
						if($ilimitai>0) 
							{
							$descunidpresent=pg_result($xsqlai,0,0);		
							//echo $descunidpresent;
							} 
						else 
							{?>
							<script>
							alert(" El código de la unidad de presenta <?php echo $unidpresent; ?> no existe !!! ")
							</script> 
							<?php  
							}
						}
					}
				?>
            <td>
			<input name="unidpresent" type="text" value="<?php echo $unidpresent; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.descunidpresent','unipresentacion')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.unidpresent','formular.descunidpresent','unipresentacion')">
			<input type="text" name="descunidpresent" tabindex=0 size="46" readonly="true" value='<?php echo $descunidpresent; ?>' >
		    </td>
          </tr>
		<tr>
			<td>Ubicacion</td>
			<td>:</td>
			<?php
			if(strlen($ubicac)>0) 
				{
				$sqlao="select cod_ubicac,desc_ubicac from inv_ta_ubicacion where cod_ubicac like '%".trim($ubicac)."%' and cod_almacen='".$almacen."' ";
				// echo $sqlao;
				$xsqlao=pg_exec($coneccion,$sqlao);
				$ilimitao=pg_numrows($xsqlao);
				if($ilimitao>0)
					{
					//  $codao=pg_result($xsqlao,0,0);	
					$txtalma=pg_result($xsqlao,0,0);
					$descubic=pg_result($xsqlao,0,1);	
					}
				}
			?>
            <td><input name="ubicac" type="text" value="<?php echo $ubicac; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.descubic','ubicaciones<?php echo $almacen;?>')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.ubicac','formular.descubic','ubicaciones<?php echo $almacen;?>')">
			<input type="text" name="descubic" tabindex=0 size="46" readonly="true" value='<?php echo $descubic; ?>' >
		    </td>
			
		</tr>
		
		<tr>
            <td>Codigo SKU</td>
            <td>:</td>
			<?php
			if(strlen($v_codigosku)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$v_codigosku."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$v_codigosku=pg_result($xsqlsku,0,0);
					$v_desc_sku=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="v_codigosku" type="text" value="<?php echo $v_codigosku; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.v_codigosku','formular.v_desc_sku','SKU')">
			<input type="text" name="v_desc_sku" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku; ?>' >
		    </td>

			
			
		</tr>

	</table>
</td><td>
<?php if($itemserv=="N") { ?>

<table border="1" cellpadding="0" cellspacing="0">
          <tr> 
            <td>Stock General Mínimo</td>
            <td>:</td>
            <td><input type="text" name="stkgnrlmin" value="<?php echo $stkgnrlmin;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Stock General Máximo</td>
            <td>:</td>
            <td><input name="stkgnrlmax" type="text" value="<?php echo $stkgnrlmax;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Promedio de Consumo</td>
            <td>:</td>
            <td><input name="promconsumo" type="text" value="<?php echo $promconsumo; ?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Plazo de Repos. Promedio</td>
            <td>:</td>
            <td><input name="plazorepprom" type="text" value="<?php echo $plazorepprom;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>D&iacute;as de Reposici&oacute;n</td>
            <td>:</td>
            <td><input name="diareposic" type="text" value="<?php echo $diareposic; ?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr>
          <tr> 
            <td>Costo Inicial Compra</td>
            <td>:</td>
            <td><input name="costoinicompra" type="text" value="<?php echo $costoinicompra;?>" onKeyPress="return esInteger(event)" style="text-align:right"></td>
          </tr>
	<!--	  <tr> 
            <td>Stock Inicial Compra</td>
            <td>:</td>
            <td><input name="stkinicompra" type="text" value="<?php echo $stkinicompra;?>" onKeyPress="return esIntspto(event)" style="text-align:right"></td>
          </tr> -->
          <tr> 
            <td>Fecha Costo Reposic</td>
            <td>:</td>
            <td>
<!--			
			<input name="feccostreposd" type="text" style="text-align:right" value="<?php echo $feccostreposd;?>" size="4" maxlength="2">/
			<input name="feccostreposm" type="text" style="text-align:right" value="<?php echo $feccostreposm;?>" size="4" maxlength="2">/
			<input name="feccostreposa" type="text" style="text-align:right" value="<?php echo $feccostreposa;?>" size="6" maxlength="4">
-->
			<input type="text" name="feccostrepos" size="16" maxlength="10" value='<?php echo $feccostrepos ; ?>'  tabindex="1" onKeyUp="javascript:validarFecha(this)" onblur="javascript:validarFecha(this)" >
			<a href="javascript:show_calendar('formular.feccostrepos');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;">
			<img src="/sistemaweb/images/show-calendar.gif" width=24 height=22 border=0></a>


			
			</td>
          </tr>
		  <td>Costo Reposic</td>
            <td>:</td>
            <td><input name="ctoreposic" type="text" value="<?php echo $ctoreposic;?>" onKeyPress="return esInteger(event)" style="text-align:right">

			</td>
          </tr>
<?php
if($fupd=="Z")
	{
	$xsqlprecio=pg_exec($coneccion,"select util_fn_precio_articulo('".$codarti."')");
	//			pre_precio_act1 from fac_lista_precios where art_codigo='".$codarti."' and pre_lista_precio='02'");
	$ilimitprecio=pg_numrows($xsqlprecio);
	if ( $ilimitprecio=0 ) 
		{
		$precio1=0;
		}
	else
		{
		$precio1=pg_result($xsqlprecio,0,0);
		}
	}
?>
          <tr> 
            <td height="24">Precio1</td>
            <td>:</td>
            <td><input name="precio1" type="text" value="<?php echo $precio1;?>" onKeyPress="return esInteger(event)" style="text-align:right"></td>
          </tr>
        </table>
<?php } ?>
</td></tr>
<tr>
      <td>&nbsp;</td>
      <td align="right">
<?php //if($boton=="Siguiente") { } else {?>  <!--    <input type="submit" name="boton" value="Siguiente"> --><?php //} ?>
<input type="submit" name="boton" value="Grabar">
<p><input type="submit" name="boton" value="Regresar"></p></td></tr>
</table>
</form>
</body>
</html>
<?php pg_close($coneccion);?>