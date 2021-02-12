 function nuevoAjax(){
                        var xmlhttp=false;
                         try {
                          xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                         } catch (e) {
                          try {
                           xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                          } catch (E) {
                           xmlhttp = false;
                          }
                         }
     
                        if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
                          xmlhttp = new XMLHttpRequest();
                        }
                        return xmlhttp;
                        } //esto siempre se define x tipo de navegador, si soporta y se puede agregar mas catch segun uno lo necesite....
     
function cargarContenido(codpedido,ruc,fhd,placa,idplaca,galo,i){
                          
       contenedor0 = document.getElementById('resultado0'+i);
       contenedor1 = document.getElementById('resultado1'+i);
       
       contenedor2 = document.getElementById('resultado2'+i);
       contenedor3 = document.getElementById('resultado3'+i);
 
        
       ajax = nuevoAjax();
       ajax.open("GET", "control.php?rqst=MOVIMIENTOS.PROGRAMRUTA&action=Actualizar&codpedido="+codpedido+"&ruc="+ruc+"&fhd="+fhd+"&placa="+placa+"&idplaca="+idplaca+"&galo="+galo,true); 

       
        ajax.onreadystatechange=function() {
            if (ajax.readyState==4) {                   
		var l_a_json = eval('('+ajax.responseText+')');
		  var ihtml="",ihtml1=""; 
            for(val in l_a_json){
            	if(l_a_json[val][0]!=1 && l_a_json[val][0]!=2){

	 contenedor0.innerHTML = l_a_json[val][0];
	 contenedor1.innerHTML = l_a_json[val][1];
	 contenedor2.innerHTML ='<font color=blue><b>Vehiculo Actualizado!!!</b></font>';

	 }
	  if(l_a_json[val][0]==1){
	 contenedor2.innerHTML ='<font color=red><b>Este Vehiculo ya esta en uso!!!</b></font>';
	 }
	 if(l_a_json[val][0]==2){
	 contenedor2.innerHTML ='<font color=red><b>Valor vacio, asginar Placa!!!</b></font>';
	 }

//	ihtml+=l_a_json[val][0]; 
	//ihtml1+=l_a_json[val][1];
	
	}
	
            }      
     
	//  $('#con').html(ihtml);
      //      $('#con1').html(ihtml1);
          
    }    
   
   ajax.send("loQueSale="+codpedido);
 


}

