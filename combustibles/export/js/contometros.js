function PaginarRegistros(rxp, valor) {
	urlPagina = 'control.php?rqst=REPORTES.CONTOMETROS&rxp='+rxp+'&pagina='+valor;
	document.getElementById('control').src = urlPagina;
}
