<?php
class templateRankingVentas {
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
		<script type="text/javascript" charset="utf-8" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script type="text/javascript" charset="utf-8" src="/sistemaweb/assets/js/init.js?ver=3.0"></script>
		<script type="text/javascript" charset="utf-8" src="/sistemaweb/combustibles/js/application.ranking_ventas.js?ver=1.0"></script>
		<script type="text/javascript" charset="utf-8" src="/sistemaweb/combustibles/js/functions.ranking_ventas.js?ver=1.0"></script>
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
	public function index($arrDataHelper, $iYearNow, $iMonthNow, $arrResponseModel) {
		$this->head(array('title' => 'Ranking Ventas - Buscar'));
	?>
	<section class="section">
		<div class="container">
            <h1 align="center">Ranking de Ventas</h1>
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
				            <label class="label">Generado por</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-generado">
									<option value="T">Todos</option>
									<option value="P">Playa</option>
									<option value="O">Oficina</option>
								</select>
							</span>
						</div>
					  	<div class="column">
				            <label class="label">Año</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-year">
								<?php
								$sSelected='';
								foreach ($arrDataHelper['arrYearStart'] as $row) {
									$sSelected='';
									if ( $row->year == $iYearNow )
										$sSelected='selected="selected"';
									echo '<option value="' . trim($row->year) . '" ' . $sSelected . '>' . trim($row->year) . '</option>';
								}
								?>
								</select>
							</span>
						</div>
					  	<div class="column">
				            <label class="label">Mes</label>
			    			<span class="select">
								<select class="is-select" id="cbo-filtro-month">
								<?php
								$sSelected='';
								foreach ($arrDataHelper['arrMonth'] as $row) {
									$sSelected='';
									if ( $row->valor == $iMonthNow )
										$sSelected='selected="selected"';
									echo '<option value="' . trim($row->valor) . '" ' . $sSelected . '>' . trim($row->mes) . '</option>';
								}
								?>
								</select>
							</span>
						</div>
				    </div>
			    </div>
			</div>
<br>
            <div class="columns is-centered">
				<div class="column is-6">
            		<div class="columns is-mobile">
					  	<div class="column">
					  		<button style="width: 100%;" class="button is-info btn-info" id="btn-html-ranking-ventas"><i class="fa fa-search icon-size" aria-hidden="true"> <label class="label-btn-name">Buscar</label></i></button>
						</div>
					</div><!-- ./ Row button -->
            	</div>
			</div>

			<br>
			<br>

            <div class="columns is-centered">
				<div class="div-ranking-ventas">
					<?php $this->tableGridViewHTML($arrResponseModel, $arrDataHelper); ?>
				</div>
			</div>

            <div class="columns is-centered">
				<div class="div-ranking-ventas-detalle">
					<?php $this->tableGridViewHTMLDetail($arrResponseModel, $arrDataHelper); ?>
				</div>
			</div>
        </div>
        <!-- ./ Container -->
	</section>
	<?php
	} // ./ function index buscar

	public function tableGridViewHTML($arrResponseModel, $arrPost) { ?>
	<section class="section">
		<div class="container">
			<?php
			if ( $arrResponseModel['sStatus'] == 'success' ) { ?>
				<div id="div-ranking-ventas" class="table__wrapper StandardTable">
		            <table id="table-ranking-ventas" class="table">
		            	<thead>
			                <tr>
			                	<th class="text-center" align="center">#</th>
			                	<th class="text-center">Cliente</th>
			                	<th class="text-center" title="Cantidad de veces de consumos en la empresa">Nro. de Veces</th>
			                	<th class="text-center" title="Los documentos en dólares, el sistema realizará la conversión a soles, según el tipo de cambio registrado en sistemaweb">S/ Total</th>
			                </tr>
		              	</thead>
		              	<tbody>
						<?php
							$iCount = 0;
						    foreach ($arrResponseModel["arrData"] as $rows) {
						    	$row = (object)$rows;
								$sClassColorTd = ($iCount%2==0? "grid_detalle_par" : "grid_detalle_impar");
								?>
								<tr id="tr-<?php echo $iCount;?>" class="<?php echo $sClassColorTd;?>">
									<td class="text-center"><?php echo $iCount; ?></td>
									<td class="text-left"><?php echo $row->nu_documento_identidad_cliente . ' ' . $row->no_cliente; ?></td>
									<td class="text-center"><?php echo $row->nu_cantidad; ?></td>
									<td class="text-right"><?php echo number_format($row->ss_total, 2, '.', ','); ?></td>
									<td class="text-center">
										<a class="button is-small" title="Ver Detalle" onclick="obtenerDetalle('<?php echo $arrPost['sAlmacen'];?>', '<?php echo $arrPost['sGeneradoPor'];?>', '<?php echo $arrPost['sYear'];?>', '<?php echo $arrPost['sMonth'];?>', '<?php echo trim($row->id_codigo_cliente);?>', '<?php echo $row->nu_documento_identidad_cliente . ' ' . $row->no_cliente; ?>', '<?php echo trim($iCount);?>')"><span class="tag is-info">Ver detalle</span></a>
									</td>
						    	</tr>
						    <?php
						    	$iCount++;
							} // /. Foreach recorrido de data
						?>
						</tbody>
		            </table>
		        </div><!-- ./ Div mostrar registros de facturas de venta -->
    		<?php
			} else { ?>
		        <div class="columns">
		            <div class="column is-12 text-center">
		            	<div class="notification is-<?php echo $arrResponseModel["sStatus"]; ?>"><?php echo $arrResponseModel["sMessage"]; ?></div>
				    </div>
				</div>
			<?php
			}
			?>
		</div><!-- ./ Container -->
		<br>
	</section>
	<?php
	}

