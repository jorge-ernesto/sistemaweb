<?php 

$combustible_importe_total	= $venta_combustible[ "liquido"] + $venta_combustible[ "glp"]; 
$combustible_cantidad_total	= $venta_combustible["liquido_canti"] + $venta_combustible[ "glp_canti"];

?>


<form method="post" action="control.php" target="control" name="frm">
	<input type="hidden" id="rqst" name="rqst" value="REPORTES.VENTADIARIANEW">

	<center>
		<h1><b>LIQUIDACION DE VENTA TOTAL</b></h1>
	</center>

	<table border="0" align="center">

		<tr>
			<th colspan="5">Almacen :
				<select name="almacen" id="almacen" tabindex="5">
				<?php
					for($i = 0; $i < count($almacen); $i++) 
						echo "<option value='".$almacen[$i][ "ch_almacen"]. "'>".$almacen[$i][ "ch_nombre_almacen"]. "</option>";
				?>
				</select>
			</th>
		</tr>

		<tr>
			<th colspan="2">A&ntilde;o:<input type="text" name="anio" id="anio" size="4" value="<?php echo $_POST["anio"];?>" tabindex="1" /></th>
		</tr>

		<tr>
            		<th colspan="2">Mes:<input type="text" name="mes" id="mes" size="2" value="<?php echo $_POST["mes"];?>" tabindex="2" /></th>
		</tr>

		<tr>
			<th align="right">Desde:<input type="text" name="dia_desde" id="dia_desde" size="2" value="<?php echo $_POST["dia_desde"];?>" tabindex="3" />&nbsp;</th>
			<th align="left">Hasta:<input type="text" name="dia_hasta" id="dia_hasta" size="2" value="<?php echo $_POST["dia_hasta"];?>" tabindex="4" />&nbsp;</th>
		</tr>

	</table>

	<div align="center">
        	<br>
		<th><button name="action" type="submit" value="Buscar"><img src="/sistemaweb/images/search.png" align="right" />Reporte</button></th>
		<button name="fm" value="<?php echo $fm;?>" onClick="javascript:parent.location.href='/sistemaweb/combustibles/LiquidacionVentasPDF_new.php?almacen=<?php echo urlencode($_POST['almacen']);?>&desde=<?php echo urlencode($_POST["dia_desde"]);?>&hasta=<?php echo urlencode($_POST["dia_hasta"]);?>&mes=<?php echo urlencode($_POST["mes"]);?>&anio=<?php echo urlencode($_POST["anio"]);?>';return false"><img src="/sistemaweb/images/icono_pdf.gif" alt="left"/> PDF</button>
	</div>

