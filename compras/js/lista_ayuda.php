<?php
session_start();
//include("config.php");
include("../../functions.php");
include("../store_procedures.php");
require("../../clases/funciones.php");

$funcion = new class_funciones;

// crea la clase para controlar errores
$clase_error = new OpensoftError;
// conectar con la base de datos
$conector_id=$funcion->conectar("","","","","");
$filtro = $des_campo;
if($accion=="reporte"){
	generarPedidoAutomatico($consulta , $lista, $almacen);
	
	?>
	<script>
	window.open('cmpr_reporte_pedido_auto.php?filtro=<?php echo $filtro;?>','win1','width=810,height=500,scrollbars=yes,menubar=no,left=0,top=10');
	window.close();
	</script>
	<?php
	
}else{
	if($lista==""){
		if($Buscar=="Buscar"){
			if($busca!=""){
			//$q = "select trim(pro_codigo), pro_razsocial from int_proveedores where pro_razsocial like '%$busca%' ";
			//$rs = pg_exec($q);
			$rs = ayuda($consulta,$busca,$busca);
			}else{
			//$rs = combo("proveedores");
			$rs = ayuda($consulta,"null","null");
			}
		}else{
			//$rs = combo("proveedores");
			$rs = ayuda($consulta,"null","null");
		}
	}else{
		if($Buscar!="Buscar"){
		//$rs = pg_exec("select trim(pro_codigo), pro_razsocial from int_proveedores 
		//where trim(pro_codigo)='$lista'");
		$rs = ayuda($consulta,$lista,"null");
		$A = pg_fetch_array($rs,0);
		$desc1 = $A[1];
		
		$costo_unitario = costoUnitario($lista)
		?>
			<script language="JavaScript">
			//var valor = '<?php echo $valor;?>';
			//alert('<?php echo $des;?>');
			//opener.document.<?php echo $des;?>.value = '<?php echo $desc1;?>';
			//opener.document.all("<?php echo $des;?>").innerText = '<?php echo $desc1;?>';
			//eval("opener.document.form1.<?php echo $des_campo;?>.value = '<?php echo $desc1?>'");
			//if(valor=="S"){
			//opener.document.form1.art_costo_uni.value='<?php echo $costo_unitario;?>';
			//}
			//window.close();
			
			</script>
		<?php
		}
	}
}

pg_close();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<title>Ayuda Proveedores</title>
<head>
<script language="JavaScript">
function pasarValorOpener(lista,form,cod,des,des_campo){
var valor = lista.value;
//alert(valor);
//opener.document.form1.cod_proveedor.value = valor;
//alert("opener.document."+cod+".value = '"+valor+"'");
//eval("opener.document.form1."+cod+".value = '"+valor+"'");

//alert("opener.document.form1."+cod+".value = '"+valor+"'");
form1.accion.value = 'reporte';
form.submit();
//opener.alerta('Hola!!');

}

function abrirReporte(){
	window.open('cmpr_reporte_pedido_auto.php?filtro=<?php echo $filtro;?>','win1','width=800,height=500,scrollbars=yes,menubar=no,left=10,top=10');
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="/sistemaweb/sistemaweb.css" type="text/css">
</head>

<body>
<form name="form1" method="post" action="">
  Busqueda 
  <input type="text" name="busca">
  <input type="submit" name="Buscar" value="Buscar">
  <input type="hidden" name="des" value="<?php echo $des;?>">
  <input type="hidden" name="cod" value="<?php echo $cod;?>">
  <input type="hidden" name="consulta" value="<?php echo $consulta;?>">
  <input type="hidden" name="des_campo" value="<?php echo $des_campo;?>">
  <input type="hidden" name="valor" value="<?php echo $valor;?>">
  <?php echo $filtro;?>
  <input type="hidden" name="filtro" value="<?php echo $filtro;?>">
  <br>
  <select name="lista[]" size="12" multiple>
    <?php if($accion!="reporte"){
			for($i=0;$i<pg_numrows($rs);$i++){
  			$A = pg_fetch_array($rs,$i);	
	  		print "<option value='$A[0]'>$A[0] -- $A[1]</option>";
  			} 
		}
  ?>
  </select>
  <br>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:form1.accion.value = 'reporte' , form1.submit();">
  <input type="hidden" name="accion" value="">
</form>
</body>
</html>
