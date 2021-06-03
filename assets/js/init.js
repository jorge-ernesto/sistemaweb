var fToday = new Date();
var fYear = fToday.getFullYear();
var fMonth = fToday.getMonth() + 1; //hoy es 0!
var fDay = fToday.getDate();

$(document).ready(function() {
$( '.input-number_guion' ).on('input', function () {this.value = this.value.replace(/[^0-9\-]/g,'');});

	$( '.input-number_letter' ).on('input', function () {
		this.value = this.value.replace(/[^a-zA-Z0-9\-]/g,'');
	});

	$( '.input-number' ).on('input', function () {
		this.value = this.value.replace(/[^0-9]/g,'');
	});

	$( '.input-decimal' ).on('input', function () {
		numero = parseFloat(this.value);
		if(!isNaN(numero)){
			this.value = this.value.replace(/[^0-9\.]/g,'');
		if (numero < 0)
			this.value = '';
		} else
			this.value = this.value.replace(/[^0-9\.]/g,'');
	});

	$.datepicker.regional['es'] = {
	    closeText: 'Cerrar',
	    prevText: '<Ant',
	    nextText: 'Sig>',
	    currentText: 'Hoy',
	    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
	    monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
	    dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
	    dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
	    dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
	    weekHeader: 'Sm',
	    dateFormat: 'dd/mm/yy',
	    firstDay: 1,
	    isRTL: false,
	    showMonthAfterYear: false,
	    yearSuffix: ''
	};

	$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );
	
	$( "#txt-fe_inicial" ).datepicker({
		changeMonth: true,
		changeYear: true,
		maxDate: $("#txt-fe_final").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_final").datepicker("option", "minDate", selectedDate);
		},
	});

    $( "#txt-fe_final" ).datepicker({
    	changeMonth: true,
    	changeYear: true,
		minDate: $("#txt-fe_inicial").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_inicial").datepicker("option", "maxDate", selectedDate);
		}
    });

	//Date picker invoice
	$( '.date-picker-invoice' ).datepicker({
		changeMonth: true,
		changeYear: true,
		autoclose : true,
		maxDate: new Date(fYear, fToday.getMonth(), fDay),
		todayHighlight: true
	})

	$( '#cbo-filtro-tipo_documento' ).change(function() {
		var tipo_filtro = $( '#cbo-filtro-tipo_documento' ).data('tipo');
		searchSalesSerial(tipo_filtro);
	});
});

(function() {
  /**
   * Ajuste decimal de un número.
   *
   * @param {String}  tipo  El tipo de ajuste.
   * @param {Number}  valor El numero.
   * @param {Integer} exp   El exponente (el logaritmo 10 del ajuste base).
   * @returns {Number} El valor ajustado.
   */
  function decimalAdjust(type, value, exp) {
    // Si el exp no está definido o es cero...
    if (typeof exp === 'undefined' || +exp === 0) {
      return Math[type](value);
    }
    value = +value;
    exp = +exp;
    // Si el valor no es un número o el exp no es un entero...
    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
      return NaN;
    }
    // Shift
    value = value.toString().split('e');
    value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
  }

  // Decimal round
  if (!Math.round10) {
    Math.round10 = function(value, exp) {
      return decimalAdjust('round', value, exp);
    };
  }
  // Decimal floor
  if (!Math.floor10) {
    Math.floor10 = function(value, exp) {
      return decimalAdjust('floor', value, exp);
    };
  }
  // Decimal ceil
  if (!Math.ceil10) {
    Math.ceil10 = function(value, exp) {
      return decimalAdjust('ceil', value, exp);
    };
  }
})();

