/**
 * Orden de Compra
 * Eventos y funciones
 * kwn
 */
var url;
$(document).ready(function() {
	//IMPORTANTE, evaluar que TAGs existen
	$( "#consult-initial-date" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	$( "#consult-end-date" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	$( "#order-date" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	$( "#order-date-delivery" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	$( "#order-date-movement" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	$( "#export-initial-date" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});
	
	$( "#export-end-date" ).datepicker({
		dateFormat: 'dd/mm/yy'
	});

	if ($('#order-observation').length) {
		$('#order-observation').click(function() {
			$(".order-content-observation").toggle(this.checked);
		});
		if (document.getElementById('order-observation').checked) {
			$(".order-content-observation").toggle(true);
		}
	}

	if ($('#order-perception').length) {
		$('#order-perception').click(function() {
			$(".order-content-perception").toggle(this.checked);
		});
		if (document.getElementById('order-perception').checked) {
			$(".order-content-perception").toggle(true);
		}
	}

	if ($('#order-freight').length) {
		$('#order-freight').click(function() {
			$(".order-content-freight").toggle(this.checked);
		});
		if (document.getElementById('order-freight').checked) {
			$(".order-content-freight").toggle(true);
		}
	}

	if ($('#include-merchandise-order-id').length) {
		$('#include-merchandise-order-id').val('');
	}

	$(document).on('click', '.btn-search-orders', function(event) {
		searchOrders();
	});

	$(document).on('click', '.btn-ref-new-orders', function(event) {
		window.location = '/sistemaweb/compras/ordencompra.php?action=add';
	});

	$(document).on('click', '.btn-add-order-cancel', function(event) {
		window.location = '/sistemaweb/compras/ordencompra.php';
	});

	$(document).on('click', '.btn-order-merchandise-order', function(event) {
		console.log('pre');
		getProductsByMerchandiseOrder();
	});

	$(document).on('click', '#btn-save-order', function(event) {
		console.log('save');
		saveOrderPurchase();
	});

	$( '#order-currency' ).change(function(){
		$( '#order-exangerate' ).removeClass('required');
		if ( $(this).val() === '000002' )//Dolarés
			$( '#order-exangerate' ).addClass('required');
	});
});

//Funciones
function searchOrders() {
	$('.container-result-order').html(loading());
	var params = {
		action: 'search-orders',
		initial_date: $('#consult-initial-date').val(),
		end_date: $('#consult-end-date').val(),
	}

	$.ajax({
		url: '/sistemaweb/compras/ordencompra.php',
		type: 'POST',
		dataType: 'html',
		data: params,
		success: function(data) {
			console.log(data);
			$('.container-result-order').html(data);
		}
	});
}

function autocompleteBridge(type) {
	console.log('type: '+type);
	if (type == 0) {
		var bpartner = $("#order-bpartner-text");
		if(bpartner.val() !== undefined) {
			generalAutocomplete('#order-bpartner-text', '#order-bpartner-id', 'getPartnersByRucOrName', []);
		}
	} else if (type == 1) {
		var bpartner = $("#order-carrier-text");
		if(bpartner.val() !== undefined) {
			generalAutocomplete('#order-carrier-text', '#order-carrier-id', 'getPartnersByRucOrName', []);
		}
	} else if (type == 2) {
		var bpartner = $("#in-order-product-name");
		if(bpartner.val() !== undefined) {
			generalAutocomplete('#in-order-product-name', '#in-order-product-id', 'getProductXByCodeOrName', []);
		}
	}
}

function getProductsByMerchandiseOrder() {
	console.log('->');
	$('.msg-process-order').html('');

	if ($('#include-merchandise-order-id').val() != '') {
		$('.msg-process-order').html('Ya existe agregado un Pedido de Mercadería');
		return false;
	}

	if ($('#order-merchandise-order-id').val().length === 0) {
		$( '.help-block' ).html('Ingresar valor');
	} else {
		$( '.help-block' ).html('');

		$('.container-result-order').html(loading());
		var params = {
			action: 'search-merchandise-order',
			id: $('#order-merchandise-order-id').val(),
		}

		$('.msg-process-order').html('');
		var html = '';
		$.ajax({
			url: '/sistemaweb/compras/ordencompra.php',
			type: 'POST',
			dataType: 'json',
			data: params,
			success: function(data) {
				console.log('sql');
				console.log(data);
				if (!data.error) {
					$('#include-merchandise-order-id').val($('#order-merchandise-order-id').val());
					var merchandise = data.data;
					for (var i = 0; i < merchandise.length; i++) {
						html += lineInputOrderPurchase(merchandise[i]);
					};
					$('.order-add-tbody').prepend(html);
				} else {
					html = `
<div align="center" class="alert alert-warning">
	<div>${data.message}</div>
</div>`;
					$('.msg-process-order').html(html);
				}
			}
		});
	}
}

function lineInputOrderPurchase(merchandise) {
	var html = `
<tr class="it-tr-${merchandise.art_codigo} order-tr-${merchandise.num_pedido}">
	<td><input type="text" id="it-order-product-id[]" value="${merchandise.art_codigo}" size="18" readonly></td>
	<td><input type="text" value="${merchandise.art_descripcion}" size="32"></td>
	<td><input type="text" id="it-order-product-uom[]" value="${merchandise.art_unidad}" size="8"></td>
	<td><input type="text" id="in-order-product-quantity[]" value="${merchandise.ped_cantidad}" size="10"></td>
	<td><input type="text" id="in-order-product-unitcost[]" size="10"></td>
	<td><input type="text" id="in-order-product-discount[]" size="10"></td>
	<td><input type="text" id="in-order-product-subtotal[]" size="10"></td>
	<td><button onclick="removeFormNewProduct('${merchandise.art_codigo}')">Quitar</button></td>
</tr>`;
	return html;
}

function saveOrderPurchase() {
	updateSubtotal();
	//validar g_subtotal y otros acumulados
	console.log('->');
	// Validacion de Formulario
	var verify_inputs_required = true;
	var action = 'save-order';

	$(".required").each(function(){
		if ( $($(this)).val().length === 0) {
			verify_inputs_required = false;
			$( "#" + $(this)[0]['id'] ).addClass('has-danger');
		} else
			$( "#" + $(this)[0]['id'] ).removeClass('has-danger');
	});

	if ( (document.getElementById('order-observation').checked) && $( '[name="textarea-observation"]' ).val().length === 0 ) {
		$( '.span-observation' ).html('Ingresar observación');
	} else if ( (document.getElementById('order-perception').checked) && $( '#order-value-perception' ).val().length === 0 ) {
		$( '#order-value-perception' ).addClass('has-danger');
	} else if ((document.getElementById('order-freight').checked) && $( '#order-plate' ).val().length === 0 ) {
		$( '.span-plate' ).html('Ingresar placa');
	} else if ((document.getElementById('order-freight').checked) && $( '#order-license' ).val().length === 0 ) {
		$( '.span-license' ).html('Ingresar licencia');
	} else if ((document.getElementById('order-freight').checked) && $( '#order-certificate-inscription' ).val().length === 0 ) {
		$( '.span-certificate-inscription' ).html('Ingresar Certificado de Inscripciòn');
	} else if ((document.getElementById('order-freight').checked) && ($( '#order-certificate-inscription' ).val().length === 0 || $( '#order-carrier-id' ).val().length === 0) ) {
		$( '.span-carrier-id' ).html('Ingresar Transportista');
	} else if (verify_inputs_required) {
		$( '.help-block' ).html('');
		$( '#order-value-perception' ).removeClass('has-danger');

		var arrHeaderOrderPurchase = {
			type : '01',
			serie: $( '#order-warehouse' ).val(),
			order_date: sTypeDate('fecha_dmy', $( '#order-date' ).val(), '/'),
			number: $( '#hidden-number-order' ).val(),
			bpartner_id: $( '#order-bpartner-id' ).val(),
			currency: $( '#order-currency' ).val(),
			exangerate: $( '#order-exangerate' ).val(),
			isCredit: $( '[name="is-credit"]:checked' ).attr('value'),
			invoiceText: $( '#order-invoice-text' ).val(),
			tendertype: $( '#order-tendertype' ).val(),
			comment: $( '#order-comment' ).val(),
			dateDelivery: sTypeDate('fecha_dmy', $( '#order-date-delivery' ).val(), '/'),
			observation: $( '[name="textarea-observation"]' ).val(),
			isPerception: document.getElementById('order-perception').checked ? true : false,
			perception: $( '#order-value-perception' ).val(),
			subtotal: $('#g_subtotal').val(),
		}

		console.log('arrHeaderOrderPurchase');
		console.log(arrHeaderOrderPurchase);

		var arrFreightageOrderPurchase = {
			orderFreight: document.getElementById('order-freight').checked ? true : false,
			orderDateTransfer: sTypeDate('fecha', $( '#order-date-transfer' ).val(), '/'),
			orderReasonTransfer: $( '#order-reason-transfer' ).val(),
			orderPlate: $( '#order-plate' ).val(),
			orderLicense: $( '#order-license' ).val(),
			orderCertificateInscription: $( '#order-certificate-inscription' ).val(),
			orderCarrierId: $( '#order-carrier-id' ).val(),
		}

		var arrDetailOrderPurchase = [];
		var arrValidarNumerosEnCero = [];
		var counterNumerosEnCero = 0;

		$("#table-order_purcharse_detail > tbody > tr").each(function(){
			var ID_Producto = $('td:eq(0) input', this).val();
			var No_Producto = $('td:eq(1) input', this).val();
			var ID_Unidad_Medida = $('td:eq(2) input', this).val();
			var Qt_Producto = $('td:eq(3) input', this).val();
			var Ss_Costo_Unitario = $('td:eq(4) input', this).val();
			var Ss_Descuento = $('td:eq(5) input', this).val();
			var Ss_SubTotal = $('td:eq(6) input', this).val();
			console.log(Ss_Costo_Unitario);

			if ( ((isNaN(parseFloat(Qt_Producto))) || parseFloat(Qt_Producto) == 0) || ((isNaN(parseFloat(Ss_Costo_Unitario))) || parseFloat(Ss_Costo_Unitario) == 0)  || ((isNaN(parseFloat(Ss_SubTotal))) || parseFloat(Ss_SubTotal) == 0)){
				arrValidarNumerosEnCero[counterNumerosEnCero] = ID_Producto;
				$('.it-tr-' + ID_Producto).addClass('alert-danger');
			}

			var obj = {};
			/*obj.type = '01';
			obj.serie = $( '#order-warehouse' ).val();
			obj.number = $( '#hidden-number-order' ).val(),*/
			obj.product_id = ID_Producto;
			obj.product_name = No_Producto;
			obj.uom = ID_Unidad_Medida;
			obj.qty = Qt_Producto;
			obj.cost = Ss_Costo_Unitario;
			obj.disc = Ss_Descuento == '' ? 0.0 : Ss_Descuento;
			obj.subtotal = Ss_SubTotal;

			arrDetailOrderPurchase.push(obj);
			counterNumerosEnCero++;
		});

		var tr_foot = '';

		console.log('entro ');

		$('.msg-process-purchase').html('');

		//agregar los mensaje de compra en $('.msg-process-purchase')

		console.log(arrValidarNumerosEnCero);
		if (arrDetailOrderPurchase.length == 0) {
			$( '.tr-sin_detalle' ).remove();
			console.log('sin detalle');
			tr_foot +=
			'<tr class="tr-sin_detalle">'
				+'<th colspan="8">'
					+'<div align="center" class="alert alert-danger">'
						+'<div>Sin detalle</div>'
					+'</div>'
				+'</th>'
			+'</tr>';
			//$( '#table-order_purcharse_detail > tbody' ).after(tr_foot);
			$('.msg-process-purchase').html(tr_foot);
		} else if (arrValidarNumerosEnCero.length > 0) {
			$( '.tr-valores_cero' ).remove();
			console.log('productos con cantidad / precio / total en cero');
			tr_foot +=
			'<tr class="tr-valores_cero">'
				+'<th colspan="8">'
					+'<div align="center" class="alert alert-danger">'
						+'<div>Item(s) con Cantidad / Costo Unitario / SubTotal en cero</div>'
					+'</div>'
				+'</th>'
			+'</tr>';
			//$( '#table-order_purcharse_detail > tbody' ).after(tr_foot);
			$('.msg-process-purchase').html(tr_foot);
		} else {
			console.log('bien !');
			$( '.tr-sin_detalle' ).remove();
			$( '.tr-valores_cero' ).remove();

			var params = {
				action: action,
				arrHeaderOrderPurchase: arrHeaderOrderPurchase,
				arrFreightageOrderPurchase: arrFreightageOrderPurchase,
				arrDetailOrderPurchase: arrDetailOrderPurchase
			};

			console.log(params);
			$.ajax({
				url: '/sistemaweb/compras/ordencompra.php',
				type: 'POST',
				dataType: 'json',
				data: params,
				success: function(data) {
					console.log(data);
					if (!data.error) {
						alert(data.message);
						window.location = '/sistemaweb/compras/ordencompra.php';
					} else {
						alert(data.message);
					}
				}
			});
	    }
	}
}

function addFormNewProduct() {
	//validaciones
	var id = $('#in-order-product-id');
	var name = $('#in-order-product-name');
	var uom = $('#in-order-product-uom');
	var quantity = $('#in-order-product-quantity');
	var unitcost = $('#in-order-product-unitcost');
	var discount = $('#in-order-product-discount');
	var subtotal = $('#in-order-product-subtotal');

	var html = '<tr class="it-tr-'+id.val()+'">'
		+'<td><input type="text" id="it-order-product-id[]" value="'+id.val()+'" size="18" readonly></td>'
		+'<td><input type="text" value="'+name.val()+'" size="32"></td>'
		+'<td><input type="text" id="it-order-product-uom[]" value="'+uom.val()+'" size="8"></td>'
		+'<td><input type="text" id="in-order-product-quantity[]" value="'+quantity.val()+'" size="10"></td>'
		+'<td><input type="text" id="in-order-product-unitcost[]" value="'+unitcost.val()+'" size="10"></td>'
		+'<td><input type="text" id="in-order-product-discount[]" value="'+discount.val()+'" size="10"></td>'
		+'<td><input type="text" id="in-order-product-subtotal[]" value="'+subtotal.val()+'" size="10"></td>'
		+'<td align="center"><button onclick="removeFormNewProduct(\''+id.val()+'\')">Quitar.</button></td>'
	+'</tr>';
	$('.order-add-tbody').append(html);
	id.val('');
	name.val('');
	uom.val('');
	quantity.val('');
	unitcost.val('');
	discount.val('');
	subtotal.val('');
	updateSubtotal();
}

function removeFormNewProduct(product_id) {
	$('.it-tr-'+product_id).remove();

	if ($('#include-merchandise-order-id').val() != '') {
		if ($('.order-tr-'+$('#include-merchandise-order-id').val()).length <= 0) {
			$('#include-merchandise-order-id').val('');
		}
	}
	updateSubtotal();
}

function updateSubtotal() {
	console.log('updateSubtotal');
	var g_qty = 0, g_cost_unit = 0, g_desc = 0, g_subtotal = 0;
	//#in-order-product-
	$("#table-order_purcharse_detail > tbody > tr").each(function() {
		var qty = $('td:eq(4) input', $(this)).val();
		var cost_unit = $('td:eq(4) input', $(this)).val();
		var desc = $('td:eq(5) input', this).val();
		var subtotal = $('td:eq(6) input', this).val();
		console.log('Encontrado: cost_unit: '+cost_unit+', desc: '+desc+', subtotal: '+subtotal);

		g_qty += isNaN(parseFloat(qty)) ? 0 : parseFloat(qty);
		g_cost_unit += isNaN(parseFloat(cost_unit)) ? 0 : parseFloat(cost_unit);
		g_desc += isNaN(parseFloat(desc)) ? 0 : parseFloat(desc);
		g_subtotal += isNaN(parseFloat(subtotal)) ? 0 : parseFloat(subtotal);
	});
	$('#g_qty').val(g_qty);
	$('#g_cost_unit').val(g_cost_unit);
	$('#g_desc').val(g_desc);
	$('#g_subtotal').val(g_subtotal);
}

function reportResumePDF() {
	var initialDate = sTypeDate('fecha', $('#consult-initial-date').val(), '/');
	var endDate = sTypeDate('fecha', $('#consult-end-date').val(), '/');
	window.location = '/sistemaweb/compras/ordencompra.php?action=report-resume-pdf&initial_date='+initialDate+'&end_date='+endDate;
}

function calcTotal() {
	var quantity = parseFloat($('#in-order-product-quantity').val());
	quantity = isNaN(quantity) ? 0 : quantity;
	var unitcost = parseFloat($('#in-order-product-unitcost').val());
	unitcost = isNaN(unitcost) ? 0 : unitcost;
	//var subtotal = parseFloat($('#in-order-product-subtotal').val());
	//subtotal = isNaN(subtotal) ? 0 : subtotal;

	subtotal = quantity * unitcost;
	$('#in-order-product-subtotal').val(subtotal);
}

function loading() {
	return '<div class="container-alert"><div align="center" class="alert alert-info">Cargando...</div></div>';
}