function isNumberKey(evt)
{
	var charCode = (evt.which) ? evt.which : evt.keyCode
	if (charCode > 31 && (charCode < 48 || charCode > 57))
		return false;
	return true;
}


function modificar_igv()
{
	window.document.getElementById("igv").readOnly = false
}

function grabo_igv()
{
	window.document.getElementById("igv").readOnly = true
}

function cambiarCombo(obj) {
	if(obj.options[0].value == obj.value){
		window.document.getElementById("turno1").style.display="block";
	   	window.document.getElementById("turno2").style.display="none";
		window.document.getElementById("turno3").style.display="none";
		window.document.getElementById("turno").value = '1'
	} 
	else {
		if (obj.options[1].value == obj.value){
			window.document.getElementById("turno1").style.display="none";
		   	window.document.getElementById("turno2").style.display="block";
			window.document.getElementById("turno3").style.display="none";
			window.document.getElementById("turno").value = '2'
		} else {
			window.document.getElementById("turno1").style.display="none";
		   	window.document.getElementById("turno2").style.display="none";
			window.document.getElementById("turno3").style.display="block";
			window.document.getElementById("turno").value = '3'
		}
	}
}
