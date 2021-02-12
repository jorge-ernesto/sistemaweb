<?php
class templateSalesXEmployee {
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
		<script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/init.js?ver=2.0"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/ventas_clientes/js/application.ventas_x_trabajador.js?ver=1.0"></script>
		<script charset="utf-8" type="text/javascript" src="/sistemaweb/ventas_clientes/js/functions.ventas_x_trabajador.js?ver=1.0"></script>
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
	public function index($arrDataHelper, $arrResponseModel) {
		$this->head(array('title' => 'Ventas x Trabajador - Buscar'));
	?>
	<section class="section">
		<div class="container">
            <h1 align="center">Informe de Ventas por Trabajador</h1>
            <br>
            <div class="columns is-centered">
                <div class="column is-6">
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
			</div>

            <div class="columns is-centered">
                <div class="column is-3">
            		<div class="columns is-mobile">
					  	<div class="column">
				        	<label class="label">Trabajador</label>
				        	<input type="hidden" class="input" id="hidden-filtro-trabajador-id" autocomplete="off" value="" />
				        	<input type="text" class="input" id="txt-filtro-trabajador-nombre" autocomplete="off" value="" placeholder="Ingresar código / nombre"  onkeyup="autocompleteBridge(2)"/>
			    		</div>
			    	</div>
			    </div>

                <div class="column is-3">
            		<div class="columns is-mobile">
					  	<div class="column">
				        	<label class="label">Artículo</label>
				        	<input type="hidden" class="input" id="hidden-filtro-item-id" autocomplete="off" value="" />
				        	<input type="text" class="input" id="txt-filtro-item-nombre" autocomplete="off" value="" placeholder="Ingresar código / nombre" onkeyup="autocompleteBridge(1)"/>
				        	<p class="help is-danger"></p>
			    		</div>
			    	</div>
			    </div>
			</div>

            <div class="columns is-centered">
				<div class="column is-6">
            		<div class="columns is-mobile">
					  	<div class="column">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-html-sales-employee"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
						</div>
					</div><!-- ./ Row button -->
            	</div>
			</div>

			<br>
			<br>

            <div class="columns is-centered">
				<div class="div-sales-employee">
					<?php $this->tableSalesXEmployee($arrResponseModel); ?>
				</div>
			</div>				
        </div>
        <!-- ./ Container -->
	</section>
	<?php
	} // ./ function index buscar

	public function tableSalesXEmployee($arrResponseModel) { ?>
	<section class="section">
		<div class="container">
			<?php
			if ( $arrResponseModel["sStatus"] != "success" ) { ?>
		        <div class="columns">
		            <div class="column is-12 text-center">
		            	<div class="notification is-<?php echo $arrResponseModel["sStatus"]; ?>"><?php echo $arrResponseModel["sMessage"]; ?></div>
				    </div>
				</div>
			<?php
			} else { ?>
				<div id="div-sales-employee" class="table__wrapper StandardTable">
		            <table id="table-sales-employee" class="table">
		            	<thead>
			                <tr>
			                	<th class="text-center">Tipo Documento</th>
			                	<th class="text-center">Nro. Ticket</th>
			                	<th class="text-center">Caja</th>
			                	<th class="text-center">Turno</th>
			                	<th class="text-center">F. Emisión</th>
			                	<th class="text-center">RUC</th>
			                	<th class="text-center">Cód. Item</th>
			                	<th class="text-center">Descripción</th>
			                	<th class="text-center">Cantidad</th>
			                	<th class="text-center">Precio</th>
			                	<th class="text-center">Total</th>
			                </tr>
		              	</thead>
		              	<tbody>
						<?php
							$iCount = 0;
							$sCodigoTrabajador = '';
							$iCountTrabajador=0;
							$fTotCantidadTrabajador=0.00;
							$fTotSolesTrabajador=0.00;
						    foreach ($arrResponseModel["arrData"] as $rows) {
						    	$row = (object)$rows;
						    	if ( $sCodigoTrabajador != $row->id_trabajador ) {
									if ( $iCountTrabajador != 0 ) { ?>
										<tr>
											<td class="text-right" colspan="8"><b>TOTAL</b></td>
											<td class="text-right"><b><?php echo $fTotCantidadTrabajador; ?></b></td>
											<td class="text-right"></td>
											<td class="text-right"><b><?php echo $fTotSolesTrabajador; ?></b></td>
										</tr>
									<?php
										$fTotCantidadTrabajador=0.00;
										$fTotSolesTrabajador=0.00;										
									}
									?>
									<tr>
										<td class="text-left" colspan="11"><b><?php echo $row->id_trabajador . ' - ' . $row->no_nombre_trabajador . $row->no_apellido_paterno . $row->no_apellido_materno; ?></b></td>
									</tr>
								<?php
									$sCodigoTrabajador = $row->id_trabajador;
						    	}
								$sClassColorTd = ($iCount%2==0? "grid_detalle_par" : "grid_detalle_impar");
								?>
								<tr class="<?php echo $sClassColorTd;?>">
									<td class="text-center"><?php echo $row->no_tipo_documento; ?></td>
									<td class="text-center"><?php echo $row->nu_id_trans; ?></td>
									<td class="text-center"><?php echo $row->nu_caja; ?></td>
									<td class="text-center"><?php echo $row->nu_turno; ?></td>
									<td class="text-center"><?php echo $this->allTypeDate($row->fe_emision, "-", "fecha_ymd_hms"); ?></td>
									<td class="text-left"><?php echo $row->nu_ruc; ?></td>
									<td class="text-left"><?php echo $row->nu_id_item; ?></td>
									<td class="text-left"><?php echo $row->no_nombre_item; ?></td>
									<td class="text-right"><?php echo $row->qt_cantidad; ?></td>
									<td class="text-right"><?php echo $row->ss_precio; ?></td>
									<td class="text-right"><?php echo $row->ss_total; ?></td>
						    	</tr>
						    <?php
								$fTotCantidadTrabajador+=$row->qt_cantidad;
								$fTotSolesTrabajador+=$row->ss_total;

						    	$iCount++;
						    	$iCountTrabajador++;
							} // /. Foreach recorrido de data
						?>
							<tr>
								<td class="text-right" colspan="8"><b>TOTAL</b></td>
								<td class="text-right"><b><?php echo $fTotCantidadTrabajador; ?></b></td>
								<td class="text-right"></td>
								<td class="text-right"><b><?php echo $fTotSolesTrabajador; ?></b></td>
							</tr>
						</tbody>
		            </table>
		        </div><!-- ./ Div mostrar registros de facturas de venta -->
    		<?php
			}
			?>
		</div><!-- ./ Container -->
		<br>
	</section>
	<?php
	}

	function allTypeDate($dFecha, $sValorCaracter, $sTipoAccion){
		if ($sTipoAccion == 'fecha_ymd_hms'){
			$d = explode($sValorCaracter, $dFecha);
			$h = explode(" ", $d[2]);
			return $h[0] . '/' . $d[1] . '/' . $d[0] . ' ' . $h[1];
		}
	}
}// ./ Class