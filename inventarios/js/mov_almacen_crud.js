function activarOrden(tipo){
	$( '#tab_orden_no' ).addClass('is-active');
	$( '#tab_orden_si' ).removeClass('is-active');
	$( '#txt-tab_orden_si' ).val('0');
	$( '#div-detalle_producto' ).show();
	$( '#div-orden_compra' ).hide();

	//Btn agregar
	$( "#btn-save" ).prop( "disabled", true );

	$('#txt-Nu_BI_RC').val(0);
	$('#txt-Nu_IGV_RC').val(0);
	$('#txt-Nu_Totacl_RC').val(0);

	$('#label-Nu_BI_RC').val(0);
	$('#label-Nu_IGV_RC').val(0);
	$('#label-Nu_Totacl_RC').val(0);

	$('#txt-Nu_Cantidad_Tot_Actual').val(0.00);
	$('.txt-Nu_Cantidad_Tot_Actual').val(0.00);

	$('#txt-Nu_Total_SIGV_Tot_Actual').val(0.00);
	$('.txt-Nu_Total_SIGV_Tot_Actual').val(0.00);

	$('#txt-Nu_Total_CIGV_Tot_Actual').val(0.00);
	$('.txt-Nu_Total_CIGV_Tot_Actual').val(0.00);

	if (tipo == 1){
    	var No_Proveedor 			= $("#txt-No_Proveedor").val();
    	var Nu_Documento_Identidad 	= $("#txt-Nu_Documento_Identidad").val();

    	var Nu_Tipo_Documento_Compra 	= $("#cbo-Nu_Tipo_Documento_Compra").val();
    	var Nu_Serie_Compra 			= $("#txt-Nu_Serie_Compra").val();
    	var Nu_Numero_Compra 			= $("#txt-Nu_Numero_Compra").val();

		if (
    		No_Proveedor.length === 0
			&&
	    		(
	    			$TipoFormulario == '01' ||
	    			$TipoFormulario == '21' ||
	    			$TipoFormulario == '05'
	    		)
    		) {
    		$( '.help' ).show();
		    $( '.help' ).html("");
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').html('Ingresar Proveedor');
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (
	    	Nu_Documento_Identidad.length === 0
			&&
	    		(
	    			$TipoFormulario == '01' ||
	    			$TipoFormulario == '21' ||
	    			$TipoFormulario == '05'
	    		)
	    	) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-No_Proveedor' ).closest('.column').find('.help').html('Debe seleccionar un proveedor');
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Tipo_Documento_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#cbo-Nu_Tipo_Documento_Compra' ).closest('.column').find('.help').html('Debe seleccionar un tipo de documento');
			$( '#cbo-Nu_Tipo_Documento_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Serie_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Serie_Compra' ).closest('.column').find('.help').html('Ingresar serie');
			$( '#txt-Nu_Serie_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Numero_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Numero_Compra' ).closest('.column').find('.help').html('Ingresar numero');
			$( '#txt-Nu_Numero_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else {
	    	$( '.help' ).hide();
	    	$( '.help' ).html("");
			var block_loding_modal = $('<div class="block-loading" />');

			$( "#template-Movimiento_Inventario_Agregar" ).prepend(block_loding_modal);
			//Btn agregar
			$( "#btn-save" ).prop( "disabled", false );

			$( '#div-detalle_producto' ).hide();
			$( '#div-orden_compra' ).show();

			$( '#tab_orden_no' ).removeClass('is-active');
			$( '#tab_orden_si' ).addClass('is-active');
			$( '#txt-tab_orden_si' ).val('1');

			arrDataOrdenCabecera = {
				'nu_orden_compra' : $("#txt-Numero_Orden").val(),
				'pro_codigo' : $("#txt-Nu_Documento_Identidad").val(),
			};

			$.post( "../assets/helper.php", {
				accion 	: 'getOrdenesCompra',
		        arrData : arrDataOrdenCabecera,
			}, function(response){
				$( '#cbo-arrOrdenesCompra' ).html('');
				$( ".block-loading" ).remove();
				if(response.status == 'error'){
					$( '#cbo-arrOrdenesCompra' ).append( '<option>No hay datos</option>' );
				} else {
					arrOrdenesCompra = response.arrOrdenesCompra;
					for (var i = 0; i < arrOrdenesCompra.length; i++)
						$( '#cbo-arrOrdenesCompra' ).append( '<option value="' + arrOrdenesCompra[i].nu_orden_compra + arrOrdenesCompra[i].nu_codigo_producto + '">' + arrOrdenesCompra[i].nu_orden_compra + ' ' + arrOrdenesCompra[i].no_producto + '</option>' );
				}
			}, 'JSON')
		}
	}
}

function verCintillo(id_proveedor, Nu_Formulario, Nu_Tipo_Movimiento_Inventario, Fe_Emision, sIdTipoDocumento, sSerieDocumento, sNumeroDocumento){
	arrData = {
		'id_proveedor' 					: id_proveedor,
		'Nu_Formulario' 				: Nu_Formulario,
		'Nu_Tipo_Movimiento_Inventario' : Nu_Tipo_Movimiento_Inventario,
		'Fe_Emision' 					: Fe_Emision,
		'sIdTipoDocumento' : sIdTipoDocumento,
		'sSerieDocumento' : sSerieDocumento,
		'sNumeroDocumento' : sNumeroDocumento,
	};

	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');
	$( "#div-Movimiento_Inventario_Table" ).prepend(block_loding_modal);

	$.post( "../assets/helper.php", {
		accion 	: 'getCintillo',
        arrData : arrData,
	}, function(response){
		$( ".block-loading" ).remove();
		if(response.status == 'error'){
			$( '.message' ).removeClass('is-primary');
			$( '.message' ).addClass('is-danger');
			$( '.message-status' ).html(response.message);
		} else {
			$( '.modal-cintillo' ).show();
			$( '.message' ).addClass('is-primary');
			$( '.message' ).removeClass('is-danger');
			var arrCintillo = response.arrCintillo;
        	var row = 0;
        	var fila = 0;
        	var proveedor = (arrCintillo[0]['no_razon_social'] != null ? arrCintillo[0]['no_razon_social'] : '-');
        	var content	= ''
        	+ '<table class="bulma table report_CRUD">'
        		+ '<thead>'
	        		+ '<tr>'
	        			+ '<th class="text-center" colspan="8" style="background: #FFFFFF; color:#23201f"><b>' + arrCintillo[0]['no_tipo_operacion_inventario'] + '<b></th>'
	        		+ '</tr>'
	        		+ '<tr>'
	        			+ '<th class="text-center" colspan="8" style="background: #FFFFFF; color:#FFFFFF"></th>'
	        		+ '</tr>'
	        		+ '<tr>'
	        			+ '<th class="text-center">Alma. Ori.</th>'
	        			+ '<th class="text-center">Alma Dest.</th>'
	        			+ '<th class="text-center">Formulario</th>'
	        			+ '<th class="text-center">Fecha Emisión</th>'
	        			+ '<th class="text-center" colspan="2">Proveedor</th>'
	        			+ '<th class="text-center">Tipo</th>'
	        			+ '<th class="text-center">Serie</th>'
	        			+ '<th class="text-center">Número</th>'
	        		+ '</tr>'
	        		+ '<tr>'
	        			+ '<td class="text-center">' + arrCintillo[0]['alma_ori'] + '</td>'
	        			+ '<td class="text-center">' + arrCintillo[0]['alma_des'] + '</td>'
	        			+ '<td class="text-center">' + Nu_Formulario + '</td>'
	        			+ '<td class="text-center">' + Fe_Emision + '</td>'
	        			+ '<td class="text-center" colspan="2">' + proveedor + '</td>'
	        			+ '<td class="text-center">' + arrCintillo[0]['no_tipo_documento'] + '</td>'
	        			+ '<td class="text-center">' + arrCintillo[0]['nu_serie_documento'] + '</td>'
	        			+ '<td class="text-center">' + arrCintillo[0]['nu_numero_documento'] + '</td>'
	        		+ '</tr>'
	    		+ '</thead>'
	    		+'<tbody>'
	        		+ '<tr><td>&nbsp;</td></tr><tr>'
	        			+ '<th colspan="2" class="text-center">Producto</th>'
	        			+ '<th class="text-center">Cantidad</th>'
	        			+ '<th class="text-center">Costo Unitario</th>'
	        			+ '<th class="text-center">Valor Venta</th>'
	        			+ '<th class="text-center">Total</th>'
	        			+ '<th class="text-center">Margen</th>'
	        			+ '<th class="text-center">P.V.</th>'
	        			+ '<th class="text-center">Margen Real</th>'
	        		+ '</tr>';

			for (var i = 0; i < arrCintillo.length; i++) {
				fila = row++;
				content	+= ''
					+ '<tr>'
						+ '<td colspan="2" class="text-left" align="left">' +  arrCintillo[fila]['no_producto'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['nu_cantidad'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['nu_costo_unitario'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['nu_total_sigv'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['nu_total_cigv'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['margen'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['precio_compra'] + '</td>'
						+ '<td class="text-right" align="right">' +  arrCintillo[fila]['margen_real'] + '</td>'
					+ '</tr>';
			}

			content += ''
				+ '</tbody>'
				+ '<tr><td>&nbsp;</td></tr>'
        		+ '<thead>'
	        		+ '<tr>'
	        			+ '<th class="text-center" style="background-color:white" colspan="6">&nbsp;</th>'
	        			+ '<th class="text-center">Valor Venta</th>'
	        			+ '<th class="text-center">I.G.V.</th>'
	        			+ '<th class="text-center">Total</th>'
	        		+ '</tr>'
					+ '<tr>'
	        			+ '<td class="text-right" style="background-color:white" colspan="6">&nbsp;</td>'
						+ '<td class="text-right" align="right">' +  response.Ss_base_imponible + '</td>'
						+ '<td class="text-right" align="right">' +  response.Ss_igv + '</td>'
						+ '<td class="text-right" align="right">' +  response.Ss_total + '</td>'
					+ '</tr>'
				+'</thead>'
			+'</table>';
			$( '.message-status' ).html(content);
	    }
	}, 'JSON')
}

function printDiv(){
    var divToPrintCintillo = '';
    divToPrintCintillo=document.getElementById('message-status-print');
    var newWin=window.open('','Print-Window');
    newWin.document.open();
    newWin.document.write('<html><body onload="window.print()">'+divToPrintCintillo.outerHTML+'</body></html>');
    newWin.document.close();
    setTimeout(function(){newWin.close();},10);
}

var save_accion;

$( function() {
	/* Conversion de GLP Compras */
	$('.checkbox-conversionGLP').hide();
	$('.div-conversionGLP').hide();
	$('.div-datosComplementarios').hide();
	$('.div-LotePedidoVencimiento').hide();

	var Nu_Kilos 				= $( "#txt-Nu_Kilos" );
	var Nu_Gravedad_Especifica 	= $( "#txt-Nu_Gravedad_Especifica" );
	var Nu_Galones_GLP 			= $( "#txt-Nu_Galones_GLP" );

	if(Nu_Kilos.val() !== undefined)
		Nu_Kilos.keyup (function() { calcularConversionGLP() });

	if(Nu_Gravedad_Especifica.val() !== undefined)
		Nu_Gravedad_Especifica.keyup (function() { calcularConversionGLP() });

	if(Nu_Galones_GLP.val() !== undefined)
		Nu_Galones_GLP.keyup (function() { calcularConversionGLP2() });

	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');

	$(window).load(function(){
		$('.block-loading').fadeOut('slow',function(){$(this).remove();});
	});

	$( '#template-Movimiento_Inventario_Agregar' ).hide();
	$( '.div-table_producto' ).hide();
	$( '.div-RegistroCompras').hide();

	/* Buscar ORDENES DE COMPRA CABECERA */
	$( "#btn-buscarOrdenCompra" ).click(function(){
		$( '#btn-buscarOrdenCompra' ).attr('disabled', true);
		$( '#btn-buscarOrdenCompra' ).addClass('is-loading');

		arrDataOrdenCabecera = {
			'nu_orden_compra' : $("#txt-Numero_Orden").val(),
			'pro_codigo' : $("#txt-Nu_Documento_Identidad").val(),
		};

		$.post( "../assets/helper.php", {
			accion 	: 'getOrdenesCompra',
	        arrData : arrDataOrdenCabecera,
		}, function(response){
			$( '#cbo-arrOrdenesCompra' ).html('');
			if(response.status == 'error'){
				$( '#cbo-arrOrdenesCompra' ).append( '<option>No hay datos</option>' );
			} else {
				arrOrdenesCompra = response.arrOrdenesCompra;
				for (var i = 0; i < arrOrdenesCompra.length; i++)
					$( '#cbo-arrOrdenesCompra' ).append( '<option value="' + arrOrdenesCompra[i].nu_orden_compra + arrOrdenesCompra[i].nu_codigo_producto + '">' + arrOrdenesCompra[i].nu_orden_compra + ' ' + arrOrdenesCompra[i].no_producto + '</option>' );
			}

			$( '#btn-buscarOrdenCompra' ).removeClass('is-loading');
			$( '#btn-buscarOrdenCompra' ).attr('disabled', false);
		}, 'JSON')
	})

	/* Buscar ORDENES DE COMPRA CON DETALLE */
	$( "#cbo-arrOrdenesCompra" ).click(function(){
	    if( isExistTableTemporalOrdenCompra($.trim($(this).val())) ){
	    	$( '#msg-orden_compra' ).html('<br><b>Ya existe orden compra</b>');
	    } else {
	    	$( '#msg-orden_compra' ).html('');
			$.post( "../assets/helper.php", {
				accion 	: 'getOrdenCompraDetalle',
				Nu_Almacen_Interno : $( '#txt-Nu_Almacen_Interno' ).val(),
		        nu_orden_compra_codigo_producto : $(this).val(),
			}, function(response){

				var ymargen = 0;

				if (response.status2 == 'success'){
				
				var xprecio = (response.arrMargen["precio"]);
				var xmargen = parseFloat(response.arrMargen["margen"]);
				ymargen = 0;

				var arrOrdenesCompraDetalle = response.arrOrdenesCompraDetalle;
					for (var i = 0; i < arrOrdenesCompraDetalle.length; i++) {
						var art_costo_uni = arrOrdenesCompraDetalle[i].ss_costo_unitario;
					}

				
				if(art_costo_uni != '0' || art_costo_uni != '0.00' || art_costo_uni > 0){	
				
				ymargen = parseFloat(((((xprecio/1.18)-art_costo_uni))/art_costo_uni)*100); 

				var wmargen  = Math.ceil(Math.log10(ymargen + 1)); 

				if (wmargen > 6){
					ymargen = 999999;
					//al registrar transferencias gratuitas por ingreso de compras generarn un margen mayor al soportado por la columnda de la BD.
				}

				if(xmargen > ymargen){
						$( '.help' ).show();
					    $( '.help' ).html("");

						$( '#table-MargenGanancia >tbody' ).empty();

						$( '.modal-MargenGanancia' ).show();
						$( '.message' ).addClass('is-primary');
						$( '.message' ).removeClass('is-danger');
						var tr_body =
						"<tr>"
				        	+"<td colspan='6' style='font-size: 9px'><strong>El costo unitario ingresado genera un margen menor al establecido por la linea, verificar precio de venta.</strong></td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Precio de venta actual:</strong></td>"
				        	+"<td class='text-left' align='left'>" + xprecio + "</td>"
				        	+"<td><strong>Margen actual de la linea:</strong></td>"
				            +"<td class='text-left' align='left'>" + xmargen + "</td>"
				            +"<td><strong>Margen en base al costo ingresado:</strong></td>"
				            +"<td class='text-left' align='left'>" + ymargen.toFixed(4) + "</td>"
				        +"</tr>"
		        		$( '#table-MargenGanancia >tbody' ).append(tr_body);
		  					
						return false;
					}
				}
			} 

				//pendiente enviar la serie tabmien de la orden compra
				if (response.status == 'success'){
					var arrOrdenesCompraDetalle = response.arrOrdenesCompraDetalle;
					for (var i = 0; i < arrOrdenesCompraDetalle.length; i++) {
						var tr_body =
				        "<tr id='tr_detalle_orden_compra" + arrOrdenesCompraDetalle[i].nu_orden_compra + arrOrdenesCompraDetalle[i].nu_codigo_producto + "'>"
				        	+"<td class='text-center' style='display:none;'>" + arrOrdenesCompraDetalle[i].nu_orden_compra + arrOrdenesCompraDetalle[i].nu_codigo_producto + "</td>"
				        	+"<td class='text-center'>" + arrOrdenesCompraDetalle[i].nu_serie_orden_compra + "</td>"
				        	+"<td class='text-center'>" + arrOrdenesCompraDetalle[i].nu_orden_compra + "</td>"
				        	+"<td class='text-center'>" + arrOrdenesCompraDetalle[i].nu_codigo_producto + "</td>"
				        	+"<td class='text-center'>" + arrOrdenesCompraDetalle[i].no_producto + "</td>"
				        	+"<td class='text-right' align='right'>" + arrOrdenesCompraDetalle[i].qt_cantidad_pedida + "</td>"
				        	+"<td class='text-right' align='right'>" + arrOrdenesCompraDetalle[i].qt_cantidad_atendida + "</td>"
				        	+"<td class='text-right' align='right'>" + arrOrdenesCompraDetalle[i].ss_costo_unitario + "</td>"
				        	+"<td class='text-right' align='right'>" + arrOrdenesCompraDetalle[i].ss_total_sigv + "</td>"
				        	+"<td class='text-right' align='right'>" + (arrOrdenesCompraDetalle[i].ss_total_sigv * $('#txt-Nu_IGV').val()).toFixed(2) + "</td>"
				        	+"<td class='text-right' align='right'>" + arrOrdenesCompraDetalle[i].qt_stock_actual + "</td>"
				        	+"<td class='text-right' align='right' style='display:none'>" + parseFloat(ymargen).toFixed(4) + "</td>"
				        	+"<td class='text-center' align='center'><button type='button' id='btn-deleteOrdenCompra' class='button is-danger is-small icon-size btn-danger btn-deleteProducto'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td>"      	
				        +"<tr>";
		        		$( '#table-OrdenCompra >tbody' ).append(tr_body);
		        	}
				}
				var fCantidad = 0.00;
				var fTotal = 0.00;
				var fTotalIGV = 0.00;
				$("#table-OrdenCompra tbody tr").each(function(){
			        var rows = $(this);
			        var iSerieOrdenCompra	= rows.find("td:eq(1)").text();
			        var iNumeroOrdenCompra	= rows.find("td:eq(2)").text();
			        if (iSerieOrdenCompra.length > 0 && iNumeroOrdenCompra.length > 0) {
				        fCantidad += parseFloat(rows.find("td:eq(5)").text());
				        fTotal += parseFloat(rows.find("td:eq(8)").text());
				        fTotalIGV += parseFloat(rows.find("td:eq(9)").text());
				    }
				    var Nu_Margen_Real			= rows.find("td:eq(11)").text();	
			    })
				$('#table-OrdenCompra tfoot').empty();
			    var tr_foot =
			    "<tr>"
			    	+"<td class='text-right' align='right' colspan='4'>Total </td>"
			    	+"<td class='text-right' align='right'>" + fCantidad.toFixed(4) + "</td>"
			    	+"<td></td>"
			    	+"<td></td>"
			    	+"<td class='text-right' align='right'>" + fTotal.toFixed(4) + "</td>"
			    	+"<td class='text-right' align='right'>" + fTotalIGV.toFixed(2) + "</td>"
			    +"</tr>";
		        $( '#table-OrdenCompra >tfoot' ).append(tr_foot);

				$('#txt-Nu_BI_RC').val(0);
				$('#txt-Nu_IGV_RC').val(0);
				$('#txt-Nu_Totacl_RC').val(0);

				$('#label-Nu_BI_RC').val(0);
				$('#label-Nu_IGV_RC').val(0);
				$('#label-Nu_Totacl_RC').val(0);

				$('#txt-Nu_BI_RC').val(fTotal.toFixed(4));
				$('#txt-Nu_IGV_RC').val((fTotalIGV - fTotal).toFixed(2));
				$('#txt-Nu_Totacl_RC').val(fTotalIGV.toFixed(2));

			    $('#label-Nu_BI_RC').val(fTotal.toFixed(4));
			    $('#label-Nu_IGV_RC').val((fTotalIGV - fTotal).toFixed(2));
			    $('#label-Nu_Totacl_RC').val(fTotalIGV.toFixed(2));
			}, 'JSON')
		}
	})
	
	//Cambiar precio venta
	$( "#btn-cambiarprecio" ).click(function(){
	    $( '#btn-cambiarprecio' ).text('');
	    $( '#btn-cambiarprecio' ).attr('disabled', true);
	    $( '#btn-cambiarprecio' ).append( '<label class="label-btn-name"> Guardando </label><i class="fa fa-refresh fa-spin fa-lg fa-fw"></i>' );
	    
		$.post( "../assets/helper.php", {
			accion: "updNewPrice",
			nu_tipo_lista_precio: $( '#txt-Nu_Tipo_Lista_Precio' ).val(),
			id_producto_nuevo: $( '#txt-ID_Producto_Nuevo' ).val(),
			ss_precio_venta_sugerido: $.trim($( '#txt-Ss_Precio_Sugerido' ).val()),
		}, function(data){
			if (data.status == 'success'){
				$( '.div-msg_PrecioVentaMargen' ).show();
				$( '.div-msg_PrecioVentaMargen' ).removeClass('is-danger');
				$( '.div-msg_PrecioVentaMargen' ).addClass('is-success');
				$( '#div-msg_PrecioVentaMargen' ).html('<b>' + data.message + '</b>');
			} else {
				$( '.div-msg_PrecioVentaMargen' ).hide();
				$( '.div-msg_PrecioVentaMargen' ).removeClass('is-success');
				$( '.div-msg_PrecioVentaMargen' ).addClass('is-danger');
				$( '#div-msg_PrecioVentaMargen' ).html('<b>' + data.message + '</b>');
			}
	        $( '#btn-cambiarprecio' ).text('');
	        $( '#btn-cambiarprecio' ).append( '<label class="label-btn-name">Cambiar Precio </label>' );
	        $( '#btn-cambiarprecio' ).attr('disabled', false);
		}, 'JSON')
	})
  
	$( '#table-OrdenCompra tbody' ).on('click', '#btn-deleteOrdenCompra', function(){
    	$(this).closest('tr').remove();
		$('#txt-Nu_BI_RC').val(0);
		$('#txt-Nu_IGV_RC').val(0);
		$('#txt-Nu_Totacl_RC').val(0);

		$('#label-Nu_BI_RC').val(0);
		$('#label-Nu_IGV_RC').val(0);
		$('#label-Nu_Totacl_RC').val(0);

		var fCantidad = 0.00;
		var fTotal = 0.00;
		var fTotalIGV = 0.00;
		$("#table-OrdenCompra tbody tr").each(function(){
	        var rows = $(this);
	        var iSerieOrdenCompra	= rows.find("td:eq(1)").text();
	        var iNumeroOrdenCompra	= rows.find("td:eq(2)").text();
	        if (iSerieOrdenCompra.length > 0 && iNumeroOrdenCompra.length > 0) {
		        fCantidad += parseFloat(rows.find("td:eq(5)").text());
		        fTotal += parseFloat(rows.find("td:eq(8)").text());
		        fTotalIGV += parseFloat(rows.find("td:eq(9)").text());
		    }
		    var Nu_Margen_Real = rows.find("td:eq(11)").text();
	    })
		$('#table-OrdenCompra tfoot').empty();
	    var tr_foot =
	    "<tr>"
	    	+"<td class='text-right' align='right' colspan='4'>Total </td>"
	    	+"<td class='text-right' align='right'>" + fCantidad.toFixed(4) + "</td>"
	    	+"<td></td>"
	    	+"<td></td>"
	    	+"<td class='text-right' align='right'>" + fTotal.toFixed(4) + "</td>"
	    	+"<td class='text-right' align='right'>" + fTotalIGV.toFixed(2) + "</td>"
	    +"</tr>";
        $( '#table-OrdenCompra >tfoot' ).append(tr_foot);

		$('#txt-Nu_BI_RC').val(fTotal.toFixed(4));
		$('#txt-Nu_IGV_RC').val((fTotalIGV - fTotal).toFixed(2));
		$('#txt-Nu_Totacl_RC').val(fTotalIGV.toFixed(2));

	    $('#label-Nu_BI_RC').val(fTotal.toFixed(4));
	    $('#label-Nu_IGV_RC').val((fTotalIGV - fTotal).toFixed(2));
	    $('#label-Nu_Totacl_RC').val(fTotalIGV.toFixed(2));
    })

	$( "#btn-buscar" ).click(function(){
		$( "#div-Movimiento_Inventario_Table" ).show();
		$( "#div-Movimiento_Inventario_Table" ).prepend(block_loding_modal);

		$( '#btn-buscar' ).attr('disabled', true);
		$( '#btn-buscar' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 		: $('#cbo-almacen option:selected').val(),
	        Fe_Inicial 		: $('#txt-fe_inicial').val(),
	        Fe_Final 		: $('#txt-fe_final').val(),
	        Nu_Documento 	: $('#txt-Nu_Documento').val().toUpperCase(),
	        No_Producto 	: $('#txt-No_Producto').val(),
	        No_Proveedor 	: $('#txt-No_Proveedor').val(),
	        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario').val()
		};

		$.post( "reportes/c_mov_almacen_crud.php", {
			accion 	: 'listAll',
       		data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-Movimiento_Inventario_Table" ).html(data);

			$( '#btn-buscar' ).removeClass('is-loading');
			$( '#btn-buscar' ).attr('disabled', false);
		})
	});

	$( "#btn-excel" ).click(function(e) {
		var No_Tipo_Movimiento_Inventario = $.trim($('#No_Tipo_Movimiento_Inventario').text());

		$( "#div-Movimiento_Inventario_Table" ).show();
		$( "#div-Movimiento_Inventario_Table" ).prepend(block_loding_modal);

		$( '#btn-excel' ).attr('disabled', true);
		$( '#btn-excel' ).addClass('is-loading');

		var data = {
	        Nu_Almacen 		: $('#cbo-almacen option:selected').val(),
	        Fe_Inicial 		: $('#txt-fe_inicial').val(),
	        Fe_Final 		: $('#txt-fe_final').val(),
	        Nu_Documento 	: $('#txt-Nu_Documento').val().toUpperCase(),
	        No_Producto 	: $('#txt-No_Producto').val(),
	        No_Proveedor 	: $('#txt-No_Proveedor').val(),
	        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario').val()
		};

		$.post( "reportes/c_mov_almacen_crud.php", {
			accion 	: 'listAll',
       		data 	: data,
       		page 	: 1
		}, function(data){
			$( "#div-Movimiento_Inventario_Table" ).html(data);
		})

		$.post( "reportes/c_mov_almacen_crud.php", {
			accion 	: 'listAllExcel',
       		data 	: data,
		}, function(data){
			$( '#btn-excel' ).removeClass('is-loading');
			$( '#btn-excel' ).attr('disabled', false);

			$( "#div-excel" ).hide();
			$( "#div-excel" ).html(data);
			var No_Archivo_Excel = No_Tipo_Movimiento_Inventario + '_' + $('#txt-fe_inicial').val() + '-' + $('#txt-fe_final').val();

			e.preventDefault();
	         $("#div-excel").table2excel({
	            exclude: ".noExl",
	            name: "demo",
	            filename : No_Archivo_Excel
	        });
		})
	})

	$( "#btn-agregar" ).click(function() {
		ValidarCamposCRUD();

		$( "#template-Movimiento_Inventario" ).hide();
		$( "#div-Movimiento_Inventario_Table" ).hide();
		$( '#template-Movimiento_Inventario_Agregar' ).show();

		$( '#div-detalle_producto' ).show();
		$( '#div-orden_compra' ).hide();

		$( '#tab_orden_no' ).addClass('is-active');
		$( '#tab_orden_si' ).removeClass('is-active');

		$( '#txt-tab_orden_si' ).val('0');

		$('#table-OrdenCompra tbody').empty();
		$('#table-OrdenCompra tfoot').empty();

		$( ".MsgData" ).hide();
		$( '.div-msg_PrecioVentaMargen' ).hide();

		//$( "#btn-save" ).classList.add('is-primary');cai

		$(".btn-deleteProducto").prop( "disabled", false );
		$( "#btn-addProducto" ).prop( "disabled", false );
		
    	$( '.div-table_producto' ).hide();

		$('.checkbox-conversionGLP').hide();
		$('.div-conversionGLP').hide();
    	$( '#txt-Enviar_Conversion_GLP' ).val('');
    	
		// Datos Complementarios
		$("#txt-Nu_Numero_Scop_Recepcion").val('');
		$("#txt-Txt_Observacion_Recepcion").val('');

		$( "#chk-datosComplementarios" ).prop( "disabled", false );
    	if ($( "#txt-Fe_Emision_Compra" ).val().length === 0)
    		$( "#chk-datosComplementarios" ).prop( "disabled", true );

		$('.div-datosComplementarios').hide();
		$( "#chk-datosComplementarios" ).prop('checked', false);

		$('.div-LotePedidoVencimiento').hide();
		$( "#chk-pedido_vencimiento" ).prop('checked', false);
		
		$('#table-producto tbody').empty();
		//$('#table-producto tfoot').empty();

		//Totales hidden
		$('#txt-Nu_Cantidad_Tot_Actual').val(0.00);
		$('.txt-Nu_Cantidad_Tot_Actual').val(0.00);

		$('#txt-Nu_Total_SIGV_Tot_Actual').val(0.00);
		$('.txt-Nu_Total_SIGV_Tot_Actual').val(0.00);

		$('#txt-Nu_Total_CIGV_Tot_Actual').val(0.00);
		$('.txt-Nu_Total_CIGV_Tot_Actual').val(0.00);

		$('#txt-Nu_BI_RC').val(0);
		$('#txt-Nu_IGV_RC').val(0);
		$('#txt-Nu_Totacl_RC').val(0);

		$('#label-Nu_BI_RC').val(0);
		$('#label-Nu_IGV_RC').val(0);
		$('#label-Nu_Totacl_RC').val(0);

		/* CLEAN HIDDEN, TEXT and SELECT */
		$(".required").each(function(){	
			$($(this)).val('');
		});

		$ ( "#txt-Txt_Glosa_RC" ).val('');

		save_accion = 'add';

		$( ".div-ReferenciaDocumentoOriginal" ).hide();

		/* FLETES */
		$( ".div-Fletes" ).hide();
		$( "#chk-addFlete" ).prop('checked', false);

		/* Registro de Compras */
		$( ".div-PrincipalRegistroCompras" ).hide();
		$( "#chk-addCUentasXPagar" ).prop('checked', false);
        $( ".div-RegistroCompras" ).hide();
		$( '.div-Fe_Periodo' ).hide();
		$( '.div-Inafecto_IGV_RC' ).hide();

		$( "select" ).each( function () {
			var $combobox = $('#' + $( this )['context']['id']);
			$combobox.val($combobox.children('option:first').val());
		});
	})

    $( ".btn-close" ).click(function(){
		$( "#template-Movimiento_Inventario" ).show();
		$( "#div-Movimiento_Inventario_Table" ).show();
		$( "#template-Movimiento_Inventario_Agregar" ).hide();
		$( ".div-PrincipalRegistroCompras" ).hide();
		$( ".MsgData" ).hide();
    })
    
    var Nu_Total_Cantidad_tmp 	= 0.00;
    var Nu_Total_Cantidad_Tot 	= 0.00;
    var Nu_Cantidad_Tot_Actual	= 0.00;

    var Nu_Total_SIGV_tmp 			= 0.00;
    var Nu_Total_SIGV_Tot 			= 0.00;
    var Nu_Total_SIGV_Tot_Actual	= 0.00;

    var Nu_Total_CIGV_tmp 			= 0.00;
    var Nu_Total_CIGV_Tot 			= 0.00;
    var Nu_Total_CIGV_Tot_Actual	= 0.00;

    var Nu_IGV_RC = 0.00;
    
    $("#btn-addProducto").click(function(){ //Click en Agregar Item
		$( '.div-msg_PrecioVentaMargen' ).hide();
    	//Mostrar tabla temporal Del ingreso de producto
    	var No_Proveedor 			= $("#txt-No_Proveedor").val();
    	var Nu_Documento_Identidad 	= $("#txt-Nu_Documento_Identidad").val();

    	var Nu_Tipo_Documento_Compra 	= $("#cbo-Nu_Tipo_Documento_Compra").val();
    	var Nu_Serie_Compra 			= $("#txt-Nu_Serie_Compra").val();
    	var Nu_Numero_Compra 			= $("#txt-Nu_Numero_Compra").val();

    	var No_Producto 		= $("#txt-No_Producto_Detalle_Compra").val();
    	var Nu_Id_Producto 		= $("#txt-Nu_Id_Producto").val();
    	var Nu_Cantidad 		= $("#txt-Nu_Cantidad_Compra").val();
    	var Nu_Costo_Unitario 	= $("#txt-Nu_Costo_Unitario").val();
    	var Nu_Total_SIGV 		= $("#txt-Nu_Total_SIGV").val();
    	var Nu_Total_CIGV 		= $("#txt-Nu_Total_CIGV").val();

    	var Nu_Margen_Real 		= $("#txt-Nu_Margen_Real").val();

    	var $TipoFormulario = $("#txt-Nu_Tipo_Movimiento_Inventario_Agregar").val();

    	var $Nu_Lote = '';
    	var $Fe_Vencimiento_Pedido = '';
		if ( $("#chk-pedido_vencimiento").prop("checked") ){
			$Nu_Lote = $("#txt-Nu_Lote").val();
			$Fe_Vencimiento_Pedido = $("#txt-Fe_Vencimiento_Pedido").val();
		}

		var art_costo_uni = Nu_Costo_Unitario;
		var ymargen = 0;



		$.post( "../assets/helper.php", {
            	accion: "getCalculoMargen",
				id_producto: $.trim(Nu_Id_Producto),
			}, function(response){
				console.log("getCalculoMargen");
				console.log(response);

				if (response.status == 'success'){
				
					var xprecio = (response.data["precio"]);
					var xmargen = parseFloat(response.data["margen"]);
					ymargen = 0;
					
					if(art_costo_uni != '0' || art_costo_uni != '0.00' || art_costo_uni > 0){	
					//if(xmargen != '0' || xmargen != '0.00' || xmargen > 0 ){	ya no quieren verificar margen en 0
					if($TipoFormulario == '01' || $TipoFormulario == '07' || $TipoFormulario == '08' || $TipoFormulario == '05'){

					ymargen = parseFloat(((((xprecio/1.18)-art_costo_uni))/art_costo_uni)*100); 

					var wmargen  = Math.ceil(Math.log10(ymargen + 1)); 

					if (wmargen > 6){
						ymargen = 999999;
						//al registrar transferencias gratuitas por ingreso de compras generarn un margen mayor al soportado por la columnda de la BD.
					}

					if(xmargen > ymargen){
							$( '.help' ).show();
							$( '.help' ).html("");
							$( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').html('Verificar Costo');
							$( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').addClass('is-danger');

							$( '#table-MargenGanancia >tbody' ).empty();

							$( '.modal-MargenGanancia' ).show();
							$( '.message' ).addClass('is-primary');
							$( '.message' ).removeClass('is-danger');
							var tr_body =
							"<tr>"
								+"<td colspan='6' style='font-size: 9px'><strong>El costo unitario ingresado genera un margen menor al establecido por la linea, verificar precio de venta.</strong></td>"
							+"</tr>"
							+"<tr>"
								+"<td><strong>Precio de venta actual:</strong></td>"
								+"<td class='text-left' align='left'>" + xprecio + "</td>"
								+"<td><strong>Margen actual de la linea:</strong></td>"
								+"<td class='text-left' align='left'>" + xmargen + "</td>"
								+"<td><strong>Margen en base al costo ingresado:</strong></td>"
								+"<td class='text-left' align='left'>" + ymargen.toFixed(4) + "</td>"
							+"</tr>"
							$( '#table-MargenGanancia >tbody' ).append(tr_body);
								
							return false;
							}
						}
					}
				} 

    	if (
    		No_Proveedor.length === 0
			&&
	    		(
	    			$TipoFormulario == '01' ||
	    			$TipoFormulario == '21' ||
	    			$TipoFormulario == '05'
	    		)
    		) {
    		$( '.help' ).show();
		    $( '.help' ).html("");
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').html('Ingresar Proveedor');
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').addClass('is-danger');
	    }else if (
	    	Nu_Documento_Identidad.length === 0
			&&
	    		(
	    			$TipoFormulario == '01' ||
	    			$TipoFormulario == '21' ||
	    			$TipoFormulario == '05'
	    		)
	    	) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-No_Proveedor' ).closest('.column').find('.help').html('Debe seleccionar un proveedor');
			$( '#txt-No_Proveedor' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Tipo_Documento_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#cbo-Nu_Tipo_Documento_Compra' ).closest('.column').find('.help').html('Debe seleccionar un tipo de documento');
			$( '#cbo-Nu_Tipo_Documento_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Serie_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Serie_Compra' ).closest('.column').find('.help').html('Ingresar serie');
			$( '#txt-Nu_Serie_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Numero_Compra.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Numero_Compra' ).closest('.column').find('.help').html('Ingresar numero');
			$( '#txt-Nu_Numero_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (No_Producto.length === 0) {
    		$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').html('Ingresar Nombre');
			$( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Id_Producto.length === 0) {
	    	$( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').html('Debe seleccionar un producto');
			$( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Cantidad.length === 0 && false == $("#chk-conversionGLP").prop("checked")) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Cantidad_Compra' ).closest('.column').find('.help').html('Ingresar cantidad');
			$( '#txt-Nu_Cantidad_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Cantidad == 0 && false == $("#chk-conversionGLP").prop("checked")) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Cantidad_Compra' ).closest('.column').find('.help').html('<b>La cantidad debe ser mayor a 0</b>');
			$( '#txt-Nu_Cantidad_Compra' ).closest('.column').find('.help').addClass('is-danger');
	    } else if (Nu_Costo_Unitario.length === 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').html('Ingresar Costo Unitario');
			$( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').addClass('is-danger');
		} else if (Nu_Costo_Unitario == 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').html('<b>El Costo Unitario debe ser mayor a 0</b>');
			$( '#txt-Nu_Costo_Unitario' ).closest('.column').find('.help').addClass('is-danger');
		} else if(($( '#txt-Nu_Galones_GLP' ).val().length === 0 && $( '#txt-Nu_Galones_GLP' ).val() == 0) && ($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Kilos' ).val().length === 0){/* Conversion GLP compras */
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Kilos' ).closest('.column').find('.help').html('Ingresar Kilos');
			$( '#txt-Nu_Kilos' ).closest('.column').find('.help').addClass('is-danger');
    	} else if(($( '#txt-Nu_Galones_GLP' ).val().length === 0 && $( '#txt-Nu_Galones_GLP' ).val() == 0) && ($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Kilos' ).val() == 0){/* Conversion GLP compras */
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Kilos' ).closest('.column').find('.help').html('<b>Los kilos debe ser mayor a 0</b>');
			$( '#txt-Nu_Kilos' ).closest('.column').find('.help').addClass('is-danger');
    	} else if (($( '#txt-Nu_Galones_GLP' ).val().length === 0 && $( '#txt-Nu_Galones_GLP' ).val() == 0) && ($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Gravedad_Especifica' ).val().length === 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Gravedad_Especifica' ).closest('.column').find('.help').html('Ingresar Gravedad Especifica');
			$( '#txt-Nu_Gravedad_Especifica' ).closest('.column').find('.help').addClass('is-danger');
    	} else if (($( '#txt-Nu_Galones_GLP' ).val().length === 0 && $( '#txt-Nu_Galones_GLP' ).val() == 0) && ($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Gravedad_Especifica' ).val() == 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Gravedad_Especifica' ).closest('.column').find('.help').html('<b>La G.E debe ser mayor a 0</b>');
			$( '#txt-Nu_Gravedad_Especifica' ).closest('.column').find('.help').addClass('is-danger');
    	} else if (($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Galones_GLP' ).val().length === 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Galones_GLP' ).closest('.column').find('.help').html('Ingresar Galones GLP');
			$( '#txt-Nu_Galones_GLP' ).closest('.column').find('.help').addClass('is-danger');
		} else if (($("#chk-conversionGLP").prop("checked")) && $( '#txt-Nu_Galones_GLP' ).val() == 0) {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Galones_GLP' ).closest('.column').find('.help').html('<b>El Galonaje debe ser mayor a 0</b>');
			$( '#txt-Nu_Galones_GLP' ).closest('.column').find('.help').addClass('is-danger');
		} else if (($("#chk-pedido_vencimiento").prop("checked")) && $( '#txt-Nu_Lote').val().length === 0 )  {
		    $( '.help' ).show();
		    $( '.help' ).html("");
	        $( '#txt-Nu_Lote' ).closest('.column').find('.help').html('<b>Ingresar número lote</b>');
			$( '#txt-Nu_Lote' ).closest('.column').find('.help').addClass('is-danger');
		} else {
			var table_producto = 
			"<tbody>"
		        + "<tr id='output_newrow_" + No_Producto + "'>"
		        	+ "<td class='text-left' id='num_row_" + Nu_Id_Producto + "'>" + Nu_Id_Producto + "</td>"
		        	+ "<td class='text-left' id='num_row_" + No_Producto + "'>" + No_Producto + "</td>"
			    	+ "<td class='text-right' align='right' id='num_row_" + Nu_Cantidad + "'>" + parseFloat(Nu_Cantidad).toFixed(4) + "</td>"
			    	+ "<td class='text-right' align='right' id='num_row_" + Nu_Costo_Unitario + "'>" + parseFloat(Nu_Costo_Unitario).toFixed(6) + "</td>"
			    	+ "<td class='text-right' align='right' id='td-Nu_Total_SIGV'>" + parseFloat(Nu_Total_SIGV).toFixed(4) + "</td>"
			    	+ "<td class='text-right' align='right' id='td-Nu_Total_CIGV'>" + parseFloat(Nu_Total_CIGV).toFixed(4) + "</td>"
			    	+ "<td class='text-right' align='right' id='num_row_" + $Nu_Lote + "'>" + $Nu_Lote + "</td>"
			    	+ "<td class='text-right' align='right' id='num_row_" + $Fe_Vencimiento_Pedido + "'>" + $Fe_Vencimiento_Pedido + "</td>"
			    	+ "<td class='text-center' align='center'><button type='button' data-Nu_Id_Producto='" + Nu_Id_Producto + "'  data-Nu_Cantidad_Compra='" + parseFloat(Nu_Cantidad).toFixed(4) + "' data-Nu_Total_SIGV='" + parseFloat(Nu_Total_SIGV).toFixed(4) + "' data-Nu_Total_CIGV='" + parseFloat(Nu_Total_CIGV).toFixed(4) + "' class='button is-danger is-small icon-size btn-danger btn-deleteProducto'><span class='icon is-small'><i class='fa fa-trash'></i></span></button></td>"
		        	+ "<td style='visibility: hidden' class='text-left' id='Nu_Margen_Real'>" + parseFloat(ymargen).toFixed(4) + "</td>"
	        + "</tr>"
		    + "</tbody>";
		    
		    if(isExistTableTemporalProducto(Nu_Id_Producto)){
		    	$( '.help' ).show();
		    	$( '.help' ).html("");
		    	$( '#txt-No_Producto_Detalle_Compra' ).val("");
		    	$( '#txt-Nu_Cantidad_Compra' ).val("");
		    	$( '#txt-Nu_Costo_Unitario' ).val("");
				$( '#txt-Nu_Total_SIGV' ).val("");
				$( '#txt-Nu_Total_CIGV' ).val("");
		        $( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').html('Ya existe el <b>producto: ' + No_Producto + '</b>');
				$( '#txt-No_Producto_Detalle_Compra' ).closest('.column').find('.help').addClass('is-danger');

				$( '#txt-No_Producto_Detalle_Compra' ).focus();
				$( '#txt-No_Producto_Detalle_Compra' ).select();
		    }else{
		    	//Actualizar Precio Venta según margen de linea, consultar tabla int_parametros fila actualiza_precio = 1
				$.post( "../assets/helper.php", {
					accion: "getNewPrice",
					id_producto: $.trim(Nu_Id_Producto),
					id_proveedor: $.trim(Nu_Documento_Identidad),
					ss_costo_sigv: parseFloat(Nu_Costo_Unitario).toFixed(6),
					qt_cantidad: parseFloat(Nu_Cantidad).toFixed(4),
				}, function(data){
					console.log("getNewPrice");
					console.log(data);

					$( '#table-PrecioVentaMargen >tbody' ).empty();

					if(data.status == 'success'){
						$( '.modal-PreciVentaMargen' ).show(); //Muestra el modal modal-PreciVentaMarge
						$( '.message' ).addClass('is-primary');
						$( '.message' ).removeClass('is-danger');

						/**
						 * Validamos Código de Impuesto	del Articulo
						 * Campo art_impuesto1 de int_articulos: Si es null entonces es INAFECTO / Si tiene otros valores diferente de null se considera IMPUESTO IGV 18
						 */
						if( data.reponseData.art_impuesto1 === null ) { //INAFECTO
							var ss_precio_venta_sugerido = parseFloat(data.reponseData.ss_costo_sigv) * (1 + (parseFloat(data.reponseData.ss_porcentaje_margen) / 100));
						} else { //OTROS
							var ss_precio_venta_sugerido = parseFloat(data.reponseData.ss_costo_sigv) * parseFloat($('#txt-Nu_IGV').val()) * (1 + (parseFloat(data.reponseData.ss_porcentaje_margen) / 100));
						}
												
						var tr_body =
						"<tr>"
							+"<td><input type='hidden' class='input' id='txt-Nu_Tipo_Lista_Precio' value='" + data.reponseData.nu_tipo_lista_precio + "' /></td>"
							+"<td><input type='hidden' class='input' id='txt-ID_Producto_Nuevo' value='" + data.reponseData.art_codigo + "' /></td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Codigo</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.art_codigo + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Producto</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.art_descripcion + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Linea</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.no_linea + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Costo con IGV</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.ss_costo_cigv + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Costo sin IGV</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.ss_costo_sigv + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Cantidad</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.qt_cantidad + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Sub Total</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.ss_subtotal + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td style='font-size: 9px'><strong>Precio Actual</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.ss_precio_venta + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td><strong>Margen</strong></td>"
				        	+"<td class='text-left' align='left'>" + data.reponseData.ss_porcentaje_margen + "</td>"
				        +"</tr>"
				        +"<tr>"
				        	+"<td style='font-size: 9px'><strong>Precio Sugerido</strong></td>"
				        	+"<td class='text-left' align='left'><input type='tel' class='input input-decimal' id='txt-Ss_Precio_Sugerido' value='" + ss_precio_venta_sugerido.toFixed(2) + "' autocomplete='off' size='10' maxlength='10' /></td>"
				        +"</tr>";
		        		$( '#table-PrecioVentaMargen >tbody' ).append(tr_body); //Agrega contenido al body del modal modal-PreciVentaMargen

						$( '.input-decimal' ).on('input', function () {
							numero = parseFloat(this.value);
							if(!isNaN(numero)){
								this.value = this.value.replace(/[^0-9\.]/g,'');
								if (numero < 0)
									this.value = '';
							} else
								this.value = this.value.replace(/[^0-9\.]/g,'');
						});
					}

				}, 'JSON');

				$( "#table-producto" ).show();

		    	/* Conversion GLP compras */
		    	if(($("#chk-conversionGLP").prop("checked")) && $.trim(Nu_Id_Producto) == '11620307'){
		    		$(".div-conversionGLP").hide();

					$("#txt-Enviar_Conversion_GLP").val('true');
					$("#txt-_Nu_Kilos").val($("#txt-Nu_Kilos").val());
					$("#txt-_Nu_Gravedad_Especifica").val($("#txt-Nu_Gravedad_Especifica").val());
					$("#txt-_Nu_Galones_GLP").val($("#txt-Nu_Galones_GLP").val());
					$("#txt-_Nu_Litros_GLP").val($("#txt-Nu_Litros_GLP").val());

					$(".conversionGLP").each(function(){	
						$($(this)).val('');
					});

			        $("#txt-Nu_Cantidad_Compra").show();
					$(".span-Nu_Cantidad_Compra").show();

					$("#chk-conversionGLP").prop('checked', false);
					$('.checkbox-conversionGLP').hide();

		    	}else if($.trim(Nu_Id_Producto) == '11620307'){
					$("#chk-conversionGLP").prop('checked', false);
					$('.checkbox-conversionGLP').hide();
		    	}

		    	$( '.help' ).hide();
		    	$( '.help' ).html("");

    			$('.div-table_producto').show();
				$("#table-producto").append(table_producto);

				Nu_Total_Cantidad_tmp	= parseFloat(Nu_Cantidad);
				Nu_Total_Cantidad_Tot	= parseFloat($('#txt-Nu_Cantidad_Tot_Actual').val());

				Nu_Cantidad_Tot_Actual	= (Nu_Total_Cantidad_tmp + Nu_Total_Cantidad_Tot);

				Nu_Total_SIGV_tmp	= parseFloat(Nu_Total_SIGV);
				Nu_Total_SIGV_Tot	= parseFloat($('#txt-Nu_Total_SIGV_Tot_Actual').val());

				Nu_Total_SIGV_Tot_Actual	= (Nu_Total_SIGV_tmp + Nu_Total_SIGV_Tot);

				Nu_Total_CIGV_tmp	= parseFloat(Nu_Total_CIGV);
				Nu_Total_CIGV_Tot	= parseFloat($('#txt-Nu_Total_CIGV_Tot_Actual').val());
				
				Nu_Total_CIGV_Tot_Actual	= (Nu_Total_CIGV_tmp + Nu_Total_CIGV_Tot);
			    
			    $('#txt-Nu_Cantidad_Tot_Actual').val(Nu_Cantidad_Tot_Actual.toFixed(4));
			    $('.txt-Nu_Cantidad_Tot_Actual').text(Nu_Cantidad_Tot_Actual.toFixed(4));

			    $('#txt-Nu_Total_SIGV_Tot_Actual').val(Nu_Total_SIGV_Tot_Actual.toFixed(4));
			    $('.txt-Nu_Total_SIGV_Tot_Actual').text(Nu_Total_SIGV_Tot_Actual.toFixed(4));
			    
			    $('#txt-Nu_Total_CIGV_Tot_Actual').val(Nu_Total_CIGV_Tot_Actual.toFixed(4));
			    $('.txt-Nu_Total_CIGV_Tot_Actual').text(Nu_Total_CIGV_Tot_Actual.toFixed(4));

				/* Registro de compras */
				//if ($("#chk-addCUentasXPagar").prop("checked")){
					Nu_IGV_RC = parseFloat(Nu_Total_CIGV_Tot_Actual - Nu_Total_SIGV_Tot_Actual).toFixed(2);
				    $('#txt-Nu_BI_RC').val(Nu_Total_SIGV_Tot_Actual.toFixed(2));
				    $('#txt-Nu_IGV_RC').val(Nu_IGV_RC);
				    $('#txt-Nu_Totacl_RC').val(Nu_Total_CIGV_Tot_Actual.toFixed(2));

				    $('#label-Nu_BI_RC').val(Nu_Total_SIGV_Tot_Actual.toFixed(2));
				    $('#label-Nu_IGV_RC').val(Nu_IGV_RC);
				    $('#label-Nu_Totacl_RC').val(Nu_Total_CIGV_Tot_Actual.toFixed(2));
				//}

				//Limpiar Cajas de Texto - Formulario Compras
				$('#txt-No_Producto_Detalle_Compra').val("");
        		$(".txt-Nu_Id_Producto").val("");
        		$('#txt-Nu_Cantidad_Actual').val("");
				$('#txt-Nu_Cantidad_Compra').val("");
				$('#txt-Nu_Costo_Unitario').val("");
				$('#txt-Nu_Total_SIGV').val("");
				$('#txt-Nu_Total_CIGV').val("");
				$('#label-Nu_Total_CIGV').val("");

				if ($("#chk-pedido_vencimiento").prop("checked")) {
					$(".div-LotePedidoVencimiento").hide();
					$("#chk-pedido_vencimiento").prop('checked', false);
					$('#txt-Nu_Lote').val("");
				}

				$( '#txt-No_Producto_Detalle_Compra' ).focus();

				//Btn agregar
				$( "#btn-save" ).prop( "disabled", false );
		    	}
			}

		}, 'JSON');
    })

    // Find and remove selected table rows
	var _Nu_Total_Cantidad 			= 0.00;
	var _Delete_Nu_Total_Cantidad	= 0.00;

	var _Nu_Total_SIGV 			= 0.00;
	var _Delete_Nu_Total_SIGV	= 0.00;

	var _Nu_Total_CIGV 			= 0.00;
	var _Delete_Nu_Total_CIGV	= 0.00;
	
	var _Nu_Valor_Impuesto		= $( '#txt-Nu_IGV' ).val();
	
	$('#table-producto').on('click', '.btn-deleteProducto', function(){
    	$(this).closest ('tr').remove ();

		_Nu_Total_Cantidad 			= parseFloat($(this).attr('data-Nu_Cantidad_Compra')).toFixed(4);
		_Delete_Nu_Total_Cantidad	= parseFloat($('#txt-Nu_Cantidad_Tot_Actual').val()).toFixed(4);
		
		$('.txt-Nu_Cantidad_Tot_Actual').text((_Delete_Nu_Total_Cantidad - _Nu_Total_Cantidad).toFixed(4));
		$('#txt-Nu_Cantidad_Tot_Actual').val((_Delete_Nu_Total_Cantidad - _Nu_Total_Cantidad).toFixed(4));

		_Nu_Total_SIGV 			= parseFloat($(this).attr('data-Nu_Total_SIGV')).toFixed(4);
		_Delete_Nu_Total_SIGV	= parseFloat($('#txt-Nu_Total_SIGV_Tot_Actual').val()).toFixed(4);
		
		$('.txt-Nu_Total_SIGV_Tot_Actual').text((_Delete_Nu_Total_SIGV - _Nu_Total_SIGV).toFixed(4));
		$('#txt-Nu_Total_SIGV_Tot_Actual').val((_Delete_Nu_Total_SIGV - _Nu_Total_SIGV).toFixed(4));
		
		_Nu_Total_CIGV 			= parseFloat($(this).attr('data-Nu_Total_CIGV')).toFixed(4);
		_Delete_Nu_Total_CIGV	= parseFloat($('#txt-Nu_Total_CIGV_Tot_Actual').val()).toFixed(4);
		
		$('.txt-Nu_Total_CIGV_Tot_Actual').text((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV).toFixed(4));
		$('#txt-Nu_Total_CIGV_Tot_Actual').val((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV).toFixed(4));

		/* Registro de Compras */
		$('#txt-Nu_BI_RC').val((_Delete_Nu_Total_SIGV - _Nu_Total_SIGV).toFixed(2));
		$('#txt-Nu_IGV_RC').val(((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV) + (_Delete_Nu_Total_SIGV - _Nu_Total_SIGV)).toFixed(2));
		$('#txt-Nu_Totacl_RC').val((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV).toFixed(2));
		
		$('#label-Nu_BI_RC').val((_Delete_Nu_Total_SIGV - _Nu_Total_SIGV).toFixed(2));
		$('#label-Nu_IGV_RC').val(((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV) + (_Delete_Nu_Total_SIGV - _Nu_Total_SIGV)).toFixed(2));
		$('#label-Nu_Totacl_RC').val((_Delete_Nu_Total_CIGV - _Nu_Total_CIGV).toFixed(2));

		/* conversion GLP compras */
		if($.trim($(this).attr('data-Nu_Id_Producto')) == '11620307')
			$("#txt-Enviar_Conversion_GLP").val('false');

		if ($('#table-producto >tbody >tr').length == 0){
        	Nu_Cantidad_Tot_Actual = 0;
        	Nu_Total_SIGV_Tot_Actual = 0.00;
        	Nu_Total_CIGV_Tot_Actual = 0.00;
			$('.div-table_producto').hide();
			$( "#btn-save" ).prop( "disabled", true );
		}else{
			$('.div-table_producto').show();
			$( "#btn-save" ).prop( "disabled", false );
						        
		}
    });

	$( "#btn-save" ).click(function(){ //BOTON GUARDAR DE FORMULARIOS DE MOVIMIENTOS DE INVENTARIO
		// Validacion Orden Compra
		//$( "#btn-save" ).prop( "disabled", true );

		if ( $( "#table-OrdenCompra > tbody > tr" ).length == 0 && $( "#txt-tab_orden_si" ).val() == 1){
			$( '.MsgData' ).show();
			$( '.message-status' ).html('');
			$( '.MsgData' ).delay(1200).hide(600);
			$( '.message' ).removeClass('is-primary');
			$( '.message' ).addClass('is-danger');
			$( '.message-status' ).html('Debe ingresar al menos 1 orden compra');
		} else {
			// Validacion de Formulario
		    var verify_inputs_required = true;

			$(".required").each(function(){
				if ($($(this)).val().length === 0){
					verify_inputs_required = false;
					$( ".help" ).show();
					$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').html('Ingresar valor');
					$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').addClass('is-danger');
				}else
					$( "#" + $( this )['context']['id'] ).closest('.column').find('.help').html('');
			})

			// Validaciones para ingresar inventario
			if(verify_inputs_required){
				//$( "#template-Movimiento_Inventario_Agregar" ).prepend(block_loding_modal);

				// conversion GLP compras
				var arrConversionGLP = '';

				if($( "#txt-Enviar_Conversion_GLP" ).val() == 'true'){
					arrConversionGLP = {
						'Enviar_Conversion_GLP' 	: $('#txt-Enviar_Conversion_GLP').val(),
						'Nu_Kilos' 					: $('#txt-_Nu_Kilos').val(),
						'Nu_Gravedad_Especifica' 	: $('#txt-_Nu_Gravedad_Especifica').val(),
						'Nu_Galones_GLP' 			: $('#txt-_Nu_Galones_GLP').val(),
						'Nu_Litros_GLP' 			: $('#txt-_Nu_Litros_GLP').val(),
					};
				}else{
					arrConversionGLP = { 'Enviar_Conversion_GLP' : $('#txt-Enviar_Conversion_GLP').val()}
				}

				var arrFormAgregar = {
					'Nu_Tipo_Movimiento_Inventario' 		: $('#txt-Nu_Tipo_Movimiento_Inventario_Agregar').val(),
					'Nu_Almacen_Interno' 					: $('#txt-Nu_Almacen_Interno').val(),
					'Nu_Almacen_Origen' 					: $('.Nu_Almacen_Origen').val(),
					'Nu_Almacen_Destino' 					: $('.Nu_Almacen_Destino').val(),
					'Nu_Naturaleza_Movimiento_Inventario' 	: $('#txt-Nu_Naturaleza_Movimiento_Inventario').val(),
					'Nu_Tipo_Cambio_Compra' 				: $('#txt-Nu_Tipo_Cambio_Compra').val(),
					'Fe_Emision' 							: $('#txt-Fe_Emision_Compra').val(), //F. Emisión (Inventario)
					'Fe_Sistema' 							: $('#txt-Fe_Sistema').val(),
					'Nu_Documento_Identidad' 				: $('#txt-Nu_Documento_Identidad').val(),
					'Nu_Tipo_Documento_Compra' 				: $('#cbo-Nu_Tipo_Documento_Compra option:selected').val(),
					'Nu_Serie_Compra' 						: $('#txt-Nu_Serie_Compra').val().toUpperCase(),
					'Nu_Numero_Compra' 						: $('#txt-Nu_Numero_Compra').val(),
					'Nu_Total_SIGV' 						: $('#txt-Nu_Total_SIGV_Tot_Actual').val(),
					'Fe_Emision_Registro_Compra' 			: $('#txt-Fe_Emision_Registro_Compra').val(), //Fecha Emision (Factura)
				};

				var arrFormAgregarDocumentoReferencia = '';
				if ($('#cbo-Nu_Tipo_Documento_Compra option:selected').val() == '11' || $('#cbo-Nu_Tipo_Documento_Compra option:selected').val() == '20') {
					arrFormAgregarDocumentoReferencia = {
						'Nu_Tipo_Documento_Compra_Referencia' 				: $('#cbo-Nu_Tipo_Documento_Compra_Referencia option:selected').val(),
						'Nu_Serie_Compra_Referencia' 						: $('#txt-Nu_Serie_Compra_Referencia').val().toUpperCase(),
						'Nu_Numero_Compra_Referencia' 						: $('#txt-Nu_Numero_Compra_Referencia').val(),
					};
				} else {
					arrFormAgregarDocumentoReferencia = '';
				}

				var arrDataVenta = [];
			   	
			   	if ($( "#txt-tab_orden_si" ).val() == 1){//Ingreso por Orden Compra
					$("#table-OrdenCompra tbody tr").each(function(){
				        var rows = $(this);
				        var iSerieOrdenCompra	= rows.find("td:eq(1)").text();
				        var iNumeroOrdenCompra	= rows.find("td:eq(2)").text();
				        if (iSerieOrdenCompra.length > 0 && iNumeroOrdenCompra.length > 0) {
					        var Nu_Id_Producto		= rows.find("td:eq(3)").text();
					        var Nu_Cantidad			= rows.find("td:eq(5)").text();
					        var Nu_Costo_Unitario	= rows.find("td:eq(7)").text();
					        var Nu_Total_SIGV		= rows.find("td:eq(8)").text();
					        var Nu_Total_CIGV		= rows.find("td:eq(9)").text();
							var Nu_Margen_Real		= rows.find("td:eq(11)").text();

							var obj = {};
							
							obj.iSerieOrdenCompra	= iSerieOrdenCompra;
							obj.iNumeroOrdenCompra	= iNumeroOrdenCompra;
					        obj.Nu_Id_Producto		= Nu_Id_Producto;
					        obj.Nu_Cantidad 		= Nu_Cantidad;
					        obj.Nu_Costo_Unitario	= Nu_Costo_Unitario;
					        obj.Nu_Total_SIGV		= Nu_Total_SIGV;
					        obj.Nu_Total_CIGV		= Nu_Total_CIGV;
					        obj.Nu_Lote				= '';
					        obj.Fe_Vencimiento_Pedido = '';
					        obj.Nu_Margen_Real		= Nu_Margen_Real;

					        arrDataVenta.push(obj);
					    }
					});			   		
			   	} else {
					$("#table-producto tbody tr").each(function(){
				        var rows = $(this);

				        var Nu_Id_Producto		= rows.find("td:eq(0)").text();
				        var Nu_Cantidad			= rows.find("td:eq(2)").text();
				        var Nu_Costo_Unitario	= rows.find("td:eq(3)").text();
				        var Nu_Total_SIGV		= rows.find("td:eq(4)").text();
				        var Nu_Total_CIGV		= rows.find("td:eq(5)").text();
				        var Nu_Lote					= rows.find("td:eq(6)").text();
				        var Fe_Vencimiento_Pedido	= rows.find("td:eq(7)").text();
				        var Nu_Margen_Real			= rows.find("td:eq(9)").text();
			
						var obj = {};
						
						obj.iSerieOrdenCompra	= '';
						obj.iNumeroOrdenCompra	= '';
				        obj.Nu_Id_Producto		= Nu_Id_Producto;
				        obj.Nu_Cantidad 		= Nu_Cantidad;
				        obj.Nu_Costo_Unitario	= Nu_Costo_Unitario;
				        obj.Nu_Total_SIGV		= Nu_Total_SIGV;
				        obj.Nu_Total_CIGV		= Nu_Total_CIGV;
				        obj.Nu_Lote					= Nu_Lote;
				        obj.Fe_Vencimiento_Pedido	= Fe_Vencimiento_Pedido;
				        obj.Nu_Margen_Real			= Nu_Margen_Real;

				        arrDataVenta.push(obj);
					});
				}

				// FLETES
				if ($("#chk-addFlete").prop("checked")){
					var arrFletes = {
						'Enviar_Flete' 					: $("#chk-addFlete").prop("checked"),
						'Fe_Flete' 						: $('#txt-Fe_Flete').val(),
						'ID_Motivo_Traslado'			: $('#cbo-MotivoTraslado option:selected').val(),
						'No_Placa' 						: $('#txt-Placa').val(),
						'No_Licencia' 					: $('#txt-Licencia').val(),
						'No_Certificado_Inscripcion' 	: $('#txt-Autorizacion').val(),
						'ID_Transportista_Proveedor' 	: $('#txt-ID_Transportista_Proveedor').val(),
					};
				}else{
					var arrFletes = { 'Enviar_Flete' : $("#chk-addFlete").prop("checked")}
				}

				// Registro de compras
				if ($("#chk-addCUentasXPagar").prop("checked")){
					var arrRegistroCompras = {
						'Enviar_Regisro_Compra' 	: $("#chk-addCUentasXPagar").prop("checked"), //Puede ser true o false
						'Nu_Dias_Vencimiento_RC' 	: $('#txt-Nu_Dias_Vencimiento_RC').val(),
						'Fe_Vencimiento_RC' 		: $('#txt-Fe_Vencimiento_RC').val(),
						'Fe_Periodo_RC' 			: $('#txt-Fe_Periodo_RC').val(),
						'Rubros_RC' 				: $('#cbo-Rubros_RC option:selected').val(),
						'Nu_TC_RC' 					: $('#txt-Nu_TC_RC').val(),
						'Moneda_RC' 				: $('#cbo-Moneda_RC option:selected').val(),
						'Nu_BI_RC' 					: $('#txt-Nu_BI_RC').val(),
						'Nu_IGV_RC' 				: $('#txt-Nu_IGV_RC').val(),
						'Nu_Totacl_RC' 				: $('#txt-Nu_Totacl_RC').val(),
						'Nu_Percepcion_RC' 			: $('#txt-Nu_Percepcion_RC').val(),
						'Nu_Inafecto_IGV_RC' 		: $('#txt-Nu_Inafecto_IGV_RC').val(),
						'Txt_Glosa_RC' 				: $('#txt-Txt_Glosa_RC').val(),
					};
				}else{
					var arrRegistroCompras = { 'Enviar_Regisro_Compra' : $("#chk-addCUentasXPagar").prop("checked")} //Puede ser true o false
				}

				// Datos Complementarios
				if ($("#chk-datosComplementarios").prop("checked")){
					var hora_recepcion = $( "#txt-Fe_Hora_Recepcion" ).val().split(' ');
	                var Minuto = hora_recepcion[2];
	                var Tipo_Horario = hora_recepcion[3];
	                var Hora;

	                if (Tipo_Horario == 'PM'){
	                    if (hora_recepcion[0] == '01')
	                        Hora = '13';
	                    else if (hora_recepcion[0] == '02')
	                        Hora = '14';
	                    else if (hora_recepcion[0] == '03')
	                        Hora = '15';
	                    else if (hora_recepcion[0] == '04')
	                        Hora = '16';
	                    else if (hora_recepcion[0] == '05')
	                        Hora = '17';
	                    else if (hora_recepcion[0] == '06')
	                        Hora = '18';
	                    else if (hora_recepcion[0] == '07')
	                        Hora = '19';
	                    else if (hora_recepcion[0] == '08')
	                        Hora = '20';
	                    else if (hora_recepcion[0] == '09')
	                        Hora = '21';
	                    else if (hora_recepcion[0] == '10')
	                        Hora = '22';
	                    else if (hora_recepcion[0] == '11')
	                        Hora = '23';
	                    else
	                        Hora = '00';
	                }else
	                    Hora = hora_recepcion[0];

	                var Fe_Hora_Recepcion = Hora + ':' + Minuto;

					var arrDatosComplementarios = {
						'Enviar_Datos_Complementarios' 	: $("#chk-datosComplementarios").prop("checked"),
						'Fe_Recepcion' 					: $('#txt-Fe_Recepcion').val(),
						'Fe_Hora_Recepcion' 			: Fe_Hora_Recepcion,
						'Nu_Turno_Recepcion' 			: $('#cbo-Nu_Turno_Recepcion').val(),
						'Nu_Numero_Scop_Recepcion' 		: $('#txt-Nu_Numero_Scop_Recepcion').val(),
						'Txt_Observacion_Recepcion' 	: $('#txt-Txt_Observacion_Recepcion').val(),
					};
				}else{
					var arrDatosComplementarios = { 'Enviar_Datos_Complementarios' : $("#chk-datosComplementarios").prop("checked")}
				}
				//console.log('-> arrFletes');
				//console.log(arrFletes);

				console.log('save_accion');
				console.log(
					{
						accion 								: save_accion,
						arrFormAgregar 						: arrFormAgregar,
						arrTableAgregar						: arrDataVenta,
						arrConversionGLP 					: arrConversionGLP,
						arrRegistroCompras 					: arrRegistroCompras,
						arrFormAgregarDocumentoReferencia 	: arrFormAgregarDocumentoReferencia,
						arrDatosComplementarios 			: arrDatosComplementarios,
						arrFletes 							: arrFletes,
						enviar_orden_compra 				: $( "#txt-tab_orden_si" ).val()
					}
				);
				// return;

				$.post( "reportes/c_mov_almacen_crud.php", {
					accion 								: save_accion,
				    arrFormAgregar 						: arrFormAgregar,
				    arrTableAgregar						: arrDataVenta,
				    arrConversionGLP 					: arrConversionGLP,
				    arrRegistroCompras 					: arrRegistroCompras,
				    arrFormAgregarDocumentoReferencia 	: arrFormAgregarDocumentoReferencia,
				    arrDatosComplementarios 			: arrDatosComplementarios,
				    arrFletes 							: arrFletes,
				    enviar_orden_compra 				: $( "#txt-tab_orden_si" ).val()
				}, function(response){
					$( '.MsgData' ).show();
					$( '.message-status' ).html('');

					if(response.status == 'success'){

						$( ".div-PrincipalRegistroCompras" ).hide();
						$( ".div-RegistroCompras" ).hide();
						$( "#table-producto" ).hide();

						$( '.MsgData' ).delay(1000).hide(600);
						$( '.message' ).removeClass('is-danger');
						$( '.message' ).addClass('is-primary');
						$( '.message-status' ).html(response.message);
						//$( "#btn-save" ).classList.add('is-primary');

						$(".required").each(function(){	
							$($(this)).val('');
						});

						$( "select" ).each( function () {
							var $combobox = $('#' + $( this )['context']['id']);
							$combobox.val($combobox.children('option:first').val());
						});

						$('#table-producto tbody').empty();

						//Totales hidden
						$('#txt-Nu_Cantidad_Tot_Actual').val(0.00);
						$('.txt-Nu_Cantidad_Tot_Actual').val(0.00);

						$('#txt-Nu_Total_SIGV_Tot_Actual').val(0.00);
						$('.txt-Nu_Total_SIGV_Tot_Actual').val(0.00);

						$('#txt-Nu_Total_CIGV_Tot_Actual').val(0.00);
						$('.txt-Nu_Total_CIGV_Tot_Actual').val(0.00);

						$('#txt-Nu_BI_RC').val(0.00);
						$('#txt-Nu_IGV_RC').val(0.00);
						$('#txt-Nu_Totacl_RC').val(0.00);
						$('#txt-Nu_Percepcion_RC').val(0.00);
						$('#txt-Nu_Inafecto_IGV_RC').val(0.00);

						$('#label-Nu_BI_RC').val(0.00);
						$('#label-Nu_IGV_RC').val(0.00);
						$('#label-Nu_Totacl_RC').val(0.00);
						
						$( "#btn-save" ).prop( "disabled", true );

						var data = {
					        Nu_Almacen 		: $('#cbo-almacen option:selected').val(),
					        Fe_Inicial 		: $('#txt-fe_inicial').val(),
					        Fe_Final 		: $('#txt-fe_final').val(),
					        Nu_Documento 	: '',
					        No_Producto 	: '',
					        No_Proveedor 	: '',
					        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario_Agregar').val()
						};

						$.post( "reportes/c_mov_almacen_crud.php", {
							accion 	: 'listAll',
				       		data 	: data,
				       		page 	: 1
						}, function(data){
							setTimeout(function(){
								$( "#template-Movimiento_Inventario_Agregar" ).hide();
								$( "#template-Movimiento_Inventario" ).show();
								$( "#div-Movimiento_Inventario_Table" ).show();
								$( "#div-Movimiento_Inventario_Table" ).html(data);
							}, 1050);
						})
					}else{
						$( "#btn-save" ).prop( "disabled", false );
						$( '.message' ).removeClass('is-primary');
						$( '.message' ).addClass('is-danger');
						$( '.message-status' ).html(response.message);
					}

					//$( ".block-loading" ).remove();
				}, 'JSON')
			} else {
				alert( 'Faltan ingresar datos' );
			}// /. Validaciones para ingresar inventario
		}
	})

/*
  $('#btn-save').click(function(){
    $(this).val('Please wait ...').attr('disabled','disabled');
    });
*/  

    //Buscar antes de cargar la pagina
	var data = {
        Nu_Almacen 		: '',
        Fe_Inicial 		: $('#txt-fe_inicial').val(),
        Fe_Final 		: $('#txt-fe_final').val(),
        Nu_Documento 	: '',
        No_Producto 	: '',
        No_Proveedor 	: '',
        Nu_Tipo_Movimiento_Inventario : $('#txt-Nu_Tipo_Movimiento_Inventario').val()
	};

	BuscarCompras('listAll', data, 1);

	/* Registro de Compras */
	$( "#cbo-Contablizar_Periodo_RC" ).change(function(){
		if($(this).val() == 'S')
			$( '.div-Fe_Periodo' ).show();
		else
			$( '.div-Fe_Periodo' ).hide();
	})

	$( "#cbo-Nu_Inafecto_IGV_RC" ).change(function(){
		if($(this).val() == 'S')
			$( '.div-Inafecto_IGV_RC' ).show();
		else
			$( '.div-Inafecto_IGV_RC' ).hide();
	})
});

function BuscarCompras(accion, data, page){
	/* Cargador */
	var block_loding_modal = $('<div class="block-loading" />');
	$( "#div-Movimiento_Inventario_Table" ).show();
	$( "#div-Movimiento_Inventario_Table" ).prepend(block_loding_modal);

	$.post( "reportes/c_mov_almacen_crud.php", {
		accion 	: accion,
   		data 	: data,
   		page 	: page
	}, function(data){
		$( "#div-Movimiento_Inventario_Table" ).html(data);
	})
}

function isExistTableTemporalProducto(Nu_Id_Producto){
    return Array.from($('tr[id*=output_newrow]'))
              .some(element => ($('td:nth(0)',$(element)).html()===Nu_Id_Producto));
}

function checkVerifyconversionGLP(check){
	if ($("#chk-conversionGLP").prop("checked")){
		$(".div-conversionGLP").show();
		$("#txt-Nu_Cantidad_Compra").prop( "disabled", true );

	    scrollToError($( ".div-conversionGLP" ));

	    $("#txt-Nu_Kilos").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		
	    $("#txt-Nu_Gravedad_Especifica").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
		
	    $("#txt-Nu_Galones_GLP").keydown(function (e) {
	        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
	            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105))
	            e.preventDefault();
	    });
	}else{
		if(false == $("#chk-conversionGLP").prop("checked")){
	        $(".div-conversionGLP").hide();
			$("#txt-Nu_Cantidad_Compra").prop( "disabled", false );
			$("#txt-Nu_Cantidad_Compra").val('');
			$("#txt-Nu_Total_SIGV").val('');
			$("#txt-Nu_Total_CIGV").val('');
		}
	}
}

function calcularConversionGLP(){
	var Nu_Kilos 				= null;
    var Nu_Gravedad_Especifica 	= null;
    var Nu_Galones_GLP 			= null;
    var Nu_Litros_GLP 			= null;

	Nu_Kilos 				= $( "#txt-Nu_Kilos" ).val();
	Nu_Gravedad_Especifica 	= $( "#txt-Nu_Gravedad_Especifica" ).val();
	Nu_Galones_GLP 			= $( "#txt-Nu_Galones_GLP" ).val();
	Nu_Litros_GLP 			= $( "#txt-Nu_Litros_GLP" ).val();

	if (Nu_Kilos > 0 && Nu_Gravedad_Especifica > 0) {
		Nu_Litros_GLP = (parseFloat(Nu_Kilos).toFixed(2) / parseFloat(Nu_Gravedad_Especifica).toFixed(4)).toFixed(2);
		Nu_Galones_GLP = (Nu_Litros_GLP / 3.785411784).toFixed(2);
		$( "#txt-Nu_Litros_GLP" ).val(Nu_Litros_GLP);
		$( "#label-Nu_Litros_GLP" ).val(Nu_Litros_GLP);
		$( "#txt-Nu_Galones_GLP" ).val(Nu_Galones_GLP);
		$( "#txt-Nu_Cantidad_Compra" ).val(Nu_Litros_GLP);
    }

	var Nu_IGV 				= $( "#txt-Nu_IGV" );
    var Nu_Costo_Unitario 	= null;
    Nu_Costo_Unitario 		= $( "#txt-Nu_Costo_Unitario" ).val();

    if(Nu_Litros_GLP > 0 && Nu_Costo_Unitario > 0){
        $( "#txt-Nu_Total_SIGV" ).val(parseFloat(Nu_Litros_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)).toFixed(4));
    	$( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Litros_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    	$( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Litros_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    }
}

function calcularConversionGLP2(){
	var Nu_Kilos 				= null;
    var Nu_Gravedad_Especifica 	= null;
    var Nu_Galones_GLP 			= null;

	Nu_Kilos 				= $( "#txt-Nu_Kilos" ).val();
	Nu_Gravedad_Especifica 	= $( "#txt-Nu_Gravedad_Especifica" ).val();
	Nu_Galones_GLP 			= $( "#txt-Nu_Galones_GLP" ).val();

	if(Nu_Galones_GLP > 0){
    	$( "#txt-Nu_Kilos" ).val(0.00);
    	$( "#txt-Nu_Gravedad_Especifica" ).val(0.00);
    	var Nu_Litros = (parseFloat(Nu_Galones_GLP).toFixed(2) * 3.785411784).toFixed(2);
    	$( "#txt-Nu_Litros_GLP" ).val(Nu_Litros);
    	$( "#label-Nu_Litros_GLP" ).val(Nu_Litros);
    	$( "#txt-Nu_Cantidad_Compra" ).val(Nu_Litros);
    }

	var Nu_IGV 				= $( "#txt-Nu_IGV" );
    var Nu_Costo_Unitario 	= null;

    Nu_Costo_Unitario 		= $( "#txt-Nu_Costo_Unitario" ).val();

    if(Nu_Galones_GLP > 0 && Nu_Costo_Unitario > 0){
        $( "#txt-Nu_Total_SIGV" ).val(parseFloat(Nu_Galones_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)).toFixed(4));
    	$( "#txt-Nu_Total_CIGV" ).val(parseFloat((Nu_Galones_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    	$( "#label-Nu_Total_CIGV" ).val(parseFloat((Nu_Galones_GLP * parseFloat(Nu_Costo_Unitario).toFixed(6)) * Nu_IGV.val()).toFixed(4));
    }
}

function addFlete(check){
	if ($("#chk-addFlete").prop("checked")){
		$( "#txt-Fe_Flete" ).addClass('required');
		$( "#cbo-MotivoTraslado" ).addClass('required');
		$( "#txt-Placa" ).addClass('required');
		$( "#txt-Licencia" ).addClass('required');
		$( "#txt-Autorizacion" ).addClass('required');

		$(".div-Fletes").show();
	} else {
		if(false == $("#chk-addFlete").prop("checked")){
	        $(".div-Fletes").hide();

			$("#txt-Fe_Flete").removeClass('required');
			$("#cbo-MotivoTraslado").removeClass('required');
			$("#txt-Placa").removeClass('required');
			$("#txt-Licencia").removeClass('required');
			$("#txt-Autorizacion").removeClass('required');
		}
	}
}

function addCuentasXPagar(check){
	if ($("#chk-addCUentasXPagar").prop("checked")){
		var ingreso_x_ordenes_de_compra = $( '#txt-tab_orden_si' ).val();
		var posta = false;

		if(ingreso_x_ordenes_de_compra == 1){ //SI SE REALIZA EL INGRESO POR ORDENES DE COMPRA
			if ( $( "#table-OrdenCompra > tbody > tr" ).length == 0 ){	//SE EVALUA SI HAY DETALLE EN #table-OrdenCompra
				alert('Primero se debe de agregar al menos 1 item');
				$( '#chk-addCUentasXPagar' ).attr('checked', false);				
			}else{
				posta = true;
			}
		}else if(ingreso_x_ordenes_de_compra == 0){ //SI SE REALIZA EL INGRESO DIRECTO
			if ( $( "#table-producto > tbody > tr" ).length == 0 ){ //SE EVALUA SI HAY DETALLE EN #table-producto
				alert('Primero se debe de agregar al menos 1 item');
				$( '#chk-addCUentasXPagar' ).attr('checked', false);				
			}else{
				posta = true;
			}
		}

		if( posta == true ){
			$("#cbo-Rubros_RC").addClass('required');
			$("#cbo-Moneda_RC").addClass('required');
			$("#txt-Nu_TC_RC").addClass('required');
			$("#label-Nu_TC_RC").addClass('required');

			$("#txt-Nu_BI_RC").addClass('required');
			$("#txt-Nu_IGV_RC").addClass('required');
			$("#txt-Nu_Totacl_RC").addClass('required');

			$(".div-RegistroCompras").show();

			$( function() {
				$.post( "../assets/helper.php", {
	            	accion : 'getCorrelativoRC',
				}, function(response){
					$( ".label-Nu_Registro_Compra" ).text(response.data["nu_registro_compra"]);
				}, 'JSON');

				$.post( "../assets/helper.php", {
	            	accion 				: 'getRubros',
				}, function(response){
					var arrRubros = response.arrRubros;
		    		$("select[name=cbo-Rubros_RC]").html('<option value="">Seleccionar..</option>');
					for (var i = 0; i < arrRubros.length; i++){
						var selected = '';
						if($( '#txt-Nu_Memoria_Rubro' ).val() == $.trim(arrRubros[i]['ch_codigo_rubro']))
							selected = "selected";
			    		$('#cbo-Rubros_RC').append( '<option value="' + arrRubros[i]['ch_codigo_rubro']+'" ' + selected + '>' + arrRubros[i]['ch_descripcion'] + '</option>' );
					}
				}, 'JSON');

				var Fe_Emision_Compra = $( "#txt-Fe_Emision_Compra" ).val();
				var _FE = Fe_Emision_Compra.split('/');
				var _Fe_Emision_Compra = _FE[2] + '-' + _FE[1] + '-' + _FE[0];

				$.post( "../assets/helper.php", {
	            	accion 				: 'getTipoCambio',
			        Fe_Emision_Compra 	: _Fe_Emision_Compra,
				}, function(response){
					//$( "#txt-Nu_TC_RC" ).val(response.data["tca_compra_oficial"]);
					//$( "#label-Nu_TC_RC" ).val(response.data["tca_compra_oficial"]);

					//console.log('tipo de cambio -> ' + response.fTipoCambio);
					$( "#txt-Nu_TC_RC" ).val( response.fTipoCambio );
					$( "#label-Nu_TC_RC" ).val( response.fTipoCambio );
				}, 'JSON');

				$.post( "../assets/helper.php", {
	            	accion 				: 'getMonedas',
				}, function(response){
					var arrMonedas = response.arrMonedas;
		    		$("select[name=cbo-Moneda_RC]").html('<option value="">Seleccionar..</option>');
					for (var i = 0; i < arrMonedas.length; i++){
						var selected = '';
						if($( '#txt-Nu_Memoria_Moneda' ).val() == $.trim(arrMonedas[i]['currency']))
							selected = "selected";
			    		$('#cbo-Moneda_RC').append( '<option value="' + arrMonedas[i]['currency']+'" ' + selected + '>' + arrMonedas[i]['mone'] + '</option>' );
					}
				}, 'JSON');
			})
		}
	}else{
		if(false == $("#chk-addCUentasXPagar").prop("checked")){
	        $(".div-RegistroCompras").hide();

			$("#cbo-Rubros_RC").removeClass('required');
			$("#cbo-Moneda_RC").removeClass('required');

			$("#txt-Nu_BI_RC").removeClass('required');
			$("#txt-Nu_IGV_RC").removeClass('required');
			$("#txt-Nu_Totacl_RC").removeClass('required');
		}
	}
}

function activeDatosComplementarios(check){
	if ($("#chk-datosComplementarios").prop("checked")){
		//$("#txt-Fe_Recepcion").addClass('required');
		//$("#txt-Fe_Hora_Recepcion").addClass('required');

		$("#cbo-Nu_Turno_Recepcion").addClass('required');
		$("#txt-Nu_Numero_Scop_Recepcion").addClass('required');

		$(".div-datosComplementarios").show();

	    scrollToError($( ".div-datosComplementarios" ));
	}else{
		if(false == $("#chk-datosComplementarios").prop("checked")){
			//$("#txt-Fe_Recepcion").removeClass('required');
			//$("#txt-Fe_Hora_Recepcion").removeClass('required');

			$("#cbo-Nu_Turno_Recepcion").removeClass('required');
			$("#txt-Nu_Numero_Scop_Recepcion").removeClass('required');

	        $(".div-datosComplementarios").hide();
		}
	}
}

var contador = 0;

function activarPedidoVencimiento(check){
	if ($( "#chk-pedido_vencimiento" ).prop("checked")){
		$( ".div-LotePedidoVencimiento" ).show();

		contador++;
		if (contador == 1){
	  	$( '#chk-addCUentasXPagar' ).prop('checked', true);

		$("#cbo-Rubros_RC").addClass('required');
		$("#cbo-Moneda_RC").addClass('required');
		$("#txt-Nu_TC_RC").addClass('required');
		$("#label-Nu_TC_RC").addClass('required');

		$("#txt-Nu_BI_RC").addClass('required');
		$("#txt-Nu_IGV_RC").addClass('required');
		$("#txt-Nu_Totacl_RC").addClass('required');

		$(".div-RegistroCompras").show();

		$( function() {
			$.post( "../assets/helper.php", {
            	accion 				: 'getCorrelativoRC',
			}, function(response){
				console.log(response);
				$( ".label-Nu_Registro_Compra" ).text(response.data["nu_registro_compra"]);
			}, 'JSON');

			$.post( "../assets/helper.php", {
            	accion 				: 'getRubros',
			}, function(response){
				var arrRubros = response.arrRubros;
	    		$("select[name=cbo-Rubros_RC]").html('<option value="">Seleccionar..</option>');
				for (var i = 0; i < arrRubros.length; i++){
					var selected = '';
					if($( '#txt-Nu_Memoria_Rubro' ).val() == $.trim(arrRubros[i]['ch_codigo_rubro']))
						selected = "selected";
		    		$('#cbo-Rubros_RC').append( '<option value="' + arrRubros[i]['ch_codigo_rubro']+'" ' + selected + '>' + arrRubros[i]['ch_descripcion'] + '</option>' );
				}
			}, 'JSON');

			var Fe_Emision_Compra = $( "#txt-Fe_Emision_Compra" ).val();
			var _FE = Fe_Emision_Compra.split('/');
			var _Fe_Emision_Compra = _FE[2] + '-' + _FE[1] + '-' + _FE[0];

			$.post( "../assets/helper.php", {
            	accion 				: 'getTipoCambio',
		        Fe_Emision_Compra 	: _Fe_Emision_Compra,
			}, function(response){
				//$( "#txt-Nu_TC_RC" ).val(response.data["tca_compra_oficial"]);
				//$( "#label-Nu_TC_RC" ).val(response.data["tca_compra_oficial"]);

				$( "#txt-Nu_TC_RC" ).val( response.fTipoCambio );
				$( "#label-Nu_TC_RC" ).val( response.fTipoCambio );
			}, 'JSON');

			$.post( "../assets/helper.php", {
            	accion 				: 'getMonedas',
			}, function(response){
				var arrMonedas = response.arrMonedas;
	    		$("select[name=cbo-Moneda_RC]").html('<option value="">Seleccionar..</option>');
				for (var i = 0; i < arrMonedas.length; i++){
					var selected = '';
					if($( '#txt-Nu_Memoria_Moneda' ).val() == $.trim(arrMonedas[i]['currency']))
						selected = "selected";
		    		$('#cbo-Moneda_RC').append( '<option value="' + arrMonedas[i]['currency']+'" ' + selected + '>' + arrMonedas[i]['mone'] + '</option>' );
				}
			}, 'JSON');
		})
		}
	}else{
		if(false == $( "#chk-pedido_vencimiento" ).prop("checked")){
		    $( ".div-LotePedidoVencimiento" ).hide();
		    if (contador == 1){
	  		$( '#chk-addCUentasXPagar' ).prop('checked', false);
	        $(".div-RegistroCompras").hide();

			$("#cbo-Rubros_RC").removeClass('required');
			$("#cbo-Moneda_RC").removeClass('required');

			$("#txt-Nu_BI_RC").removeClass('required');
			$("#txt-Nu_IGV_RC").removeClass('required');
			$("#txt-Nu_Totacl_RC").removeClass('required');
			}
		}
	}
}

function isExistTableTemporalOrdenCompra($nu_orden_compra_codigo_producto){
  return Array.from($('tr[id*=tr_detalle_orden_compra]'))
    .some(element => ($('td:nth(0)',$(element)).html()===$nu_orden_compra_codigo_producto));
}
