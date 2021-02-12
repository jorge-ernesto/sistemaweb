<html>
<head>
<link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
<link rel="stylesheet" href="/sistemaweb/assets/css/style.css" type="text/css">
<script language="JavaScript1.2" src="/sistemaweb/images/stm31.js"></script>
<script language="JavaScript" src="/sistemaweb/maestros/js/compras.js"></script>
<script language="JavaScript" src="/sistemaweb/maestros/js/sismaestros.js"></script>
  <script src="/sistemaweb/js/jquery-2.0.3.js" type="text/javascript"></script>
  <script charset="utf-8" type="text/javascript">
	window.onload = function () {
		$(document).ready(function() {
			$( document ).on('click', '#btn-excel-lista_precio', function(event) {
				$( "#btn-excel-lista_precio" ).prop('disabled', true);
				$( "#btn-excel-lista_precio" ).append( 'Cargando...' );

				var url = 'control.php';
				var params = {
					rqst: 'MAESTROS.ITEMS',
					action: 'ExcelListaPrecios',
				}

				$.post( url, params, function( response ) {
					if ( response.sStatus=='success' ){
						iCountData = response.arrData.length;
						/*
  						for(let i=0;i<iCountData;i++) {
  							console.log(response.arrData[i].no_lista_precio);
						}
						*/

						//var tab_text="<table border='2px'><tr bgcolor='#87AFC6'><td colspan=''>hola</tr>";
						var tab_text="<table border='0.5px'>";
						var textRange;
						var j=0;

						tab = document.getElementById('headerTable');

  						for(let i=0;i<iCountData;i++) {
  							tab_text=tab_text+"<tr>";
  							tab_text=tab_text+"<td>";
						    tab_text=tab_text+response.arrData[i].no_lista_precio;
						    tab_text=tab_text+"</td>";
  							tab_text=tab_text+"<td>";
						    tab_text=tab_text+response.arrData[i].nu_codigo_item;
						    tab_text=tab_text+"</td>";
  							tab_text=tab_text+"<td>";
						    tab_text=tab_text+response.arrData[i].no_nombre_item;
						    tab_text=tab_text+"</td>";
  							tab_text=tab_text+"<td>";
						    tab_text=tab_text+response.arrData[i].pre_precio_act1;
						    tab_text=tab_text+"</td>";
						    tab_text=tab_text+"</tr>";
						}

						tab_text=tab_text+"</table>";
						tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
						tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
						tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input params

						var ua = window.navigator.userAgent;
						var msie = ua.indexOf("MSIE "); 

						if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
						    txtArea1.document.open("txt/html","replace");
						    txtArea1.document.write(tab_text);
						    txtArea1.document.close();
						    txtArea1.focus(); 
						    sa=txtArea1.document.execCommand("SaveAs",true,"Say Thanks to Sumit.xls");
						} else {//other browser not tested on IE 11
							$( "#btn-excel-lista_precio" ).prop('disabled', false);
							$( "#btn-excel-lista_precio" ).html( '<img src="/sistemaweb/icons/gexcel.png" align="right"> Excel Lista Precio' );
						    sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  
						}

						return (sa);
					} else {
						alert(response.sMessage);
					}
				}, "json");
			})
		});
	}
  </script>
</head>
<body>
<?php include "../menu_princ.php"; ?>
<div id="content">
    <div id="content_title">&nbsp;</div>
    <div id="content_body">&nbsp;</div>
    <div id="content_footer">&nbsp;</div>
</div>
<div id="footer">&nbsp;</div>
<iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.ITEMS&pagina=1&clear=1" frameborder="1" width="10" height="10"></iframe>
</body>
</html>
