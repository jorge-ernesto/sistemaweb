function mueveReloj(k_par){ 
    momentoActual = new Date() 
    hora = momentoActual.getHours() 
    minuto = momentoActual.getMinutes() 
    segundo = momentoActual.getSeconds() 

    str_segundo = new String (segundo) 
    if (str_segundo.length == 1) 
       segundo = "0" + segundo 

    str_minuto = new String (minuto) 
    if (str_minuto.length == 1) 
       minuto = "0" + minuto 

    str_hora = new String (hora) 
    if (str_hora.length == 1) 
       hora = "0" + hora 

    horaImprimible = hora + " : " + minuto + " : " + segundo 
    eval("document."+k_par+".value = horaImprimible")
    

    setTimeout("mueveReloj('"+k_par+"')",1000) 
} 
