/*

  @Funciones JavaScript 
  @Sistema de Conbustibles :: ACOSA
  @TBCA modìficado por @MATT

*/

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
  else
    document.getElementById('control').src = accionN;
}

function confirmarForm(pregunta, form){
  if(confirm(pregunta)) 
    return true;
  return false;
}

//Mètodos para Sobrantes y Faltantes de Combustibles

function getSucursal(codigo)
{
    url = 'control.php?rqst=REPORTES.SOBRA_FALTA&action=setTanques&codigo='+codigo;
    document.getElementById('control').src = url;
    return;
}

