<?php
//include("../valida_sess.php");
//include("config.php");
//include("inc_top_cpagar.php");
include("../menu_princ.php");
include("cpag_rep_ingxordcompras_support.php");
extract($_REQUEST);
extract($_POST);
extract($_GET);

switch($tipo_reporte) {
	case "ord":
  $rs1 = reporteORD(trim($opcion), trim($fechad), trim($fechaa), trim($cod_proveedor), trim($des_proveedor), trim($cod_articulo), trim($des_articulo), trim($opcion), trim($codigo_almacen));    
  // print_r($rs1);
  // for($i=0;$i<pg_numrows($rs1);$i++){
  //   $A = pg_fetch_array($rs1,$i);
  //   echo "<pre>";
  //   print_r($A);
  //   echo "</pre>";
  // }
	break;

	case "dev":
  $rs1 = reporteDEV(trim($opcion), trim($fechad), trim($fechaa), trim($cod_proveedor), trim($des_proveedor), trim($cod_articulo), trim($des_articulo), trim($opcion), trim($codigo_almacen));  
	break;
}

if (!($_REQUEST['fechad'] && $_REQUEST['fechaa'])){
	$fechaa = date('d/m/Y');
	$fechad = date('d/m/Y');
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<script language="JavaScript" src="js/miguel.js"></script>
	<script language="JavaScript" src="js/codethatcalendarstd.js"></script>
	<script language="JavaScript" src="js/local_ex.js"></script>

	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>

	<script language="javascript">
		var c5 = new CodeThatCalendar(caldef5);
	</script>
	<script language="JavaScript">
		function mandarDatos(form, ope) {
			//var ok = validarInclusionFacturas(form);
			var ok = true;
			var msg = "";

			if (form.marcado.value=="") { ok = false;  msg = "Debes elegir una opcion: Todos o Pendientes o Atendidos"; }

			if(ok) {
				form.accion.value = ope;
				form.submit();
			} else {
				alert(msg);
			}
		}
	</script>
	<title>Reporte de Ingresos por Ordenes de Compra</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
    <script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-3.2.0.min.js"></script>
    <script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
    <script charset="utf-8" type="text/javascript">
      window.onload = function() {
        $(function() {
          $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sab'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
          };

          $.datepicker.setDefaults($.datepicker.regional['es']);

          $( "#id5" ).datepicker({
            changeMonth: true,
            changeYear: true,
          })

          $( "#id6" ).datepicker({
            changeMonth: true,
            changeYear: true,
          })
        });
      }      
    </script>
    <style type="text/css">
      #ui-datepicker-div{
        z-index: 9000 !important;
      }
    </style>