	public function tableGridViewHTMLDetail($arrResponseModel, $arrPost) { ?>
	<section class="section">
		<div class="container">
			<?php
			if ( $arrResponseModel['sStatus'] == 'success' ) { ?>
				<div id="div-ranking-ventas-detalle" class="table__wrapper StandardTable">
		            <table id="table-ranking-ventas-detalle" class="table">
		            	<thead>
			                <tr>
			                	<th style="text-align:center" colspan="5">Año: <?php echo $arrPost['sYear'];?> - Mes: <?php echo $arrPost['sMonth'];?></th>
			                </tr>
			                <tr>
			                	<th style="text-align:center" colspan="5"><?php echo $arrPost['sNombreCliente'];?></th>
			                </tr>
			                <tr>
			                	<th style="text-align:center">F. Emisión</th>
			                	<th style="text-align:center">Tipo</th>
			                	<th style="text-align:center">Serie</th>
			                	<th style="text-align:center">Numero</th>
			                	<th style="text-align:center">S/ Total</th>
			                	<th style="text-align:center; background: white !important;">
			                		<a class="button is-small" title="Ver Detalle" onclick="regresarCliente('<?php echo $arrPost['iIdTr'];?>')"><span class="tag is-info">Regresar</span></a>
			                	</th>
			                </tr>
		              	</thead>
		              	<tbody>
						<?php
							$iCount = 0;
						    foreach ($arrResponseModel["arrData"] as $rows) {
						    	$row = (object)$rows;
								$sClassColorTd = ($iCount%2==0? "grid_detalle_par" : "grid_detalle_impar");
								?>
								<tr class="<?php echo $sClassColorTd;?>">
									<td class="text-left"><?php echo $row->fe_emision; ?></td>
									<td class="text-left"><?php echo $row->no_tipo_documento; ?></td>
									<td class="text-left"><?php echo $row->no_serie_documento; ?></td>
									<td class="text-left"><?php echo $row->no_numero_documento; ?></td>
									<td class="text-right"><?php echo number_format($row->ss_total, 2, '.', ','); ?></td>
						    	</tr>
						    <?php
						    	$iCount++;
							} // /. Foreach recorrido de data
						?>
						</tbody>
						<tfoot>
							<tr class="<?php echo $sClassColorTd;?>">
			                	<th style="text-align:center" colspan="5"></th>
			                	<th style="text-align:center; background: white !important;">
			                		<a class="button is-small" title="Ver Detalle" onclick="regresarCliente('<?php echo $arrPost['iIdTr'];?>')"><span class="tag is-info">Regresar</span></a>
			                	</th>
					    	</tr>
						</tfoot>
		            </table>
		        </div><!-- ./ Div mostrar registros de facturas de venta -->
    		<?php
			} else { ?>
		        <div class="columns">
		            <div class="column is-12 text-center">
		            	<div class="notification is-<?php echo $arrResponseModel["sStatus"]; ?>"><?php echo $arrResponseModel["sMessage"]; ?></div>
				    </div>
				</div>
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