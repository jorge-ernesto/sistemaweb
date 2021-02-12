function actVTotal(valor){ 
	var vventa = document.getElementsByName('rgvventa')[0];
	var impuesto = document.getElementsByName('rgimpuesto')[0];
	var viaf = document.getElementsByName('rgimpinafecto')[0];
	
	var dif0 = Number(valor) - ( Number(impuesto.value)+Number(vventa.value) );
	var dif = Math.round(dif0*100)/100;
	viaf.value = dif;		
}

function actVVenta(valor, igv){ 
	var vtotal = document.getElementsByName('rgvtotal')[0];
	var impuesto = document.getElementsByName('rgimpuesto')[0];
	var viaf = document.getElementsByName('rgimpinafecto')[0];
	var dif1 = Number(valor)*(Number(igv)-1);
	var dif11 = Math.round(dif1*100)/100;
	impuesto.value = dif11;
	var dif2 = Number(vtotal.value) - ( Number(impuesto.value)+ Number(valor) );
	var dif22 = Math.round(dif2*100)/100;
	viaf.value = dif22;	
}

function actImpuesto(valor){ 
	var vtotal = document.getElementsByName('rgvtotal')[0];
	var vventa = document.getElementsByName('rgvventa')[0];
	var viaf = document.getElementsByName('rgimpinafecto')[0];
	
	var dif0 = Number(vtotal.value) - ( Number(vventa.value)+ Number(valor) );
	var dif = Math.round(dif0*100)/100;
	viaf.value = dif;	
}


