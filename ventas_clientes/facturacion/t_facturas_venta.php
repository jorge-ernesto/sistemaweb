<?php
class templateSalesInvoice {
	public function head($data) { ?>
<!DOCTYPE html>
<html lang="es" class="bulma">
	<head class="bulma">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo (isset($data['title']) ? $data['title'].' - ' : '') ?> Opensoft</title>
		<link rel="stylesheet" href="/sistemaweb/assets/css/fonts_awesome_icons/font-awesome.css">
	    <link rel="stylesheet" href="/sistemaweb/assets/css/bulma.css">
	    <link rel="stylesheet" href="/sistemaweb/assets/css/style.css">
	    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/init.js?ver=2.0"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/ventas_clientes/js/facturas_venta.js?ver=15.8"></script>
	</head>
	<style type="text/css">
		h2{font-size: 12.5px;}
	</style>
	<body class="bulma">
		<?php require '/sistemaweb/include/menu.php'; ?>
	</body>
	<?php
	}
	public function footer() { ?>
	<footer>
	</footer>
</html>
	<?php
	}
	public function index($arrDataHelper, $arrData) {
		$this->head(array('title' => 'Facturas de Venta - Buscar'));
	?>
	<section class="section">
		<div class="container">
            <h1 align="center">Facturas de Venta</h1>
            <br>
            <div class="columns is-centered">
                <div class="column">
            		<div class="columns is-mobile">
					  	<div class="column">
				            <label class="label">Almacen</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-almacen">
								<?php
								if ($arrDataHelper['arrAlmacenes']['sStatus'] == 'success') {
									echo '<option value="">Todos</option>';
									foreach ($arrDataHelper['arrAlmacenes']['arrData'] as $row)
										echo '<option value="' . trim($row['id']) . '">' . trim($row['name']) . '</option>';
								} else {
									echo '<option value="">Sin Valor</option>';
								}
								?>
								</select>
			    			</span>
			    		</div>
					</div>
				</div>

                <div class="column">
            		<div class="columns is-mobile">
					  	<div class="column">
				        	<label class="label">Fecha Inicio</label>
				        	<input type="text" class="input" id="txt-fe_inicial" value="<?php echo $arrDataHelper['dInicial']; ?>" />
				        </div>
					  	<div class="column">
				        	<label class="label">Fecha Final</label>
				        	<input type="text" class="input" id="txt-fe_final" value="<?php echo $arrDataHelper['dFinal']; ?>" />
				        </div>
				    </div>
			    </div>

                <div class="column">
            		<div class="columns is-mobile">
					  	<div class="column">
				            <label class="label">Tipo</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-tipo_documento" data-tipo="buscar">
								<?php
								if ($arrDataHelper['arrDocumentos']['sStatus'] == 'success') {
									echo '<option value="">Todos</option>';
									foreach ($arrDataHelper['arrDocumentos']['arrData'] as $row)
										echo '<option value=' . trim(substr($row['id'], 4, 2)) . '>' . trim($row['short_name']) . '</option>';
								} else {
									echo '<option value="">Sin Valor</option>';
								}
								?>
								</select>
			    			</span>
			    		</div>
						
					  	<div class="column">
				            <label class="label">Serie</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-serie_documento">
								<?php
								if ($arrDataHelper['arrDocumentos']['sStatus'] == 'success') {
									echo '<option value="">Todos</option>';
								} else {
									echo '<option value="">Sin valor/option>';
								}
								?>
								</select>
			    			</span>
			    		</div>
					</div>
				</div>

				<div class="column">
					<div class="columns is-mobile">
						<div class="column">
							<label class="label">Número</label>
							<input type="text" class="input input-number_letter" id="txt-filtro-numero_documento" value="" autocomplete="off" />
						</div>
					</div>
				</div>
			</div>

            <div class="columns is-centered">
                <div class="column is-5">
            		<div class="columns is-mobile">
					  	<div class="column">
				        	<label class="label">Cliente</label>
				        	<input type="hidden" class="input" id="hidden-filtro-cliente-id" autocomplete="off" value="" />
				        	<input type="text" class="input" id="txt-filtro-cliente-nombre" autocomplete="off" value="" placeholder="Ingresar código / nombre"  onkeyup="autocompleteBridge(2)"/>
			    		</div>
					</div>
				</div>
				
                <div class="column">
            		<div class="columns is-mobile">
					  	<div class="column">
				            <label class="label">Estado</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-estado">
									<option value="">Todos</option>
									<option value="0">Registrado</option>
									<option value="1">Completado</option>
									<option value="2">Anulado</option>
									<option value="3">Completado—Enviado</option>
									<option value="4">Completado—Error</option>
									<option value="5">Anulado—Enviado</option>
									<option value="6">Anulado—Error</option>
								</select>
							</span>
	            		</div>
	            	</div>
            	</div>

				<div class="column is-4">
            		<div class="columns is-mobile">
					  	<div class="column">
					        <label class="label">&nbsp;</label>
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-html-sales_invoice"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
						</div>

						<div class="column">
					        <label class="label">&nbsp;</label>
					  		<button style="width: 100%;" class="button is-primary btn-primary" id="btn-add-sale_invoice"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar</label></i></button>
						</div>
					</div><!-- ./ Row button -->
            	</div>
			</div>

			<br>
			<br>

            <div class="columns is-centered">
				<div class="div-sales_invoice">
					<?php $this->table_sales_invoice($arrData); ?>
				</div>
			</div>				
        </div>
        <!-- ./ Container -->
	</section>
	<?php
	} // ./ function index buscar

