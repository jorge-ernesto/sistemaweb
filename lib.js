function chequear(texto){
 if (texto == null || texto == "") //chequeamos que este avacio o NULL
 alert("Esta Vacio")
 else
 alert("Esta Lleno")
 }

function esIntguion(e) {
 var charCode
 if (navigator.appName == "Netscape") 
 charCode = e.which  
 else
 charCode = e.keyCode 
 status = charCode
 if (charCode>31 && (charCode<45 || (charCode>45 && charCode<48) || charCode>57))
 {  //  Chequeamos que sea un numero comparandolo con los valores ASCII
  alert("Ingrese Numeros !!")
  return false
 }
 return true
}


function esInteger(e) {
 var charCode
 if (navigator.appName == "Netscape") // Veo si es Netscape o Explorer (mas adelante lo explicamos)
 charCode = e.which  // leo la tecla que ingreso
 else
 charCode = e.keyCode  // leo la tecla que ingreso
 status = charCode
 var cont=0
 if(cont>1) { return false }
 else
 {
 if (charCode > 31 && (charCode < 46 || (charCode>46 && charCode<48) || charCode> 57))    
 {  //  Chequeamos que sea un numero comparandolo con los valores ASCII

  alert("Ingrese Numeros !!!")
  return false
 }
 else{
	if(charCode==46) 
	{
	  cont=cont++;	  		
	}
     }
 return true
 }
}

function esIntspto(e) {
 var charCode
 if (navigator.appName == "Netscape") // Veo si es Netscape o Explorer (mas adelante lo explicamo$
 charCode = e.which  // leo la tecla que ingreso
 else
 charCode = e.keyCode  // leo la tecla que ingreso
 status = charCode
 if (charCode> 31 && (charCode <48 || charCode> 57))
 {  //  Chequeamos que sea un numero comparandolo con los valores ASCII
  alert("Ingrese Numeros !!")
  return false
 }
 return true
}

function enRango(texto){
 num = parseInt(texto) // tranformamos el texto en numerico
 if (num>= 1 && num <=21){ // comparamos ...
 alert("Esta entre 1 y 21")
 return false
 }
 alert("NO Esta entre 1 y 21")
 return true
}

function esEmail(texto) 
{ 
 var textoStr = texto.toString() // transformo a string todo el campo
 var tiene = 0
 
 if(texto.length==0){  }
 else
  {
   for(var i=0;i<texto.length;i++)
   { // recorro letra por letra
      var oneChar = textoStr.charAt(i) 
      if (oneChar == "@")
      { // busco una arroba en cada letra
        tiene = 1 
      } 
   }
 
   if (tiene == 1){ return true } 
   else { 
          alert("El Email no es valido") 
	  return false 
        } 

  }
}