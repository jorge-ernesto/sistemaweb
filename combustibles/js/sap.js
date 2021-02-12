function getSapMapeoTablas(){
	$( '#cbo-sap-mapeo-tablas' ).change(function() {
		var id_tipo_tabla = $(this).val();
		var url = "control.php?rqst=MAESTROS.SAPMAPEOTABLAS&action=Buscar&id_tipo_tabla=" + id_tipo_tabla;
		control.location.href = url;
	});
}

function updateSapMapeoTabla($id_tipo_tabla, $id_tipo_tabla_detalle){
	var $valueSapCodigo = document.getElementById('valueSapCodigo' + $id_tipo_tabla + $id_tipo_tabla_detalle).value;
	var url = "control.php?rqst=MAESTROS.SAPMAPEOTABLAS&action=UPD&id_tipo_tabla=" + $id_tipo_tabla + "&id_tipo_tabla_detalle=" + $id_tipo_tabla_detalle  + "&valueSapCodigo=" + $valueSapCodigo;
	control.location.href = url;
}