	public function table_sales_invoice($arrData) { ?>
	<?php //echo "<script>console.log('" . json_encode($arrData) . "')</script>"; ?>
	
	<section class="section">
		<div class="container">
			<?php
			if ( $arrData["sStatus"] != "success" ) { ?>
		        <div class="columns">
		            <div class="column is-12 text-center">
		            	<div class="notification is-<?php echo $arrData["sStatus"]; ?>"><?php echo $arrData["sMessage"]; ?></div>
				    </div>
				</div>
			<?php
			} else { ?>
				<div id="div-sales_invoice" class="table__wrapper StandardTable">
		            <table id="table-sales_invoice" class="table">
		            	<thead>
			                <tr>
			                	<th class="text-center">F. Emisión</th>
			                	<th class="text-center">Tipo</th>
			                	<th class="text-center">Serie</th>
			                	<th class="text-center">Número</th>
			                	<th class="text-center">Cliente</th>
			                	<th class="text-center">M</th>
			                	<th class="text-center">Total</th>
			                	<th class="text-center">Impuesto</th>
			                	<th class="text-center">F. Pago</th>
			                	<th class="text-center">Estado</th>
			                	<th class="text-center">Acciones</th>
			                	<th class="text-center">Descargó Stock</th>
			                	<th class="text-center">Despacho Pérdido</th>
			                	<th class="text-center">Liquidación</th>
			                	<th class="text-center">Ref. Tipo</th>
			                	<th class="text-center">Ref. Serie</th>
			                	<th class="text-center">Ref. Número</th>
			                	<th class="text-center">Ref. Fecha</th>
			                	<th class="text-center">Estado Doc. Ref.</th>
			                </tr>
		              	</thead>
		              	<tbody>
						<?php
							$iCountData = (int)$arrData["arrData"]->rows;
							if ( $iCountData > 0 ){
								$iCount = 0;
							    foreach ($arrData["arrData"]->rows as $rows) {
							    	$row = (object)$rows;

							    	$sCodigoImpuestoTributario = $row->no_codigo_impuesto;
							    	$sEstado = $row->nu_estado_documento_sunat;

									$sClassColorTd = ($iCount%2==0? "grid_detalle_par" : "grid_detalle_impar");

									$sNombreImpuestoTributario = 'Op. Gravadas';
							    	if ($sCodigoImpuestoTributario == 'S')
										$sNombreImpuestoTributario = 'Op. Exoneradas';
							    	else if ($sCodigoImpuestoTributario == 'T')
							    		$sNombreImpuestoTributario = 'Op. Gratuitas';
							    	else if ($sCodigoImpuestoTributario == 'U')
							    		$sNombreImpuestoTributario = 'Op. Gratuitas + Exoneradas';
							    	else if ($sCodigoImpuestoTributario == 'V')
							    		$sNombreImpuestoTributario = 'Op. Inafectas';
							    	else if ($sCodigoImpuestoTributario == 'W')
							    		$sNombreImpuestoTributario = 'Op. Gratuitas + Inafectas';

							    	if ($sEstado == '0') {
							    		$sEstado = 'Registrado';
							    		$sEstadoColor = 'primary';
							    	} else if ($sEstado == '1') {
										$sEstado = 'Completado';
							    		$sEstadoColor = 'info';
							    	} else if ($sEstado == '2') {
										$sEstado = 'Anulado';
							    		$sEstadoColor = 'warning';
							    	} else if ($sEstado == '3') {
										$sEstado = 'Completado—Enviado';
							    		$sEstadoColor = 'info';
							    	} else if ($sEstado == '4') {
										$sEstado = 'Completado—Error';
							    		$sEstadoColor = 'danger';
							    	} else if ($sEstado == '5') {
										$sEstado = 'Anulado—Enviado';
							    		$sEstadoColor = 'warning';
							    	} else if ($sEstado == '6') {
										$sEstado = 'Anulado—Error';
										$sEstadoColor = 'danger';
							    	} else {
							    		$sEstado = 'Registrado';
							    		$sEstadoColor = 'primary';
							    	}

                        			$sPrimerCaracterSerie = substr(trim($row->no_serie_documento), 0, 1);

                        			$sFormaPago = $row->no_forma_pago;
                        			if ( $row->no_anticipo == 'S' )
                        				$sFormaPago = $row->no_forma_pago . ' (ANTICIPO)';
									?>
									<tr class="<?php echo $sClassColorTd;?>">
										<td class="text-center"><?php echo $this->allTypeDate($row->fe_emision, "-", "fecha_ymd"); ?></td>
										<td class="text-left"><?php echo $row->no_tipo_documento; ?></td>
										<td class="text-left"><?php echo $row->no_serie_documento; ?></td>
										<td class="text-right"><?php echo $row->nu_numero_documento; ?></td>
										<td class="text-left"><?php echo $row->no_razsocial_breve_cliente; ?></td>
										<td class="text-center"><?php echo $row->no_signo_moneda; ?></td>
							        	<td class="text-right"><?php echo $row->ss_total; ?></td>
							        	<td class="text-left"><?php echo $sNombreImpuestoTributario; ?></td>
							        	<td class="text-right"><?php echo $sFormaPago; ?></td>
							        	<td class="text-center"><span class="tag is-<?php echo $sEstadoColor;?>"><?php echo $sEstado; ?></span></td>
							        	<td class="text-center">
									    	<div class="select">
												<select class="cbo-acciones">
													<option value="0" selected="selected">Seleccionar</option>
													<?php
													
													//echo "<script>console.log('******')</script>";
													//echo "<script>console.log('no_tipo_documento: " . json_encode($row->no_tipo_documento) . "')</script>";
													//echo "<script>console.log('no_serie_documento: " . json_encode($row->no_serie_documento) . "')</script>";
													//echo "<script>console.log('nu_numero_documento: " . json_encode($row->nu_numero_documento) . "')</script>";
													//echo "<script>console.log('sEstado: " . json_encode($sEstado) . "')</script>";
													//echo "<script>console.log('no_anulado: " . json_encode($row->no_anulado) . "')</script>";

													$nu_liquidacion = trim($row->nu_liquidacion);
													if (
														(strlen(trim($row->no_serie_documento)) == 4 && ($sEstado == 'Registrado' || $sEstado == 'Completado—Error')) ||
														(strlen(trim($row->no_serie_documento)) == 3 && $row->no_anulado != "S") ||
														(strlen(trim($row->no_serie_documento)) == 3 && $row->no_anulado == "S" && $sEstado == 'Registrado')
													) { ?>
													<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="editar" data-nu_tipo_forma_vales="1" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Editar</option>
													<?php
													  }
													$sAccionTextoAdicionalVales = '';
													if ( !empty($row->nu_liquidacion) && $row->no_anulado != "S" ) {
														$sAccionTextoAdicionalVales = '(Sin soltar vales)';
													}
													if (
														(strlen(trim($row->no_serie_documento)) == 4 && $sEstado == 'Completado—Enviado') ||
														strlen(trim($row->no_serie_documento)) == 3 && $row->no_anulado != "S"
													) { ?>
														<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="anular" data-nu_tipo_forma_vales="1" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Anular <?php echo $sAccionTextoAdicionalVales; ?></option>
														<?php
														if ( !empty($nu_liquidacion) ){ ?>
															<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="anular" data-nu_tipo_forma_vales="2" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Anular (Soltando Vales)</option>
														<?php
														} ?>
													<?php
													} ?>
													<?php
													if (
														(strlen(trim($row->no_serie_documento)) == 4 && ($sEstado == 'Registrado' || $sEstado == 'Completado—Error')) ||
														(strlen(trim($row->no_serie_documento)) == 3 && $row->no_anulado != "S") ||
														(strlen(trim($row->no_serie_documento)) == 3 && $row->no_anulado == "S" && $sEstado == 'Registrado')
													) { ?>
														<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="eliminar" data-nu_tipo_forma_vales="1" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Eliminar <?php echo $sAccionTextoAdicionalVales; ?></option>
														<?php
														if ( !empty($nu_liquidacion) && $row->nu_liquidacion != "0000000000" ){ ?>
															<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="eliminar" data-nu_tipo_forma_vales="2" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Eliminar (Soltando Vales)</option>
														<?php
														} ?>
													<?php
													} ?>
													<?php
														if (strlen(trim($row->no_serie_documento)) == 4) { ?>
														<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="representacion_interna_pdf_sunat" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Representación Interna</option>
													<?php
														}
														if (
															strlen(trim($row->no_serie_documento)) == 4 &&
															( $sEstado == 'Registrado' || $sEstado == 'Anulado' || $sEstado == 'Completado—Error' || $sEstado == 'Anulado—Error' )
														) { ?>
															<option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="enviar_sunat" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Enviar a SUNAT</option>
													<?php
														}
														//Boton extornar (Regla: Cualquier documento, no debe ser NC)
														// if ((strlen(trim($row->no_serie_documento)) == 4 || strlen(trim($row->no_serie_documento)) == 3) && $row->no_tipo_documento != "N/CRED.") { ?>	
														<!-- <option value="<?php echo $row->nu_estado_documento_sunat; ?>" data-action="extornar" data-nu_codigo_almacen="<?php echo $row->nu_codigo_almacen; ?>" data-nu_tipo_documento="<?php echo $row->nu_tipo_documento; ?>" data-no_serie_documento="<?php echo $row->no_serie_documento; ?>" data-nu_numero_documento="<?php echo $row->nu_numero_documento; ?>" data-fe_emision="<?php echo $row->fe_emision; ?>" data-nu_liquidacion="<?php echo $row->nu_liquidacion; ?>" data-nu_codigo_cliente="<?php echo $row->nu_codigo_cliente; ?>">Extornar</option> -->
													<?php															
														// }
													?>
												</select>
											</div>
							        	</td>
							        	<td class="text-center"><?php echo ($row->no_descargar_stock != null ? $row->no_descargar_stock : 'N'); ?></td>
							        	<td class="text-center"><?php echo ($row->no_despacho_perdido != null ? $row->no_despacho_perdido : ''); ?></td>
							        	<td class="text-center"><?php echo ($row->nu_liquidacion != null ? $row->nu_liquidacion : ''); ?></td>
							        	<td class="text-center"><?php echo ($row->no_tipo_documento_referencia != null ? $row->no_tipo_documento_referencia : ''); ?></td>
							        	<td class="text-center"><?php echo ($row->no_serie_documento_referencia != null ? $row->no_serie_documento_referencia : ''); ?></td>
							        	<td class="text-center"><?php echo ($row->nu_numero_documento_referencia != null ? $row->nu_numero_documento_referencia : ''); ?></td>
							        	<td class="text-center"><?php echo ($row->fe_emision_referencia != null ? $this->allTypeDate($row->fe_emision_referencia, "-", "fecha_ymd") : ''); ?></td>
							        	<td class="text-center"><?php echo $row->txt_mensaje_referencia_documento; ?></td>
							    	</tr>
							    <?php
							    	$iCount++;
								} // /. Foreach recorrido de data
							} else { ?>
						    <tr>
						    	<td colspan="25" align="center">No hay registros</td>
							</tr>
							<?php
							}// /. verificar si tenemos
						?>
						</tbody>
		            </table>

					<!-- Pagination -->
		            <input type="hidden" id="pageActual" value="<?php echo $arrData["arrData"]->page ?>">
		            <input type="hidden" id="cantidadPage" value="<?php echo $arrData["arrData"]->total ?>">
					<nav class="bulma pagination is-centered">
						<a class="bulma pagination-previous"><<</a>
						<a class="bulma pagination-next">>></a>
						<ul class="bulma pagination-list">
							<?php
							for ( $i=1; $i<=$arrData["arrData"]->total; $i++) { ?>
							    <li>
							    	<a href="#" class="bulma pagination-link <?php echo ($i == $arrData["arrData"]->page ? 'is-current' : '') ?>" data-page="<?php echo $i ?>">
							    		<?php echo $i ?>
							    	</a>
							    </li>
						    <?php
							} ?>
						</ul>
					</nav><!-- /. Pagination -->
					<br>
		        </div><!-- ./ Div mostrar registros de facturas de venta -->
    		<?php
			}
			?>
		</div><!-- ./ Container -->
		<br>
	</section>
	<?php
	}

