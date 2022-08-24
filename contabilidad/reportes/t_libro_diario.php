<?php
class LibroDiarioTemplate extends Template {
	function Inicio($estaciones, $fecha) { ?>
        <div id="template-LibroDiario">
        	<div class="container">
		        <h1 align="center">LIBRO DIARIO</h1>
		        <br>
	    	</div>

			<div class="columns">
	  			<div class="column">
		            <label class="label">Almacen</label>
	    			<span class="select" style="width: 100%">
					    <select id="cbo-almacen" style="width: 100%">
						    <option value="" selected>Todos</option>
						    <?php
								foreach($estaciones as $value)
									if ($_SESSION['almacen'] == $value['almacen'])
										echo "<option selected value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
									else 
										echo "<option value='" . $value['almacen'] . "'>" . $value['almacen'] . " - " . $value['nombre'] . "</option>";
							?>
					    </select>
				    </span>
	        	</div>

				<div class="column">
		        	<label class="label">Periodo</label>
		        	<input type="text" class="input" id="txt-periodo" name="periodo" autocomplete="on" placeholder="Ingresar periodo" value="<?php echo date("Y"); ?>" />
		       	</div>

				<div class="column">
		        	<label class="label">Mes</label>
		        	<input type="text" class="input" id="txt-mes" name="mes" autocomplete="on" placeholder="Ingresar mes" value="<?php echo date("m"); ?>" />
		       	</div>

				<div class="column">
		        	<label class="label">Cantidad Registros</label>
		        	<input type="text" class="input" id="txt-cantidadregistros" name="mes" autocomplete="on" placeholder="Ingresar cantidad de registros" value="30" />
		       	</div>
		    </div>

	        <br/>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
					  	<div class="column is-4">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-buscar"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
	            		</div>	

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-warning" id="btn-pdf"><i class="fa fa-file-pdf-o icon-size" aria-hidden="true"> <label class="label-btn-name">PDF</label></i></button>
	  					</div>

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-excel"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Excel</label></i></button>
	  					</div>
					</div>
				</div>				
			</div>

			<div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">			
					  	<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-ple"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Exportar PLE</label></i></button>
	  					</div>
					</div>
				</div>
			</div>
		</div>

		<!--Modal Message Delete-->
		<div class="modal MsgError">
			<div class="modal-content">
				<article class="message">
					<div class="message-header">
						<div class="message-header-text"></div>
						<button class="delete btn-close">
					</div>
					<div class="message-body">
					</div>
				</article>
			</div>
		</div>

		<div class="columns is-desktop" id="div-LibroDiario_CRUD">
<?php
	}

