<?php

class DesenpenoTemplate extends Template {

    function titulo() {
        return '<h2 align="center"><b>Desenpeño del Trabajador.</b></h2>';
    }

    function FormularioPrincipal() {
        ?>
        <div style="text-align: center;position: relative;" id="id_nuevo_registro_view">
            <div id="cargardor" style="position: absolute;display: inline;z-index: 999;"><img src="movimientos/cg.gif" /></div>
            <div ><h3 style="color: #336699; text-align: center;"><b>Desenpe&ntilde;o del Trabajador.</b></h3></div>
            <table style="text-align: left;position: relative;margin: 5px auto;">
                <tr>
                    <td>Sucursal: </td>
                    <td><div style="float: left;" id="cmbtipooperacion">
                        </div> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>Fecha Inicio: <input type="hidden" id="tmp_ini" value="<?php echo date('Y-m-') . "01"; ?>"/></td>
                    <td><input type='text' id='fecha_inicial' class='fecha_formato'/> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>Fecha Final: <input type="hidden" id="tmp_final" value="<?php echo date('Y-m-d'); ?>"/></td>
                    <td><input type='text' id='fecha_final' class='fecha_formato'/> </td>
                    <td></td>
                </tr>

                <tr>
                    <td>Tipo Visto:</td>
                    <td><select id="type_view" name="type_view">
                            <option value="D">Detallado</option>
                            <option value="R">Resumido</option>
                       
                            
                            
                        </select> </td>
                    <td></td>
                </tr>
                <tr>
                    <td><button id="btnseleccionar"><img align="right" src="/sistemaweb/images/search.png"/>Consultar</button> </td>
                    <td><button id="id_nuevo_registro"><img align="right" src="/sistemaweb/icons/agregar.gif">Ingreso GNV</button> </td>
                    
                    <td><button id="btnexcel"><img align="right" src="/sistemaweb/images/search.png"/>Excel</button> </td>
                </tr>
            </table>




        </div>
        <div  id="contenidoTablaSelecionar" style="text-align: center;position: relative;">

        </div>



        <?php
    }

    function FormularioPrincipalSegundario($estaciones, $caja, $operacion, $almacen, $fecha_actual, $serie,$tipo_cambio) {
        ?>

        <div ><h3 style="color: #336699; text-align: center;">Ingreso y Visaualizacion de GNV.</h3></div>
        <div id="cargardor" style="position: absolute;display: inline;z-index: 999;"><img src="movimientos/cg.gif" /></div>
        <table style="text-align: left;position: relative;margin: 5px auto;">
            <tr>
                <td>Almacenes : </td>
                <td colspan="2"> <select id="cmnsucursal_id" >
                        
                        <?php
                        foreach ($estaciones as $key => $valor) {
                        	$view=$valor[1];
                            if (strcmp($almacen, $valor[0]) == 0) {
                                echo "<option value='$valor[0]' selected>$view</option>";
                            } else {
                                echo "<option value='$valor[0]'>$view</option>";
                            }
                        }
                        ?>
                    </select> </td>
                    
            </tr>
           
           



            <tr>
                <td>Trabajadores:  </td>
                <td id="idcaja" colspan="2"> <select id="cmncaja_id">
                        <?php
                        foreach ($caja as $key => $valor) {
                        	$view=$valor[0]."   -   ".$valor[1];
                            echo "<option value='$valor[0]'>$view</option>";
                        }
                        ?>
                    </select></td>
            </tr>
            <tr>
                <td>Turno:  </td>
                <td id="idturno" colspan="2"> <select id="cmnturno_id">
                   
                            <option value='1'>#1</option>
                            <option value='2'>#2</option>
                            <option value='3'>#3</option>
                            <option value='4'>#4</option>
                            <option value='5'>#5</option>
                            <option value='6'>#6</option>
                      
                    </select></td>
            </tr>
             <tr>
                <td>Fecha  <input type="hidden" id="fecha_tmp" value="<?php echo $fecha_actual; ?>"/></td>
                <td colspan="2"> <input type='text' id='fecha_mostar' class='fecha_formato' /> </td>
            </tr>
            <tr>
                <td>Cantidad M3  </td>
                <td colspan="2"> <input type='text' id='txtcantida'  value="0.00"/> </td>
            </tr>
   			<tr>
                <td>Importe S/  </td>
                <td colspan="2"> <input type='text' id='txtimporte'  value="0.00"/> </td>
            </tr>

            <tr >
                <td><input type="hidden" id="tipo_accion" value="-" /> 
                    <button id="btnbuscar"><img align="right" src="/sistemaweb/images/search.png"/>Ver</button></td>
                    
                     <td><input type="hidden" id="tipo_accion" value="-" /> 
                    <button id="btninsert"><img align="right" src="/sistemaweb/images/search.png"/>Agregar</button></td>
                <td><button  onclick="irhome()"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar</button> </td>
            </tr>



        </table>


        <?php
    }



