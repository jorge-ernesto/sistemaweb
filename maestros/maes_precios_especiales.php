<?php
//include("../valida_sess.php");
//include("/sistemaweb/ventas_clientes/inc_top.php");
//include("../config.php");
include "../menu_princ.php";
require("../clases/funciones.php");

$funcion = new class_funciones;
// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");

switch($accion){

	case "Ingresar":
		if($c_fec_fin==""){$c_fec_fin=" ";}
		$q = "insert into fac_precios_clientes
		(ch_tipo_precio , ch_codigo_cliente_grupo , art_codigo
		,dt_fecha_inicio	
		,dt_fecha_fin
		,nu_preciopactado)
		values
		('$tipo_precio' , '$cod_cliente' , '$cod_articulo'
		, nullif(to_date('$c_fec_inicio','dd/mm/yyyy'),'0001-01-01 BC')
		, nullif(to_date('$c_fec_fin','dd/mm/yyyy'),'0001-01-01 BC')
		,$c_precio_pactado
		)
		";
		echo $q;
		pg_exec($q);
	
	break;
	
	case "Eliminar":
	
		for($i=0;$i<count($items);$i++){
			$k = $items[$i];
			$q = "delete from fac_precios_clientes 
			where ch_tipo_precio='".$ar_tipo_precio[$k]."' 
			and ch_codigo_cliente_grupo='".$ar_cliente[$k]."' 
			and art_codigo='".$ar_articulo[$k]."' 
			and dt_fecha_inicio=to_date('".$ar_fecha_inicio[$k]."','dd/mm/yyyy')";
			//echo $q;
			pg_exec($q);
		}
	
	break;
	
	case "Modificar":
	
		for($i=0;$i<count($items);$i++){
			$k = $items[$i];
			$q = "update fac_precios_clientes set 
			dt_fecha_fin=nullif(to_date('".$ar_fecha_fin[$k]."','dd/mm/yyyy'),'0001-01-01 BC') 
			, nu_preciopactado=".$ar_precio_pactado[$k]." 
			where ch_tipo_precio='".$ar_tipo_precio[$k]."' 
			and ch_codigo_cliente_grupo='".$ar_cliente[$k]."' 
			and art_codigo='".$ar_articulo[$k]."' 
			and dt_fecha_inicio=to_date('".$ar_fecha_inicio[$k]."','dd/mm/yyyy')
			";
			//echo $q;
			pg_exec($q);
		}
		
	break;
}

