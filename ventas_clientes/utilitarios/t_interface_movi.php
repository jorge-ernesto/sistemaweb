<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class InterfaceMovTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Interface central para movimientos de consulta</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function ResultadoEjecucion($msg)
  {
    return '<blink>'.$msg.'</blink>';
  }

  function ListadoModulos()
  {
    $CbModulos = array( 
		"TODOS"         => "[ Todos ]",
		"VENTAS"        => "VENTAS",
		"VENTASC"       => "VENTAS COMBUSTIBLES",
		"VENTASM"       => "VENTAS MARKET",
		"COMPRAS"       => "COMPRAS y CPAGAR",
		"INVENTARIOS"   => "INVENTARIOS",
		"VALES"         => "VALES",
		"FACTURACION"   => "FACTURACION",
		"PRECANCELACION"=> "PRECANCELACION",
		"SERVICIOS"     => "SERVICIOS",
		"PLANILLA"      => "COMISIONES y ASISTENCIA"
   );
                
  return $CbModulos;
  }
	
	
	function ListadoMes()
  {
    $CbMes = array( 
		"01" => "ENERO",
		"02" => "FEBRERO",
		"03" => "MARZO",
		"04" => "ABRIL",
		"05" => "MAYO",
		"06" => "JUNIO",
		"07" => "JULIO",
		"08" => "AGOSTO",
		"09" => "SETIEMBRE",
		"10" => "OCTUBRE",
		"11" => "NOVIEMBREA",
		"12" => "DICIEMBRE"
  );
                
  return $CbMes;
  }
	

  function formInterfaceMov($datos)
  {
  $CbModulos = InterfaceMovTemplate::ListadoModulos();
  $CbMes = InterfaceMovTemplate::ListadoMes();
  $CbSucursales = VariosModel::sucursalCBArray();
 
/*
  if(empty($datos["fecha"]))
  {
    $dia  = date("d");
    $mes  = date("m");
    $anio = date("Y");
    $datos["fecha"] = $dia."/".$mes."/".$anio;
  }
*/  
  if(empty($datos["fechaini"]))
  {
    $dia  = date("d");
    $mes  = date("m");
    $anio = date("Y");
    $datos["fechaini"] = $dia."/".$mes."/".$anio;
  }
  if(empty($datos["fechafin"]))
  {
    $dia  = date("d");
    $mes  = date("m");
    $anio = date("Y");
    $datos["fechafin"] = $dia."/".$mes."/".$anio;
  }
  
  
    $form = new form2('INTERFACE DE CONSULTA', 'form_agen_ret', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'UTILITARIOS.INTERFACES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'INTERFACES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('registroid', @$datos["ch_ruc"]));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="5" cellpadding="5"> <tr><td class="form_td_title">'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[modulos]','M&oacute;dulos </td><td>: ', trim(@$datos["modulos"]), $CbModulos, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[sucursal]','Sucursales </td><td>: ', trim(@$datos["sucursal"]), $CbSucursales, espacios(50)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_combo('datos[mes]','Mes </td><td>: ', date('m'), $CbMes, espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr><tr><td valign="top">'));


/*
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fecha]','Fecha </td><td>: ', @$datos["fecha"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));

*/
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechaini]','Fecha Inicio</td><td>: ', @$datos["fechaini"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('datos[fechafin]','Fecha Fin</td><td>: ', @$datos["fechafin"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('&nbsp;&nbsp;<div id="MensajeFecha" style="display:inline;" class="form_label">Formato : <b>d&iacute;a/mes/a&ntilde;o</b></div>'));
	
	

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="Resultado" align="center"></div>'));
    $form->addGroup ('buttons', '');
    $form->addElement('buttons', new f2element_submit('action','Actualizar', espacios(2)));
    //$form->addElement('buttons', new f2element_submit('action','Regresar', espacios(2)));
   
    return $form->getForm().'<div id="error_body" align="center"></div><hr>';
  }

}

?>