    function CrearTablareporte($dataOrdena,$datoscliente,$type_view) {
    
        //primero mostraremos las ventas 
                $suma_monto_galonTotal=0;
				$suma_monto_importeTotal=0;
				$suma_monto_gnv_galonTotal=0;
				$suma_monto_gnv_importeTotal=0;
				$suma_monto_lubricanteTotal=0;
        foreach($dataOrdena as $keyturno => $coditra){
        	
        		$suma_monto_galonT=0;
				$suma_monto_importeT=0;
				$suma_monto_gnv_galonT=0;
				$suma_monto_gnv_importeT=0;
				$suma_monto_lubricanteT=0;
			
        	?>
        <table cellspacing="0" cellpadding="2" border="0" style="text-align: left;position: relative;margin: 30px auto;">
        <tbody>
        	<tr>
                 <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4"><?php echo "TURNO #".$keyturno;?></td>
           </tr>
           <tr>
               
                 <td style="color:#FFFFFF;font-size:11px;text-align: center;margin:5px;" bgcolor="#4682B4" colspan="2"></td>
                 <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4" colspan="2">LIQUIDOS</td>
                 <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4" colspan="2">GNV</td>
                 <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">LUBRICANTES</td>
           </tr>
               
            
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4"></td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4"> FECHA</td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4"> CANTIDAD (GLNS)</td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">IMPORTE (S/)</td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4" >CANTIDAD (M3) </td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4" >IMPORTE (S/) </td>
            <td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">IMPORTE(S/)</td>
            
        
     
            <?php

        $i = 0;
	
		
		  	foreach($coditra as $keycod => $dia){
		  		$suma_monto_galon=0;
				$suma_monto_importe=0;
				$suma_monto_gnv_galon=0;
				$suma_monto_gnv_importe=0;
				$suma_monto_lubricante=0;
				
				if($type_view=="D"){
					echo "<tr class='$estila'><td>TRABAJADOR : ".$keycod." ".$datoscliente[trim($keycod)] ."</td></tr>";
				}
				ksort($dia);
			foreach($dia as $keydia => $valuedata){
						
			
                	

				if($type_view=="D"){

                echo "<tr >";//class='$estila'
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'></td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . $keydia . "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($valuedata["monto_galon"],2). "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($valuedata["monto_importe"],2) . "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($valuedata['monto_gnv_galon'],2) . "</td>";
				echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($valuedata['monto_gnv_importe'],2) . "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($valuedata['monto_lubricante'],2) . "</td>";
                echo "</tr>";
					}

                $i++;
                $suma_monto_galon+=$valuedata["monto_galon"];
				$suma_monto_importe+=$valuedata["monto_importe"];
				$suma_monto_gnv_galon+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importe+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricante+=$valuedata['monto_lubricante'];
				
				$suma_monto_galonT+=$valuedata["monto_galon"];
				$suma_monto_importeT+=$valuedata["monto_importe"];
				$suma_monto_gnv_galonT+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importeT+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricanteT+=$valuedata['monto_lubricante'];
				
				 
				
				$suma_monto_galonTotal+=$valuedata["monto_galon"];
				$suma_monto_importeTotal+=$valuedata["monto_importe"];
				$suma_monto_gnv_galonTotal+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importeTotal+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricanteTotal+=$valuedata['monto_lubricante'];
				
				

            }
				
				if($type_view=="R"){
				echo "<tr >";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: left;bgcolor=#FFFFFF'>".$keycod." ".$datoscliente[trim($keycod)] ."</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'></td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($suma_monto_galon,2). "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($suma_monto_importe,2) . "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($suma_monto_gnv_galon,2) . "</td>";
				echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($suma_monto_gnv_importe,2) . "</td>";
                echo "<td style='color:#1C1D1C;font-size:11px;text-align: right;bgcolor=#FFFFFF'>" . number_format($suma_monto_lubricante,2) . "</td>";
                echo "</tr>";
				
				}else{//si es detallado
				
		echo "<tr>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' ></td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >SUBTOTAL</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_galon,2). "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_importe,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_gnv_galon,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_gnv_importe,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_lubricante,2) . "</td>";
		echo "</tr>";
		}


}
if($type_view=="R"){
		echo "<tr>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' ></td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >SUBTOTAL</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_galonT,2). "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_importeT,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_gnv_galonT,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_gnv_importeT,2) . "</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'>" . number_format($suma_monto_lubricanteT,2) . "</td>";
		echo "</tr>";
	
	}
		


