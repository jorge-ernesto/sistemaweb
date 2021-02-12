<?php

class SobraFaltaTemplate extends Template
{
    
    function titulo()
    {
	    return '<h2 align="center"><b>Reporte de Sobrantes y Faltantes de combustibles</b></h2>';
    }


    function formReporteSobraFalta($codsuc)
    {//Inicio de Función 
        if($_REQUEST['reporte']['dia_i'] && $_REQUEST['reporte']['dia_f'])
        {
            $dia_i = $_REQUEST['reporte']['dia_i'];
            $dia_f = $_REQUEST['reporte']['dia_f'];
        }
        else
        {
            $dia_i = date ("d");
            $dia_f = date ("d");
        }
        
        if($_REQUEST['reporte']['mes_i'] && $_REQUEST['reporte']['mes_f'])
        {
            //echo "ENTRO POST\n";
            $mes_i = $_REQUEST['reporte']['mes_i'];
            $mes_f = $_REQUEST['reporte']['mes_f'];
        }
        else
        {
            //echo "ENTRO\n";
            $mes_i = date ("m");
            $mes_f = date ("m");
            //echo "MES INI : $mes_i  MES FIN : $mes_f \n";
        }

        if($_REQUEST['reporte']['anio_i'] && $_REQUEST['reporte']['anio_f'])
        {
            $anio_i = $_REQUEST['reporte']['anio_i'];
            $anio_f = $_REQUEST['reporte']['anio_f'];
        }
        else
        {
            $anio_i = date ("Y");
            $anio_f = date ("Y");
        }

        /*$mes    = date ("m");
        $anio   = date ("Y");*/
        


	$SucCB     = VariosModel::sucursalCBArray();
	$TanCB     = VariosModel::tanquesCBArray($codsuc?$codsuc:$_REQUEST['reporte']['sucursal']);
	$AniosCB   = VariosModel::aniosCBArray();
	$MesCB     = VariosModel::mesesCBArray();
	$DiasCB    = VariosModel::diasCBArray();
        
        $titulo_formulario = "Sobrantes y Faltantes de combfustibles";
        
	//$titulo_reporte = $task;
	$form = new form2(strtoupper($titulo_formulario), 'form_consulta', FORM_METHOD_POST, 'control.php', '', 'control');
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'REPORTES.SOBRA_FALTA'));
	$form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', ''));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('<table><tbody class="grid_body"><tr><td align="left">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[sucursal]', 'SUCURSAL:<br>', $codsuc?$codsuc:$_REQUEST['reporte']['sucursal'], $SucCB, '</td><td>', array("onChange"=>"javascript:getSucursal(this.options[this.selectedIndex].value)")));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[dia_i]', '<br>DESDE : ', $dia_i, $DiasCB, '</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[mes_i]', '<br>', $mes_i, $MesCB, '</td><td align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[anio_i]', '<br>', $anio_i, $AniosCB, '</td><td align="center">'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[dia_f]', '<br>'.espacios(3).'HASTA : ', $dia_f, $DiasCB, '</td><td>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[mes_f]', '<br>', $mes_f, $MesCB, '</td><td align="center">'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[anio_f]', '<br>', $anio_f, $AniosCB, '</td><td align="left">'));
	
	$form->addElement(FORM_GROUP_MAIN, new f2element_combo('reporte[codtanque]', 'COD TANQUE :<br>', $_REQUEST['reporte']['codtanque'], $TanCB, '</td><td><br>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_submit('action','Reporte', '</td></tr>'));
	$form->addElement(FORM_GROUP_MAIN, new f2element_freeTags ('</tbody></table>'));
	
	return $form->getForm().'<div id="reporte" align="center"></div>';
    
    }//Fin de Función

}

