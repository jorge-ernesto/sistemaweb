<?php
class ConsvalesTemplate extends Template {

  function titulo(){
    $titulo = '<div align="center"><h2>Reportes de Ventas</h2></div><hr>';
    return $titulo;
  }

  function formReporte(){
    
    $form = new form2('DIFERENCIA DE PRECIOS ', 'form_consulta', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.CONSUMO_VALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'CONSUMO_VALES'));
    
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('<table border="0" cellspacing="5" cellpadding="5"><tbody class="grid_body"><tr><td align="center">'));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_text ('reporte[cli_fac]','N&#186; de documento </td><td>: ', @$reporte["cli_fac"], '', 12, 11));
    //$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('</td><td>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_text ('reporte[ch_liquidacion]','Cod. Liquidaci&oacute;n </td><td>: ', @$reporte["ch_liquidacion"], '', 12, 10));
    $form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte', '</td></tr>'));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('</tbody></table>'));

    return $form->getForm().'<div id="reporte" align="center"></div>';
  }   
}