            ?>

        </tbody>
        </table>

        

        <?php }
        echo"<table cellspacing='0' cellpadding='2' border='0' style='text-align: left;position: relative;margin: 30px auto;' >";
        echo"<tbody>";
        
	    echo "<tr>";
		echo "<td bgcolor='#4682B4'  align='rigth' style='color:#FFFFFF;font-size:11px;text-align: left;' >TOTAL CANTITAD LIQ(GLNS) </td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >" . number_format($suma_monto_galonTotal,2). "</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td bgcolor='#4682B4'  align='left' style='color:#FFFFFF;font-size:11px;text-align: left;' >TOTAL IMPORTE LIQ S/ </td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >" . number_format($suma_monto_importeTotal,2). "</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td bgcolor='#4682B4'  align='rigth' style='color:#FFFFFF;font-size:11px;text-align: left;' >TOTAL CANTIDAD GNV (M3)</td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >" . number_format($suma_monto_gnv_galonTotal,2). "</td>";
		echo "</tr>";
		
		
		echo "<tr>";
		echo "<td bgcolor='#4682B4'  align='rigth' style='color:#FFFFFF;font-size:11px;text-align: left;' >TOTAL IMPORTE GNV S/ </td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >" . number_format($suma_monto_gnv_importeTotal,2). "</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td bgcolor='#4682B4'  align='rigth' style='color:#FFFFFF;font-size:11px;text-align: left;' >TOTAL LUBRICANTES S/ </td>";
		echo "<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' >" . number_format($suma_monto_lubricanteTotal,2). "</td>";
		echo "</tr>";
		
		echo"</table >";
    }

    
	    function CrearTablaExcel($dataOrdena,$datoscliente,$type_view,$fi,$ff) {
	    	  //primero mostraremos las ventas 
        $suma_monto_galonTotal=0;
		$suma_monto_importeTotal=0;
		$suma_monto_gnv_galonTotal=0;
		$suma_monto_gnv_importeTotal=0;
		$suma_monto_lubricanteTotal=0;
	/////////---------------------------------
	    $workbook = new Workbook("report.xls");
		$formato0 =& $workbook->add_format();
		$formato2 =& $workbook->add_format();
		$formato5 =& $workbook->add_format();
		$totales =& $workbook->add_format();
		$numeros =& $workbook->add_format();
		$fecha =& $workbook->add_format();
		$texttotal =& $workbook->add_format();
		$trabaj =& $workbook->add_format();
		
		$fecha->set_size(11);
		$fecha->set_align('center');

		$formato0->set_size(11);
		$formato0->set_bold(1);
		$formato0->set_align('left');
		$formato2->set_size(10);
		$formato2->set_bold(1);
		$formato2->set_align('center');
		$formato5->set_size(11);
		$formato5->set_align('left');
		
		$numeros->set_size(11);
		$numeros->set_align('right');
		
		$totales->set_size(11);
		$totales->set_bold(1);
		$totales->set_align('right');
		
	    $texttotal->set_bold(1);
		$texttotal->set_align('left');
		$texttotal->set_size(11);
		
		
		$trabaj->set_bold(1);
		$trabaj->set_align('left');
		$trabaj->set_size(11);

		$worksheet1 =& $workbook->add_worksheet('Trabajador');
		$worksheet1->set_column(0, 0, 16);
		$worksheet1->set_column(1, 1, 30);
		$worksheet1->set_column(2, 2, 30);
		$worksheet1->set_column(3, 3, 30);
		$worksheet1->set_column(4, 4, 30);
		$worksheet1->set_column(5, 5, 30);
		$worksheet1->set_column(6, 6, 30);

		$worksheet1->set_zoom(100);
		$worksheet1->set_landscape(100);
		 
         

		$worksheet1->write_string(1, 3, "DESEMPEÑO DE COLABORADOR",$formato0);	
		$worksheet1->merge_cells(4, 0, 4, 1);	
		$worksheet1->write_string(4, 0, "FECHA DEL   ".$fi." AL   ".$ff,$formato0);
		$worksheet1->write_string(5, 0, " ",$formato0);

		$a = 7;

		
	    	
	    	
    
      
        foreach($dataOrdena as $keyturno => $coditra){
        	
        		$suma_monto_galonT=0;
				$suma_monto_importeT=0;
				$suma_monto_gnv_galonT=0;
				$suma_monto_gnv_importeT=0;
				$suma_monto_lubricanteT=0;
				
		//---------
		$worksheet1->write_string($a, 0, "TURNO",$formato2);
		$worksheet1->write_string($a, 1, "$keyturno",$formato2);
		$a++;
		$a++;
		//---------		
		$worksheet1->write_string($a, 0, "FECHA",$formato2);
		$worksheet1->write_string($a, 1, "CANTIDAD LIQ (GLNS)",$formato2);
		$worksheet1->write_string($a, 2, "IMPORTE LIQ (S)",$formato2);
		$worksheet1->write_string($a, 3, "CANTIDAD GNV (M3)",$formato2);	
		$worksheet1->write_string($a, 4, "IMPORTE  GNV (S)",$formato2);
		$worksheet1->write_string($a, 5, "IMPORTE  LUBRICANTE (S)",$formato2);
		$a++;
			
        	?>

 <?php

        $i = 0;
	
		
		  	foreach($coditra as $keycod => $dia){
		  		$suma_monto_galon=0;
				$suma_monto_importe=0;
				$suma_monto_gnv_galon=0;
				$suma_monto_gnv_importe=0;
				$suma_monto_lubricante=0;
				
				if($type_view=="D"){
				
				$worksheet1->write_string($a, 0, "TRABAJADOR",$formato2);
				$worksheet1->merge_cells($a, 1, $a, 3);
				$worksheet1->write_string($a, 1, $keycod.", ".trim($datoscliente[trim($keycod)]),$trabaj);
				$a++;
		
		
		
				}
				ksort($dia);
			foreach($dia as $keydia => $valuedata){
						
			
                	

				if($type_view=="D"){

					$worksheet1->write_string($a, 0, "$keydia",$fecha);
					$worksheet1->write_string($a, 1, number_format($valuedata["monto_galon"],2),$numeros);
					$worksheet1->write_string($a, 2,  number_format($valuedata["monto_importe"],2),$numeros);
					$worksheet1->write_string($a, 3, number_format($valuedata['monto_gnv_galon'],2),$numeros);	
					$worksheet1->write_string($a, 4, number_format($valuedata['monto_gnv_importe'],2),$numeros);
					$worksheet1->write_string($a, 5, number_format($valuedata['monto_lubricante'],2),$numeros);
					$a++;
				
					}

                $i++;
                $suma_monto_galon+=$valuedata["monto_galon"];
				$suma_monto_importe+=$valuedata["monto_importe"];
				$suma_monto_gnv_galon+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importe+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricante+=$valuedata['monto_lubricante'];
				
				$suma_monto_galonT+=$valuedata["monto_galon"];
				$suma_monto_importeT+=$valuedata["monto_importe"];
				$suma_monto_gnv_galonT+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importeT+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricanteT+=$valuedata['monto_lubricante'];
				
				 
				
				$suma_monto_galonTotal+=$valuedata["monto_galon"];
				$suma_monto_importeTotal+=$valuedata["monto_importe"];
				$suma_monto_gnv_galonTotal+=$valuedata['monto_gnv_galon'];
				$suma_monto_gnv_importeTotal+=$valuedata['monto_gnv_importe'];
				$suma_monto_lubricanteTotal+=$valuedata['monto_lubricante'];
				
				

            }
				
				if($type_view=="R"){
					$worksheet1->write_string($a, 0, $keycod." ".$datoscliente[trim($keycod)],$totales);
					$worksheet1->write_string($a, 1, number_format($suma_monto_galon,2),$totales);
					$worksheet1->write_string($a, 2,  number_format($suma_monto_importe,2),$totales);
					$worksheet1->write_string($a, 3, number_format($suma_monto_gnv_galon,2),$totales);	
					$worksheet1->write_string($a, 4, number_format($suma_monto_gnv_importe,2),$totales);
					$worksheet1->write_string($a, 5, number_format($suma_monto_lubricante,2),$totales);
					$a++;
				
				}else{//si es detallado
				
		
					$a++;
		            $worksheet1->write_string($a, 0, "SUBTOTAL",$totales);
					$worksheet1->write_string($a, 1, number_format($suma_monto_galon,2),$totales);
					$worksheet1->write_string($a, 2, number_format($suma_monto_importe,2),$totales);
					$worksheet1->write_string($a, 3, number_format($suma_monto_gnv_galon,2),$totales);	
					$worksheet1->write_string($a, 4, number_format($suma_monto_gnv_importe,2),$totales);
					$worksheet1->write_string($a, 5, number_format($suma_monto_lubricante,2),$totales);
					$a++;
		
		
		}


}
if($type_view=="R"){		
					$a++;
		            $worksheet1->write_string($a, 0, "SUBTOTAL",$totales);
					$worksheet1->write_string($a, 1, number_format($suma_monto_galonT,2),$totales);
					$worksheet1->write_string($a, 2, number_format($suma_monto_importeT,2),$totales);
					$worksheet1->write_string($a, 3, number_format($suma_monto_gnv_galonT,2),$totales);	
					$worksheet1->write_string($a, 4, number_format($suma_monto_gnv_importeT,2),$totales);
					$worksheet1->write_string($a, 5, number_format($suma_monto_lubricanteT,2),$totales);
					$a++;
		
	
	}
		


            ?>

        

        <?php }
					$a++;
		            $worksheet1->write_string($a, 2, "TOTAL CANTITAD LIQ(GLNS)",$texttotal);
					$worksheet1->write_string($a, 3, number_format($suma_monto_galonTotal,2,".",""),$totales);
					$a++;
					$worksheet1->write_string($a, 2, "TOTAL IMPORTE LIQ S/",$texttotal);
					$worksheet1->write_string($a, 3, number_format($suma_monto_importeTotal,2),$totales);	
					$a++;
					$worksheet1->write_string($a, 2, "TOTAL CANTIDAD GNV (M3)",$texttotal);
					$worksheet1->write_string($a, 3, number_format($suma_monto_gnv_galonTotal,2),$totales);
					$a++;
					$worksheet1->write_string($a, 2, "TOTAL IMPORTE GNV S/",$texttotal);
					$worksheet1->write_string($a, 3, number_format($suma_monto_gnv_importeTotal,2),$totales);
					$a++;
					$worksheet1->write_string($a, 2, "TOTAL LUBRICANTES S/",$texttotal);
					$worksheet1->write_string($a, 3, number_format($suma_monto_lubricanteTotal,2),$totales);
		
		$workbook->close();	
    }

	function CrearTablaGNV($registros) {
        	?>

        	<span style="color:#30767F;font-weight: bold;">Detalle de Ventas GNV</span>
        	<table cellspacing="0" cellpadding="2" border="0" style="text-align: left;position: relative;margin: 30px auto;">	

		   	<tr>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">FECHA</td>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">TURNO</td>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">COD TRABAJADOR</td>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">CANTIDAD M3</td>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">IMPORTE S/</td>
			    	<td style="color:#FFFFFF;font-size:11px;text-align: center;" bgcolor="#4682B4">-</td>
			</tr>

			<tbody id="registros">

		    	<?php

		    	$i = 0;

		    	$sumacantidad = 0;
				$sumaimporte = 0;

	  		foreach ($registros as $llave => $value) {
	  			

		        	$estila = "fila_registro_imppar";

		        	if ($i % 2 == 0)
		            		$estila = "fila_registro_par";

			
					$sumacantidad += (float)$value['cantidad'];
					$sumaimporte += (float)$value['importe'];

				echo "<tr >";//class='$estila'
				echo "<td>" . $value['dia'] . "</td>";
				echo "<td>" . $value['turno'] . "</td>";
				echo "<td>" . $value['codigo_trabajador'] . "</td>";
				echo "<td>" . number_format($value['cantidad'],2) . "</td>";
				echo "<td>" . number_format($value['importe'],2). "</td>";
				$dia=trim($value['dia']);
				$turno=trim($value['turno']);
				$codigo_trabajador=trim($value['codigo_trabajador']);
				$iddel=$dia."*".$turno."*".$codigo_trabajador;
				echo "<td class='td_tabla_selecinar'><button value='$iddel' class='idelimnargnv'>Eliminar</button></td>";
				echo "</tr>";

				$i++;

			}

		    	?>

			</tbody>

			<tfoot>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'></td>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'></td>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;' alt="ssss">Total GNV </td>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'><?php echo number_format($sumacantidad, 2) ?> </td>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'><?php echo number_format($sumaimporte, 2) ?></td>
				<td bgcolor='#97C2E5'  align='center' style='color:#1C1D1C;font-size:11px;text-align: right;'</td>
			</tfoot>

        	</table>



        <?php
    }

 


  

}
