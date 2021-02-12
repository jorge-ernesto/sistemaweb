function Ubicaciones() {
	var almacen;

	almacen = document.getElementById('almacen').value;

	if (document.getElementById('almacen').value == 'SELE')
		return false;
		
	url = 'control.php?rqst=REPORTES.AJUSTEINVENTARIO&task=REGISTROS&action=Ubica&almacen='+almacen;
	document.getElementById('control').src = url;
	return;
}

function validar(e,tipo){

	tecla=(document.all)?e.keyCode:e.which;
	if (tecla==13 || tecla==8)
		return true;
	
	switch(tipo){
		/*letras y numeros, puntos */
		case 1: patron=/[A-Z a-z0-9./:,;.-]/;break;
		/*solo numeros enteros */
		case 2: patron=/[0-9]/;break;
		/*solo numeros dobles*/
		case 3: patron=/[0-9.]/;break;
		/*solo letras*/
		case 4: patron=/[A-Z a-z]/;break;
		/*telefonos y faxes*/
		case 5: patron=/[0-9/-]/;break;
	}

	teclafinal=String.fromCharCode(tecla);
	return patron.test(teclafinal);

}

function check(f, cual,check, key){

	if(document.getElementById(key).value.length < 1){
		document.getElementById(check).checked = false;
	}else{
		document.getElementById(check).checked = true;	
		document.getElementById('proce').disabled = false;
	}
	
}

function Procesar(pregunta, f, cual, almacen, ubica, producto){

  	if(confirm(pregunta)){

		codigo	= new Array();
		fisico	= new Array();

		if(f[cual].length === undefined){
			fisico = document.getElementById('stockfisico'+producto).value;
			codigo = producto;
		}else{

			for (var i = 0, total = f[cual].length; i < total; i++){

				if (f[cual][i].checked){

					var valor, stock, producto;

					producto	= f[cual][i].value;

					valor		= 'stockfisico' + producto;
					stock		= document.getElementById(valor).value;

					codigo[codigo.length] = producto;
					fisico[fisico.length] = stock;

				}

			}

		}

		url = 'control.php?rqst=REPORTES.AJUSTEINVENTARIO&action=fisico&task=PROCESANDO&almacen='+almacen+'&producto='+codigo+'&stkfisico='+fisico+'&ubica='+ubica;
		document.getElementById('control').src = url;
		return;

	}

}

function Excel(almacen, producto, ubica){

	url = 'control.php?rqst=REPORTES.AJUSTEINVENTARIO&action=reporte&task=EXCEL&almacen='+almacen+'&codigo='+producto+'&ubica='+ubica;
	document.getElementById('control').src = url;
	return;

}



