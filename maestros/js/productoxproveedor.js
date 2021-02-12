function mostrarAyuda(url,cod,des,consulta){
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=390,top=20');
}

function regresar(){
	url = 'control.php?rqst=MAESTROS.PRODXPROV';
    document.getElementById('control').src = url;
    return;
}

function frmProveedorEliminar() {
	control.location.href="control.php?rqst=PRODXPROV.BUSCAR";
}