	function gridView($response) {
		$response = json_decode($response);
		if($response->status != "success"){ ?>
            <div class="column is-12 text-center">
            	<div class="notification is-<?php echo $response->status; ?>"><?php echo $response->message; ?></div>
		    </div>
		<?php
		}else{
		?>
	  		<div class="column is-12 table__wrapper StandardTable">
				<table class="table report_CRUD">
					<thead>
		                <th colspan="3" align="text-center">Correlativo de Asiento</th>
		                <th align="text-center">Fecha Ope.</th>
		                <th align="text-center">Glosa o Descripción de la Operación</th>
		                <th colspan="3" align="text-center">Referencia de la Operación</th>
		                <th colspan="2" align="text-center">Cuenta Contable Asociada</th>
		                <th colspan="2" align="text-center">Movimiento</th>
				    </thead>
					<thead>
		                <th align="text-center" style="width:4%;">M.</th>
		                <th align="text-center" style="width:4%;">S/D</th>
		                <th align="text-center" style="width:4%;">ASI</th>
		                <th align="text-center" style="width:6%;"></th>
		                <th align="text-center"></th>
		                <th align="text-center" style="width:4%;">Código de Libro Registro</th>
						<th align="text-center" style="width:4%;">Número Correla.</th>
						<th align="text-center">Número de Documento Sustentatorio</th>
						<th align="text-center">Código</th>
		                <th align="text-center">Denominación</th>
						<th align="text-center" style="width:10%;">Debe</th>
		                <th align="text-center" style="width:10%;">Haber</th>	
				    </thead>
					<tbody>
					<?php
						$i = 0;
						$contador_asiento = 0;
						foreach ($response->data->rows as $rows) {
							if ($act_entry_id != $rows->act_entry_id) {
								$i++;
								$color = ($i%2==0?" grid_detalle_par ":" grid_detalle_impar ");
								$contador_asiento = 0;	
							}
							$contador_asiento++;
														
							/**
							* Obtenemos Código de Libro o Registro (Campo "bookcode" de la tabla "act_entrytype")					 							 
							* 14: REGISTRO DE VENTAS E INGRESOS
							* 01: LIBRO CAJA Y BANCOS
							* 08: REGISTRO DE COMPRAS
							* 05: LIBRO DIARIO
							*/
							$bookcode = $rows->bookcode;	
							
							/**
							* Obtenemos Numero del Documento Sustentatorio
							*/
					?>
							<tr class="grid_detalle_impar <?php echo $color; ?>">
	            				<!-- <td align="center"><?php echo $response->data->page; ?></td> -->
								<td align="center"><?php echo $response->param->Fe_Mes; ?></td>
	            				<td align="center"><?php echo $rows->subbookcode; ?></td>
	            				<td align="center"><?php echo $rows->registerno; ?></td>
	            				<td align="left"><?php echo $rows->documentdate; ?></td>
	            				<td align="left"><?php echo $rows->description_detail; ?></td>
	            				<td align="center"><?php echo $bookcode; ?></td>
	            				<td align="center"><?php echo $contador_asiento; ?></td>
	            				<td align="center"><?php echo $rows->tableid . " " . $rows->regid;  ?></td>
	            				<td align="center"><?php echo $rows->acctcode; ?></td>
								<td align="left"><?php echo $rows->name;  ?></td>
								<td align="right"><?php echo $rows->amtdt; ?></td>
								<td align="right"><?php echo $rows->amtct; ?></td>
	            			</tr>
					<?php
							$act_entry_id = $rows->act_entry_id;
						}
					?>
					</tbody>
				</table>
				<!--Pagination-->
    			<script type="text/javascript" src="/sistemaweb/assets/js/paginador/paginador.js"></script>
	            <input type="hidden" id="pageActual" value="<?php echo $response->data->page ?>">
	            <input type="hidden" id="cantidadPage" value="<?php echo $response->data->total ?>"><!--Cantidad de Paginas -->
				<nav class="bulma pagination is-centered">
					<a class="bulma pagination-previous pagination-previousCONT_LD"><<</a>
					<a class="bulma pagination-next pagination-nextCONT_LD">>></a>
					<ul class="bulma pagination-list">
						<?php
						for ($i=1; $i <= $response->data->total; $i++) {
							if($i >= $response->data->page - $response->data->Pagelimit && $i <= $response->data->page + $response->data->Pagelimit) {
						?>
					    <li>
					    	<a href="#" class="bulma pagination-link pagination-linkCONT_LD <?php echo ($i == $response->data->page ? 'is-current' : '') ?>" data-page="<?php echo $i ?>">
					    		<?php echo $i ?>
					    	</a>
					    </li>
					    <?php
							}
						}
						?>
					</ul>
				</nav>
			</div>
		</div>

	<div class="columns is-desktop" id="div-excel">
<?php
		}
	}

	function gridViewExcel($response) {				
	}

