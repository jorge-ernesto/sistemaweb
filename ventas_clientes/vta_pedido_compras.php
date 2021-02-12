<?php //ob_start(); ?>
<?php require_once("/sistemaweb/valida_sess.php"); ?>

<html>
	<head>
		<title>Sistema de Combustibles - Editar Lista de Compras</title>
		<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
		<script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
		<script language="JavaScript" src="js/pedidocompras.js"></script>				
		
		<link rel="stylesheet" type="text/css" href="/sistemaweb/assets/css/jquery.dataTables.css"> 
		<?php include "../header2.php"; ?>
		<?php include "../footer2.php"; ?>
		<script type="text/javascript" charset="utf8" src="/sistemaweb/assets/js/helper/jquery.dataTables.js"></script>

		<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js"></script>
		<script type="text/javascript" charset="utf-8">
			window.onload = function() {
				$(function() {
					$.datepicker.regional['es'] = {
						closeText: 'Cerrar',
						prevText: '<Ant',
						nextText: 'Sig>',
						currentText: 'Hoy',
						monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
						monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
						dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
						dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
						dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
						weekHeader: 'Sm',
						dateFormat: 'dd/mm/yy',
						firstDay: 1,
						isRTL: false,
						showMonthAfterYear: false,
						yearSuffix: ''
					};

					$.datepicker.setDefaults($.datepicker.regional['es']);

					$( "#desde" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})

					$( "#hasta" ).datepicker({
						changeMonth: true,
						changeYear: true,
					})
				});
			}

			function getFechaEmision(){
				$.datepicker.regional['es'] = {
					closeText: 'Cerrar',
					prevText: '<Ant',
					nextText: 'Sig>',
					currentText: 'Hoy',
					monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
					monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
					dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
					dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
					dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
					weekHeader: 'Sm',
					dateFormat: 'dd/mm/yy',
					firstDay: 1,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''
				};

				$.datepicker.setDefaults($.datepicker.regional['es']);

				$( "#desde" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})

				$( "#hasta" ).datepicker({
					changeMonth: true,
					changeYear: true,
				})
			}

		function autocompleteBridge(type) {
			//console.log('type: '+type);
			if (type == 0) {
				//type para linea
				var line = $("#cod_linea2");
				if(line.val() !== undefined){
					//console.log('text: '+line.val());
					//console.log(line.val());
					generalAutocomplete('#cod_linea2', '#nom_linea2', 'getLineByCodeOrName', []);
				}
			} else if(type == 1) {
				//tipo para proveedor
				var partner = $("#cod_proveedor2");
				if(partner.val() !== undefined){
					//console.log('text: '+partner.val());
					//console.log(partner.val());
					generalAutocomplete('#cod_proveedor2', '#nom_proveedor2', 'getPartnersByRucOrName', []);
				}
			}else if(type == 2) {
				//tipo para productopedido
				var productox = $("#descripcionx");
				if(productox.val() !== undefined){
					//console.log('text: '+productox.val());
					//console.log(productox.val());
					generalAutocomplete('#descripcionx', '#codigox', 'getProductXByCodeOrName', ['AutoMercaderia()']);
				}
			}
		}

		function AutoMercaderia() {

				var producto = $('#codigox').val();
				var almacen = $('#almacen2').val();
				var tipopedido = $('#tipopedido').val();

		        $('#cargardor').css({'display':'inline'});
		        $.ajax({
				type: "POST",
				url: "c_pedido_compras_relacion.php",
				data: {
		                accion:'completar_pedido',
				        'producto':producto,
				        'almacen':almacen,
				        'tipopedido':tipopedido,
		            	},
		            	success:function(xm){
		               		var obj=eval('('+xm+')'); 
		               		$('#mes1x').val(obj.dato1);
		               		$('#mes2x').val(obj.dato2);
		               		$('#mes3x').val(obj.dato3);
		               		$('#stk_actualx').val(obj.dato4);
		               		$('#stk_minimox').val(obj.dato5);
		               		$('#stk_maximox').val(obj.dato6);
		               		$('#pedidox').val(obj.dato7);
		               		$('#sugeridox').val(obj.dato8);
				}
		        });
			
		}


                $(document).on('click', '.check-all', function() {
        		$('input:checkbox').not(this).prop('checked', this.checked);
				});
			function getFormData($form) {
				var unindexed_array = $form.serializeArray();
				var indexed_array = {};

				$.map(unindexed_array, function(n, i){
					indexed_array[n['name']] = n['value'];
				});

				return indexed_array;
			}

			/**
			 * Modificaciones para envio POST con JSON
			 *
			 */
			$(document).on('click', '#guardar-pedido', function() {
				guardarPedido();
			});
			function guardarPedido() {			
				console.log("guardarPedido con JavaScript");

				$('#cargardor').css({'display':'inline'});

				var product_check = [];
				var product_code = [];				
				var stk_min = [];
				var stk_max = [];
				var qty = [];

				/*var _product_code = '';
				var _product_check = '';
				var _stk_min = '';
				var _stk_max = '';
				var _qty = '';*/

				var i_qty = 0;

				$('.product-check').each(function() {
					product_check.push(($(this).is(':checked') ? 'S' : 'N'));
					//_product_check += ($(this).is(':checked') ? 'S' : 'N')+'|';
				});
				$('.product-code').each(function() {
					product_code.push($(this).val());
					//_product_code += $(this).val()+'|';
				});
				$('.stk-min').each(function() {
					stk_min.push($(this).val());
					//_stk_min += $(this).val()+'|';
				});
				$('.stk-max').each(function() {
					stk_max.push($(this).val());
					//_stk_max += $(this).val()+'|';
				});
				var err_qty = 0;
				$('.qty').each(function() {	
					//Validacion de checks con pedidos mayores a cero				
					// if (product_check[i_qty] == 'S')  {
					// 	if (!$.isNumeric($(this).val())) {
					// 		alert('Cantidad inválida del producto ' + product_code[i_qty]);
					// 		err_qty++;
					// 		return false;
					// 	} else if ($(this).val() <= 0) {
					// 		alert('Cantidad inválida del producto ' + product_code[i_qty]);
					// 		err_qty++;
					// 		return false;
					// 	}
					// }
					// i_qty++;
					//Cerrar validacion de checks con pedidos mayores a cero
					qty.push($(this).val());

					//console.log('qty: '+$(this).val()+' - product_check['+i_qty+']: '+product_check[i_qty]);
					//_qty += $(this).val()+'|';
				});

				if (err_qty == 0) {
					var params = {
						rqst: 'REPORTES.PEDIDOCOMPRAS',
						cab_almacen: $('#almacen2').val(),
						sCodLinea: $('#nom_linea2').val(),
						sCodProveedor: $('#nom_proveedor2').val(),
						cab_nropedido: $('#nropedido').val(),
						cab_tipo: $('#tipopedido').val(),
						cab_observacion: $('#observacion').val(),
						action: 'GuardarPedido',
						/*product_check: _product_check,
						product_code: _product_code,
						stk_min: _stk_min,
						stk_max: _stk_max,
						qty: _qty,*/
						data: JSON.stringify({
							"product_check": product_check, "product_code": product_code,
							"stk_min": stk_min, "stk_max": stk_max, "qty": qty,
						}),
					};
					console.log('params: ', params);					

					$.ajax({
						url: 'control.php',
						type: 'POST',
						dataType: 'json',
						data: params,
					})
					.done(function(data) {
						$('#cargardor').css({'display':'none'});
						//console.log(data);
						//console.log("success");
						if (!data.error) {
							alert(data.message);
							window.location = 'vta_pedido_compras.php';
						} else {
							alert(data.message);
						}
					})
					.fail(function(err) {
						$('#cargardor').css({'display':'none'});
						//console.log(err);
						//console.log("error");
					});
				}	
			}

			/**
			 * Modificaciones para insertar nueva linea de producto POST con JSON
			 *
			 */
			$(document).on('click', '#insertar-pedido', function() {
				insertarPedido();
			});
			function insertarPedido() {
				console.log("insertarPedido con JavaScript");
				
				var row          = $('#insertar-pedido').data('rowinsertar');
				var codigox      = $('#codigox').val();
				var descripcionx = $('#descripcionx').val();
				var mes1x        = $('#mes1x').val();
				var mes2x        = $('#mes2x').val();
				var mes3x        = $('#mes3x').val();
				var stk_actualx  = $('#stk_actualx').val();
				var stk_minimox  = $('#stk_minimox').val();
				var stk_maximox  = $('#stk_maximox').val();
				var pedidox  = $('#pedidox').val();

				if(codigox == '' || descripcionx == ''){
					alert('Debe ingresar un producto');
					return;
				}
				
				$('#row_insertar').remove();

				var row_html = `
				<tr class=" grid_detalle_par">
					<td>
						<input type="checkbox" name="vec_check[${row}]" class="product-check" value="S" checked="">
					</td>
					<td>
						<input type="text" name="codigo[${row}]" class="product-code" id="codigo[${row}]" value="${codigox}" size="18" maxlength="18" readonly="">
					</td>
					<td>${descripcionx}</td>
					<td>${mes1x}</td>
					<td>${mes2x}</td>
					<td>${mes3x}</td>
					<td>${stk_actualx}</td>
					<td>
						<input type="text" name="stk_minimo[${row}]" class="stk-min" id="stk_minimo[${row}]" value="${stk_minimox}" onkeyup="javascript:actPedido(this.value, '0.0000', 'pedido[${row}]')" size="11" maxlength="11">
					</td>
					<td>
						<input type="text" name="stk_maximo[${row}]" class="stk-max" id="stk_maximo[${row}]" value="${stk_maximox}" onkeyup="javascript:actPedido(this.value, '0.0000', 'pedido[${row}]')" size="11" maxlength="11">
					</td>
					<td>
						<input type="text" name="pedido[${row}]" class="qty" id="pedido[${row}]" value="${pedidox}" size="15" maxlength="15">
					</td>
					<td>0</td>
				</tr>
				`;
				$('#tablaprueba tfoot').append(row_html);

				var row_html_2 = `
				<tr id="row_insertar">
					<td></td>
					<td>
						<input type="input" maxlength="18" size="18" class="form_input" value="" id="codigox" name="codigox">
					</td>
					<td>
						<input type="text" style="width: 100%" maxlength="13" size="13" class="form_input" value="" id="descripcionx" onkeyup="autocompleteBridge(2)" name="descripcionx" placeholder="Ingrese Nombre o Codigo de Producto">
					</td>
					<td>
						<input type="text" name="mes1x" id="mes1x" value="" class="form_input" size="10" maxlength="10" readonly="">
					</td>
					<td>
						<input type="text" name="mes2x" id="mes2x" value="" class="form_input" size="10" maxlength="10" readonly="">
					</td>
					<td>
						<input type="text" name="mes3x" id="mes3x" value="" class="form_input" size="10" maxlength="10" readonly="">
					</td>
					<td>
						<input type="text" name="stk_actualx" id="stk_actualx" value="" class="form_input" size="11" maxlength="11" readonly="">
					</td>
					<td>
						<input type="text" name="stk_minimox" id="stk_minimox" value="" class="form_input" size="11" maxlength="11">
					</td>
					<td>
						<input type="text" name="stk_maximox" id="stk_maximox" value="" class="form_input" size="11" maxlength="11">
					</td>
					<td>
						<input type="text" name="pedidox" id="pedidox" value="" class="form_input" size="15" maxlength="15">
					</td>
					<td>
						<input type="text" name="sugeridox" id="sugeridox" value="" class="form_input" size="17" maxlength="17" readonly="">
					</td>
					<td>
						<input type="button" id="insertar-pedido" data-rowinsertar="${row + 1}" name="action" value="Insertar">
					</td>
				</tr>
				`;
				$('#tablaprueba tfoot').append(row_html_2);
			}

			/**
			 * Modificaciones para obtener listado de pedido con JavaScript usando datatables
			 *
			 */
			$(document).on('click', '#listar-pedido', function() {
				listarPedido();
			});
			function listarPedido() {
				console.log("listarPedido con JavaScript");
				$('#content_footer').empty();
				$('#content_footer').append(`<div id='listado-pedido'></div>`);			

				var params = {
					rqst: 'REPORTES.PEDIDOCOMPRAS',
					cab_almacen: $('#almacen2').val(),
					sCodLinea: $('#nom_linea2').val(),
					sCodProveedor: $('#nom_proveedor2').val(),
					cab_nropedido: $('#nropedido').val(),
					cab_tipo: $('#tipopedido').val(),
					cab_observacion: $('#observacion').val(),
					modo: $('input[name=modo]:checked').val(),
					action: 'ListarPedidoWithDataTables',					
				};
				console.log('params: ', params);
				
				var input_hidden = `
					<input type="hidden" name="rqst" value="REPORTES.PEDIDOCOMPRAS">
					<input type="hidden" name="cab_almacen" class="cab_almacen" value="${params.cab_almacen}">
					<input type="hidden" name="sCodLinea" class="sCodLinea" value="${params.sCodLinea}">
					<input type="hidden" name="sCodProveedor" class="sCodProveedor" value="${params.sCodProveedor}">
					<input type="hidden" name="cab_nropedido" class="cab_nropedido" value="${params.cab_nropedido}">
					<input type="hidden" name="cab_tipo" class="cab_tipo" value="${params.cab_tipo}">
					<input type="hidden" name="cab_observacion" class="cab_observacion" value="${params.cab_observacion}">
				`;
				$('#listado-pedido').append(input_hidden);

				var tabla = `
					<table border="0" style="" id="tablaprueba">
						<thead>
							<tr>
								<th class="grid_cabecera" rowspan="2">&nbsp;<input type="checkbox" class="check-all"></th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Codigo&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Descripcion&nbsp;</th>
								<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Venta 3 Ultimos Meses&nbsp;&nbsp;</th>
								<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Stock&nbsp;&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Pedido&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Sugerida&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Opcion&nbsp;</th>
							</tr>
							<tr>
								<th class="grid_cabecera">&nbsp;Mes 1&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Mes 2&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Mes 3&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Actual&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Minimo&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Maximo&nbsp;</th>
								<!-- <th style="display:none"></th> -->
							</tr>
						</thead>
						<tbody style="text-align:center;">							
						</tbody>
						<tfoot style="text-align:center;">
							<tr id="row_insertar">
								<td></td>
								<td>
									<input type="input" maxlength="18" size="18" class="form_input" value="" id="codigox" name="codigox">
								</td>
								<td>
									<input type="text" style="width: 100%" maxlength="13" size="13" class="form_input" value="" id="descripcionx" onkeyup="autocompleteBridge(2)" name="descripcionx" placeholder="Ingrese Nombre o Codigo de Producto">
								</td>
								<td>
									<input type="text" name="mes1x" id="mes1x" value="" class="form_input" size="10" maxlength="10" readonly="">
								</td>
								<td>
									<input type="text" name="mes2x" id="mes2x" value="" class="form_input" size="10" maxlength="10" readonly="">
								</td>
								<td>
									<input type="text" name="mes3x" id="mes3x" value="" class="form_input" size="10" maxlength="10" readonly="">
								</td>
								<td>
									<input type="text" name="stk_actualx" id="stk_actualx" value="" class="form_input" size="11" maxlength="11" readonly="">
								</td>
								<td>
									<input type="text" name="stk_minimox" id="stk_minimox" value="" class="form_input" size="11" maxlength="11">
								</td>
								<td>
									<input type="text" name="stk_maximox" id="stk_maximox" value="" class="form_input" size="11" maxlength="11">
								</td>
								<td>
									<input type="text" name="pedidox" id="pedidox" value="" class="form_input" size="15" maxlength="15">
								</td>
								<td>
									<input type="text" name="sugeridox" id="sugeridox" value="" class="form_input" size="17" maxlength="17" readonly="">
								</td>
								<td>
									<input type="button" id="insertar-pedido" data-rowinsertar="" name="action" value="Insertar">
								</td>
							</tr>
						</tfoot>
					</table>					
					<div align="center"><input type="button" id="guardar-pedido" name="action" value="Guardar Pedido">&nbsp;&nbsp;&nbsp;<input type="button" value="Regresar" onclick="regresar()"></div>
				`;
				$('#listado-pedido').append(tabla);

				$('#tablaprueba').DataTable({
					"processing": true,
					"serverSide": false,
					ajax: {
						url: 'control.php',
						type: 'POST',
						dataType: 'json',
						data: params,
						complete: function(response) {
							console.log('complete: ', response);												
							$('#insertar-pedido').attr('data-rowinsertar',response.responseJSON.recordsTotal)						
						},						
						error: function(e) {
							console.log(e.responseText);
							if(e.responseText == "Debe ingresar un proveedor"){
								alert(e.responseText);
							}
						}						
					},
					language: {
						"sProcessing":     "Procesando...",
						"sLengthMenu":     "Mostrar _MENU_ registros",
						"sZeroRecords":    "No se encontraron resultados",
						"sEmptyTable":     "Ningún dato disponible en esta tabla",
						"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
						"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
						"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
						"sInfoPostFix":    "",
						"sSearch":         "Buscar:",
						"sUrl":            "",
						"sInfoThousands":  ",",
						"sLoadingRecords": "Cargando...",
						"oPaginate": {
							"sFirst":    "Primero",
							"sLast":     "Último",
							"sNext":     "Siguiente",
							"sPrevious": "Anterior"
						},
						"oAria": {
							"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
							"sSortDescending": ": Activar para ordenar la columna de manera descendente"
						}
					},
					"lengthMenu": [[5, 10, 25, 50, 100, 500, -1], [5, 10, 25, 50, 100, 500, "All"]],
					"pageLength": -1,
					dom: 'Blfrtip', // Blfrtip, Bfrtip
					buttons: [
						'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
					]					
				});				

				$('#tablaprueba').removeClass('dataTable no-footer');				
				$('#tablaprueba tbody').attr('style', 'text-align:center;');				
				$('.dataTables_length').attr('style', 'margin-bottom:10px');
				$('.dataTables_filter').attr('style', 'margin-bottom:10px');				
				$('.dataTables_info').attr('style', 'margin-top:10px');
				$('.dataTables_paginate').attr('style', 'margin-top:10px');				
			}

			/**
			 * Modificaciones para obtener listado de pedido con JavaScript usando datatables para modificar
			 *
			 */
			// $(document).on('click', '.editar-pedido', function(e) {				
			// 	editarPedido();
			// });
			function editarPedido(num_pedido, fecha, tipo, almacen, nombre_almacen, observacion) {				
				console.log("editarPedido con JavaScript");										

				var params = {
					rqst: 'REPORTES.PEDIDOCOMPRAS',
					num_pedido: num_pedido,
					fecha: fecha,
					tipo: tipo,
					almacen: almacen,
					nombre_almacen: nombre_almacen,
					observacion: observacion,
					action: 'EditarPedidoWithDataTables',					
				};				
				console.log('params: ', params);				
				
				$('#content_body').empty();				
				$('#content_body').append(`<div id='cabecera-pedido'></div>`);		
				$('#content_footer').empty();
				$('#content_footer').append(`<div id='listado-pedido'></div>`);		

				var input_hidden = `
					<input type="hidden" name="rqst" value="REPORTES.PEDIDOCOMPRAS">
					<input type="hidden" name="cab_almacen" class="cab_almacen" value="${params.almacen}">
					<input type="hidden" name="sCodLinea" class="sCodLinea" value="">
					<input type="hidden" name="sCodProveedor" class="sCodProveedor" value="">
					<input type="hidden" name="cab_nropedido" class="cab_nropedido" value="${params.num_pedido}">
					<input type="hidden" name="cab_tipo" class="cab_tipo" value="${params.tipo}">
					<input type="hidden" name="cab_observacion" class="cab_observacion" value="${params.observacion}">
				`;
				$('#cabecera-pedido').append(input_hidden);

				var cabecera_html = `					
					<div align="center">
						<h2 style="color:#336699">Pedido de Compra</h2>
					</div>
					<table border="0" align="center" cellpadding="5">
						<tbody>
							<tr>
								<td align="right">Almacen</td><td>:</td>
								<td><input type="text" style="width:160px" value="${params.nombre_almacen}" readonly=""></td>
							</tr>
							<tr>
								<td align="right">Nro. Pedido</td>
								<td>:</td>
								<td><input type="text" style="width:100px" value="${params.num_pedido}" readonly=""></td>
							</tr>
							<tr>
								<td align="right">Fecha</td>
								<td>:</td>
								<td><input type="text" style="width:70px" value="${params.fecha}" readonly=""></td>
							</tr>
							<input type="hidden" name="cab_fecha" value="${params.fecha}">
							<tr>
								<td align="right">Tipo de Pedido</td>
								<td>:</td>
								<td><input type="text" style="width:60px" value="${params.tipo}" readonly=""></td>
							</tr>
							<tr>
								<td>Observación</td><td>:</td>
								<td><input type="text" name="observacion" id="observacion" value="${params.observacion}" class="form_input" size="50" maxlength="80"></td>
							</tr>
						</tbody>
					</table>
				`;
				$('#cabecera-pedido').append(cabecera_html);		

				var tabla = `
					<table border="0" style="" id="tablaprueba">
						<thead>
							<tr>
								<th class="grid_cabecera" rowspan="2">&nbsp;<input type="checkbox" class="check-all"></th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Codigo&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Descripcion&nbsp;</th>
								<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Venta 3 Ultimos Meses&nbsp;&nbsp;</th>
								<th class="grid_cabecera" colspan="3">&nbsp;&nbsp;Stock&nbsp;&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Pedido&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Cantidad Sugerida&nbsp;</th>
								<th class="grid_cabecera" rowspan="2">&nbsp;Opcion&nbsp;</th>
							</tr>
							<tr>
								<th class="grid_cabecera">&nbsp;Mes 1&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Mes 2&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Mes 3&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Actual&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Minimo&nbsp;</th>
								<th class="grid_cabecera">&nbsp;Maximo&nbsp;</th>
								<!-- <th style="display:none"></th> -->
							</tr>
						</thead>
						<tbody style="text-align:center;">							
						</tbody>						
					</table>					
					<div align="center"><input type="button" id="modificar-pedido" name="action" value="Modificar Pedido">&nbsp;&nbsp;&nbsp;<input type="button" value="Regresar" onclick="regresar()"></div>
				`;
				$('#listado-pedido').append(tabla);

				$('#tablaprueba').DataTable({
					"processing": true,
					"serverSide": false,
					ajax: {
						url: 'control.php',
						type: 'POST',
						dataType: 'json',
						data: params,
						complete: function(response) {
							console.log('complete: ', response);
						},						
						error: function(e) {
							console.log(e.responseText);
							if(e.responseText == "Debe ingresar un proveedor"){
								alert(e.responseText);
							}
						}						
					},
					language: {
						"sProcessing":     "Procesando...",
						"sLengthMenu":     "Mostrar _MENU_ registros",
						"sZeroRecords":    "No se encontraron resultados",
						"sEmptyTable":     "Ningún dato disponible en esta tabla",
						"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
						"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
						"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
						"sInfoPostFix":    "",
						"sSearch":         "Buscar:",
						"sUrl":            "",
						"sInfoThousands":  ",",
						"sLoadingRecords": "Cargando...",
						"oPaginate": {
							"sFirst":    "Primero",
							"sLast":     "Último",
							"sNext":     "Siguiente",
							"sPrevious": "Anterior"
						},
						"oAria": {
							"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
							"sSortDescending": ": Activar para ordenar la columna de manera descendente"
						}
					},
					"lengthMenu": [[5, 10, 25, 50, 100, 500, -1], [5, 10, 25, 50, 100, 500, "All"]],
					"pageLength": -1,
					dom: 'Bfrtip', // Blfrtip, Bfrtip
					buttons: [
						'copy', 'csv', 'excel', 'pdf', 'print', 'pageLength'
					]					
				});				

				$('#tablaprueba').removeClass('dataTable no-footer');				
				$('#tablaprueba tbody').attr('style', 'text-align:center;');				
				$('.dataTables_length').attr('style', 'margin-bottom:10px');
				$('.dataTables_filter').attr('style', 'margin-bottom:10px');				
				$('.dataTables_info').attr('style', 'margin-top:10px');
				$('.dataTables_paginate').attr('style', 'margin-top:10px');				
			}

			/**
			 * Modicar pedido por POST con JSON
			 *
			 */
			$(document).on('click', '#modificar-pedido', function() {
				modificarPedido();
			});
			function modificarPedido() {
				console.log('modificarPedido con JavaScript');

				$('#cargardor').css({'display':'inline'});

				var id_det = [];
				var product_check = [];
				var product_code = [];				
				var stk_min = [];
				var stk_max = [];
				var qty = [];

				/*var _product_code = '';
				var _product_check = '';
				var _stk_min = '';
				var _stk_max = '';
				var _qty = '';*/

				var i_qty = 0;

				$('.id_det').each(function() {
					id_det.push($(this).val());
					//_product_code += $(this).val()+'|';
				});

				$('.product-check').each(function() {
					product_check.push(($(this).is(':checked') ? 'S' : 'N'));
					//_product_check += ($(this).is(':checked') ? 'S' : 'N')+'|';
				});
				$('.product-code').each(function() {
					product_code.push($(this).val());
					//_product_code += $(this).val()+'|';
				});
				$('.stk-min').each(function() {
					stk_min.push($(this).val());
					//_stk_min += $(this).val()+'|';
				});
				$('.stk-max').each(function() {
					stk_max.push($(this).val());
					//_stk_max += $(this).val()+'|';
				});
				var err_qty = 0;
				$('.qty').each(function() {	
					//Validacion de checks con pedidos mayores a cero				
					// if (product_check[i_qty] == 'S')  {
					// 	if (!$.isNumeric($(this).val())) {
					// 		alert('Cantidad inválida del producto ' + product_code[i_qty]);
					// 		err_qty++;
					// 		return false;
					// 	} else if ($(this).val() <= 0) {
					// 		alert('Cantidad inválida del producto ' + product_code[i_qty]);
					// 		err_qty++;
					// 		return false;
					// 	}
					// }
					// i_qty++;
					//Cerrar validacion de checks con pedidos mayores a cero
					qty.push($(this).val());

					//console.log('qty: '+$(this).val()+' - product_check['+i_qty+']: '+product_check[i_qty]);
					//_qty += $(this).val()+'|';
				});

				if (err_qty == 0) {
					var params = {
						rqst: 'REPORTES.PEDIDOCOMPRAS',
						cab_almacen: $('.cab_almacen').val(),	
						cab_nropedido: $('.cab_nropedido').val(),
						cab_tipo: $('.cab_tipo').val(),											
						cab_observacion: $('.cab_observacion').val(),						
						action: 'ModificarPedido',
						/*product_check: _product_check,
						product_code: _product_code,
						stk_min: _stk_min,
						stk_max: _stk_max,
						qty: _qty,*/
						data: JSON.stringify({
							"id_det": id_det,
							"vec_check": product_check, "codigo": product_code,
							"stk_minimo": stk_min, "stk_maximo": stk_max, "pedido": qty,
						}),
					};
					console.log('params: ', params);

					$.ajax({
						url: 'control.php',
						type: 'POST',
						dataType: 'json',
						data: params,
					})
					.done(function(data) {
						$('#cargardor').css({'display':'none'});
						//console.log(data);
						//console.log("success");
						if (!data.error) {
							alert(data.message);
							window.location = 'vta_pedido_compras.php';
						} else {
							alert(data.message);
						}
					})
					.fail(function(err) {
						$('#cargardor').css({'display':'none'});
						//console.log(err);
						//console.log("error");
					});
				}
			}

			function isInt(n){
				return Number(n) === n && n % 1 === 0;
			}

			function isFloat(n){
				return Number(n) === n && n % 1 !== 0;
			}
		</script>
	</head>
	<body>
		<?php include "../menu_princ.php"; ?>
		<div id="content">
			<script language="JavaScript" src="/sistemaweb/js/calendario.js"></script>
			<script language="JavaScript" src="/sistemaweb/js/overlib_mini.js"></script>					
			<div id="content_title">&nbsp;</div>
			<div id="content_body">&nbsp;</div>
			<div id="content_footer">&nbsp;</div>						
		</div>
		<div id="footer">&nbsp;</div>
		<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.PEDIDOCOMPRAS" frameborder="1" width="10" height="10"></iframe>
	</body>
</html>
