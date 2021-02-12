<html>
<head>
<title>Sistema de Inventarios - Kardex</title>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<script language="JavaScript" src="js/kardex.js"></script>
<!--<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>-->
<!--<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
<script src="/sistemaweb/js/jquery-ui.js"></script>
<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>-->
</head>
<body>
<?php include "../menu_princ.php"; ?>
<div id="header">&nbsp;</div>
<div id="content">
<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=REPORTES.KARDEXACT" frameborder="1" width="10" height="10"></iframe>

<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
<script src="/sistemaweb/js/jquery-ui.js"></script>
<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
<script type="text/javascript">
	function autocompleteBridge(type) {
		if (type == 0) {
			//new
			var No_Producto = $("#txt-No_Producto");
			if(No_Producto.val() !== undefined){
				console.log(No_Producto.val());
				autocompleteProducto(No_Producto);
				//generalAutocomplete('#txt-No_Producto', '#txt-Nu_Id_Producto', 'getProductXByCodeOrName', ['']);
			}
		} else {
			//buscar

		}
	}

	$(window).load(function() {
	$( function() {
		//alert('hola');
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

		$.datepicker.setDefaults( $.datepicker.regional[ "es" ] );

		$( "#desde" ).datepicker({
			changeMonth: true,
			changeYear: true,
			maxDate: $("#hasta").val(),
			onClose: function (selectedDate) {
				$("#hasta").datepicker("option", "minDate", selectedDate);
			},
		});

	    $( "#hasta" ).datepicker({
	    	changeMonth: true,
	    	changeYear: true,
			minDate: $("#desde").val(),
			onClose: function (selectedDate) {
				$("#desde").datepicker("option", "maxDate", selectedDate);
			}
	    });
	})
	})
</script>
</body>
</html>
