<?php

 /* para cada registro */
class paginador {  		//Inicio de Clase paginador
	var $pagina;		//Pagina en la que se encuentra
	var $pp; 		//número de Registros por Página
	var $numero_registros; 	//Total de registros recuperados de la consulta.
    
	function __construct ($numero_registros, $pp, $pagina) {
		$this->numero_registros = $numero_registros; 
		$this->pagina           = $pagina;
		$this->pp               = $pp;
	}

	function numero_paginas() {   
		$integer_div = floor($this->numero_registros / $this->pp);
		$modulo = $this->numero_registros % $this->pp;
		if ($modulo>0)	{
			return  ($integer_div + 1);
		}else{
			return $integer_div;
		}
	}
    
	function paginas() {
		if ($this->pagina < 1)	{
			return  1;
		}
		elseif ($this->pagina > $this->numero_paginas()){
	   		return $this->numero_paginas();
		}else{
	  		return $this->pagina;
		}
    	}
    
    	function partir() {
        	return (($this->paginas()-1 ) * $this->pp);
    	}
    
    	function fin() {    
        	$remainer = ($this->numero_registros - $this->partir()); 
		if ($this->pp > $remainer){
			$blocksize = $remainer;
		}else{
			$blocksize = $this->pp;
		}
		return ($this->partir() + $blocksize);    
    	}
    
    	function pagina_previa() {
        	if ($this->paginas() > 1){
			return ($this->paginas() - 1);
		}else{
			return 1;
		}    
    	}
    
    	function pagina_siguiente () {
    		if ($this->paginas() < $this->numero_paginas()){
	   		return ($this->paginas() + 1);
		}else{
	   		return $this->numero_paginas();
		}
    	}
    
    	function primera_pagina() {
        	return 1;
    	}
    
    	function ultima_pagina () {
        	return $this->numero_paginas();
    	}

}//Fin de Clase paginador
?>
