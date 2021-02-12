function openHelperWindow(name){
    	window.open("/sistemaweb/helper/helper.php?action=ARTICULO&dstname=" + name, "wndHelper", "dependent,with=370,height=400,menubar=no,resizable=no,toolbar=no");
}

function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
	window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

function mostrarAyudita(url,cod,des,mes3,mes2,mes1,actual,mini,maxi,canti,consulta,valor) {
	url = url+"?cod="+cod+"&des="+des+"&mes3="+mes3+"&mes2="+mes2+"&mes1="+mes1+"&actual="+actual+"&mini="+mini+"&maxi="+maxi+"&canti="+canti+"&consulta="+consulta+"&valor="+valor;
	window.open(url,'miwin','width=450,height=260,scrollbars=yes,menubar=no,left=390,top=20');
}

function buscar(opcion) {

	var codigo = document.getElementsByName('art_desde')[0];
	var codigo2 = document.getElementsByName('art_desde2')[0];

	var linea = document.getElementsByName('art_linea')[0];
	var linea2 = document.getElementsByName('art_linea2')[0];

	if(opcion == 'C') {
		document.getElementById("label").style.display="inline";
		document.getElementById("label2").style.display="inline";
		codigo.style.display = 'inline';
		codigo2.style.display = 'inline';
		//document.getElementById("imgc").style.visibility="visible";//imagen codigo
		linea.style.visibility = 'hidden';
		linea2.style.visibility = 'hidden';
		document.getElementById("l").style.visibility="hidden";
		document.getElementById("l2").style.visibility="hidden";
		//document.getElementById("imgl").style.visibility="hidden";//imagen linea
		linea.value = '';
		linea2.value = '';
	}else{

		document.getElementById("label").style.display="none";
		document.getElementById("label2").style.display="none";
		codigo.style.display = 'none';
		codigo2.style.display = 'none';
		codigo.value = '';
		codigo2.value = '';
		//document.getElementById("imgc").style.visibility="hidden";//imagen codigo
		linea.style.visibility = 'visible';
		linea2.style.visibility = 'visible';
		document.getElementById("l").style.visibility="visible";
		document.getElementById("l2").style.visibility="visible";
		//document.getElementById("imgl").style.visibility="visible";//imagen linea
	}

}