function calcAmounts(fNum1, sTipoOperador, fNum2, fResult, fImpuesto) {
	var fNum1 = parseFloat($('#' + fNum1).val());
	var fNum2 = parseFloat($('#' + fNum2).val());
	var fDecimal = 4;
	//1. Consultar si el valor venta, igv, precio y cantidad se guardará a 4 decímales, ya que la tabla tiene esos campos a 4 decímales.
	//2. Consultar, el item cuando se guarda en el detalle, graba el tipo de impuesto

	if ( sTipoOperador=='*' ) {//input cantidad y precio
		fNum1 = isNaN(fNum1) ? 0 : fNum1;
		fNum2 = isNaN(fNum2) ? 0 : fNum2;
		
		$('#' + fResult).val( Math.round10((fNum1 * fNum2), -fDecimal) );
		if ( $('#txt-subtotal' ).val() !== undefined ) {
			var ss_subtotal = ($('#' + fResult).val() / parseFloat($('#' + fImpuesto).val()) );
			$('#hidden-subtotal' ).val( Math.round10( ss_subtotal, -fDecimal) );
			$('#txt-subtotal' ).val( Math.round10( ss_subtotal, -fDecimal) );
			$('#txt-igv' ).val( Math.round10( $('#' + fResult).val() - ss_subtotal, -fDecimal) );
		}
	} else if (sTipoOperador === '/') {//input total
		fNum1 = isNaN(fNum1) ? 0 : fNum1;
		$('#' + fResult).val( fNum1 / fNum2);
		if ( $('#txt-subtotal' ).val() !== undefined) {
			// fNum1 = input txt-total
			var ss_subtotal = ( fNum1 / parseFloat($('#' + fImpuesto).val()) );
			$('#hidden-subtotal' ).val( Math.round10( ss_subtotal, -fDecimal) );
			$('#txt-subtotal' ).val( Math.round10( ss_subtotal, -fDecimal) );
			$('#txt-igv' ).val( Math.round10( fNum1 - ss_subtotal, -fDecimal) );
		}
	} else if (sTipoOperador === '-') {//input descuento
		fNum2 = isNaN(fNum2) ? 0 : fNum2;

		//Entra si es exonerada
		if ( $( '#cbo-add-exonerado' ).val() == 'S' || $( '#hidden-codigo_impuesto_item' ).val().length == 0 )
			fNum1 = Math.round10( parseFloat($('#txt-cantidad' ).val()) * parseFloat($('#txt-precio_venta' ).val()), -fDecimal);

		$('#' + fResult).val( Math.round10( fNum1 - fNum2, -fDecimal) );
		if ( $('#txt-subtotal' ).val() !== undefined) {
			var ss_total = ($('#' + fResult).val() * parseFloat($('#' + fImpuesto).val()) );

			//Entra si es exonerada
			if ( $( '#cbo-add-exonerado' ).val() == 'S' || $( '#hidden-codigo_impuesto_item' ).val().length == 0 )
				ss_total = ($('#' + fResult).val());

			$('#txt-total' ).val( Math.round10( ss_total, -fDecimal) );
			$('#txt-igv' ).val( Math.round10( ss_total - $('#' + fResult).val(), -fDecimal) );
		}
	}
}

function searchSalesSerial(tipo_filtro){
	if (tipo_filtro === 'buscar')
		$( '#cbo-filtro-serie_documento' ).html('<option value="" selected="selected">Todos</option>');
	else if (tipo_filtro === 'add')
		$( '#cbo-filtro-serie_documento' ).html('<option value="" selected="selected">Seleccionar</option>');

	if ( $( '#cbo-filtro-tipo_documento' ).val() > 0) {
		var params = {
			action: 'search-sales_serial',
			iTipoDocumento: $( '#cbo-filtro-tipo_documento' ).val(),
		}

		url = '/sistemaweb/ventas_clientes/facturas_venta.php';
			
		$.post( url, params, function( response ) {
			for (var i = 0; i < response.arrData.length; i++)
				$( '#cbo-filtro-serie_documento' ).append( '<option value="' + $.trim(response.arrData[i].id) + '" data-ialmacen="' + $.trim(response.arrData[i].ch_almacen) + '">' + response.arrData[i].id + '</option>' );
		}, "json");
	}
}

function sTypeDate(sTypeName, dValue, sCharacter){
	var arrDate;
	if (sTypeName === 'fecha_dmy') {
		arrDate = dValue.split(sCharacter);
		return arrDate[2] + '-' + arrDate[1] + '-' + arrDate[0];
	} else if (sTypeName === 'fecha_ymd') {
		arrDate = dValue.split(sCharacter);
		return arrDate[2] + '/' + arrDate[1] + '/' + arrDate[0];
	}
}

function number_format(amount, decimals) {
    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0) 
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}

function scrollToError($id_element){
	$("html, body").animate({scrollTop: $id_element.offset().top}, 400);
}