/*
  Fecha de creacion     : Marzo 6, 2012, 9:31:17
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion : 
  Modificado por        : 
  JS de del mantenimiento de la tabla f_pump_pos
*/


function regresar(){
   window.location = '/sistemaweb/combustibles/mant_f_pump_pos.php';
}

function PaginarRegistros(rxp, valor)
{
    //rxp = rxp.value;
    urlPagina = 'control.php?rqst=MAESTROS.NEW_POS_LADOS&task=NEW_POS_LADOS&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}