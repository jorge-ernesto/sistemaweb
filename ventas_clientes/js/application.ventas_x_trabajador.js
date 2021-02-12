$(document).ready(function() {
	//Iniciando valores
	$( "#txt-fe_emision" ).datepicker({
		changeMonth: true,
		changeYear: true,
		autoclose : true,
		maxDate: $("#txt-fe_vencimiento").val(),
		onClose: function (selectedDate) {
			$("#txt-fe_vencimiento").datepicker("option", "minDate", selectedDate);
		},
		todayHighlight: true
	});

	/*
	Name function: searchSalesInvoice
	Parameters: int = number page - example = 1;
	*/
	$( document ).on('click', '.pagination-previous', function(event) {
		var iPagePrev = $( '#pageActual' ).val();
		if(parseInt(iPagePrev) > 1) {
			iPagePrev--;
			searchSalesInvoice(iPagePrev);
		}
	})

	$( document ).on('click', '.pagination-link', function(event) {
		var iPage = $(this).attr( "data-page" );
		searchSalesInvoice(iPage);
	})

	$( document ).on('click', '.pagination-next', function(event) {
		var iPageNext = $( '#pageActual' ).val();
		var iPageQuantity = $( '#cantidadPage' ).val();

		if(parseInt(iPageNext) < parseInt(iPageQuantity)) {
			iPageNext++;
			searchSalesInvoice(iPageNext);
		}
	})
	// /. Paginador


	$( document ).keyup(function(event){
		if(event.which == 13){// ENTER = Guardar
			searchSalesInvoice(1);
		}
	});

	$( '#btn-html-sales-employee' ).click(function() {
		searchSalesInvoice(1);
	});
});

function autocompleteBridge(type) {
	if (type == 1) {
		var items = $("#txt-filtro-item-nombre");
		if(items.val() !== undefined) {
			generalAutocomplete('#txt-filtro-item-nombre', '#hidden-filtro-item-id', 'getProductXByCodeOrName', ['']);
		}
	} else if (type == 2) {// Cliente buscar
		var customers = $("#txt-filtro-trabajador-nombre");
		if(customers.val() !== undefined) {
			generalAutocomplete('#txt-filtro-trabajador-nombre', '#hidden-filtro-trabajador-id', 'getEmployees', ['']);
		}
	}
}