	function gridViewPDF($response) {	
		$response = json_decode($response);
		
		require('/sistemaweb/contabilidad/include/mc_table_fpdf.php');

		$pdf = new PDF_MC_Table();

		$sTipoLetra = 'Courier';
		$pdf->AddPage();
		$pdf->SetFont($sTipoLetra, 'B', 15);		

		//HEADER
		$this->pdf_header($pdf, $sTipoLetra, $response);

		//BODY
		$arrHeaderTableDetail1 = array(
			array('text' => 'CORRELATIVO DEL ASIENTO', 'align' => 'C'),
			array('text' => 'FECHA OPE.', 'align' => 'C'),
			array('text' => $this->printText('GLOSA O DESCRIPCIÓN DE LA OPERACION'), 'align' => 'C'),
			array('text' => $this->printText('REFERENCIA DE LA OPERACIÓN'), 'align' => 'C'),
			array('text' => 'CUENTA CONTABLE ASOCIADA', 'align' => 'C'),
			array('text' => 'MOVIMIENTO', 'align' => 'C'),
		);
		$arrHeaderTableDetail2 = array(
			array('text' => 'M', 'align' => 'C'),
			array('text' => 'S/D', 'align' => 'C'),
			array('text' => 'ASI', 'align' => 'C'),
			array('text' => '', 'align' => 'C'),
			array('text' => '', 'align' => 'C'),
			array('text' => $this->printText('CÓDIGO DE LIBRO O REGISTRO'), 'align' => 'C'),
			array('text' => $this->printText('NÚMERO CORRELA.'), 'align' => 'C'),
			array('text' => $this->printText('NÚMERO DEL DOCUMENTO SUSTENTATORIO'), 'align' => 'C'),
			array('text' => $this->printText('CÓDIGO'), 'align' => 'C'),
			array('text' => $this->printText('DENOMINACIÓN'), 'align' => 'C'),
			array('text' => 'DEBE', 'align' => 'C'),
			array('text' => 'HABER', 'align' => 'C'),
		);
		$this->pdf_body($pdf, $sTipoLetra, $arrHeaderTableDetail1, $arrHeaderTableDetail2, $response);

		$pdf->Output("/sistemaweb/contabilidad/reportes/pdf/reporte_libro_diario.pdf", "F");
	}

	function pdf_header($pdf, $sTipoLetra, $response){	
		//1 FILA
		//IZQUIERDA
		$pdf->Multicell(0, 4, $this->printText(wordwrap("FORMATO 5.1: \"LIBRO DIARIO\"", 80, "\n")));

		//SETEAMOS FONT
		$pdf->SetFont($sTipoLetra, 'B', 9);

		//2 FILA
		//IZQUIERDA
		$meses = array("ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE");
		$periodo = $meses[intval($response->param->Fe_Mes)] . " " . $response->param->Fe_Periodo;
		$pdf->Cell(10, 10, 'PERIODO: ' . $periodo, 0, 0, 'L');
		$pdf->Ln(5);

		//3 FILA
		//IZQUIERDA	
		$pdf->Cell(10, 10, 'RUC: ' . $this->printText($response->data_company->ruc), 0, 0, 'L');
		$pdf->Ln(5);

		//4 FILA
		//IZQUIERDA
		$razsocial = $response->data_company->razsocial;
		$pdf->Cell(10, 10, 'RAZON SOCIAL: ' . $this->printText($response->data_company->razsocial), 0, 0, 'L');
		$pdf->Ln(5);		
	}

	function pdf_body($pdf, $sTipoLetra, $arrHeaderTableDetail1, $arrHeaderTableDetail2, $response){
		$pdf->Ln(5);

		//HEADER 1
		$w = array(21,12,35,45,40,40);
		$pdf->SetWidths($w);

		$pdf->SetFont($sTipoLetra, '', 8);

		$pdf->Row(
			array('border' => 1),
			$arrHeaderTableDetail1
		);

		//HEADER 2
		$w = array(7,7,7,12,35,15,15,15,15,25,20,20);
		$pdf->SetWidths($w);

		$pdf->SetFont($sTipoLetra, '', 6);

		$pdf->Row(
			array('border' => 1),
			$arrHeaderTableDetail2
		);

		//SETEAMOS FONT
		$pdf->SetFont($sTipoLetra, '', 7);
		//SETEAMOS ESTILOS
		// $pdf->Row()

		$pdf->Ln(5);

		//RECORREMOS ASIENTOS
		foreach ($response->data as $key => $row) {
			$pdf->Row(
				array('border' => 0),
				array(
					array('text' => $response->param->Fe_Mes, 'align' => 'C'),
					array('text' => $row->subbookcode, 'align' => 'C'),
					array('text' => $row->registerno, 'align' => 'C'),
					array('text' => $row->documentdate, 'align' => 'C'),
					array('text' => $row->description_detail, 'align' => 'C'),
					array('text' => $row->bookcode, 'align' => 'C'),
					array('text' => '', 'align' => 'C'),
					array('text' => $row->regid, 'align' => 'C'),
					array('text' => $row->acctcode, 'align' => 'C'),
					array('text' => $row->name, 'align' => 'C'),
					array('text' => $row->amtdt, 'align' => 'C'),
					array('text' => $row->amtct, 'align' => 'C'),
				)				
			);
		}
	}

	function printText($text) {
		return utf8_decode($text);
	}
}