</head>
<body>
	<script src="/sistemaweb/js/calendario.js" type="text/javascript" ></script>
	<script src="/sistemaweb/js/overlib_mini.js" type="text/javascript" ></script>

	<script src="/sistemaweb/js/jquery-1.9.1.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/sistemaweb/css/jquery-ui.css" />
	<link rel="stylesheet" href="/sistemaweb/helper/css/style.css" />
	<script src="/sistemaweb/js/jquery-ui.js"></script>
	<script type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js" ></script>
	<script type="text/javascript">
		function autocompleteBridge(type) {
			if (type == 0) {
				//new
				var No_Producto = $("#txt-No_Producto");
				if(No_Producto.val() !== undefined) {
					console.log(No_Producto.val());
					autocompleteProducto(No_Producto);
				}
			} else if (type == 1) {
				var Np_Proveedor = $("txt-No_Proveedor");
				if(No_Proveedor.val() !== undefined) {
					console.log(No_Proveedor.val());
					autocompleteProducto(No_Proveedor);
				}
			} else {
				//buscar
			}
		}
	</script>

	<div>
		<h2 style="color:#336699;" align="center">COMPRAS DEL REGISTRO DE COMPRAS DEL: <?php echo $fechad; ?> al <?php echo $fechaa; ?></h2>
	</div>
	<form name="form1" method="post" action="">
		<table border="0" align="center">
			<tr> 
				<td colspan="2">
					<div align="left">
						<font size="-4" face="Arial, Helvetica, sans-serif"> Desde
							<input id="id5" type="text" name="fechad" size="11" value="<?php echo $fechad; ?>">
							<!--
              <a href="javascript:show_calendar('form1.fechad');">
								<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
							</a>
							<div id="overDiv" style="position:absolute; visibility:hidden; z-index:0;"></div>
              -->
						</font>
					</div>
				</td>
				<td colspan="2">
					<font size="-4" face="Arial, Helvetica, sans-serif"> Hasta 
						<input id="id6" type="text" name="fechaa" size="11" value="<?php echo $fechaa; ?>">
            <!--
						<a href="javascript:show_calendar('form1.fechaa');">
							<img src="/sistemaweb/images/showcalendar.gif" border="0" align="top"/>
						</a>
            -->
					</font>
				</td>
				<td width="299"> Mostrar 
					<select name="tipo_reporte">
						<option value="ord" <?php if($tipo_reporte=="ord"){echo "selected";}?> > Ordenes de Compra </option>
						<option value="dev" <?php if($tipo_reporte=="dev"){echo "selected";}?> > Devoluciones </option>
					</select>
				</td>
			</tr>
			<tr> 
				<td width="62">
					<font size="-4" face="Arial, Helvetica, sans-serif"> Proveedor: </font>
				</td>
        <td width="205">
          <font size="-4" face="Arial, Helvetica, sans-serif"> 
            <!--<input type="text" name="des_proveedor" size="30" value="<?php //echo $des_proveedor; ?>" onKeyUp="javascript:cod_proveedor.value=des_proveedor.value">
            <input type="text" name="cod_proveedor" size="2" value="<?php //echo $cod_proveedor; ?>" >
            <img src="../images/help.gif" onMouseOver="this.style.cursor='hand'" width="16" height="15" onClick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_proveedor','form1.des_proveedor','proveedores')">-->
            <input type="hidden" name="cod_proveedor" id="txt-Nu_Id_Proveedor" placeholder="Ingresar codigo proveedor" value="<?php echo $cod_proveedor; ?>" maxlength="25" size="25">
            <input type="text" name="des_proveedor" id="txt-No_Proveedor" class="mayuscula" onkeyup="autocompleteBridge(1)" placeholder="Ingresar Código o Nombre" autocomplete="off" value="<?php echo $des_proveedor; ?>" maxlength="35" size="35">

          </font>
        </td>
        <td width="46"><font size="-4" face="Arial, Helvetica, sans-serif"> Todos </font></td>
        <td width="116">
          <input type="radio" name="opcion" value="todos" onClick="javascript:marcado.value='true';" <?php if($opcion=="todos"){echo "checked";}?>>
        </td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp; </font></td>
      </tr>
      <tr> 
        <td><font size="-4" face="Arial, Helvetica, sans-serif"> Articulo: </font></td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif"> 
        <!--<input type="text" name="des_articulo" size="30" value="<?php //echo $des_articulo; ?>" onKeyUp="javascript:cod_articulo.value=des_articulo.value">
        <input type="text" name="cod_articulo" size="2" value="<?php //echo $cod_articulo; ?>">-->
        <input type="hidden" id="txt-Nu_Id_Producto" name="cod_articulo" placeholder="Ingresar codigo producto" value="<?php echo $cod_articulo; ?>" maxlength="25" size="25">
        <input type="text" id="txt-No_Producto" onkeyup="autocompleteBridge(0)" class="mayuscula" name="des_articulo" placeholder="Ingresar Código o Nombre" autocomplete="off" value="<?php echo $des_articulo; ?>" maxlength="35" size="35">  

        <!--<img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','form1.cod_articulo','form1.des_articulo','articulos');"> -->
        </font></td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">Pendientes</font></td>
        <td><input type="radio" name="opcion" value="pendientes" onClick="javascript:marcado.value='true';" <?php if($opcion=="pendientes"){echo "checked";}?>></td>
        <td><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp; </font></td>
        </tr>
    <tr> 
      <td height="23"><font size="-4" face="Arial, Helvetica, sans-serif">Almacen:</font></td>
      <td height="23"><font size="-4" face="Arial, Helvetica, sans-serif"> 
        <input type="text" name="des_almacen" size="30" value="<?php echo $des_almacen; ?>" onKeyUp="javascript:codigo_almacen.value=des_almacen.value">
        <input type="text" name="codigo_almacen" size="2" value="<?php echo $codigo_almacen; ?>">
        <img src="../images/help.gif" width="16" height="15" onMouseOver="this.style.cursor='hand'" onclick="javascript:mostrarAyuda('lista_ayuda.php','form1.codigo_almacen','form1.des_almacen','almacenes');"> 
        </font></td>
      <td><font size="-4" face="Arial, Helvetica, sans-serif">Atendidos</font></td>
      <td><input type="radio" name="opcion" value="atendidos" onClick="javascript:marcado.value='true';" <?php if($opcion=="atendidos"){echo "checked";}?>></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td height="23" colspan="2"> <input type="hidden" name="accion"></td>
      <td>&nbsp;</td>
      <td> <input type="hidden" name="marcado" value="<?php echo $opcion?>"> <input type="button" name="btn_buscar" value="Buscar" onClick="mandarDatos(form1,'Buscar')"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <br>
  <?php if($tipo_reporte=="ord"){ ?>
  <a href="#" onClick="javascript:window.open('cpag_rep_ingxordcompras-reportexls.php?tipo_reporte=<?php echo $tipo_reporte; ?>&fechad=<?php echo $fechad; ?>&fechaa=<?php echo $fechaa; ?>&opcion=<?php echo $opcion; ?>&cod_proveedor=<?php echo $cod_proveedor; ?>&des_proveedor=<?php echo $des_proveedor; ?>&cod_articulo=<?php echo $cod_articulo; ?>&des_articulo=<?php echo $des_articulo; ?>&cod_almacen=<?php echo $codigo_almacen; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar Reporte
  </a> <br>
  <table width="767" border="1">
    <tr> 
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Fecha</font></div></td>
      <td width="55"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Orden 
          Compra</font></div></td>
      <td width="101"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Proveedor</font></div></td>
      <td width="53"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Docum. 
          Ref</font></div></td>
      <td width="43"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Almacen</font></div></td>
      <td width="99"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></div></td>
      <td width="48"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cantidad</font></div></td>
      <td width="50"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
          Unitario</font></div></td>
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
          Total</font></div></td>
      <td width="64"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Nro. 
          <br>
          Movimiento</font></div></td>
      <td width="108"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Facturacion 
          Proveedor </font></div></td>
    </tr>
    <?php for($i=0;$i<pg_numrows($rs1);$i++){ ?>
    <?php $A = pg_fetch_array($rs1,$i); ?>
    <?php if($A[11]=="01/01/1841"){ $A[11]="";} ?>  
    <tr> 
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[0]; ?></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $A[1]; ?></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">&nbsp;<?php echo $A[2]; ?> 
          <?php echo $A[14]; ?></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[4]; ?>&nbsp;<?php echo $A[3]; ?></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[5]; ?>&nbsp;</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[6]; ?> 
          <?php echo $A[13]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[7]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[8]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[9]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[10]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[11]; ?> 
          <?php echo $A[12]; ?> &nbsp;</font></div></td>
    </tr>
   <?php } ?>
    <tr> 
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
  </table><?php } ?>
  <br>
  <?php if($tipo_reporte=="dev"){ ?>
  <a href="#" onClick="javascript:window.open('cpag_rep_ingxordcompras-reportexls.php?tipo_reporte=<?php echo $tipo_reporte; ?>&fechad=<?php echo $fechad; ?>&fechaa=<?php echo $fechaa; ?>&opcion=<?php echo $opcion; ?>&cod_proveedor=<?php echo $cod_proveedor; ?>&des_proveedor=<?php echo $des_proveedor; ?>&cod_articulo=<?php echo $cod_articulo; ?>&des_articulo=<?php echo $des_articulo; ?>&cod_almacen=<?php echo $codigo_almacen; ?>','miwin','width=800,height=450,scrollbars=yes,menubar=yes,left=0,top=0');">Exportar 
  Reporte </a> <br>
  <table width="706" border="1">
    <tr> 
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Fecha</font></div></td>
      <td width="101"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Proveedor</font></div></td>
      <td width="53"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Docum. 
          Ref</font></div></td>
      <td width="43"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Almacen</font></div></td>
      <td width="99"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Articulo</font></div></td>
      <td width="48"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Cantidad</font></div></td>
      <td width="50"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
          Unitario</font></div></td>
      <td width="38"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Costo 
          Total</font></div></td>
      <td width="64"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Nro. 
          <br>
          Movimiento</font></div></td>
      <td width="108"><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif">Facturacion 
          Proveedor </font></div></td>
    </tr>
  <?php for($i=0;$i<pg_numrows($rs1);$i++){ ?>
  <?php $A = pg_fetch_array($rs1,$i); ?>  
    <tr> 
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[0]; ?></font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[1]; ?> 
          <br>
          <?php echo $A[13]; ?>&nbsp;</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[2]; ?><?php echo $A[3]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[4]; ?>&nbsp;</font></div></td>
      <td><div align="center"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[5]; ?><br>
          <?php echo $A[12]; ?></font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[6]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[7]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[8]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"><?php echo $A[9]; ?>&nbsp;</font></div></td>
      <td><div align="right"><font size="-4" face="Arial, Helvetica, sans-serif"> 
          <?php echo $A[11]; ?>&nbsp;</font></div></td>
    </tr>
    <?php } ?>
    <tr> 
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
      <td><font face="Arial, Helvetica, sans-serif">&nbsp;</font></td>
    </tr>
  </table>
  <?php } ?>
</form>
</body>
</html>
<?php
pg_close();
?>
