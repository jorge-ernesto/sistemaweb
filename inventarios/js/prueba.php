<script>
var veces = 0;
var veces2 = 0;

function getObj(name, nest) {
if (document.getElementById){
return document.getElementById(name).style;
}else if (document.all){
return document.all[name].style;
}else if (document.layers){
if (nest != ''){
return eval('document.'+nest+'.document.layers["'+name+'"]');
}
}else{
return document.layers[name];
}
}

//Hide/show layers functions
function showLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "visible";
}

function hideLayer(layerName, nest){
var x = getObj(layerName, nest);
x.visibility = "hidden";

}

function mostrarFila(fila){
showLayer(fila);
}

function ocultarFila(fila){
hideLayer(fila);
}

function hacerDesaparecer(id){
	eval(id+".style.display='none' ");
	//alert(id+".style.display='none' ");
	//id.style.display='none'; 
}

function hacerAparecer(id){
	eval(id+".style.display='' ");
	//id.style.display='';		
}


function dormir(msg){
		if(veces<5){
		setTimeout("dormir('dormir')",500);
		//alert(msg);
		if(veces==0){hacerDesaparecer("a");}
		if(veces==1){hacerDesaparecer("b");}
		if(veces==2){hacerDesaparecer("c");}
		if(veces==3){hacerDesaparecer("d");}
		if(veces==4){hacerDesaparecer("e");} 
		}
		veces++;
		
				
}

function dormir2(msg){
		
		if(veces2<5){
		setTimeout("dormir2('dormir2')",500);
		//alert(msg);
		if(veces==0){hacerAparecer("a");}
		if(veces==1){hacerAparecer("b");}
		if(veces==2){hacerAparecer("c");}
		if(veces==3){hacerAparecer("d");}
		if(veces==4){hacerAparecer("e");} 
		}
		veces2++;		
}

</script>
<form name="form1" method="post">
<table width="592" border="1" id="fila">
  <tr id="a">
      <td width="308" >1</td>
      <td width="268">&nbsp;</td>
  </tr>
    <tr id="b"> 
      <td>2</td>
    <td>&nbsp;</td>
  </tr>
  <tr id="c">
      <td>3</td>
      <td>&nbsp;</td>
  </tr>
  <tr id="d">
      <td>4</td>
    <td>&nbsp;</td>
  </tr>
  <tr id="e">
      <td>5</td>
      <td><a href="http://www.stconsulting.com.pe/JAmanda.ppt">aaaaaa</a></td>
  </tr>
</table>
  <input name="Aparecer" type="button" id="Aparecer" onClick="hacerAparecer(fila);" value="Submit">
  <input type="button" name="Submit2" value="Desaparecer" onClick="hacerDesaparecer('fila');">
  <input type="button" name="Submit22" value="Esperar" onClick="dormir('hola');">
  <input type="button" name="Submit222" value="Esperar" onClick="dormir2('hola');">
</form>