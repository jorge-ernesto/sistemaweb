<?php
  /*
    Templates para Tabla ccob_ta_cabecera
    @MATT
  */

class RepProcDiaTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>Procesos de Replicacion</h2></div><hr>';
    return $titulo;
  }

  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  function listado($registros){
    $titulo_grid = "PROCESOS DE REPLICACION";
    //formulario de busqueda
    $SiNo = array('S'=>'SI',
                  'N'=>'NO');
                   
    $SisID = RepProcDiaModel::ListadoTbl();
    $ListaAlmacenes = VariosModel::almacenCBArray();
    
    //print_r($SisID);
    $columnas = array('FECHA','ALMACEN', 'SISTEMAS','FECHA ACT.','USUARIO');
    $listado = '<div id="resultados_grid" class="grid" align="center"><br>
                      <table border="0">
                      <caption class="grid_title">'.$titulo_grid.'</caption>
                      <thead align="center" valign="center" >
                      <tr class="grid_header">';
    for($i=0;$i<count($columnas);$i++)
    {
      $listado .= '<th class="grid_columtitle"> '.strtoupper($columnas[$i]).'</th>';
    }
    $listado .= '<th>'.espacios(3).'</th><th>'.espacios(3).'</th></tr><tbody class="grid_body" style="height:250px;">';

    //detalle
    //print_r($registros);
    $x=0;
    foreach($registros as $regi)
    {
    //echo "X = $x \n";
       $registros2[] = $registros[$x];
       
        foreach($SisID as $llave => $valor)
        {
	    //echo "VALOR : ".$valor['id']." \n";
	    $Sistemas = RepProcDiaModel::ListadoSistemas($regi[0], $valor['id'], $regi[1]);
	    if($Sistemas) $registros2[] = $Sistemas;
	    //print_r($Sistemas);
	    //print_r($regi);
	}
    $x++;
    }
    //print_r($registros2);
    $y = 0;
    foreach($registros2 as $reg)
    {
     // echo "SIS ID : ".$SisID[$x][$x];
      
      if(!is_array($reg[0]))
      {
        //echo  "Y : ".($y)."\n";
        if(($y+1)>1){
            //$listado .= '</divxxx>'."\n\n";
	    $listado .= '</td>';
	    $listado .= '</tr>'."\n";

        }
        $listado .= '<tr height="10px;" class="grid_row" '.resaltar('white','#CDCE9C').' onClick="displaygrid(document.getElementById(\'detalle_'.($y+1).'\'));">'."\n";
        
      $y++;
      }
      //$regCod = trim($reg["cli_codigo"]);
      $reg[2] = substr($reg[2], 1, -1);
      $reg[1] = $ListaAlmacenes[$reg[1]];
      
      for ($i=0; $i < count($columnas); $i++)
      {
            if(!is_array($reg[0]))
	    {
               $listado .= '<td class="grid_item">'.$reg[$i].'</td>';
            }

            if(is_array($reg[$i]))
            {
               //echo  "Y : ".$y."\n";
                 $listado .= '<table border="0" bgcolor="#FFFFCC">'."\n";
                 $listado .= '<tr class="grid_row" '.resaltar('white','#CDCE9C').'>';
                 $reg[$i][1] = substr($reg[$i][1], 1, -1);
                 $reg[$i][1] = str_replace('"', '',$reg[$i][1]);
                 $reg[$i][2] = $SiNo[$reg[$i][2]];
                 for ($c=0; $c < (count($reg[$i])/2); $c++)
                 {
                   // echo "REGISTRO : ".$c."\n";
                    if($c == 0) $width = ' width = "250"';
                    if($c == 1) $width = ' width = "350"';
                    if($c == 2) $width = ' width = "30"';
                     $listado .= '<td class="grid_item"'.$width.'>'.$reg[$i][$c].'</td>';
                 }
                 $listado .= '</tr>'."\n";
                 $listado .= '</table>'."\n";
            }
            
      }
     // $listado .= '<td><A href="control.php?rqst=MOVIMIENTOS.REP_PROC_DIA&task=REP_PROC_DIA'.
                  //'&action=Aplicacion&registroid='.trim($regCod).trim($reg[2]).trim($reg[3]).trim($reg[4]).'" target="control"><img src="/sistemaweb/icons/open.gif" alt="Editar" align="middle" border="0"/></A></td>';
      if(!is_array($reg[0]))
      {
        $listado .= '</tr>'."\n\n";
       /* if($y>1){
        //$listado .= '</divxxx>'."\n\n";
	    $listado .= '</td>';
	    $listado .= '</tr>'."\n";

        }*/
        //$listado .= '<divxxx>'."\n\n";
	$listado .= '<tr class="grid_row">'."\n";
	$listado .= '<td bgcolor="#FFFFCC"></td><td class="grid_item" colspan="'.(count($columnas)-1).'" id="detalle_'.$y.'">'."\n";
      }
    
    }
    
//    $listado .= '</divfff>'."\n\n";
	$listado .= '</td>'."\n";
	$listado .= '</tr>'."\n";
    $listado .= '</tbody></table></div>';
    return $listado;
  }

  // Solo Formularios y otros
  function formBuscar($paginacion){
  print_r($paginacion);
  echo "PAGINACION \n";
   if (!$_REQUEST['busqueda']['fecha_ini']) $_REQUEST['busqueda']['fecha_ini'] = date("d")."/".date("m")."/".date("Y");
   if (!$_REQUEST['busqueda']['fecha_fin']) $_REQUEST['busqueda']['fecha_fin'] = date("d/m/Y");
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'FACTURACION.REP_PROC_DIA'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REP_PROC_DIA'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[codigo]','Codigo :', '', espacios(5), 6, 5, array("onkeyup"=>"javascript:this.value=this.value.toUpperCase();")));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_ini]','Fecha del :', $_REQUEST['busqueda']['fecha_ini'], espacios(2), 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_fin]','al :', $_REQUEST['busqueda']['fecha_fin'], espacios(5), 10, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_checkbox ('busqueda[pendiente]','Pendientes : ', 'si', '', '', $checked));
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

