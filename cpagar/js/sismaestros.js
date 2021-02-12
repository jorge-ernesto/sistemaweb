function PaginarRegistros(rxp, valor)
{
   //rxp = rxp.value;
   send = document.getElementsByName('task')[0].value;
   urlPagina = 'control.php?rqst=MAESTROS.'+send+'&task='+send+'&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function openPDFWindow()
{
    window.open('control.php?rqst=REPORTES.REIMPRESION&task=IMPRESION&action=PDF', 'pdf');
}