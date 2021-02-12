<?php

class Reporte_NumTransFidelizaTemplate extends Template {

    //METODO QUE DEVUELVE EL TITULO
    function titulo() {
      $titulo = '<div align="center"><h2>REPORTE DE NUMERO DE TRANSACCIONES DE FIDELIZACION</h2></div><hr>';
      return $titulo;
    }

    //METODO QUE RETORNA UN MENSAJE DE ERROR
    function errorResultado($errormsg) {
        return '<blink>' . $errormsg . '</blink>';
    }

    //LISTADO DE LOS PRODUCTOS EN CANJE
    function listado($registros) {
        $contador = 0;

        $listado = '';


          $listado .= '
          <br />
          <table align="center">
          <tr class="grid_header">
            <td>
              <a href="/sistemaweb/maestros/reporteMaestros/NumTransFidelizacion_' . session_id() . '.csv">Exporta Datos</a>
            </td>
          </tr>
          </table>
          <br />
          ';

        if (count($registros) > 0) {
            //CREAREMOS LA PAGINACION - DPC 09/05/09
            //==========================================
            //formulario de busqueda
/*
            $listado .= ' <p align="right">
            <a href="/sistemaweb/maestros/reporteMaestros/NumTransFidelizacion_' . session_id() . '.csv">Exporta Datos
            </a>
            </p>';
*/
          $listado .= '
          <div id="resultados_grid" class="grid" align="center">
					 <table width="60%">
          ';

          $listado .= '
					<caption ><hr></caption>
					<thead align="center" valign="center" >
					<tr class="grid_header">';

            $listado .='	<td class="grid_cabecera" rowspan="2">FECHA</td>
					<td class="grid_cabecera" rowspan="2">TURNO</td>
					<td class="grid_cabecera" rowspan="2">VENTA SOLES</td>
					<td class="grid_cabecera" rowspan="2">TRANS. TOTAL</td>
					<td class="grid_cabecera" rowspan="2">TRANS. FIDE</td>		
					<td class="grid_cabecera" rowspan="2">% FIDELIZA </td>		
					</tr></thead>';


            foreach ($registros as $reg) {


                if ($reg["turno"] == 'A') {
                    $color = ($contador % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");

                    $listado .= '<tr style="font-size:16px;background:#04B4AE;">';
                    $listado .='<td align="center">&nbsp;</td>';
                    $listado .='<td align="center">&nbsp;</td>';
                    $listado .='<td align="right">' . $reg["ventasoles"] . '&nbsp;</td>';
                    $listado .='<td align="center">' . $reg["trans_total"] . '&nbsp;</td>';
                    $listado .='<td align="center">' . $reg["trans_fide"] . '&nbsp;</td>';
                    $listado .='<td align="center">' . $reg["porcentaje"] . '%&nbsp;</td>';
                    $listado .='</tr>';
                } else {
                    $color = ($contador % 2 == 0 ? "grid_detalle_par" : "grid_detalle_impar");

                    $listado .= '<tr>';
                    $listado .='<td align="center" class="' . $color . '">' . $reg["fecha"] . '&nbsp;</td>';
                    $listado .='<td align="center" class="' . $color . '">' . $reg["turno"] . '&nbsp;</td>';
                    $listado .='<td align="right" class="' . $color . '">' . $reg["ventasoles"] . '&nbsp;</td>';
                    $listado .='<td align="center" class="' . $color . '">' . $reg["trans_total"] . '&nbsp;</td>';
                    $listado .='<td align="center" class="' . $color . '">' . $reg["trans_fide"] . '&nbsp;</td>';
                    $listado .='<td align="center" class="' . $color . '">' . $reg["porcentaje"] . '%&nbsp;</td>';
                    $listado .='</tr>';
                }

                $contador++;
            }
            $listado .= '</tbody></table></div>';
        }
        return $listado;
    }

  function formBuscar($dIni, $dFin){
    $almacenes = Reporte_NumTransFidelizaModel::obtenerAlmacenes();
    $almacenes[''] = "Todos los Almacenes";

    $form = new form2('', 'Buscar', FORM_METHOD_GET, 'control.php', '', 'control', '');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.REPORTE_NUMTRANSFIDELIZA'));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REPORTE_NUMTRANSFIDELIZA'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table border="0" align="center" cellpadding="5" cellspacing="5">'));

      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Almacén: </td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left" colspan="3">'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_combo('almacen', '', '', $almacenes, espacios(3), array("onfocus" => "getFechasIF();"), ''));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));  
      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</tr>'));

      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Inicial: </td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("fechainicio", "", $dIni, '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Fecha Final: </td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text("fechafin", "", $dFin, '', 12, 10));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="right">Turno: </td>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td align="left">'));
          $form->addElement(FORM_GROUP_MAIN, new f2element_text('turno', '', $_REQUEST['turno'], '', 8, 3, '', array('onkeypress="return soloNumeros(event)"')));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<td colspan="4" align="center">'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<button id="btn-html" name="action" type="submit" value="Consultar"><img src="/sistemaweb/icons/gbuscar.png" align="right" />Buscar </button>'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</td>"));
      $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags("</tr>"));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags(
    '<script>
      window.onload = function() {
        parent.document.getElementById("almacen").focus();
      }
    </script>'
    ));
    return $form->getForm();
  }

    function formPaginacion($paginacion, $fechaini, $fechafin, $intListaPuntos) {
        $form = new form2('', 'Paginacion', FORM_METHOD_GET, 'control.php', '', 'control');
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('rqst', 'PROMOCIONES.REPORTE_NUMTRANSFIDELIZA'));
        $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('task', 'REPORTE_NUMTRANSFIDELIZA'));
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<center>'));


        if ($intListaPuntos > 0) {
            $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<br>'));
            $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('P&aacute;gina ' . $paginacion['paginas'] . ' de ' . $paginacion['numero_paginas'] . ' P&aacute;ginas&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));

            $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/first.gif', espacios(2), array("border" => "0", "alt" => "Primera P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['primera_pagina'] . "','" . $fechaini . "','" . $fechafin . "')")));
            $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/left.gif', espacios(5), array("border" => "0", "alt" => "P&aacute;gina Anterior", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_previa'] . "','" . $fechaini . "','" . $fechafin . "')")));

            $form->addElement(FORM_GROUP_MAIN, new f2element_text('paginas', '', $paginacion['paginas'], espacios(5), 3, 2, array("onChange" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "',this.value,'" . $fechaini . "','" . $fechafin . "')")));

            $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/right.gif', espacios(2), array("border" => "0", "alt" => "P&aacute;gina Siguente", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['pagina_siguiente'] . "','" . $fechaini . "','" . $fechafin . "')")));
            $form->addElement(FORM_GROUP_MAIN, new f2element_obj_image('/sistemaweb/icons/last.gif', espacios(2), array("border" => "0", "alt" => "&Uacute;ltima P&aacute;gina", "onclick" => "javascript:PaginarRegistros('" . $paginacion['pp'] . "','" . $paginacion['ultima_pagina'] . "','" . $fechaini . "','" . $fechafin . "')")));
            $form->addElement(FORM_GROUP_MAIN, new f2element_text('numero_registros', 'Registros por P&aacute;gina : ', $paginacion['pp'], espacios(2), 4, 4, array("onChange" => "javascript:PaginarRegistros(this.value,'" . $paginacion['primera_pagina'] . "','" . $fechaini . "','" . $fechafin . "')")));
        }

        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</center>'));

        return $form->getForm();
    }

  function formMovimientopuntos($intListaPuntos) {
    $form = new form2(' ', 'Buscar', FORM_METHOD_POST, 'control.php', '', 'control', '');
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('grupo', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('auxilio', ''));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('fecServer', date('d/m/Y')));
    $form->addElement(FORM_GROUP_HIDDEN, new f2element_hidden('accion', ''));
    // Inicio Contenido TD 1
    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<table width="100%" border="0" cellspacing="2" cellpadding="2">'));

    if ($intListaPuntos > 0) {
    } else {
        $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('<tr><td class="msg_informacion"><img src="/sistemaweb/icons/messagebox_info32x32.png" border="0">No existe información para la consulta realizada.</td><tr>'));
    }

    $form->addElement(FORM_GROUP_MAIN, new f2element_freeTags('</table>'));
    return $form->getForm();
  }
}

