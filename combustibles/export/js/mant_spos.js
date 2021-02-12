
/*
  Fecha de creacion     : Marzo 6, 2012, 9:31:31
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion : 
  Modificado por        : 
  JS de del mantenimiento de la tabla spos
*/

function regresar(){
    window.location = '/sistemaweb/combustibles/mant_spos.php';
}

function PaginarRegistros(rxp, valor)
{
    //rxp = rxp.value;
    urlPagina = 'control.php?rqst=MAESTROS.NEW_POS_PUNTO_VENTA&task=NEW_POS_PUNTO_VENTA&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}
