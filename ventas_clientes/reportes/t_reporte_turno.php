<?php
function reporte_excel($arreglo, $worksheet1, $formato6) {
	$total["84"] = array("cantidad" => 0, "importe" => 0);
	$total["90"] = array("cantidad" => 0, "importe" => 0);
	$total["95"] = array("cantidad" => 0, "importe" => 0);
	$total["97"] = array("cantidad" => 0, "importe" => 0);
	$total["DB5 UV"] = array("cantidad" => 0, "importe" => 0);
	$total["DB5 S50"] = array("cantidad" => 0, "importe" => 0);
	$total["GLP"] = array("cantidad" => 0, "importe" => 0);
	$total["total_liquido"] = array("cantidad" => 0, "importe" => 0);
	$total["total"] = array("importe" => 0);
	$total["M"] = array("importe" => 0);
	$i = 8;

	$formato6 -> set_bold(1);
	
	foreach ($arreglo as $indice => $valor) {

		$worksheet1 -> write_string($i, 0, $indice, $formato6);
		$worksheet1 -> write_string($i, 1, "               84", $formato6);
		$worksheet1 -> write_string($i, 3, "               90", $formato6);
		$worksheet1 -> write_string($i, 5, "               95", $formato6);
		$worksheet1 -> write_string($i, 7, "               97", $formato6);
		$worksheet1 -> write_string($i, 9, "              DB5 UV", $formato6);
		$worksheet1 -> write_string($i, 11, "        DB5 S50", $formato6);
		$worksheet1 -> write_string($i, 13, "        Total Liquido", $formato6);
		$worksheet1 -> write_string($i, 15, "           GLP", $formato6);
		$worksheet1 -> write_string($i, 17, "Market", $formato6);
		$worksheet1 -> write_string($i, 18, "Total", $formato6);

		$i++;
		$worksheet1 -> write_string($i, 1, "Galones", $formato6);
		$worksheet1 -> write_string($i, 2, "Importe", $formato6);
		$worksheet1 -> write_string($i, 3, "Galones", $formato6);
		$worksheet1 -> write_string($i, 4, "Importe", $formato6);
		$worksheet1 -> write_string($i, 5, "Galones", $formato6);
		$worksheet1 -> write_string($i, 6, "Importe", $formato6);
		$worksheet1 -> write_string($i, 7, "Galones", $formato6);
		$worksheet1 -> write_string($i, 8, "Importe", $formato6);
		$worksheet1 -> write_string($i, 9, "Galones", $formato6);
		$worksheet1 -> write_string($i, 10, "Importe", $formato6);
		$worksheet1 -> write_string($i, 11, "Galones", $formato6);
		$worksheet1 -> write_string($i, 12, "Importe", $formato6);
		$worksheet1 -> write_string($i, 13, "Galones", $formato6);
		$worksheet1 -> write_string($i, 14, "Importe", $formato6);
		$worksheet1 -> write_string($i, 15, "Galones", $formato6);
		$worksheet1 -> write_string($i, 16, "Importe", $formato6);
		$worksheet1 -> write_string($i, 17, "Importe", $formato6);
		$worksheet1 -> write_string($i, 18, "Importe", $formato6);
		$i++;

		$gasolina["84"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["90"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["95"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["97"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["DB5 UV"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["DB5 S50"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["GLP"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["total_liquido"] = array("cantidad" => 0, "importe" => 0);
		$gasolina["total"] = array("importe" => 0);
		$gasolina["M"] = array("importe" => 0);

		foreach ($valor as $indice2 => $valor2) {

			if ($indice2 != $turno) {
				$turno = $indice2;
			}
			$worksheet1 -> write_string($i, 0, "Turno " . $turno, $formato6);
			$e = 1;
			$total_importe_turno = 0;
			$total_galones_turno = 0;
			foreach ($valor2 as $indice3 => $valor3) {
				if ($indice3 == "GLP") {
					$gasolina["total_liquido"]["importe"] = $gasolina["total_liquido"]["importe"] + $total_importe_turno;
					$gasolina["total_liquido"]["cantidad"] = $gasolina["total_liquido"]["cantidad"] + $total_galones_turno;

					$worksheet1 -> write_string($i, $e, f($total_galones_turno), $formato5);
					$e++;
					$worksheet1 -> write_string($i, $e, f($total_importe_turno), $formato5);
					$e++;
				}

				if ($indice3 != "M") {
					$total_importe_turno = $total_importe_turno + $valor3["importe"];
					$total_galones_turno = $total_galones_turno + $valor3["cantidad"];
					$gasolina[$indice3]["importe"] = $gasolina[$indice3]["importe"] + $valor3["importe"];
					$gasolina[$indice3]["cantidad"] = $gasolina[$indice3]["cantidad"] + $valor3["cantidad"];

					$worksheet1 -> write_string($i, $e, f($valor3["cantidad"]), $formato5);
					$e++;
					$worksheet1 -> write_string($i, $e, f($valor3["importe"]), $formato5);
					$e++;
				}

			}

			if ($indice3 == "M") {
				$total_importe_turno = $total_importe_turno + $valor3["importe"];
				$gasolina["M"]["importe"] = $gasolina["M"]["importe"] + $valor3["importe"];
				$gasolina["total"]["importe"] = $gasolina["total"]["importe"] + $total_importe_turno;

				$worksheet1 -> write_string($i, $e, f($valor3["importe"]), $formato5);
				$e++;
				$worksheet1 -> write_string($i, $e, f($total_importe_turno), $formato5);
				$e++;
			}

			$i++;
		}

	$worksheet1 -> write_string($i, 0, "Total Turno", $formato6);
		$worksheet1 -> write_string($i, 1, f($gasolina["84"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 2, f($gasolina["84"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 3, f($gasolina["90"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 4, f($gasolina["90"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 5, f($gasolina["95"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 6, f($gasolina["95"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 7, f($gasolina["97"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 8, f($gasolina["97"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 9, f($gasolina["DB5 UV"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 10, f($gasolina["DB5 UV"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 11, f($gasolina["DB5 S50"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 12, f($gasolina["DB5 S50"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 13, f($gasolina["total_liquido"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 14, f($gasolina["total_liquido"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 15, f($gasolina["GLP"]["cantidad"]), $formato5);
		$worksheet1 -> write_string($i, 16, f($gasolina["GLP"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 17, f($gasolina["M"]["importe"]), $formato5);
		$worksheet1 -> write_string($i, 18, f($gasolina["total"]["importe"]), $formato5);
		$i++;

		$total["84"] = array("cantidad" => $total["84"]["cantidad"] + $gasolina["84"]["cantidad"], "importe" => $total["84"]["importe"] + $gasolina["84"]["importe"]);
		$total["90"] = array("cantidad" => $total["90"]["cantidad"] + $gasolina["90"]["cantidad"], "importe" => $total["90"]["importe"] + $gasolina["90"]["importe"]);
		$total["95"] = array("cantidad" => $total["95"]["cantidad"] + $gasolina["95"]["cantidad"], "importe" => $total["95"]["importe"] + $gasolina["95"]["importe"]);
		$total["97"] = array("cantidad" => $total["97"]["cantidad"] + $gasolina["97"]["cantidad"], "importe" => $total["97"]["importe"] + $gasolina["97"]["importe"]);
		$total["DB5 UV"] = array("cantidad" => $total["DB5 UV"]["cantidad"] + $gasolina["DB5 UV"]["cantidad"], "importe" => $total["DB5 UV"]["importe"] + $gasolina["DB5 UV"]["importe"]);
		$total["DB5 S50"] = array("cantidad" => $total["DB5 S50"]["cantidad"] + $gasolina["DB5 S50"]["cantidad"], "importe" => $total["DB5 S50"]["importe"] + $gasolina["DB5 S50"]["importe"]);
		$total["total_liquido"] = array("cantidad" => $total["total_liquido"]["cantidad"] + $gasolina["total_liquido"]["cantidad"], "importe" => $total["total_liquido"]["importe"] + $gasolina["total_liquido"]["importe"]);
		$total["GLP"] = array("cantidad" => $total["GLP"]["cantidad"] + $gasolina["GLP"]["cantidad"], "importe" => $total["GLP"]["importe"] + $gasolina["GLP"]["importe"]);
		$total["M"] = array("importe" => $total["M"]["importe"] + $gasolina["M"]["importe"]);
		$total["total"] = array("importe" => $total["total"]["importe"] + $gasolina["total"]["importe"]);
	}

	$i = $i + 2;
	$worksheet1 -> write_string($i, 0, "Total General", $formato6);
	$worksheet1 -> write_string($i, 1, f($total["84"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 2, f($total["84"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 3, f($total["90"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 4, f($total["90"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 5, f($total["95"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 6, f($total["95"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 7, f($total["97"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 8, f($total["97"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 9, f($total["DB5 UV"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 10, f($total["DB5 UV"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 11, f($total["DB5 S50"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 12, f($total["DB5 S50"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 13, f($total["total_liquido"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 14, f($total["total_liquido"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 15, f($total["GLP"]["cantidad"]), $formato5);
	$worksheet1 -> write_string($i, 16, f($total["GLP"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 17, f($total["M"]["importe"]), $formato5);
	$worksheet1 -> write_string($i, 18, f($total["total"]["importe"]), $formato5);

}

function f($number) {
	return number_format($number, 2, '.', ',');
}

function dia($dato) {
	$original = $dato;
	$tmp = array();
	$dia = false;

	for ($i = 0; $i < count($dato); $i++) {
		if ($dia != $dato[$i]['dia']) {
			$tmp[$dato[$i]['dia']] = array();
			$dia = $dato[$i]['dia'];
		}
	}

	return turno(array("tmp" => $tmp, "original" => $original));
	//return $tmp;
}

function turno($dato) {
	$original = $dato["original"];
	$mes = $dato["tmp"];
	$turno = false;

	for ($i = 0; $i < count($original); $i++) {

		if ($turno != $original[$i]['dia'] . $original[$i]['turno']) {
			//if($turno != $original[$i]['turno']){

			$mes[$original[$i]['dia']][$original[$i]['turno']] = tipo_gasolina(array("original" => $original, "fecha" => $original[$i]['dia'], "turno" => $original[$i]['turno']));

			$turno = $original[$i]['dia'] . $original[$i]['turno'];
			//$turno = $original[$i]['turno'];
		}
	}

	return $mes;
}

function tipo_gasolina($dato) {

	$original = $dato["original"];
	$fecha = $dato["fecha"];
	$turno = $dato["turno"];
	$tmp = array();
	$gasolina_tipo = array('84', '90', '95', '97', 'DB5 UV', 'DB5 S50', 'GLP', "M");

	for ($i = 0; $i < count($gasolina_tipo); $i++) {

		$tmp[$gasolina_tipo[$i]] = array();
	}

	for ($i = 0; $i < count($original); $i++) {

		if ($fecha == $original[$i]["dia"] && $turno == $original[$i]["turno"])
			$tmp[$original[$i]["codigo_gasolina"]] = array("cantidad" => $original[$i]["cantidad"], "importe" => $original[$i]["importe"], "");

	}

	return $tmp;

}

/* PDF */

$arreglo = dia($this -> reporte);

// echo "<pre>";
// print_r($this->reporte);
// print_r($arreglo);
// echo "</pre>";

list($ch_almacen, $nombre_almacen) = explode("___", $_POST["ch_almacen"]);
$pdf -> almacen = $nombre_almacen;
$pdf -> fecha = $_POST["perido"] . "/" . $_POST["mes"] . "/" . $_POST["desde"] . " Al " . $_POST["periodo"] . "/" . $_POST["mes"] . "/" . $_POST["hasta"];
$pdf -> AliasNbPages();
$pdf -> AddPage();

/* EXCEL */

$worksheet1 -> write_string(2, 7, "Ventas Tickets por Turnos", $formato0);
$worksheet1 -> write_string(4, 2, "Almacen: " . $nombre_almacen, $formato0);
$worksheet1 -> write_string(5, 2, "Fecha del " . $_POST["peri666odo"] . "/" . $_POST["mes"] . "/" . $_POST["desde"] . " Al " . $_POST["periggodo"] . "/" . $_POST["mes"] . "/" . $_POST["hasta"], $formato0);

if ($_POST["action"] == "Excel") {
	
 	 reporte_excel($arreglo, $worksheet1, $formato5);
	 return;
}
?>

<html>
	<head>
		<style>
			td {
				color: #333333;
				font-family: Verdana, Arial, Helvetica, sans-serif;
				font-size: 11px;
				line-height: 17px;
			}
		</style>
	</head>
	<body>
		<center><h2 style="font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 18px; line-height: 14px; color: #336699; align: center; "><b>Ventas Tickets por Turnos<b></center></h2>
<form method="post" action="control.php" target="control">
<input type="hidden" id="rqst" name="rqst" value="REPORTES.REPORTETURNO">
<input type="hidden" name="consulta" value="si">

<table align="center" align="center" style="background:#FFFFFF">
  
  <tr>
    <td colspan="2" align="center">	
        <select name="ch_almacen">
    		<?php
			for ($i = 0; $i < count($this -> estaciones); $i++) {
				$sel = "";
				if ($_POST["ch_almacen"] == $this -> estaciones[$i]["ch_almacen"])
					$sel = "selected";

				echo "<option value='" . $this -> estaciones[$i]["ch_almacen"] . "___" . $this -> estaciones[$i]["btrim"] . "' $sel>" . $this -> estaciones[$i]["btrim"] . "</option>";

			}
		?>
    	 </select>
    </td>
    </tr>
    <tr>
		<td colspan="2" align="center">Periodo<input type="input" name="periodo" value="<?php echo empty($_POST["periodo"]) ? date("Y") : $_POST["periodo"]; ?>" size="4"></div></td>
    </tr>
    <tr>
		<td colspan="2" align="center">Mes<input type="input" name="mes" value="<?php echo empty($_POST["mes"]) ? date("m") : $_POST["mes"]; ?>" size="2"></div>
       </td>
       
     </tr>
     <tr>
		<td colspan="2" align="center">Desde<input type="input" name="desde" value="<?php echo empty($_POST["desde"]) ? date("d") : $_POST["desde"]; ?>"  size="2">
		Hasta<input type="input" name="hasta" value="<?php echo empty($_POST["hasta"]) ? date("d") : $_POST["hasta"]; ?>"  size="2"></td>
      </tr>
      
     
      
      <tr>
         <td>
             <div style="float: left">Boleta<input type="checkbox" name="td[]" value="'B'"  size="2" checked></div>
             <div style="float: left">Factura<input type="checkbox" name="td[]" value="'F'"  size="2" checked></div>
             <div style="float: left">Nota de Despacho<input type="checkbox" name="td[]" value="'N'"  size="2" checked></div>
             <div style="float: left">Afericion<input type="checkbox" name="td[]" value="'A'"  size="2" checked></div>
             <div style="float: left">Efectivo<input type="checkbox" name="fpago[]" value="'1'"  size="2" ></div>
         </td>
      </tr>
      
      
     <tr>
         <td align="center">
             
             <input type="submit" name="action" value="Reporte" style="font-size: 14px">
         
             <button value="Excel" name="action" type="submit"><img alt="left" src="/sistemaweb/images/excel_icon.png">Excel</button> 
 
             <button value="PDF" name="action" type="submit" style="height: 25px;"><img alt="left" src="/sistemaweb/images/icono_pdf.gif">PDF</button>

         </td>
         
     </tr>
	</table>

	</form>
	<div id="content_footer">
	</center>

	<table align="center">
		<tbody>

		<?php
     
		if(empty($arreglo) and !empty($_POST["consulta"])){
		?>
			<tr with="120"><td>No Hay resultados</td><tr>
                      
		<?php
		}

		$total["84"]		=array("cantidad"=>0,"importe"=>0);
		$total["90"]		=array("cantidad"=>0,"importe"=>0);
		$total["95"]		=array("cantidad"=>0,"importe"=>0);
		$total["97"]		=array("cantidad"=>0,"importe"=>0);
		$total["DB5 UV"]		=array("cantidad"=>0,"importe"=>0);
		$total["DB5 S50"]		=array("cantidad"=>0,"importe"=>0);
		$total["GLP"]		=array("cantidad"=>0,"importe"=>0);
		$total["total_liquido"]	=array("cantidad"=>0,"importe"=>0);
		$total["total"]		=array("importe"=>0);
		$total["M"]		=array("importe"=>0);

		foreach ($arreglo as $indice => $valor) {
		$this->Titulos($pdf,$indice);
		?>

			<tr>
				<td bgcolor="#336699" style="color:#FFFFFF">Dia <?php echo $indice; ?></td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">84</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">90</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">95</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">97</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">DB5 UV</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">DB5 S50</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">Total Liquido</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">GLP</td>
				<td align="center" bgcolor="#336699" style="color:#FFFFFF">Market</td>
				<td align="center" colspan="2" bgcolor="#336699" style="color:#FFFFFF">Total</td>	
			</tr>

			<tr>
				<td align="right"></td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF;">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Galones</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Litros</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
				<td bgcolor="#66A3E0" style="color:#FFFFFF">Importe</td>
			</tr>
		
			<tr>
			
				<?php

				$turno = false;
			    	$gasolina["84"]			= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["90"]			= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["95"]			= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["97"]			= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["DB5 UV"]		= array("cantidad"=>0,"importe"=>0);
					$gasolina["DB5 S50"]		= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["GLP"]		= array("cantidad"=>0,"importe"=>0);
			    	$gasolina["total_liquido"]	= array("cantidad"=>0,"importe"=>0);
				$gasolina["total"]		= array("importe"=>0);
				$gasolina["M"]			= array("importe"=>0);
       
				foreach ($valor as $indice2 => $valor2){

					if($indice2 != $turno){

						$turno = $indice2;
						$pdf->Ln();?>
			
					<?php	}

				$pdf->Cell(30,4,"Turno ".$turno,1,0,L);
					?>
	              
					<td align="right" bgcolor="#3385D6" style="color:#FFFFFF">Turno <?php echo $turno; ?></td>
				
					<?php 

					$total_importe_turno = 0;
					$total_galones_turno = 0;
                
					foreach ($valor2 as $indice3 => $valor3){

						if(empty($valor3["cantidad"]))
							$valor3["cantidad"] = "0.00";
                 
                 if(empty($valor3["importe"]))
                    $valor3["importe"]="0.00";
                 
                 
                 
                  
                  if($indice3=="GLP")
                        {
                             $pdf->Cell(15,4,f($total_galones_turno),1,0);
                             $pdf->Cell(15,4,f($total_importe_turno),1,0);
                        ?>
                        <td align="right" bgcolor="#E8F0EA"><?php echo f($total_galones_turno); ?></td>
                         <td align="right" bgcolor="#E8F0EA"><?php echo f($total_importe_turno); ?></td>
                         
                        <?php
						//$total_importe_turno=$total_importe_turno+$valor3["importe"];

						$gasolina["total_liquido"]["importe"] = $gasolina["total_liquido"]["importe"] + $total_importe_turno;
						$gasolina["total_liquido"]["cantidad"] = $gasolina["total_liquido"]["cantidad"] + $total_galones_turno;
						}

						if($indice3!="M")
						{
						$total_importe_turno=$total_importe_turno+$valor3["importe"];
						$total_galones_turno=$total_galones_turno+$valor3["cantidad"];

						$gasolina[$indice3]["importe"]=$gasolina[$indice3]["importe"]+$valor3["importe"];
						$gasolina[$indice3]["cantidad"]=$gasolina[$indice3]["cantidad"]+$valor3["cantidad"];
						$pdf->Cell(15,4,f($valor3["cantidad"]),1,0);
						$pdf->Cell(15,4,f($valor3["importe"]),1,0);
               ?>
                
                 <td align="right" bgcolor="#E8F0EA"><?php echo f($valor3["cantidad"]); ?></td>
                <td align="right" bgcolor="#E8F0EA"><?php echo f($valor3["importe"]); ?></td>
                   
                    
                <?php
				}
				else if($indice3=="M")
				{
				$total_importe_turno=$total_importe_turno+$valor3["importe"];
				$gasolina["M"]["importe"]=$gasolina["M"]["importe"]+$valor3["importe"];
				$gasolina["total"]["importe"]=$gasolina["total"]["importe"]+$total_importe_turno;
				$pdf->Cell(20,4,f($valor3["importe"]),1,0,L);
				$pdf->Cell(20,4,f($total_importe_turno),1,0,L);
                ?>
                
                 <td align="right" bgcolor="#E8F0EA"><?php echo f($valor3["importe"]); ?></td>
                <td align="right" bgcolor="#E8F0EA"><?php echo f($total_importe_turno); ?></td>
                   
                    
                <?php

				}

				}
 ?>
			
			</tr>
					    
			<?php }
							$pdf->Ln();
							$pdf->Cell(30,4,"Total Turnos",1,0);
							$pdf->Cell(15,4,f($gasolina["84"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["84"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["90"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["90"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["95"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["95"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["97"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["97"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["DB5 UV"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["DB5 UV"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["DB5 S50"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["DB5 S50"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["total_liquido"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["total_liquido"]["importe"]),1,0);
							$pdf->Cell(15,4,f($gasolina["GLP"]["cantidad"]),1,0);
							$pdf->Cell(15,4,f($gasolina["GLP"]["importe"]),1,0);
							$pdf->Cell(20,4,f($gasolina["M"]["importe"]),1,0,L);
							$pdf->Cell(20,4,f($gasolina["total"]["importe"]),1,0,L);
                 ?>
                 <tr>
                <td bgcolor="#3366CC" style="color:#FFFFFF">Total Turnos</td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["84"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["84"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["90"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["90"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["95"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["95"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["97"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["97"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["DB5 UV"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["DB5 UV"]["importe"]); ?></td>
				<td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["DB5 S50"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["DB5 S50"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["total_liquido"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["total_liquido"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["GLP"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["GLP"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["M"]["importe"]); ?></td>
                <td align="right"  bgcolor="#3366CC" style="color:#FFFFFF"><?php echo f($gasolina["total"]["importe"]); ?></td>
                
            </tr>
			 <?php
				$total["84"] = array("cantidad" => $total["84"]["cantidad"] + $gasolina["84"]["cantidad"], "importe" => $total["84"]["importe"] + $gasolina["84"]["importe"]);
				$total["90"] = array("cantidad" => $total["90"]["cantidad"] + $gasolina["90"]["cantidad"], "importe" => $total["90"]["importe"] + $gasolina["90"]["importe"]);
				$total["95"] = array("cantidad" => $total["95"]["cantidad"] + $gasolina["95"]["cantidad"], "importe" => $total["95"]["importe"] + $gasolina["95"]["importe"]);
				$total["97"] = array("cantidad" => $total["97"]["cantidad"] + $gasolina["97"]["cantidad"], "importe" => $total["97"]["importe"] + $gasolina["97"]["importe"]);
				$total["DB5 UV"] = array("cantidad" => $total["DB5 UV"]["cantidad"] + $gasolina["DB5 UV"]["cantidad"], "importe" => $total["DB5 UV"]["importe"] + $gasolina["DB5 UV"]["importe"]);
				$total["DB5 S50"] = array("cantidad" => $total["DB5 S50"]["cantidad"] + $gasolina["DB5 S50"]["cantidad"], "importe" => $total["DB5 S50"]["importe"] + $gasolina["DB5 S50"]["importe"]);
				$total["total_liquido"] = array("cantidad" => $total["total_liquido"]["cantidad"] + $gasolina["total_liquido"]["cantidad"], "importe" => $total["total_liquido"]["importe"] + $gasolina["total_liquido"]["importe"]);
				$total["GLP"] = array("cantidad" => $total["GLP"]["cantidad"] + $gasolina["GLP"]["cantidad"], "importe" => $total["GLP"]["importe"] + $gasolina["GLP"]["importe"]);
				$total["M"] = array("importe" => $total["M"]["importe"] + $gasolina["M"]["importe"]);
				$total["total"] = array("importe" => $total["total"]["importe"] + $gasolina["total"]["importe"]);
				$pdf -> Ln();
				}
				if(!empty($arreglo))
				{
				$pdf->Ln();
				$pdf->Cell(30,4,"Total General",1,0);
				$pdf->Cell(15,4,f($total["84"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["84"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["90"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["90"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["95"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["95"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["97"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["97"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["DB5 UV"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["DB5 UV"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["DB5 S50"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["DB5 S50"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["total_liquido"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["total_liquido"]["importe"]),1,0);
				$pdf->Cell(15,4,f($total["GLP"]["cantidad"]),1,0);
				$pdf->Cell(15,4,f($total["GLP"]["importe"]),1,0);
				$pdf->Cell(20,4,f($total["M"]["importe"]),1,0,L);
				$pdf->Cell(20,4,f($total["total"]["importe"]),1,0,L);
                 ?>
                <tr><td colspan="17"></td></tr>
                <tr><td colspan="17"></td></tr>
                <tr><td colspan="17"></td></tr>
                <tr><td colspan="17"></td></tr>
             <tr>
                <td bgcolor="#003366" style="color:#FFFFFF">Total General</td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["84"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["84"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["90"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["90"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["95"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["95"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["97"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["97"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["DB5 UV"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["DB5 UV"]["importe"]); ?></td>
				<td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["DB5 S50"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["DB5 S50"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["total_liquido"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["total_liquido"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["GLP"]["cantidad"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["GLP"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["M"]["importe"]); ?></td>
                <td align="right"  bgcolor="#003366" style="color:#FFFFFF"><?php echo f($total["total"]["importe"]); ?></td>
                
            </tr>
            <?php } ?>
		</tbody>
	</table>
</div>
<?php ?>
