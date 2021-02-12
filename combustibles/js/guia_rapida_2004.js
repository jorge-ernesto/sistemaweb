/*

  Funciones JavaScript 
  Sistema de Contabilidad ACOSA
  @TBCA Modificado por @MATT

*/





function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   urlPagina = 'control.php?rqst=MAESTROS.GUIARAPIDA&task=GUIARAPIDA&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}




