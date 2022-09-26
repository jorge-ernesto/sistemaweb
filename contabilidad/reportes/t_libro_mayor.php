<?php
date_default_timezone_set('America/Lima');
class LibroMayorTemplate extends Template {
	function Inicio($estaciones, $fecha) { ?>
        <div id="template-LibroMayor">
        	<div class="container">
		        <h1 align="center">LIBRO MAYOR</h1>
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
		        	<label class="label">Since Account</label>
		        	<input type="text" class="input" id="txt-sinceaccount" name="mes" autocomplete="on" placeholder="Ingresar cuenta contable" value="1" />
		       	</div>

				<div class="column">
		        	<label class="label">To Account</label>
		        	<input type="text" class="input" id="txt-toaccount" name="mes" autocomplete="on" placeholder="Ingresar cuenta contable" value="9" />
		       	</div>
		    </div>

	        <br/>

	        <div class="columns is-centered">
				<div class="column is-12">
            		<div class="columns is-mobile">
						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-warning" id="btn-pdf"><i class="fa fa-file-pdf-o icon-size" aria-hidden="true"> <label class="label-btn-name">PDF</label></i></button>
	  					</div>

						<div class="column is-4">
		            		<button style="width: 100%;" class="button is-success" id="btn-excel"><i class="fa fa-file-excel-o icon-size" aria-hidden="true"> <label class="label-btn-name">Excel</label></i></button>
	  					</div>

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

		<div class="columns is-desktop" id="div-LibroMayor_CRUD">
<?php
	}

	function gridViewPDF($response) {
		$response = json_decode($response);
		require('/sistemaweb/contabilidad/include/mc_table_fpdf.php');

		$pdf = new PDF_MC_Table();
		$pdf->DefinirParametrosHeader('LIBRO_MAYOR', $response);

		$sTipoLetra = 'Helvetica';
		$pdf->AddPage();

		//HEADER
		$this->pdf_header($pdf, $sTipoLetra, $response);

		//BODY	
		$this->pdf_body($pdf, $sTipoLetra, $response);

		$pdf->Output("/sistemaweb/contabilidad/reportes/pdf/reporte_libro_mayor.pdf", "F");
	}

	function pdf_header($pdf, $sTipoLetra, $response){
	}

	function pdf_body($pdf, $sTipoLetra, $response){
		//SETEAMOS FONT
		$pdf->SetFont($sTipoLetra, '', 6);

		//OBTENEMOS DATA
		$data = $response->data;
		$param = $response->param;

		//RECORREMOS ASIENTOS
		foreach ($data as $keyAccount => $entries) {
			//MOSTRAMOS CABECERA
			$porciones             = explode("*", $keyAccount);
			$acctcode              = $porciones[1];
			$act_account_id        = $porciones[2];
			$denominacion          = $this->getDenominacion($act_account_id);
			$pdf->Cell(181, 0, $acctcode." ".$denominacion, 0, 0, 'L');
			$pdf->Ln(3);

			//VARIABLES PARA TOTALIZAR
			$total_debe  = 0;
			$total_haber = 0;

			//MOSTRAMOS DETALLE
			$cantidad_array = count($entries->detalle);
			foreach ($entries->detalle as $key => $rows) {
				$total_debe  += $rows->amtdt;
				$total_haber += $rows->amtct;

				$pdf->Row(
					array('border' => 0),
					array(
						array('text' => $rows->documentdate, 'align' => 'C'),
						array('text' => $param->Fe_Mes, 'align' => 'C'),
						array('text' => $rows->subbookcode, 'align' => 'C'),
						array('text' => $rows->registerno, 'align' => 'C'),
						array('text' => $rows->description_detail, 'align' => 'L'),
						array('text' => $rows->amtdt, 'align' => 'R'),
						array('text' => $rows->amtct, 'align' => 'R'),
					)
				);

				if ($key == $cantidad_array-1) {
					$this->mostrarTotal($pdf, $total_debe, $total_haber);
				}
			}
		}
	}

	function mostrarTotal($pdf, $total_debe, $total_haber) {
		$pdf->Cell(181, 0, '___________________________', 0, 0, 'R');
		$pdf->Ln(1.3);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => '', 'align' => 'C'),
				array('text' => $total_debe, 'align' => 'R'),
				array('text' => $total_haber, 'align' => 'R'),
			)
		);
		$pdf->Ln(1.5);
	}

	/* FUNCIONES ADICIONALES */
	function printText($text) {
		return utf8_decode($text);
	}	

	function getDenominacion($act_account_id) {
		global $sqlca;
		
		//OBTENEMOS DENOMINACION
		$sql = "
			SELECT 
				name
			FROM 
				act_account 
			WHERE 
				act_account_id = '" . TRIM($act_account_id) . "' 
			LIMIT 1";
		
		if ($sqlca->query($sql) < 0) {
			return "-";
		}

		//OBTENEMOS DENOMINACION
		$a = $sqlca->fetchRow();
		$denominacion = $a['name'];		
		
		//VALIDAMOS LARGO DE LA DENOMINACION
		if (strlen($denominacion) >= 80) {
			$denominacion = substr($denominacion, 0, 80) . "...";
		}

		//RETORNAMOS DENOMINACIONES
		return $denominacion;
	}
}
