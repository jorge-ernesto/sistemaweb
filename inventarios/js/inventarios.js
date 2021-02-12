
function abrirImportarStock()
{
    window.open("/sistemaweb/inventarios/inv_importar_stock.php", "wndImportarStock", "dependent,height=420,width=400,menubar=no,resizable=no,toolbar=no");
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
function Comparar_Data(String1,String2) {
    //alert(String1+'otro'+String2);
	Data1_arr = String1.split('/')
	Data2_arr = String2.split('/')
	
	String1 = Data1_arr[2] + Data1_arr[1] + Data1_arr[0]
	String2 = Data2_arr[2] + Data2_arr[1] + Data2_arr[0]
	String1 = parseFloat(String1);
	String2 = parseFloat(String2);
	//alert('numero 1:'+String1+' numero 2:'+String2);
	if (String1 <= String2) {
	return true;
	}
	return false;
}

/*Validacion de fechas*/
function valSep(oTxt){
var bOk = false;
bOk = bOk || ((oTxt.value.charAt(2) == "-") && (oTxt.value.charAt(5) == "-"));
bOk = bOk || ((oTxt.value.charAt(2) == "/") && (oTxt.value.charAt(5) == "/"));
return bOk;
}
function finMes(nMes){
var nRes = 0;
switch (nMes){
case 1: nRes = 31; break;
case 2: nRes = 29; break;
case 3: nRes = 31; break;
case 4: nRes = 30; break;
case 5: nRes = 31; break;
case 6: nRes = 30; break;
case 7: nRes = 31; break;
case 8: nRes = 31; break;
case 9: nRes = 30; break;
case 10: nRes = 31; break;
case 11: nRes = 30; break;
case 12: nRes = 31; break;
}
return nRes;
}

function valFecha(dia, mes, ano){
var bOk = true;
var diai = parseInt(dia,10);
var mesi = parseInt(mes,10);
var anoi = parseInt(ano,10);
if (dia>0 && dia<=finMes(mesi)) 

if (oTxt.value != ""){
bOk = bOk && (valAno(oTxt));
bOk = bOk && (valMes(oTxt));
bOk = bOk && (valDia(oTxt));
bOk = bOk && (valSep(oTxt));
if (!bOk){
alert("Fecha invalida");
oTxt.value = "";
oTxt.focus();
}
}
}