	public function generate_printed_representation_pdf_sunat($arrData) {
		$arrCompany = $arrData["arrCompany"];
		$arrHeader = $arrData["arrHeader"];
		$arrDetail = $arrData["arrDetail"];
		$arrTaxCode = $arrData["arrTaxCode"];
		$arrMontos = $arrData["arrMontos"];
		$arrMontoLetras = $arrData["arrMontoLetras"];
		$arrPlates = $arrData["arrPlates"];

		require('/sistemaweb/include/mc_table_fpdf.php');

		$pdf = new PDF_MC_Table();

		$sTipoLetra = 'Courier';
		$pdf->AddPage();
		$pdf->SetFont($sTipoLetra, 'B', 10);

		// HEADER
		$this->pdf_header($pdf, $sTipoLetra, $arrCompany, $arrHeader, $arrPlates);

		// BODY - Se mostrará solo si el documento no esta anulado
		if ( $arrHeader["no_anulado"] != "S" ) {
			$arrHeaderTableDetail = array(
				array('text' => 'CODIGO', 'align' => 'L'),
				array('text' => 'DESCRIPCION', 'align' => 'L'),
				array('text' => 'UNIDAD', 'align' => 'L'),
				array('text' => 'CANTIDAD', 'align' => 'R'),
				array('text' => 'V. U.', 'align' => 'R'),
				array('text' => 'IMPORTE', 'align' => 'R'),
			);
			$this->pdf_body($pdf, $sTipoLetra, $arrHeaderTableDetail, $arrDetail, $arrTaxCode);
		}

		// FOOTER
		$this->pdf_footer($pdf, $arrCompany, $arrMontoLetras, $arrTaxCode, $arrHeader, $arrMontos);

		$pdf->Output();
	}

