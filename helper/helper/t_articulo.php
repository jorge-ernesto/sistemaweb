<?php

class ArticuloTemplate extends Template
{

    function titulo()
    {
	return '<h2><b>Busqueda por codigo</b></h2>';
    }
    
    function formSearch($destname)
    {
	$criterios = Array( "DESCRIPCION"	=>	"Descripcion",
			    "CODIGO"		=>	"Codigo"
			);

	$form = new Form('', "Buscar", FORM_METHOD_POST, "control.php", '', "control");	
	
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("rqst", "HELPER.ARTICULO"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("action", "Search"));
	$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("dstname", $destname));
	
	$form->addElement(FORM_GROUP_MAIN, new form_element_text("Buscar:", "texto", '', '', '', 20, 60, ""));
	$form->addElement(FORM_GROUP_MAIN, new form_element_submit("submit", "Buscar", '<br>', '', 20));
	$form->addElement(FORM_GROUP_MAIN, new form_element_radio("Buscar por:", "criterio", 'DESCRIPCION', '<br>', '', 1, $criterios));
	return $form->getForm();
    }
    
    function listado($resultados, $destname)
    {
	$result = '<select name="articulo" id="articulo" size="10" width="250px">';
	
	foreach($resultados as $codigo => $descripcion) {
	    $result .= '<option value="' . htmlentities($codigo) . '">' . htmlentities($descripcion) . '</option>';
	}

	$result .= '</select>';

	$result .= '<input type="button" name="btPut" onclick="returnValue(\'' . htmlentities($destname) . '\', \'articulo\')" value="Seleccionar">';
	return $result;
    }
}

?>