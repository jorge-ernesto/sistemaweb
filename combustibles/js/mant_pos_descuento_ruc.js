/*
  Fecha de creacion     : Marzo 8, 2012, 9:31:17
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion : 
  Modificado por        : 
  JS de del mantenimiento de la tabla pos_descuento_ruc
*/


function regresar(){
    window.location = '/sistemaweb/combustibles/mant_pos_descuento_ruc.php';
}

function PaginarRegistros(rxp, valor)
{
    //rxp = rxp.value;
    urlPagina = 'control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function buscarArticulo() {
    window.open('/sistemaweb/combustibles/maestros/buscar_articulo.php',
        'win_clientes','width=400,height=250,scrollbars=yes,menubar=no');
}
function cerrar() {
    window.close();
}

function confirmarLink(pregunta, accionY, accionN, target){
  if(confirm(pregunta))
    document.getElementById('control').src = accionY;
   else{
    //document.getElementById('control').src = accionN;
   document.forms[0].action= "control.php?rqst=MAESTROS.POS_DESCUENTO_RUC&task=POS_DESCUENTO_RUC";
   }
}