$rs = pg_exec("select pre.ch_tipo_precio,
	CASE 
		WHEN pre.ch_tipo_precio='C' THEN 'Por Cliente' 
		WHEN pre.ch_tipo_precio='G' THEN 'Por Grupo'
	END as tipo,
	CASE 
		WHEN pre.ch_tipo_precio='C' THEN cli.cli_razsocial 
		WHEN pre.ch_tipo_precio='G' THEN 'Grupo: '||pre.ch_codigo_cliente_grupo
	END as cliente
	,art.art_descripcion as articulo,pre.art_codigo, to_char(pre.dt_fecha_inicio,'dd/mm/yyyy') as  dt_fecha_inicio
	, to_char(pre.dt_fecha_fin,'dd/mm/yyyy') as dt_fecha_fin
	,pre.nu_preciopactado,pre.ch_codigo_cliente_grupo
	from fac_precios_clientes pre,int_clientes cli,int_articulos art
	where pre.ch_codigo_cliente_grupo=cli.cli_codigo
	and art.art_codigo=pre.art_codigo 
	and pre.ch_tipo_precio='C'
	UNION
	select pre.ch_tipo_precio,
	CASE 
		WHEN pre.ch_tipo_precio='C' THEN 'Por Cliente' 
		WHEN pre.ch_tipo_precio='G' THEN 'Por Grupo'
	END as tipo,
	CASE 
		WHEN pre.ch_tipo_precio='C' THEN cli.cli_razsocial 
		WHEN pre.ch_tipo_precio='G' THEN 'Grupo: '||pre.ch_codigo_cliente_grupo
	END as cliente
	,art.art_descripcion as articulo,pre.art_codigo, to_char(pre.dt_fecha_inicio,'dd/mm/yyyy') as dt_fecha_inicio
	, to_char(pre.dt_fecha_fin,'dd/mm/yyyy') as dt_fecha_fin
	,pre.nu_preciopactado , pre.ch_codigo_cliente_grupo
	from fac_precios_clientes pre,int_clientes cli,int_articulos art
	where pre.ch_codigo_cliente_grupo=cli.cli_grupo
	and art.art_codigo=pre.art_codigo
	and pre.ch_tipo_precio='G'
");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<script language="JavaScript" src="/sistemaweb/clases/calendario.js"></script>
<script language="JavaScript" src="/sistemaweb/clases/overlib_mini.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/validacion.js"></script>
<script language="JavaScript" src="/sistemaweb/compras/valfecha.js"></script>
<script language="JavaScript">
function mostrarAyuda(url,cod,des,consulta,des_campo,valor){
	//onClick="javascript:window.open('reporte_detalle_ventas.php?fechad=<?php echo $fechad;?>&fechaa=<?php echo $fechaa;?>&cod_almacen=<?php echo $cod_almacen;?>&almacen_dis=<?php echo $almacen_dis;?>','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');"
	//window.open('reporte_detalle_ventas.php','miwin','width=800,height=350,scrollbars=yes,menubar=yes,left=0,top=0');
if(consulta=="clientes" && document.form1.tipo_precio.value!="G"){
//alert("-"+document.form1.tipo_precio.value+"-");
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

if(consulta!="clientes"){
//alert("-"+document.form1.tipo_precio.value+"-");
url = url+"?cod="+cod+"&des="+des+"&consulta="+consulta+"&des_campo="+des_campo+"&valor="+valor;
window.open(url,'miwin','width=500,height=350,scrollbars=yes,menubar=no,left=290,top=20');
}

}

function mandarDatos(form,opcion){
	form.accion.value=opcion;
	form.submit();
}
</script>
<title>Mantenimiento de Precios Especiales por Clientes</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:250;"></div>
<form name="form1" method="post" action="">
  <table width="75%" border="1">
    <tr> 
      <td width="29%">Tipo de Precio:</td>
      <td width="32%"><select name="tipo_precio">
          <option value="C">Por Cliente</option>
          <option value="G">Por Grupo</option>
        </select></td>
      <td width="39%">&nbsp;</td>
    </tr>
    <tr> 
      <td>Codigo (Grupo o Cliente)</td>
      <td><input type="text" name="cod_cliente">
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('/sistemaweb/inventarios/js/lista_ayuda.php','cod_cliente','descli','clientes','des_cli');">
        <input type="hidden" name="des_cli"></td>
      <td id="descli">&nbsp;</td>
    </tr>
    <tr> 
      <td>Fecha Inicio</td>
      <td><input type="text" name="c_fec_inicio">
	  <a href="javascript:show_calendar('form1.c_fec_inicio');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" > 
        <img src="../images/show-calendar.gif" width="24" height="22" border="0"> 
        </a>
	  </td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td>Fecha Fin</td>
      <td><input type="text" name="c_fec_fin">
	  <a href="javascript:show_calendar('form1.c_fec_fin');" onMouseOver="window.status='Elige fecha'; overlib('Pulsa para elegir fecha del mes actual en el calendario emergente.'); return true;" onMouseOut="window.status=''; nd(); return true;" > 
        <img src="../images/show-calendar.gif" width="24" height="22" border="0"> 
        </a>
	  </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Articulo 
        <input type="hidden" name="art_stock">
        <input type="hidden" name="art_costo_uni">
        <input type="hidden" name="des_art_campo" size="3"></td>
      <td><input type="text" name="cod_articulo">
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('/sistemaweb/inventarios/js/lista_ayuda.php','cod_articulo','des_articulo','articulos2','des_art_campo','<?php echo $valor;?>');"> 
      </td>
      <td id="des_articulo">&nbsp;</td>
    </tr>
    <tr> 
      <td>Precio Pactado</td>
      <td><input type="text" name="c_precio_pactado"></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td colspan="3"><div align="center">
          <input type="hidden" name="des_cli">
          <input type="button" name="btn_ingresar" value="Ingresar" onClick="javascript:mandarDatos(form1,'Ingresar');">
          <input type="button" name="btn_modificar" value="Modificar" onClick="javascript:mandarDatos(form1,'Modificar');">
          <input type="button" name="btn_eliminar" value="Eliminar" onClick="javascript:mandarDatos(form1,'Eliminar');">
        </div></td>
    </tr>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp; </td>
      <td><input type="hidden" name="accion"></td>
    </tr>
  </table>
  <br>
  <table width="99%" border="1">
    <tr> 
      <td width="18%"><div align="center">Cliente-Grupo</div></td>
      <td width="22%"><div align="center">Producto</div></td>
      <td width="15%"><div align="center">Fecha de Inicio</div></td>
      <td width="15%"><div align="center">Fecha de Fin</div></td>
      <td width="11%"><div align="center">Precio Pactado</div></td>
      <td width="11%"><div align="center">Tipo</div></td>
      <td width="8%"><div align="center">Marcar</div></td>
    </tr>
    <?php for($i=0;$i<pg_numrows($rs);$i++){
		$A = pg_fetch_array($rs,$i);
	?>
    <tr> 
      <td> <div align="center"><?php echo $A["cliente"];?>
          <input type="hidden" name="ar_cliente[]" value="<?php echo $A["ch_codigo_cliente_grupo"];?>">
        </div></td>
      <td> <div align="center"><?php echo $A["articulo"].$A["art_codigo"];?>
          <input type="hidden" name="ar_articulo[]" value="<?php echo $A["art_codigo"];?>">
        </div></td>
      <td> <div align="center">
          <input type="hidden" name="ar_fecha_inicio[]" value="<?php echo $A["dt_fecha_inicio"];?>">
          <?php echo $A["dt_fecha_inicio"];?></div></td>
      <td> <div align="center">
          <input type="text" name="ar_fecha_fin[]" size="15" value="<?php echo $A["dt_fecha_fin"];?>">
          </div></td>
      <td> <div align="center"><input type="text" name="ar_precio_pactado[]" size="12" value="<?php echo $A["nu_preciopactado"];?>"></div></td>
      <td> <div align="center"><?php echo $A["tipo"];?>
          <input type="hidden" name="ar_tipo_precio[]" value="<?php echo $A["ch_tipo_precio"];?>">
        </div></td>
      <td><div align="center"> 
          <input type="checkbox" name="items[]" value="<?php echo $i;?>">
        </div></td>
    </tr>
    <?php } ?>
    <tr> 
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