</form>
	<br>
	<table width="800px" cellspacing="0" cellpadding="3" border="1" align="center">
		<tr bgcolor='#81BEF7'>
			<td width="85%" align="center" style="font-size:1em">Concepto</td>
			<td width="*" align="center" style="font-size:1em">Cantidad</td>
			<td width="*" align="center" style="font-size:1em">Importe</td>
		</tr>
		<tr>
			<td width="85%" style="font-size:1.2em" colspan="3"><b>I. VENTA</b></td>
		</tr>

		<tr>
			<td width="85%" style="font-size:1.2em" colspan="3">1.&nbsp;Venta de Combustible</td>
		</tr>

		<tr>
			<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;1.1.&nbsp;Liquido</td>
			<td><p align="right" style="font-size:1.5em"><?php echo $this->f($venta_combustible["liquido_canti"]); ?></p></td>
			<td><p align="right" style="font-size:1.5em"><?php echo $this->f($venta_combustible["liquido"]); ?></p></td>
		</tr>

		<tr>
			<td style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;1.2.&nbsp;GLP</td>
			<td><p align="right" style="font-size:1.5em"><?php echo $this->f($venta_combustible["glp_canti"]); ?></p></td>
			<td><p align="right" style="font-size:1.5em"><?php echo $this->f($venta_combustible["glp"]); ?></p></td>
		</tr>

		<tr>
			<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Bruta de Combustible</b></td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo $this->f($combustible_cantidad_total); ?></b></p></td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo $this->f($combustible_importe_total); ?></b></p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="3">2.&nbsp; Incrementos / Descuentos</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp; 2.1 Diferencia de Precio Vales</span></td>
			<td><p align="right" style="font-size:1.5em">1212</p></td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp; 2.2 Descuentos</span></td>
			<td><p align="right" style="font-size:1.5em"><b>
				<?php echo $this->f($descuentos[0]['importe']); ?>
			</p></td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp; 2.3 Consumo Propio</span></td>
			<td><p align="right" style="font-size:1.5em">1212</p></td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp; 2.4 Afericiones</span></td>
			<td><p align="right" style="font-size:1.5em"><b>
				<?php echo $this->f($afericiones[0]['importe']); ?>
			</p></td>
		</tr>

		<tr>
			<td style="font-size:1.1em" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Venta Neta de Combustible</b></td>
			<td><p align="right" style="font-size:1.5em"><b>xx<?php echo htmlentities($total_venta_combustible); ?></b></p></td>
    		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">3.&nbsp; Venta de Otros Productos y Promociones</td>
			<td><p align="right" style="font-size:1.5em"><?php echo empty($venta_prod_promo[ "ventatienda"])? "0.00":f($venta_prod_promo[ "ventatienda"]); ?></p></td>
		</tr>

		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>TOTAL VENTA NETA A LIQUIDAR</b></td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo htmlentities($total_venta); ?></b></p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td width="85%" style="font-size:1.2em" colspan="3"><b>II. RUBROS DE LIQUIDACION</b></td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="3">1.&nbsp;Vales de Credito</td>
		</tr>

		<?php
			$val_t_importe = $val_t_cantidad = $i = 0; 
			for(; $i < count($vales_credito); $i++){
		?>

		<tr>
			<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php echo $vales_credito[$i][ "ruc"]. " ".$vales_credito[$i][ "cliente"];?>
			</td>

			<td><p align="right" style="font-size:1.5em"><b>
				<?php echo $this->f($vales_credito[$i]["cantidad"]); $val_t_cantidad+=$vales_credito[$i]["cantidad"]; ?></b></p>
			</td>

			<td><p align="right" style="font-size:1.5em"><b>
				<?php echo $this->f($vales_credito[$i]["importe"]);$val_t_importe+=$vales_credito[$i]["importe"];?></b></p>
			</td>
		</tr>

		<?php } ?>

		<tr>
			<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Vales de Credito</b></td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo $this->f($val_t_cantidad); ?></b></p></td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo $this->f($val_t_importe); ?></b></p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="3">2.&nbsp;Tarjetas de Credito</td>
		</tr>

		<?php
			$tar_importe = $i = 0; 
			for(; $i < count($tarjetas_credito); $i++){
		?>

		<tr>
			<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php echo $tarjetas_credito[$i][ "descripciontarjeta"];?>
			</td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><b>
				<?php echo $this->f($tarjetas_credito[$i]["importe"]); $tar_importe+=$tarjetas_credito[$i]["importe"]; ?></b></p>
			</td>
		</tr>

		<?php } ?>

		<tr>
			<td style="font-size:1.1em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Total Tarjetas de Credito</b></td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em"><b><?php echo $this->f($tar_importe); ?></b></p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em">3.&nbsp;Total Venta Contado</td>
			<td>&nbsp;</td>
			<td><p align="right" style="font-size:1.5em">
				<?php echo htmlentities($descuentos_total); ?></p>
			</td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Depositos POS</td>
			<td><p align="right" style="font-size:1.5em">
				<?php echo $this->f($depositos_pos[0]['importe']); ?>
			</p></td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Faltante / Sobrantes de Grifero</td>
			<td><p align="right" style="font-size:1.5em">
				<?php echo $this->f($sobrantes_faltantes[0]['importe']); ?>
			</p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="3"><b>III. CONCILIACION DEPOSITOS POS</b></td>
		</tr>

		<tr>
			<td colspan="2" style="font-size:1.2em">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Total Depositos POS</td>
			<td><p align="right" style="font-size:1.5em">
				<?php echo $this->f($depositos_pos[0]['importe']); ?>
			</p></td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td colspan="3" style="font-size:1.2em">&nbsp;</td>
		</tr>

		<tr>
			<td style="font-size:1.2em" colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Anomalia (Hermes)</td>
			<td><p align="right" style="font-size:1.5em">
				<?php echo $this->f($sobrantes_faltantes_manuales[0]['importe']); ?>
			</p></td>
		</tr>
	</table>
