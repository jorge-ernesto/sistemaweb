<?php
include("config.php");
$prove = $_REQUEST['prove'];
$cost = $_REQUEST['cost'];
$Moneda_art = trim($_REQUEST['moneda']);
$TCambio_art = trim($_REQUEST['tcambio']);

if($cost)
{
//echo "<!--ENTRO IF-->\n";
$varcost = ",cost";
    if($lista=="")
    {
    //echo "<!--ENTRO IF 2-->\n";
	if($Buscar=="Buscar")
	{
        //echo "<!--ENTRO IF 3-->\n";
	    if($busca!="")
	    {
	    //echo "<!--ENTRO IF 4-->\n";
	       $rs = ayudaOrdCompraCosto($consulta,$busca,$busca,trim($prove));
	       $rs2 = ayuda($consulta,$busca, $busca);
	    }else{
	    //echo "<!--ENTRO ELSE 4-->\n";
	       $rs = ayudaOrdCompraCosto($consulta,"null","null",trim($prove));
	       $rs2 = ayuda($consulta,'null', 'null');
	    }
	}else{
	//echo "<!--ENTRO ELSE 3-->\n";
	    $rs = ayudaOrdCompraCosto($consulta,"null","null",trim($prove));
	    $rs2 = ayuda($consulta,"null","null");
	}
    }else{
    //echo "<!--ENTRO ELSE 2-->\n";
	if($Buscar!="Buscar")
	{
	    $rs = ayudaOrdCompraCosto($consulta,$lista,"null",trim($prove));
	    $A = pg_fetch_array($rs, 0);
	    
	    $rs2 = ayuda($consulta,$lista, "null");
	    
	    $B = pg_fetch_array($rs2, 0);
	    
	    $desc1 = $B[1];
	    $cost1 = $A[2];
	    $Moneda = trim($A[3]);
	    
	if($Moneda_art && $TCambio_art)
	{
	   //echo "ENTRO2";
	    if(trim($Moneda_art) == trim($Moneda))
	    {
	       $cost1 = $cost1;
               //echo "ENTRO";
	    }
	    elseif($Moneda_art != $Moneda)
	    {
	     //echo "ENTRO2";
		if($Moneda_art == '01')
		{
		  //echo "ENTRO 01";
		    //$cost1 = $cost1 * $TCambio_art;
		    $cost1 = $cost1 * $TCambio_art;
		}else{
		  //echo "ENTRO 02";
		    $cost1 = $cost1 / $TCambio_art;
		    //echo "12";
		}
	    }
	    else
	    {
	     //echo "ENTRO3";
	    
	    }
	}
	    
	    ?>
	    <script language="JavaScript">
	    //alert('<?php echo $cost;?>');
	    opener.document.<?php echo $des;?>.value = '<?php echo $desc1;?>';
	    opener.document.<?php echo $cost;?>.value = '<?php echo $cost1;?>';
	    window.close();
	    </script>
	    <?php
	}
    }
}else{
echo "<!--ENTRO ELSE-->";
    if($lista=="")
    {
	if($Buscar=="Buscar")
	{
	    if($busca!="")
	    {
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
	if($Buscar!="Buscar")
	{
	//$rs = pg_exec("select trim(pro_codigo), pro_razsocial from int_proveedores 
	//where trim(pro_codigo)='$lista'");
	$rs = ayuda($consulta,$lista,"null");
	$A = pg_fetch_array($rs,0);
	$desc1 = $A[1];
	?>
		<script language="JavaScript">
		//alert('<?php echo $des;?>');
		opener.document.<?php echo $des;?>.value = '<?php echo $desc1;?>';
		window.close();
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
	function pasarValorOpener(lista,form,cod,des<?php echo $varcost;?>){
		var valor = lista.value;
		eval("opener.document."+cod+".value = '"+valor+"'");
		form.submit();
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
  <?php
  if($cost){
  ?>
    <input type="hidden" name="cost" value="<?php echo $cost;?>">
    <input type="hidden" name="prove" value="<?php echo $prove;?>">
  <?php } ?>
  <br>
  <select name="lista" size="12">
  <?php for($i=0;$i<pg_numrows($rs2);$i++)
     {
            echo "A : ".$A[0]."";
            
  		$A = pg_fetch_array($rs2,$i);	
  		print "<option value='$A[0]'>$A[0] -- $A[1]</option>";
     } ?>
  </select>
  <br>
  <?php
  if($cost){
  ?>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>')">
  <?php }else{ ?>
  <input type="button" name="Seleccionar" value="Seleccionar" onClick="javascript:pasarValorOpener(lista,form1,'<?php echo $cod;?>','<?php echo $des;?>','<?php echo $cost;?>')">
  <?php } ?>
</form>
</body>
</html>
