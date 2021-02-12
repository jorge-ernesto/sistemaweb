
<?php
echo "<pre>";
    print_r($this->reporte);
echo "</pre>";
?>
<center><h2 style="font-family: Verdana,Arial,Helvetica,sans-serif;font-size: 18px;line-height: 14px;color: #336699;align=center;"><b>Ventas con Tarjetas de Cr&eacute;dito<b></center></h2>
<form method="post" action="control.php" target="control">
<input type="hidden" id="rqst" name="rqst" value="REPORTES.TURNO">
<table align="center" class="normal">
  <tr>
    <td align="center">	
        <select name="ch_almacen">
    		<?php for($i=0;$i<count($this->estaciones);$i++)
    		{
    		  
    		  echo "<option value='".$this->estaciones[$i]["ch_almacen"]."'>".$this->estaciones[$i]["btrim"]."</option>";
    		
    		 }?>
    	 </select>
    </td>
    <tr><td align="center">Periodo<input type="input" name="periodo" value="<?php echo date("Y");?>" size="4"></td></tr>
     <tr><td align="center">Mes<input type="input" name="mes" value="<?php echo date("m");?>" size="2"></td></tr>
     <tr><td align="center">Desde<input type="input" name="desde" value="<?php echo date("d");?>"  size="2">
     			    Hasta<input type="input" name="hasta" value="<?php echo date("d");?>"  size="2"></td></tr>
     <tr><td align="center"><input type="submit" value="Reporte"></td></tr>
  </tr>
</table>
</form>
<div id="content_footer">

<button onclick="javascript:parent.location.href='control.php?rqst=REPORTES.VENTASDIARIAS&amp;action=pdf&amp;desde=16%2F03%2F2014&amp;hasta=16%2F03%2F2014&amp;modo=RESUMIDO&amp;estacion=TODAS';return false" value="" name="fm">

<img alt="left" src="/sistemaweb/images/icono_pdf.gif"> PDF</button>
	<table border="1">
		<tbody>
		<?php
			$condicion	=true;
			$turno		=false;
			$fecha		=false;

			for($i=0;$i<count($this->reporte);$i++){
                
				if($fecha!=$this->reporte[$i]["dia"]){
		?>
				<tr>
					<td>Dia <?php echo date("Y-m-d", strtotime($this->reporte[$i]["dia"]));?></td>
					<td align="center" colspan="2">84</td>
					<td align="center" colspan="2">90</td>
					<td align="center" colspan="2">95</td>
					<td align="center" colspan="2">97</td>
					<td align="center" colspan="2">D2</td>
					<td align="center" colspan="2">GLP</td>
					<td align="center" colspan="2">Market</td>
				</tr>
				<?php 
					$fecha = $this->reporte[$i]["dia"];
				}
			  
			  		if($turno!=$this->reporte[$i]["turno"]){ ?>
						<tr>
							<td>Turno <?php echo $this->reporte[$i]["turno"];?></td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>
							<td>Galones</td>
							<td>Importe</td>       
						</tr>
						<tr>
							<td></td>
					<?php
						$turno = $this->reporte[$i]["turno"]; 
					} ?>
			
						<!--	<td align="right"><?php echo $this->reporte[$i]["total"]["11620301cantidad"];?></td>
							<td align="right"><?php echo $this->reporte[$i]["total"]["11620301importe"];?></td>-->
			<?php } ?>
											<td align="right"><?php echo $this->reporte[$i]["total"]["11620301cantidad"];?></td>
							<td align="right"><?php echo $this->reporte[$i]["total"]["11620301importe"];?></td>
		</tbody>
	</table>
</div>
