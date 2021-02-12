<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class TransaccionesTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Transacciones</h2></div><hr>';
    return $titulo;
  }


  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

 
  
  function listado($registros){
    $titulo_grid = "Transacciones";

    //formulario de busqueda
    $columnas = array('SUCU','MOV','DOC','CAJA', 'TRANS', 'DIA','TURNO','CODIGO','CANTIDAD','PRECIO','IGV','IMPORTE','RUC');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table>
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(10).'</th><th>'.espacios(5).'</th></tr><tbody class="grid_body" style="height:250px;">';
    
	//detalle
	foreach($registros as $reg)
	{
		$listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
		$regCod = $reg["dia"].$reg["caja"].$reg["trans"];
		//$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
		for ($i=0; $i < count($columnas); $i++)
		{
			$listado .= '<td class="grid_item">'.$reg[$i].'</td>';
		}
		$listado .= '<td><A href="control.php?rqst=MAESTROS.TRANSACCIONES&task=TRANSACCIONES'.
				  '&action=Imprimir&registroid='.$regCod.'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A>&nbsp;';
		$listado .= '</td><td>&nbsp;</td>';


		$listado .= '</tr>';
		//$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.TRANSACCIONES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'TRANSACCIONES'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[es]','Sucursal :', '', espacios(2), 4, 3));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('fecha','Fecha :', '', espacios(2), 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<a href="javascript:show_calendar('."'Buscar.fecha'".');"><img src="/sistemaweb/images/showcalendar.gif"  border=0></a>&nbsp;&nbsp;'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[caja]','Caja :', '', espacios(2), 2, 1));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text('busqueda[trans]','Trans :', '', espacios(2), 6, 4));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('Pagina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' Paginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera Pagina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"Pagina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"Pagina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"Ultima Pagina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
   
    return $form->getForm();
  }

}

