<?php

class CPaginador
{
    var $nItems=0;		// cantidad total de items
    var $itemURL="";		// URL a la que solo hay que agregarle el numero de pagina
    var $pagActual=1;		// pagina actual
    var $items_x_pagina=100;	// items por pagina
    var $nPaginas=0;		// cantidad de paginas
    var $target="";		// valor del parametro target del hipervinculo
    var $falta=10;		// cantidad de paginas que muestra como maximo

    function __construct($nItems=0, $url="", $target="", $pagina_actual=1, $items_x_pagina=100)
    {
	$this->nItems = $nItems;
	$this->itemURL = $url;
	$this->target = $target;
	$this->pagActual = $pagina_actual;
	$this->items_x_pagina = $items_x_pagina;
	$this->nPaginas = intval($nItems/$items_x_pagina);
	if (($nItems % $items_x_pagina) != 0) $this->nPaginas++;
    }

    function desde()
    {
	return ((($this->pagActual-1)*$this->items_x_pagina)+1);
    }
    
    function hasta()
    {
	return ((($this->pagActual-1)*$this->items_x_pagina)+$this->nEstaPagina());
    }

    function nEstaPagina()
    {
	if ($this->pagActual < $this->nPaginas) return $this->items_x_pagina;
	return ($this->nItems-(($this->nPaginas-1)*$this->items_x_pagina));
    }

    function obtienePaginador()
    {
	$resultado = "";

	// Muestra "Anterior"
	if ($this->pagActual > 1) {
	    $resultado .= '<a href="' . $this->itemURL . ($this->pagActual-1) . '" target="' . $this->target . '">[&lt;- Anterior]</a>';
	}
	
	if ($this->nPaginas > $this->falta+1) {
	    if ($this->pagActual > intval($this->falta/2)) {
		$inicio = $this->pagActual - intval($this->falta/2);
	    }
	    else $inicio = 1;
	    
	    $this->falta -= ($this->pagActual - $inicio);
	    
	    if ($this->pagActual+$this->falta > $this->nPaginas) $fin = $this->nPaginas;
	    else $fin = $this->pagActual+$this->falta;
	    
	    $this->falta -= ($fin - $this->pagActual);
	    
	    if ($this->falta > 0) $inicio -= $this->falta;
	}
	else {
	    $inicio = 1;
	    $fin = $this->nPaginas;
	}

	for ($i = $inicio; $i <= $fin; $i++) {
	    if ($i == $this->pagActual) $resultado .= "[$i]";
	    else $resultado .= '<a href="' . $this->itemURL . $i . '" target="' . $this->target . '">[' . $i . ']</a>';
	    $resultado .= "\n";
	}
	
	if ($this->pagActual < $this->nPaginas) {
	    $resultado .= '<a href="' . $this->itemURL . ($this->pagActual+1) . '" target="' . $this->target . '">[Siguiente -&gt;]</a>';
	}
	
	return $resultado;
    }
}

