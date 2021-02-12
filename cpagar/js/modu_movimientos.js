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

function PaginarRegistros(rxp, valor)
{
   send = document.getElementsByName('task')[0].value;
   urlPagina = 'control.php?rqst=MOVIMIENTOS.'+send+'&task='+send+'&rxp='+rxp+'&pagina='+valor;
    document.getElementById('control').src = urlPagina;
}

function setCalcularAplicaciones(montos)
{
    //ts_cargo = document.getElementsByName('TotalSaldoCargo')[0].value;
    ts_abono = document.getElementsByName('TotalSaldoAbono')[0].value;
    total_saldo = document.getElementsByName('pro_cab_impsaldo')[0].value;
    if(montos.checked==true)
    {
        url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESDET&operacion=sumar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
        document.getElementById('control').src = url;
    return;
    }else{
        url = 'control.php?rqst=MOVIMIENTOS.APLICACIONES&action=setCalcularAplicaciones&task=APLICACIONESDET&operacion=restar&montos='+montos.value+'&total_saldo_abono='+ts_abono+'&total_import_saldo='+total_saldo;
        document.getElementById('control').src = url;
    return;
    }
}



