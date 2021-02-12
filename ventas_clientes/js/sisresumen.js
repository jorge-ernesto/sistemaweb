function getRegistroCli(campo){
  url = 'control.php?rqst=FACTURACION.RESUMEN&action=setRegistroCli&task=RESUMEN&codigocli='+campo;
  document.getElementById('control').src = url;
  return;
}
function setRegistroCli(campo){
  txt_campo = document.getElementsByName('busqueda[codigo]')[0];
  txt_campo.value = campo;
  return;
}
