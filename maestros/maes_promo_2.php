<?php
include("../valida_sess.php");
include("../menu_princ.php");
include("../functions.php");

$coneccion=pg_connect("host=".$v_host." port=5432 dbname=".$v_db." user=postgres");

$sqlpromo="select special1, special2, special3, special4, special5, special6, activ_date, deact_date 
			from promo 
			where ART_CODIGO='".$idart."' ";
$xsqlpromo=pg_exec($coneccion,$sqlpromo);
$ilimitpromo=pg_numrows($xsqlpromo);
if($ilimitpromo>0) {
	$codi=$idart;
	$sku1=pg_result($xsqlpromo,0,0);
	$sku2=pg_result($xsqlpromo,0,1);
	$sku3=pg_result($xsqlpromo,0,2);
	$sku4=pg_result($xsqlpromo,0,3);
	$sku5=pg_result($xsqlpromo,0,4);
	$sku6=pg_result($xsqlpromo,0,5);
	$feca=pg_result($xsqlpromo,0,6);
	$fecd=pg_result($xsqlpromo,0,7);
  }


if($boton=="Regresar") 
	{
	?>
	<script>
	location.href='maes_promo.php';
	</script>
	<?php
	}
elseif($boton=="Siguiente") 
	{
	}
elseif($boton=="Grabar") 
	{
	$sqlai="select ART_CODIGO from promo where ART_CODIGO='".$codi."' ";
	$xsqlai=pg_exec($coneccion,$sqlai);
	$ilimitai=pg_numrows($xsqlai);
	if($ilimitai==1) 
		{
		$fechoy=date("Y-m-d");
		if(strlen($sku1)==0) { $sku1="000000"; }
		if(strlen($sku2)==0) { $sku2="000000"; } 
		if(strlen($sku3)==0) { $sku3="000000"; } 
		if(strlen($sku4)==0) { $sku4="000000"; } 
		if(strlen($sku5)==0) { $sku5="000000"; } 
		if(strlen($sku6)==0) { $sku6="000000"; } 
		if(strlen($feca)==0) { $feca="01/01/2003"; } 
		if(strlen($fecd)==0) { $fecd="01/01/2003"; } 
						
		$sqli="update promo
				set ART_CODIGO='".strtoupper($codi)."', special1='".strtoupper($sku1)."',
				special2='".strtoupper($sku2)."', special3='".strtoupper($sku3)."', special4='".strtoupper($sku4)."', special5='".strtoupper($sku5)."', special6='".strtoupper($sku6)."',
				activ_date='".$feca."', deact_date='".$fecd."'
				where ART_CODIGO='".strtoupper($codi)."' ";
		
		$xsqli=pg_exec($coneccion,$sqli);

		?>
		<script>
		//location.href='cmprp6i5.php';
		</script>
		<?php
		}
	else 
		{
		?>
		<script>
		alert(" El código de Promocion <?php echo $codi; ?>  no existe, no se puede actualizar!!!")
		</script>
		<?php
		}
	}
?>

<html> 
<head> 

<title>Formulario Prefijos</title> 
<script language="JavaScript" src="/sistemaweb/maestros/js/jaime.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/reloj.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>

<script language="javascript"> 
var miPopup

function abreubica(){ 
    miPopup = window.open("/sistemaweb/menu/procesos/ubicac.php","miwin","width=500,height=400,scrollbars=yes") 
    miPopup.focus() 
}

function enviadatos(){
	document.formular.submit()
}

</script> 

</head> 

