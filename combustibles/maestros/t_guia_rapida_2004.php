<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');

class GuiaRapidaTemplate extends Template {

  function titulo(){
    $titulo = '<div align="center"><h2>Guia Rapida</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
    //isset($_REQUEST["paglistado"])?$pagina=$_REQUEST["paglistado"]:$pagina=1;
    $titulo_grid = "GUIA RAPIDA";
    //formulario de busqueda
    $columnas = array('EMPRESA','DESCRIPCION','ACEITE', 'AIRE', 'COMBUSTIBLE');
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
    foreach($registros as $reg){
      $listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
      $regCod = $reg["numtar"];
      //$listado .= '<td class="grid_columtitle">'.$cont.'</td>';
      for ($i=0; $i < count($columnas); $i++){
        //echo "";
            $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
      }
      
      $listado .= '</tr>';
     //$cont += 1;
    }
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
   //echo "ENTRO BUSCAR\n";
    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'MAESTROS.GUIARAPIDA'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'GUIARAPIDA'));
    //$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('pp', $paginacion['pp']));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[filtrado]','Filtro :', '', espacios(2), 20, 18));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Buscar',espacios(3)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina '.$paginacion['paginas'].' de '.$paginacion['numero_paginas'].' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2),array( "border"=>"0", "alt"=>"Primera P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['primera_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5),array( "border"=>"0", "alt"=>"P&aacute;gina Anterior","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_previa']."')")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('paginas','', $paginacion['paginas'], espacios(5), 3, 2, array( "onChange"=>"javascript:PaginarRegistros('".$paginacion['pp']."',this.value)")));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2),array( "border"=>"0", "alt"=>"P&aacute;gina Siguente","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['pagina_siguiente']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2),array( "border"=>"0", "alt"=>"&Uacute;ltima P&aacute;gina","onclick"=>"javascript:PaginarRegistros('".$paginacion['pp']."','".$paginacion['ultima_pagina']."')")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('numero_registros','Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4,array("onChange"=>"javascript:PaginarRegistros(this.value,'".$paginacion['primera_pagina']."')")));
   
    return $form->getForm();
  }

}