	public function pdf_header($pdf, $sTipoLetra, $arrCompany, $arrHeader, $arrPlates){
		// 1 FILA
		// IZQUIERDA
		$pdf->Multicell(0, 4, $this->printText(wordwrap($arrCompany["sEmpresaRazsocial"], 80, "\n")));

		// 2 FILA
		// IZQUIERDA
		$pdf->Cell(10, 10, 'RUC: ' . $arrCompany["iEmpresaRuc"], 0, 0, 'L');

		// DERECHA
		$pdf->Cell(180, 10, 'DOCUMENTO DE CONTROL INTERNO', 0, 0, 'R');

		// 3,4 FILA
		// IZQUIERDA
		$sDireccionEmpresa = $arrCompany["sEmpresaDireccion"] . "\n" . $arrCompany["sEmpresaDistrito"] . " - " . $arrCompany["sEmpresaProvincia"] . " - " . $arrCompany["sEmpresaDepartamento"];
		// DERECHA
		$sDatosDocumento = '  ' . $arrHeader["no_tipo_documento"] . ' ELECTRONICA' . "\n  " . $arrHeader["no_serie_documento"] . ' - 0' . $arrHeader["nu_numero_documento"];
		$pdf->Ln(7);//Salto de líneas
		$w = array(125,67);//total 192
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => $this->printText($sDireccionEmpresa), 'align' => 'L'),
				array('text' => $sDatosDocumento, 'align' => 'L'),
			)
		);

		$pdf->SetFont($sTipoLetra, '', 10);
		// 5,6,7 FILA
		// IZQUIERDA
	    // Mostrar el dirección del establecimiento, solo si tiene dirección diferente a la empresa
	    if (
	    	!empty($arrCompany["sEstablecimientoDireccion"]) &&
	    	!empty($arrCompany["sEstablecimientoDistrito"]) &&
	    	!empty($arrCompany["sEstablecimientoProvincia"]) &&
	    	!empty($arrCompany["sEstablecimientoDepartamento"])
		) {
			$pdf->Ln(2);// Salto de líneas
			$sDireccionEstablecimiento = $arrCompany["sEstablecimientoNombre"]  . "\n" . $arrCompany["sEstablecimientoDireccion"] . " " . $arrCompany["sEstablecimientoZona"] . " " . $arrCompany["sEstablecimientoDistrito"] . " - " . $arrCompany["sEstablecimientoProvincia"] . " - " . $arrCompany["sEstablecimientoDepartamento"];
		    $pdf->MultiCell(100, 5, $this->printText($sDireccionEstablecimiento));
		}

		// 8,9,10,11 FILA
		// IZQUIERDA
		$pdf->Ln(2);// Salto de líneas

		$pdf->Cell(10, 10, 'Fecha: ' . $arrHeader["fe_emision"], 0, 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell(10, 10, 'Moneda: ' . $arrHeader["no_nombre_moneda"], 0, 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell(10, 10, 'Forma de Pago: ' . $this->printText($arrHeader["no_nombre_forma_pago"]), 0, 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell(10, 10, 'F. Vencimiento: ' . $arrHeader["fe_vencimiento"], 0, 0, 'L');
		$pdf->Ln(5);

		// 12,13 FILA
		// IZQUIERDA - DOCUMENTO DE REFERENCIA SOLO PARA (NOTA DE CRÉDITO Y DÉBITO)
		if (
			!empty($arrHeader["txt_observaciones_referencia"]) &&
			!empty($arrHeader["nu_tipo_documento_referencia"]) &&
			!empty($arrHeader["no_serie_documento_referencia"]) &&
			!empty($arrHeader["nu_numero_documento_referencia"]) &&
			!empty($arrHeader["fe_emision_referencia"])
		) {
			$pdf->Ln(5);// Salto de líneas
			$sDatosDocumentoReferencia = $arrHeader["no_tipo_documento_referencia_sunat"] . " - " . $arrHeader["no_serie_documento_referencia"] . " - " . $arrHeader["nu_numero_documento_referencia"];
			$pdf->Cell(10, 10, 'Documento Referencia: ' . $sDatosDocumentoReferencia, 0, 0, 'L');
			$pdf->Ln(5);
            $arrFechaEmision = explode('-', $arrHeader["fe_emision_referencia"]);
            $dFechaEmision = $arrFechaEmision[2]."/".$arrFechaEmision[1]."/".$arrFechaEmision[0];
			$pdf->Cell(10, 10, 'Fecha Referencia: ' . $dFechaEmision, 0, 0, 'L');
		}

		// 12 FILA
		// IZQUIERDA
		if ( !empty($arrHeader["txt_observaciones_referencia"]) ) {
			$pdf->Ln(5);
			$pdf->Cell(10, 10, 'Observaciones: ' . $this->printText($arrHeader["txt_observaciones_referencia"]), 0, 0, 'L');
			$pdf->Ln(5);
		}

		// 12,13,14,15 FILA
		// IZQUIERDA
		$pdf->Ln(5);// Salto de líneas

		$pdf->Cell(10, 10, 'Tipo de Documento: ' . $arrHeader["sTipoDocumentoIdentidad"], 0, 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell(10, 10, $this->printText('Número de Documento: ') . $this->printText($arrHeader["nu_documento_identidad_cliente"]), 0, 0, 'L');
		$pdf->Ln(5);
		$pdf->Cell(10, 10, 'Nombre de Cliente: ' . $this->printText($arrHeader["no_razsocial_cliente"]), 0, 0, 'L');
		$pdf->Ln(5);

		if ( $arrHeader["nu_tipo_documento"] == "01" && !empty($arrPlates) ) {//SOLO SE INCLUYE LA PLACA CUANDO ES 01 = FACTURA
			foreach ($arrPlates as $key => $sPlaca)
				$arrPlatesPDF[] = $sPlaca;
			$pdf->Ln(5);
			$pdf->Multicell(0, 10, 'PLACA: ' . $this->printText(implode(',', $arrPlatesPDF)));
		}

		// Se mostrará solo sí el documento esta anulado
		if ( $arrHeader["no_anulado"] == "S" ) {
			// CENTRADO
			$pdf->Ln(10);
			$w = array(195, 25);
			$pdf->SetWidths($w);
			$pdf->Row(
				array('border' => 1),
				array(
					array('text' => "DOCUMENTO ANULADO", 'align' => 'C'),
				)
			);
			$pdf->Ln(5);
		}
	}

	public function pdf_body($pdf, $sTipoLetra, $arrHeaderTableDetail, $arrDetail, $arrTaxCode) {
		$pdf->Ln(5);

		$w = array(30,62,18,20,33,30);
		$pdf->SetWidths($w);

		$pdf->SetFont($sTipoLetra, 'B', 10);

		$pdf->Row(
			array('border' => 1),
			$arrHeaderTableDetail
		);

		//En la representacion impresa, no incluye IGV las OP. Gravadas, pero en FE si incluye IGV
		$pdf->SetFont($sTipoLetra, '', 10);
		foreach ($arrDetail as $row) {
			$fCostoUnitario = $row["ss_precio_venta_item"];
			foreach ($arrTaxCode as $row2) {
				$iCodigoImpuesto = $row2["iCodigoImpuesto"];
				if ( $iCodigoImpuesto == "10" ) {//10=Op. Gravadas
					$fCostoUnitario = round($row["ss_precio_venta_item"] / $row2["fImpuesto"], 4, PHP_ROUND_HALF_UP);
				}
			}

			if (
				$row["no_codigo_impuesto_item"] == "T" ||
				$row["no_codigo_impuesto_item"] == "U" ||
				$row["no_codigo_impuesto_item"] == "W"
			) {
				$fCostoUnitario = 0;
			}

			$pdf->Row(
				array('border' => 1),
				array(
					array('text' => $this->printText($row["nu_codigo_item"]), 'align' => 'L'),
					array('text' => $this->printText($row["no_nombre_item"]), 'align' => 'L'),
					array('text' => $this->printText($row["nu_codigo_unidad_medida_sunat"]), 'align' => 'L'),
					array('text' => $this->getFormatNumber(array('number' => (float)$row["qt_cantidad"], 'decimal' => 4)), 'align' => 'R'),
					array('text' => $this->getFormatNumber(array('number' => $fCostoUnitario, 'decimal' => 4)), 'align' => 'R'),
					array('text' => $this->getFormatNumber(array('number' => (float)$row["ss_subtotal"], 'decimal' => 2)), 'align' => 'R'),
				)
			);
		}
		$pdf->Ln();
	}

	public function pdf_footer($pdf, $arrCompany, $arrMontoLetras, $arrTaxCode, $arrHeader, $arrMontos){
		// DERECHA
		$w = array(168, 25);
		$pdf->SetWidths($w);
		foreach ($arrMontos as $row) {
			$pdf->Row(
				array('border' => 0),
				array(
					array('text' => 'Operaciones ' . $row["sDescripcionImpuesto"], 'align' => 'R'),
					array('text' => $row["fSubTotal"], 'align' => 'R'),
				)
			);
		}

		// DERECHA
		$w = array(168, 25);
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => $arrMontos[0]["sValorImpuesto"], 'align' => 'R'),
				array('text' => $arrHeader["ss_impuesto"], 'align' => 'R'),
			)
		);

		// DERECHA
		$w = array(168, 25);
		$pdf->SetWidths($w);
		$pdf->Row(
			array('border' => 0),
			array(
				array('text' => 'Importe Total', 'align' => 'R'),
				array('text' => $arrHeader["ss_total"], 'align' => 'R'),
			)
		);
		$pdf->Ln();//100

		//IZQUIERDA Y DERECHA
		$pdf->Ln(4);//Salto de líneas
		$w = array(190);
		$pdf->SetWidths($w);
		foreach ($arrMontoLetras as $row) { 
			$sValorLeyena = $row["sExtraValorLeyendaPDF"] . $row["sValorLeyena"];
			$pdf->Row(
				array('border' => 0),
				array(
					array('text' => $this->printText($sValorLeyena), 'align' => 'L'),
				)
			);
		}

		// CENTRADO
		$pdf->Ln(4);
		$pdf->Cell(186, 10, 'COPIA PARA CONTROL ADMINISTRATIVO', 0, 0, 'C');
		$pdf->Ln(5);
		//$pdf->Cell(182, 10, 'CONSULTE LA REPRESENTACION IMPRESA EN ' . $arrCompany["sEmpresaURL"], 0, 0, 'C');
		$pdf->Cell(182, 10, 'CONSULTE LA REPRESENTACION IMPRESA EN ' . $arrCompany["sEmpresaURL"], 0, 0, 'C', false, $arrCompany["sEmpresaURL"]);
		/*
		$pdf->Ln(5);
		$html = 'CONSULTE LA REPRESENTACION IMPRESA EN <a href="' . $arrCompany["sEmpresaURL"] . '" target="_blank">' . $arrCompany["sEmpresaURL"] . '</a>';
		$pdf->WriteHTML($html);
		*/
		$pdf->Ln(5);
		$pdf->Cell(188, 10, 'AUTORIZADO MEDIANTE R.I. NRO. ' . $arrCompany["sEmpresaAutorizacion"], 0, 0, 'C');
	}

	public function getFormatNumber($data) {
		return number_format($data['number'], $data['decimal'], '.', ',');
	}

	public function printText($text) {
		return utf8_decode($text);
	}

	public function page_add_sales_invoice($arrDataHelper, $sTitle, $arrDataEdit) {
		$this->head(array('title' => $sTitle . ' Factura de Venta'));
		$sinSeleccionar = ($sTitle=='Agregar' ? true : false);
		$is_disabled = ($sTitle=='Agregar' ? '' : 'disabled');
	?>
	<section class="section">
		<div id="div-add-sales_invoice" class="container">
			<input type="hidden" id="hidden-tipo_accion" value="<?php echo $sTitle; ?>">
			<input type="hidden" id="hidden-numero_liquidacion" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_liquidacion"]); ?>">
            <h1 align="center"><?php echo $sTitle; ?> Factura de Venta</h1>
            <br>

            <!-- HIDDEN -->
			<?php
			if ($arrDataHelper['fImpuesto']['sStatus'] == 'success') { ?>
            <input type="hidden" class="input" id="txt-add-igv" value="<?php echo $arrDataHelper['fImpuesto']['arrData'];?>" autocomplete="off" disabled="" />
            <?php 
        	} else { ?>
			<p class="help is-danger"><?php echo $arrDataHelper['fImpuesto']['sMessage']; ?></p>
        	<?php
        	} ?>

			<div class="columns is-centered">
            	<div class="column is-7">
					<article class="message is-primary">
						<div class="message-header">
							<div class="column">
								<label class="label color-white"><i class="fa fa-book icon-size" aria-hidden="true"> Datos de Documento</i></label>
							</div>
						</div>

						<div class="message-body">
				            <div class="columns">
				                <div class="column is-12">
									<label class="label">Almacén</label>
									<input type="text" class="input" id="txt-add-almacen" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_codigo_almacen"] . " - " . $arrDataEdit->arrData[0]["no_nombre_almacen"]); ?>" autocomplete="off" disabled="" />
				                </div>
				            </div>
				            <div class="columns">
				                <div class="column is-7">
				            		<div class="columns is-mobile">
									  	<div class="column form-group">
								        	<label class="label">F. Emisión</label>
								        	<input type="text" class="input" id="txt-fe_emision" value="<?php echo ($sTitle=='Agregar' ? $arrDataHelper['dFinal']: $arrDataEdit->arrData[0]["fe_emision"]); ?>" <?php echo $is_disabled; ?>/>
						        		</div>
										
									  	<div class="column form-group">
								            <label class="label">Tipo</label>
							    			<span class="select">
												<?php
												if ($arrDataHelper['arrDocumentos']['sStatus'] == 'success') {
													$arrData = $arrDataHelper['arrDocumentos']['arrData'];
													$value_selected_bd = ($sTitle=='Agregar' ? null : $arrDataEdit->arrData[0]["nu_tipo_documento"]);
													$use_substr = true;
													echo $this->Select('cbo-filtro-tipo_documento', 'id', 'short_name', $arrData, $value_selected_bd, true, 'tipo', 'add', $use_substr, $is_disabled);
												} else {
													echo '<option value="">Sin Valor</option>';
												}
												?>
							    			</span>
								            <p class="help is-danger"></p>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-7 -->

				                <div class="column">
				            		<div class="columns is-mobile">
									  	<div class="column form-group">
								            <label class="label">Serie</label>
							    			<span class="select">
												<select class="is-select" id="cbo-filtro-serie_documento" <?php echo $is_disabled; ?>>
							    				<?php
							    				if ( $sTitle=='Agregar' ) {
							    				?>
													<option value="">Seleccionar</option>
												<?php
							    				} else {
							    					echo '<option value="' . $arrDataEdit->arrData[0]["no_serie_documento"] . '" data-ialmacen="' . $arrDataEdit->arrData[0]["nu_codigo_almacen"] . '">' . $arrDataEdit->arrData[0]["no_serie_documento"] . '</option>';
							    				}
							    				?>
												</select>
							    			</span>
								            <p class="help is-danger"></p>
							    		</div>

										<div class="column form-group">
								        	<label class="label">Número</label>
								        	<input type="text" class="input" id="txt-add-numero_documento" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_numero_documento"]); ?>" autocomplete="off" disabled="" />
								            <p class="help is-danger"></p>
								    	</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-default -->
							</div><!-- ./ Desktop -->

				            <div class="columns">
				                <div class="column is-6">
				            		<div class="columns is-mobile">
									  	<div class="column form-group">
								            <label class="label">Forma Pago</label>
							    			<span class="select">
												<?php
												if ($arrDataHelper['arrFormaPago']['sStatus'] == 'success') {
													$arrData = $arrDataHelper['arrFormaPago']['arrData'];
													$value_selected_bd = ($sTitle=='Agregar' ? null : $arrDataEdit->arrData[0]["nu_tipo_pago"]);
													$use_substr = true;
													//echo $this->Select('cbo-add-forma_pago', 'id', 'name', $arrData, $value_selected_bd, false, null, null, $use_substr, $is_disabled);
													$status = '';
													if ( $arrDataEdit->arrData[0]["nu_estado_documento_sunat"] == "1" || $arrDataEdit->arrData[0]["nu_estado_documento_sunat"] == "3" )
														$status = $is_disabled;
													echo $this->Select('cbo-add-forma_pago', 'id', 'name', $arrData, $value_selected_bd, false, null, null, $use_substr, $status);
												} else {
													echo '<option value="">Sin Valor</option>';
												}
												?>
							    			</span>
							    			<p class="help is-danger"></p>
							    		</div>
										<div class="column">
								            <label class="label">Anticipado</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-anticipado" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_anticipo"] == 'N' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_anticipo"] == 'S' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    		</div>
							    	</div><!-- ./ Mobile -->
								</div><!-- ./ is-1 -->
							</div>
				            <div class="columns <?php echo (($sTitle=='Agregar' || trim($arrDataEdit->arrData[0]["nu_tipo_pago"]) != '06') ? 'div-fecha_credito' : ''); ?>">
				                <div class="column is-4">
				            		<div class="columns is-mobile">
									  	<div class="column is-5 form-group">
								        	<label class="label">Días pago</label>
							    			<span class="select">
												<?php
												if ($arrDataHelper['arrDiasPago']['sStatus'] == 'success') {
													$arrData = $arrDataHelper['arrDiasPago']['arrData'];
													$value_selected_bd = ($sTitle=='Agregar' ? null : $arrDataEdit->arrData[0]["nu_codigo_dias_vencimiento"]);
													$use_substr = false;
													echo $this->Select('cbo-add-dias_credito', 'id', 'name', $arrData, $value_selected_bd, true, null, null, $use_substr, $is_disabled);
												} else {
													echo '<option value="">Sin Valor</option>';
												}
												?>
							    			</span>
								        </div>
								    </div>
								</div>

				                <div class="column is-8">
				            		<div class="columns is-mobile">
									  	<div class="column is-5">
								        	<label class="label">F. Vencimiento</label>
								        	<input type="text" class="input" id="txt-fe_vencimiento" value="<?php echo ($sTitle=='Agregar' ? $arrDataHelper['dFinal'] : $arrDataEdit->arrData[0]["fe_vencimiento"]); ?>" />
								        </div>

									  	<div class="column is-2 <?php echo (($sTitle=='Agregar' || trim($arrDataEdit->arrData[0]["nu_tipo_pago"]) != '06') ? 'div-fecha_credito' : ''); ?>">
								            <label class="label">Crédito</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-credito" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_credito"] == 'N' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_credito"] == 'S' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    		</div>
								    </div><!-- ./ Row Anticipado, forma pago y F. Vencimiento -->
							    </div><!-- ./ is-6 -->
							</div><!-- ./ Desktop -->

				            <div class="columns">
				                <div class="column is-5">
								    <div class="columns is-mobile">
									  	<div class="column">
								            <label class="label">Moneda</label>
							    			<span class="select">
												<?php
												if ($arrDataHelper['arrMonedas']['sStatus'] == 'success') {
													$arrData = $arrDataHelper['arrMonedas']['arrData'];
													$value_selected_bd = ($sTitle=='Agregar' ? null : $arrDataEdit->arrData[0]["nu_codigo_moneda"]);
													$use_substr = true;
													echo $this->Select('cbo-add-moneda', 'id', 'name', $arrData, $value_selected_bd, false, null, null, $use_substr, $is_disabled);
												} else {
													echo '<option value="">Sin Valor</option>';
												}
												?>
							    			</span>
							    		</div>
							    	</div>
							    </div>

				                <div class="column is-7">
								    <div class="columns is-mobile">
									  	<div class="column is-5">
								        	<label class="label">T. Cambio</label>
								        	<?php
											if ($arrDataHelper['fTipoCambioVenta']['sStatus'] == 'success') { ?>
								        	<input type="text" class="input input-decimal" id="txt-tipo_cambio" value="<?php echo ($sTitle=='Agregar' ? $arrDataHelper['fTipoCambioVenta']['arrData']["ss_tc_venta"] : $arrDataEdit->arrData[0]["ss_tipo_cambio"]); ?>" autocomplete="off" <?php echo $is_disabled; ?>/>
								        	<?php
											} else { ?>
											<input type="text" class="input input-decimal" id="txt-tipo_cambio" value="<?php echo ($sTitle=='Agregar' ? '0' : $arrDataEdit->arrData[0]["ss_tipo_cambio"]); ?>" autocomplete="off" <?php echo $is_disabled; ?>/>
											<?php
											} ?>
								        </div>

									  	<div class="column is-7">
								            <label class="label">Descargar Stock</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-descargar_stock" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_descargar_stock"] == 'N' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_descargar_stock"] == 'S' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    		</div>
								    </div><!-- ./ Row moneda -->
							    </div><!-- ./ is-6 -->
							</div><!-- ./ Desktop -->

					    </div><!-- ./ Panel Body -->
					</article><!-- ./ Article Principal -->
	            </div><!-- ./ is-7 Datos de Documentos -->

            	<div class="column is-5">
					<article class="message is-primary">
						<div class="message-header">
							<div class="column">
								<label class="label color-white"><i class="fa fa-user icon-size" aria-hidden="true"> Datos de Cliente</i></label>
							</div>
						</div>

						<div class="message-body">
				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
									  	<div class="column form-group">
								        	<label class="label">Cliente</label>
								        	<input type="hidden" class="input" id="hidden-filtro-cliente-id" autocomplete="off" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_codigo_cliente"]); ?>" />
								        	<input type="hidden" class="input" id="hidden-filtro-cliente-ruc" autocomplete="off" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_ruc_cliente"]); ?>" />
								        	<input type="hidden" class="input" id="hidden-filtro-cliente-direccion" autocomplete="off" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["txt_direccion_cliente"]); ?>" />
								        	<input type="hidden" class="input" id="hidden-filtro-cliente-anticipo" autocomplete="off" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["no_anticipo_cliente"]); ?>" />
								        	<input type="text" class="input" id="txt-filtro-cliente-nombre" autocomplete="off" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["no_nombre_cliente"]); ?>" placeholder="Ingresar código / nombre"  onkeyup="autocompleteBridge(0)" <?php echo $is_disabled; ?>/>
								            <p class="help is-danger"></p>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ Desktop -->
							</div><!-- ./ Desktop -->

				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
									  	<div class="column is-3 form-group">
								        	<label class="label">L. Precio</label>
								        	<input type="text" class="input input-decimal" id="txt-lista_precio" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["nu_codigo_precio_cliente"]); ?>" autocomplete="off" disabled="" />
								            <p class="help is-danger"></p>
								        </div>
								        <div class="column is-9">
								        	<label class="label">.</label>
								        	<label class="label" id="label-lista_precio"><?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["no_nombre_precio_cliente"]); ?></label>
								        </div>
							    	</div><!-- ./ Is Mobile -->
							    </div><!-- ./ 12 -->
							</div><!-- ./ Desktop -->

				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
				            			<?php
				            			$class_exonerado = 'display: none;';//Exonerada
				            			if ($arrDataHelper['iTipoImpuesto']['sStatus'] == 'success') {
				            				if ($arrDataHelper['iTipoImpuesto']['arrData'][0]['par_valor'] == '1')
				            					$class_exonerado = '';
				            			}
				            			?>
							  			<div class="column form-group" style="<?php echo $class_exonerado; ?>">
								            <label class="label">¿Exonerado?</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-exonerado" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] != 'S' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] == 'S' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    			<p class="help is-danger"></p>
							    		</div>
							  			<div class="column">
								            <label class="label">¿Transf. Gratuita?</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-transferencia_gratuita" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] != 'T' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] == 'T' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    		</div>
							  			<div class="column">
								            <label class="label">¿Despacho Pérdido?</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-despacho_perdido" <?php echo $is_disabled; ?>>
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_despacho_perdido"] == 'N' ? 'selected="selected"' : '') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : ($arrDataEdit->arrData[0]["no_despacho_perdido"] == 'S' ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ Is-12 -->
							</div><!-- ./ Desktop -->
					    </div><!-- ./ Panel Body -->
					</article><!-- ./ Article Principal -->
	            </div><!-- ./ is-7 Datos del Cliente -->
			</div><!-- ./ Row Datos de Documento y Cliente -->

			<?php
			// Verificar si se activará datos complementarios
			$bActivarComplementos = false;
			$iTipoDocumentoReferencia='';
			$sSerieDocumentoReferencia='';
			$iNumeroDocumentoReferencia='';
			$bActivarDocumentoReferencia = false;
			
			$bActivarDetraccion = false;
			$iNumeroCuentaDetraccion='';
			$fImporteDetraccion='';
			$iPorcentajeDetraccion='';
			$iCodigoBienesServicioDetraccion='';
			if (
				!empty($arrDataEdit->arrData[0]["txt_observaciones"]) ||
				!empty($arrDataEdit->arrData[0]["numero_serie_tipo_documento_referencia"]) ||
				!empty($arrDataEdit->arrData[0]["fe_emision_referencia"]) ||
				!empty($arrDataEdit->arrData[0]["numcuenta_importe_porcentaje_codigoimpuestoservicio_detraccion"])
			) {
				$bActivarComplementos = true;
				if (!empty($arrDataEdit->arrData[0]["numero_serie_tipo_documento_referencia"])){
					$bActivarDocumentoReferencia = true;
					$arrDocumentoReferencia = explode("*", $arrDataEdit->arrData[0]["numero_serie_tipo_documento_referencia"]);
					$iNumeroDocumentoReferencia=$arrDocumentoReferencia[0];
					$sSerieDocumentoReferencia=$arrDocumentoReferencia[1];
					$iTipoDocumentoReferencia=$arrDocumentoReferencia[2];
				}
				if (!empty($arrDataEdit->arrData[0]["numcuenta_importe_porcentaje_codigoimpuestoservicio_detraccion"])){
					$bActivarDetraccion = true;

					$arrDetraccion = explode("*", $arrDataEdit->arrData[0]["numcuenta_importe_porcentaje_codigoimpuestoservicio_detraccion"]);
					$iNumeroCuentaDetraccion=$arrDetraccion[0];
					$fImporteDetraccion=$arrDetraccion[1];
					$iPorcentajeDetraccion=$arrDetraccion[2];
					$iCodigoBienesServicioDetraccion=$arrDetraccion[3];
				}
			}// ./ Verificar si se activará datos complementarios

			$sStyleDisabledEstado = '';
			if ( $sTitle !='Agregar' ) {
				$sStyleDisabledEstado = '';
    			if ( $arrDataEdit->arrData[0]["nu_estado_documento_sunat"] != '0' ) {// 0 = Registrado
    				$sStyleDisabledEstado = 'disabled';
    			}
    		}
			?>
            <div class="columns is-centered">
            	<div class="column is-12">
					<article class="message is-primary">
						<div class="message-header">
							<div class="column">
								<label class="label color-white"><i class="fa fa-comment-o icon-size" aria-hidden="true"> Complementarios <input type="checkbox" id="chk-activar_complemento" onclick="activeComplementary()" <?php echo ($bActivarComplementos ? 'checked' : ''); echo $sStyleDisabledEstado; ?>></i></label>
							</div>
						</div>

						<div class="message-body <?php echo ($bActivarComplementos ? '' : 'div-complementarios'); ?>">
				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
									  	<div class="column is-12 form-group">
								        	<label class="label">Observaciones</label>
								        	<input type="text" class="input" id="txt-observaciones" value="<?php echo ($sTitle=='Agregar' ? '' : $arrDataEdit->arrData[0]["txt_observaciones"]); ?>" autocomplete="off" maxlength="200"/>
								            <p class="help is-danger"></p>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-12 -->
							</div><!-- ./ Desktop Observaciones -->

				            <div class="columns <?php echo ($bActivarDocumentoReferencia ? '' : 'div-referencia_documento'); ?>">
				                <div class="column is-4">
				            		<div class="columns is-mobile">
									  	<div class="column is-6 form-group">
								        	<label class="label">F. Emisión (Modifica)</label>
								        	<input type="text" class="input date-picker-invoice" id="txt-referencia-fe_emision" value="<?php echo ($sTitle=='Agregar' ? $arrDataHelper['dFinal'] : $this->allTypeDate($arrDataEdit->arrData[0]['fe_emision_referencia'], "-", "fecha_ymd")); ?>" />
								        </div>

									  	<div class="column is-5 form-group">
								            <label class="label">Tipo (Modifica)</label>
							    			<span class="select">
												<select class="is-select" id="cbo-filtro-referencia-tipo_documento" data-tipo="add">
													<option value="">Seleccionar</option>
													<option value="35" <?php echo ( $sTitle == 'Agregar' ) ? '' : ((($bActivarDocumentoReferencia) && $iTipoDocumentoReferencia == "35")? 'selected="selected"' : '') ;?>>BOLETA DE VENTA</option>
													<option value="10" <?php echo ( $sTitle == 'Agregar' ) ? '' : ((($bActivarDocumentoReferencia) && $iTipoDocumentoReferencia == "10")? 'selected="selected"' : '') ;?>>FACTURA</option>
												</select>
							    			</span>
								            <p class="help is-danger"></p>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-4 -->

				                <div class="column is-3">
				            		<div class="columns is-mobile">
									  	<div class="column is-6 form-group">
								            <label class="label">Serie (Modifica)</label>
								            <input type="text" class="input input-number_letter" id="cbo-filtro-referencia-serie_documento" value="<?php echo ($sTitle=='Agregar' ? '' : $sSerieDocumentoReferencia); ?>" autocomplete="on" maxlength="4" />
								            <p class="help is-danger"></p>
							    		</div>

										<div class="column is-6 form-group">
								        	<label class="label" title="Playa: 8 caracteres y Oficina: 7 caracteres">Número (Modifica)</label>
								        	<input type="text" class="input input-number" id="txt-add-referencia-numero_documento" value="<?php echo ($sTitle=='Agregar' ? '' : $iNumeroDocumentoReferencia); ?>" autocomplete="off" title="Playa: 8 caracteres y Oficina: 7 caracteres" />
								            <p class="help is-danger"></p>
								    	</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-3 -->

				                <div class="column is-5">
				            		<div class="columns is-mobile">
										<div class="column form-group">
								            <label class="label">.</label>
									  		<button style="width: 100%;" class="button is-info btn-info" id="btn-search-reference-sales_invoice"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Verificar documento</label></i></button>
										</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-3 -->
							</div><!-- ./ Desktop Nota de Crédito / Débito-->

				            <div class="columns">
				                <div class="column is-12">
				                	<div class="notification div-message-sale_invoice_referencia"></div>
							    </div><!-- ./ is-7 -->
							</div><!-- ./ Desktop Mensaje de verificacion del documento de referencia -->

				            <div class="columns">
				                <div class="column is-1">
				            		<div class="columns is-mobile">
									  	<div class="column is-1 form-group">
								        	<label class="label">¿Detracción?</label>
							    			<span class="select">
												<select class="is-select" id="cbo-add-detraccion">
													<option value="N" <?php echo ( $sTitle == 'Agregar' ) ? '' : (($bActivarDetraccion) ? '' : 'selected="selected"') ;?>>No</option>
													<option value="S" <?php echo ( $sTitle == 'Agregar' ) ? '' : (($bActivarDetraccion) ? 'selected="selected"' : '') ;?>>Si</option>
												</select>
							    			</span>
								    	</div><!-- ./ is-1 -->
								    </div><!-- ./ Mobile -->
							    </div><!-- ./ is-1 -->

				                <div class="column is-3 <?php echo ($bActivarDetraccion ? '' : 'div-detraccion'); ?>">
				            		<div class="columns is-mobile">
									  	<div class="column is-12 form-group">
								        	<label class="label">Nro. Cuenta</label>
								        	<input type="text" class="input input-number_guion" id="txt-detraccion-nu_cuenta" value="<?php echo ($sTitle=='Agregar' ? '' : $iNumeroCuentaDetraccion); ?>" autocomplete="off" maxlength="200" />
								        	<p class="help is-danger"></p>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-3 -->

				                <div class="column is-5 <?php echo ($bActivarDetraccion ? '' : 'div-detraccion'); ?>">
				            		<div class="columns is-mobile">
									  	<div class="column is-4 form-group">
								        	<label class="label">Importe</label>
								        	<input type="text" class="input input-decimal" id="txt-detraccion-importe" value="<?php echo ($sTitle=='Agregar' ? '' : $fImporteDetraccion); ?>" autocomplete="off" maxlength="10" />
								        	<p class="help is-danger"></p>
								        </div>

									  	<div class="column is-4 form-group">
								        	<label class="label">Porcentaje %</label>
								        	<input type="text" class="input input-number" id="txt-detraccion-porcentaje" value="<?php echo ($sTitle=='Agregar' ? '' : $iPorcentajeDetraccion); ?>" autocomplete="off" maxlength="3" />
								        	<p class="help is-danger"></p>
								        </div>

									  	<div class="column is-4 form-group">
								        	<label class="label">Cód. Bienes y Servicios</label>
								        	<input type="text" class="input input-number" id="txt-detraccion-codigo_bienes_servicios" value="<?php echo ($sTitle=='Agregar' ? '' : $iCodigoBienesServicioDetraccion); ?>" autocomplete="off" maxlength="3" />
								        	<p class="help is-danger"></p>
								        </div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ Desktop is-5 -->
							</div><!-- ./ Desktop Detraccion-->
							<?php
							if ( $sTitle == 'Editar' ) { ?>
				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
									  	<div class="column is-12 form-group">
											<button type="button" style="width: 100%;" <?php echo $sStyleDisabledEstado; ?> class="button is-info btn-info is-large" id="btn-save-sale_invoice_complementary"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="label-btn-name">Modificar</label></i></button>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-12 -->
							</div><!-- ./ Desktop Botón modificar complementarios -->
							<?php
							} ?>
						</div><!-- ./ Body -->
					</article>
				</div>
			</div><!-- ./ Desktop Row Complementarios -->

            <div class="columns is-centered">
            	<div class="column is-12">
					<article class="message is-primary">
						<div class="message-header">
							<div class="column">
								<label class="label color-white"><i class="fa fa-shopping-cart icon-size" aria-hidden="true"> Detalle</i></label>
							</div>
						</div>

						<div class="message-body">
							<?php 
							//if ( $sTitle == 'Agregar' ) { ?>
				            <div class="columns">
				                <div class="column is-3">
				            		<div class="columns is-mobile">
									  	<div class="column is-12 form-group">
								        	<label class="label">Artículo</label>
								        	<input type="hidden" class="input" id="hidden-codigo_impuesto_item" autocomplete="off" value="" />
								        	<input type="hidden" class="input" id="hidden-codigo_tipo_plu" autocomplete="off" value="" />
								        	<input type="hidden" class="input" id="hidden-add-item-id" autocomplete="off" value="" />
								        	<input type="text" class="input" id="txt-add-item-nombre" autocomplete="off" value="" placeholder="Ingresar código / nombre" onkeyup="autocompleteBridge(1)"/>
								        	<p class="help is-danger"></p>
							    		</div>
							    	</div>
							    </div>

				                <div class="column is-3">
				            		<div class="columns is-mobile">
									  	<div class="column is-4 form-group">
								        	<label class="label">Cantidad</label>
								        	<input type="text" class="input input-decimal" id="txt-cantidad" value="" autocomplete="off" onkeyup="calcAmounts('txt-cantidad', '*', 'txt-precio_venta', 'txt-total', 'txt-add-igv');" />
								        	<p class="help is-danger"></p>
								        </div>

									  	<div class="column is-4 form-group">
								        	<label class="label">P. Venta (IGV)</label>
								        	<input type="text" class="input input-decimal" id="txt-precio_venta" value="" autocomplete="off" onkeyup="calcAmounts('txt-precio_venta', '*', 'txt-cantidad', 'txt-total', 'txt-add-igv');" />
								        	<p class="help is-danger"></p>
								        </div>

									  	<div class="column is-4">
								        	<label class="label">SubTotal</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-subtotal" value="" autocomplete="off" disabled="" />
								        	<input type="text" class="input input-decimal" id="txt-subtotal" value="" autocomplete="off" disabled="" />
								        </div>
								    </div>
								</div>

				                <div class="column is-4">
				            		<div class="columns is-mobile">
									  	<div class="column is-3">
								        	<label class="label">IGV</label>
								        	<input type="text" class="input input-decimal" id="txt-igv" value="" autocomplete="off" disabled="" />
								        </div>

									  	<div class="column is-4">
								        	<label class="label">Dscto. (Sin IGV)</label>
								        	<input type="text" class="input input-decimal" id="txt-descuento" value="" autocomplete="off" />
								        	<!--<input type="text" class="input input-decimal" id="txt-descuento" value="" autocomplete="off" onkeyup="calcAmounts('hidden-subtotal', '-', 'txt-descuento', 'txt-subtotal', 'txt-add-igv');"/>-->
								        </div>

									  	<div class="column is-5 form-group">
								        	<label class="label">Total</label>
								        	<input type="text" class="input input-decimal" id="txt-total" value="" autocomplete="off" onkeyup="calcAmounts('txt-total', '/', 'txt-cantidad', 'txt-precio_venta', 'txt-add-igv');" disabled="" />
								        	<p class="help is-danger"></p>
								        </div>
								    </div>
								</div>

				                <div class="column is-2">
				            		<div class="columns is-mobile">
							    		<div class="column is-12">
								        	<label class="label">.</label>
											<button style="width: 100%;" class="button is-primary btn-primary" id="btn-add-product_detail"><i class="fa fa-plus-circle icon-size" aria-hidden="true"> <label class="label-btn-name">Agregar item</label></i></button>
							    		</div>
							    	</div><!-- ./ Mobile -->
							    </div><!-- ./ is-2 -->
							</div><!-- ./ Desktop Agregar items -->
							<?php
							//} // ./ Agregar item ?>
				            <div class="columns">
				                <div class="column is-12">
									<div id="<?php echo ($sTitle=='Agregar' ? 'div-sales_invoice_detail' : ''); ?>" class="table__wrapper StandardTable">
							            <table id="table-sales_invoice_detail" class="table">
							            	<thead>
								                <tr>
								                	<th class="text-center">Arículo</th>
								                	<th class="text-center">Cantidad</th>
								                	<th class="text-center">P. Venta (IGV)</th>
								                	<th class="text-center">SubTotal</th>
								                	<th class="text-center">IGV</th>
								                	<th class="text-center">Dscto. (S/IGV)</th>
								                	<th class="text-center">Total</th>
								               	</tr>
								            </thead>
								            <tbody>
								            	<?php
								            	if ( $sTitle == 'Editar' ) {
									            	if ( !empty($arrDataEdit->arrData[0]["no_nombre_item"]) ) {
														foreach ($arrDataEdit->arrData as $rows) {
															$row = (object)$rows;
														?>
															<tr>
																<td class='text-left' style="display:none;"><?php echo trim($row->id_item); ?></td>
													            <td class='text-left'><?php echo $row->no_nombre_item; ?></td>
													            <td class='text-right'><?php echo $row->qt_cantidad_item; ?></td>
													            <td class='text-right'><?php echo $row->ss_precio_venta_item; ?></td>
													            <td class='text-right'><?php echo $row->ss_valor_venta_item; ?></td>
													            <td class='text-right'><?php echo $row->ss_impuesto_item; ?></td>
													            <td class='text-right'><?php echo $row->ss_descuento_item; ?></td>
													            <td class='text-right'><?php echo $row->ss_total_item; ?></td>
																<td class='text-center'><button type="button" id="btn-delete-sale_invoice_detail_item" class="button is-danger is-small icon-size btn-danger"><span class="icon is-small"><i class="fa fa-trash"></i></span></button></td>
															</tr>
														<?php
														}
													} else {
													?>
														<tr>
												            <td class='text-center' colspan="7">Sin detalle <b>(Anulado)</b></td>
														</tr>
													<?php
													}
												}
								            	?>
								            </tbody>
								        </table>
								    </div><!-- ./ table detalle de items -->
				                </div><!-- ./ is-12 -->
				            </div><!-- ./ Desktop ver items agregado por Javascript / PHP BD -->
					    </div><!-- ./ Panel Body Item -->
					</article><!-- ./ Article Principal -->
	            </div><!-- ./ is-12 -->
			</div><!-- ./ Desktop Row Detalle de items -->

            <div class="columns is-centered">
            	<div class="column is-9"></div>
            	<div class="column is-3">
					<article class="message is-primary">
						<div class="message-header">
							<div class="column">
								<label class="label color-white"><i class="fa fa-money icon-size" aria-hidden="true"> Totales</i></label>
							</div>
						</div>
						<div class="message-body">
				            <div class="columns">
				                <div class="column is-12">
				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Exonerada</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-exonerada" value="" autocomplete="off" disabled="" />
								        </div>

									  	<div class="column is-6">
								        	<label class="label" id="label-totales-exonerada"><?php echo ($sTitle=='Agregar' ? '0.00' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] == 'S' ? $arrDataEdit->arrData[0]["ss_total"] : '0.00')); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Inafecta</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-inafecta" value="" autocomplete="off" disabled="" />
								        </div>

									  	<div class="column is-6">
								        	<label class="label" id="label-totales-inafecta"><?php echo ($sTitle=='Agregar' ? '0.00' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] == 'V' ? $arrDataEdit->arrData[0]["ss_total"] : '0.00')); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Gravadas</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-gravada" value="" autocomplete="off" disabled="" />
								        </div>

								        <div class="column is-6">
								        	<label class="label" id="label-totales-gravada"><?php echo ($sTitle=='Agregar' ? '0.00' : (empty($arrDataEdit->arrData[0]["no_codigo_impuesto"]) ? $arrDataEdit->arrData[0]["ss_valor_venta"] : '0.00')); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">IGV</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-igv" value="" autocomplete="off" disabled="" />
								        </div>
									  	<div class="column is-6">
								        	<label class="label" id="label-totales-igv"><?php echo ($sTitle=='Agregar' ? '0.00' : (empty($arrDataEdit->arrData[0]["no_codigo_impuesto"]) ? $arrDataEdit->arrData[0]["ss_impuesto"] : '0.00')); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Gratuita</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-gratuita" value="" autocomplete="off" disabled="" />
								        </div>
									  	<div class="column is-6">
								        	<label class="label" id="label-totales-gratuita"><?php echo ($sTitle=='Agregar' ? '0.00' : ($arrDataEdit->arrData[0]["no_codigo_impuesto"] == 'T' ? $arrDataEdit->arrData[0]["ss_gratuita"] : '0.00')); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Dscto. Total (-)</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-descuento" value="" autocomplete="off" disabled="" />
								        </div>
									  	<div class="column is-6">
								        	<label class="label" id="label-totales-descuento"><?php echo ($sTitle=='Agregar' ? '0.00' : $arrDataEdit->arrData[0]["ss_descuento"]); ?></label>
								        </div>
								    </div>

				            		<div class="columns is-mobile">
									  	<div class="column is-6">
								        	<label class="label">Total</label>
								        	<input type="hidden" class="input input-decimal" id="hidden-totales-total" value="" autocomplete="off" disabled="" />
								        </div>
									  	<div class="column is-6">
								        	<label class="label" id="label-totales-total"><?php echo ($sTitle=='Agregar' ? '0.00' : $arrDataEdit->arrData[0]["ss_total"]); ?></label>
								        </div>
				            		</div>
				            	</div>
				           	</div>
				        </div>
				    </article>
				</div>
			</div><!-- ./ Desktop Row Totales -->

            <div class="columns">
			  	<div class="column is-6">
			  		<button style="width: 100%;" class="button is-danger btn-danger is-large" id="btn-cancel-sale_invoice"><i class="fa fa-close icon-size" aria-hidden="true"> <label class="label-btn-name">Cancelar (ESC)</label></i></button>
				</div>

				<div class="column is-6">
			  		<button type="button" style="width: 100%;" class="button is-primary btn-primary is-large" id="btn-save-sale_invoice"><i class="fa fa-save icon-size" aria-hidden="true"> <label class="label-btn-name">Guardar (ENTER)</label></i></button>
				</div>
			</div><!-- ./ Row button -->
        </div><!-- ./ Row DIV Agregar factura de venta-->
    </section>
    <!-- ./ section Factura Agregar -->
	<?php
	} // ./ function renderPageAddOrder Agregar factura de venta

    function Select($id_select, $value_select, $name_select, $arrData, $selected = null, $sinSeleccionar = false, $name_data, $value_data, $use_substr, $is_disabled = null){
        if(count($arrData) == 0) return;

        $html = "<select id=\"$id_select\" class=\"is-select\" data-$name_data=\"$value_data\" $is_disabled>";
        if($sinSeleccionar) $html .= '<option value="" selected="selected">Seleccionar</option>';
        
        foreach($arrData as $rows){
        	$row = (object)$rows;
        	if ($use_substr)//Solo para el caso de int_tabla_general
        		$row->$value_select = trim(substr($row->$value_select, 4, 2));
            if($selected != null){
                if(trim($selected) == $row->$value_select)
                    $html .= '<option selected="selected" value="' . $row->$value_select . '">' . $row->$name_select . '</option>';
                else
                    $html .= '<option value="' . $row->$value_select . '">' . $row->$name_select . '</option>';
            }else
                $html .= '<option value="' . $row->$value_select . '">' . $row->$name_select . '</option>';
        }
        $html .= "</select>";
        return $html;
    } // ./ Seletec combobox

	function allTypeDate($dFecha, $sValorCaracter, $sTipoAccion){
		if ($sTipoAccion == 'fecha_ymd'){
			$d = explode($sValorCaracter, $dFecha);
			return $d[2] . '/' . $d[1] . '/' . $d[0];
		}
	}
}// ./ Class