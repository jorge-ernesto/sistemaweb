<?php
include("../menu_princ.php");
include("../functions.php");
include("js/funciones.php");
include("store_procedures.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
//$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

extract($_REQUEST);

$fecha_desde = $_REQUEST['fecha_desde'];
$fecha_hasta = $_REQUEST['fecha_hasta'];

switch($accion){
	case "Buscar":
	
		$rs = reporte_actualizacion_maestros($tipo,$fecha_desde,$fecha_hasta,$opt_codigo,$opt_descripcion);
		
	break;
	
	default:
	
		$f = date("d/m/Y");
		$rs = reporte_actualizacion_maestros($tipo,$f,$f,$opt_codigo,$opt_descripcion);

		if($fecha_desde==""){$fecha_desde=$f;}
		if($fecha_hasta==""){$fecha_hasta=$f;}
	break;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Actualizaciones hechas en el sistema Web sistemaweb</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <script language="JavaScript">
    function enviarDatos(accion,form){
      form.accion.value = accion;
      form.combo_id.value = form.tipo.selectedIndex;
      form.submit();
    }
    </script>
  </head>
  <body>
    <script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
    <script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>
    <form name="form1" method="post" action="">
    <h2 align="center" style="color:#336699"><b> ACTUALIZACIONES DE MAESTROS </b></h2>
      <table align="center">
        <tr>
          <td> Buscar:
            <select name="tipo" size="1" >
              <option value="articulos">Articulos</option>
              <option value="proveedores">Proveedores</option>
              <option value="clientes">Clientes</option>
            	<option value="precios">Precios</option>
            	<option value="tarjetas">Tarjetas Magneticas</option>
            	<option value="enlace">Enlace</option>
            	<option value="trabajadores">Trabajadores</option>
            	<option value="activos">Activos</option>
            	<option value="promociones">Promociones</option>
            </select>
          </td>
        </tr>
        <tr>
          <td> Código:
            <input type="text" name="opt_codigo" size="15" onKeyPress="javascript:opt_descripcion.value='';" value="<?php echo $opt_codigo;?>">
          </td>
          <td>Descripción: 
            <input type="text" name="opt_descripcion" onKeyPress="javascript:opt_codigo.value='';" value="<?php echo $opt_descripcion;?>">
          </td>
        </tr>
        <tr>
          <td> Desde: 
            <input type="text" name="fecha_desde" size="11" value="<?php echo $fecha_desde ?>" readonly="true"/> &nbsp;<a href="javascript:show_calendar('form1.fecha_desde');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
            <div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></div>
          </td>
          <td>Hasta: 
            <input type="text" name="fecha_hasta" size="11" value="<?php echo $fecha_hasta ?>" readonly="true"/> &nbsp;<a href="javascript:show_calendar('form1.fecha_hasta');"><img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/></a>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <input type="button" name="Buscar" value="Buscar" onClick="javascript:enviarDatos('Buscar',form1)">
          </td>
        </tr>
      </table>
    
    <input type="hidden" name="accion">
    <input type="hidden" name="combo_id" value="0">

      <?php if(pg_numrows($rs)>0) { ?>
      <table width="776" height="56" border="1">
        <tr> 
          <td height="5" width="111">
            <div align="center"><strong>Codigo</strong></div>
          </td>
          <td>
            <div align="center"><strong>Descripcion</strong></div>
          </td>
          <td>
            <div align="center"><strong>Fecha</strong></div>
          </td>
          <td>
            <div align="center"><strong>Adicional</strong></div>
          </td>
        </tr>
          <?php for($i=0;$i<pg_numrows($rs);$i++) {
            $A = pg_fetch_array($rs,$i);
          ?>
        <tr> 
          <td width="111" height="25">
            <div align="center">
              <font size="-3"><?php echo $A["codigo"];?></font>
            </div>
          </td>
          <td width="406">
            <div align="left">
              <font size="-3"><?php echo $A["descripcion"];?></font>
            </div>
          </td>
          <td width="77">
            <div align="center">
              <font size="-3"><?php echo $A["fecha_actualizacion"];?></font>
            </div>
          </td>
          <td width="154">
            <div align="left">
              <font size="1">
              <font size="-3"><?php echo $A["adicional"];?></font>
              <font size="1">
              <font size="-3"><?php echo $A["adicional2"];?></font>
              </font>
              </font>
            </div>
          </td>
        </tr>
          <?php } ?>
      </table>
          <?php } ?>
    </form>
      <?php if($combo_id==""){$combo_id=0;}?>
    <script>
    form1.tipo.options[<?php echo $combo_id;?>].selected = true;
    </script>
  </body>
</html>
<?php pg_close(); ?>