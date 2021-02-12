function PaginarRegistros(rxp, valor, ch_tipo_consulta, tm0, tm1, tm2, td0, td1, td2, Bonus, ch_almacen, ch_lado, ch_caja, ch_turno, ch_periodo, ch_mes, ch_dia_desde, ch_dia_hasta, art_codigo, ruc, cuenta, tarjeta, ch_tipo){   	
	urlPagina = 'control.php?rqst=FACTURACION.TICKETSPOS&action=Buscar&rxp='+rxp+'&pagina='+valor+'&ch_tipo_consulta='+ch_tipo_consulta+'&tm[0]='+tm0+'&tm[1]='+tm1+'&tm[2]='+tm2+'&td[0]='+td0+'&td[1]='+td1+'&td[2]='+td2+'&Bonus='+Bonus+'&ch_almacen='+ch_almacen+'&ch_lado='+ch_lado+'&ch_caja='+ch_caja+'&ch_turno='+ch_turno+'&ch_periodo='+ch_periodo+'&ch_mes='+ch_mes+'&ch_dia_desde='+ch_dia_desde+'&ch_dia_hasta='+ch_dia_hasta+'&art_codigo='+art_codigo+'&ruc='+ruc+'&tarjeta='+tarjeta+'&ch_tipo='+ch_tipo;
	document.getElementById('control').src = urlPagina;	
}

function mostrarAyuda(url,cod,des,consulta){

url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=280,scrollbars=yes,menubar=no,left=390,top=20');
}