<body> 
<p>MODIFICAR PROMOCION ESPECIAL</p>
<form name='formular' action="maes_promo_2.php" method="post">
<!-- <input type="hidden" name='fupd' value="">  -->
<table border="0">
	<tr>
	<td>
	<table WIDTH=600 border="1" cellspacing="0" cellpadding="0">
		<tr> 
		<td>Codigo Item</td>
		<td>:</td>
		<td WIDTH=500 ><input name="codi" type="text" value="<?php echo $codi;?>" size="20" maxlength="13" READONLY>
		<?php
		if(strlen($codi)>0) 
			{
			$sqlart="select art_codigo, art_descripcion from int_articulos where art_codigo='".$codi."' ";
			$xsqlart=pg_exec($coneccion,$sqlart);
			$ilimitart=pg_numrows($xsqlart);
			if($ilimitart>0) {
				$codiart=pg_result($xsqlart,0,0);	
				$descart=pg_result($xsqlart,0,1);	
				echo $descart;
				} 
			else 
				{
				?>
				<script>
				alert(" El articulo con código <?php echo $codi;?> no existe !!! ")
				</script>
				<?php  
				}
			}
		?>
		</td>
		</tr>

		<tr>
            <td>Codigo SKU1</td>
            <td>:</td>
			<?php
			if(strlen($sku1)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku1."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku1=pg_result($xsqlsku,0,0);
					$v_desc_sku1=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku1" type="text" value="<?php echo $sku1; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku1','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku1','formular.v_desc_sku1','SKU')">
			<input type="text" name="v_desc_sku1" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku1; ?>' >
		    </td>
		</tr>

		<tr>
            <td>Codigo SKU2</td>
            <td>:</td>
			<?php
			if(strlen($sku2)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku2."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku2=pg_result($xsqlsku,0,0);
					$v_desc_sku2=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku2" type="text" value="<?php echo $sku2; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku2','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku2','formular.v_desc_sku2','SKU')">
			<input type="text" name="v_desc_sku2" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku2; ?>' >
		    </td>
		</tr>

		<tr>
            <td>Codigo SKU3</td>
            <td>:</td>
			<?php
			if(strlen($sku3)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku3."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku3=pg_result($xsqlsku,0,0);
					$v_desc_sku3=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku3" type="text" value="<?php echo $sku3; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku3','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku3','formular.v_desc_sku3','SKU')">
			<input type="text" name="v_desc_sku3" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku3; ?>' >
		    </td>
		</tr>

		<tr>
            <td>Codigo SKU 4</td>
            <td>:</td>
			<?php
			if(strlen($sku4)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku4."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku4=pg_result($xsqlsku,0,0);
					$v_desc_sku4=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku4" type="text" value="<?php echo $sku4; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku4','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku4','formular.v_desc_sku4','SKU')">
			<input type="text" name="v_desc_sku4" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku4; ?>' >
		    </td>
		</tr>

		<tr>
            <td>Codigo SKU 5</td>
            <td>:</td>
			<?php
			if(strlen($sku5)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku5."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku5=pg_result($xsqlsku,0,0);
					$v_desc_sku5=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku5" type="text" value="<?php echo $sku5; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku5','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku5','formular.v_desc_sku5','SKU')">
			<input type="text" name="v_desc_sku5" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku5; ?>' >
		    </td>
		</tr>

		<tr>
            <td>Codigo SKU 6</td>
            <td>:</td>
			<?php
			if(strlen($sku6)>0) 
				{
				$sqlsku="select tab_elemento, tab_descripcion from int_tabla_general where tab_tabla='CSKU' and tab_elemento='".$sku6."' ";
				$xsqlsku=pg_exec($coneccion,$sqlsku);
				$ilimitsku=pg_numrows($xsqlsku);
				if($ilimitsku>0)
					{
					$sku6=pg_result($xsqlsku,0,0);
					$v_desc_sku6=pg_result($xsqlsku,0,1);	
			  		}
				else
					{
					echo('<script languaje="JavaScript"> ');
					echo('alert(" No Existe Codigo SKU !!! "); ');
					echo('</script>');
					}

				}
			?>
            <td><input name="sku6" type="text" value="<?php echo $sku6; ?>" size="10" maxlength="6" onblur="javascript:mostrarProcesar('/sistemaweb/maestros/ayuda/procesando.php',this.value,'formular.v_desc_sku6','SKU')" >
			<img src="../images/help.gif" width="16" height="15"  onClick="javascript:mostrarAyuda('/sistemaweb/maestros/ayuda/lista_ayuda.php','formular.sku6','formular.v_desc_sku6','SKU')">
			<input type="text" name="v_desc_sku6" tabindex=0 size="46" readonly="true" value='<?php echo $v_desc_sku6; ?>' >
		    </td>
		</tr>
		
		<tr>
		<td>Fecha Activacion</td>
		<td>:</td>
		<td><input name="feca" type="text" value="<?php echo $feca; ?>" size="15" maxlength="10"></td>
		</tr>

		<tr>
		<td>Fecha Desactiva</td>
		<td>:</td>
		<td><input name="fecd" type="text" value="<?php echo $fecd; ?>" size="15" maxlength="10"></td>
		</tr>
		
	</table>
	</td>

	<td>&nbsp;</td>
	<td align="center">
	<p><input type="submit" name="boton" value="Grabar" ></p>
	<p><input type="submit" name="boton" value="Regresar" ></p>
	</td>
	</tr>
</table>
</form>
</body>
</html>
<?php pg_close($coneccion);?>