<?php
  /*
    Templates para Tablas Generales
    @TBCA
  */
//include('lib/paginador_new.php');
//include('../include/reportes2.inc.php');
class ConsistenciaValesTemplate extends Template {
 
  function titulo(){
    $titulo = '<div align="center"><h2>REPORTE DIARIO DE CONSISTENCIA DE VALES POR CENTRO DE COSTO</h2></div><hr>';
    return $titulo;
  }
  
  function errorResultado($errormsg){
    return '<blink>'.$errormsg.'</blink>';
  }

  // Solo Formularios y otros
  function formBuscar()
  {
   	echo "entre al form";
	$fecha_inicio = @$_REQUEST['busqueda']['fecha_ini'];
	$fecha_final = @$_REQUEST['busqueda']['fecha_fin'];
	$codi_cli = @$_REQUEST['busqueda']['estacion'];
    $fecha_ini = date("d/m/Y");
    $fecha_fin = date("d/m/Y");
    $form = new form2('', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.DIAVALES'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'DIAVALES'));
	
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_ini]', @$fecha_inicio));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('busqueda[fecha_fin]', @$fecha_final));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" cellspacing="2" cellpadding="3"> <tr><td class="form_label">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));
			$form->addElement(FORM_GROUP_MAIN, new f2element_combo ('busqueda[estacion]','Estación',$_REQUEST['busqueda']['estacion'],array('01'=>'Todos','02'=>'Por Cliente'),espacios(3)));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_ini]','Fecha Inicio  :', $_REQUEST['busqueda']['fecha_ini']?@$_REQUEST["busqueda"]["fecha_ini"]:@$fecha_ini, espacios(3), 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_text ('busqueda[fecha_fin]','Fecha Fin :', $_REQUEST['busqueda']['fecha_fin']?@$_REQUEST["busqueda"]["fecha_fin"]:@$fecha_fin, espacios(0), 20, 18));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Detallado", "DETALLADO", '', '', Array("checked")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_radio("busqueda[modo]", "Resumido", "RESUMIDO", ''));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte',espacios(0)));
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</td></tr></table>'));
    return $form->getForm();
  }
  
  function TmpReportePDF($datos)
  {
  
  }
}

