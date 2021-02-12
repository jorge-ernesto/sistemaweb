<?php
include("config.php");
include("cpag_ayuda_orddev_support.php");
include_once("../include/dbsqlca.php");
$sqlca = new pgsqlDB('localhost','postgres', 'postgres', 'acosaperu');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Iframe de Inclusiones</title>
</head>

<body>
<?php

  function recuperarRegistrosXml($codigo)
  {
    //seleccionar data de tabla
    global $sqlca;
    $query = "SELECT pro_xml_bancos FROM int_proveedores WHERE pro_codigo='".$codigo."'";
    //echo "QUERY : $query \n";
    if ($sqlca->query($query) < 0)
      return '<error>'.$sqlca->error.'</error>';
    $result = $sqlca->fetchRow();
    $registros = str_replace('<?phpxml version="1.0"?>','',$result["pro_xml_bancos"]);
    //$campos = str_replace('{', '', $result["tab_campos"]);
    $xml = '<tabla campos="codigo_banco,nro_cuenta_bancaria,tipo_cuenta_bancaria" >'.$registros.'</tabla>';
    //echo "XML : $xml \n";
    if (!$dom = domxml_open_mem($xml, DOMXML_LOAD_PARSING + //0
          DOMXML_LOAD_COMPLETE_ATTRS + //8
          DOMXML_LOAD_SUBSTITUTE_ENTITIES + //4
          DOMXML_LOAD_DONT_KEEP_BLANKS //16
          ,$error)) { 
      echo "Error al procesar XML Listado Recupera registro\n";
      return null; }
    return $dom;
  }
  
  function VerificarProveedor($registrosXml, $codigo)
  {
    if($registrosXml)
    {
      //echo "registrosXml : $registrosXml\n";
      $listado_array = array();
      $root = $registrosXml->document_element();
      $columnas = explode(',',$root->get_attribute('campos'));

      for($i=0;$i<count($columnas);$i++)
      {
        $nombres[$i] = $columnas[$i];
      }
      $node_regs = $root->get_elements_by_tagname('reg');
      $i=1;
      if(!empty($node_regs))
      {
        //print_r($node_regs);
	foreach($node_regs as $reg)
	{
	    $id = $reg->get_attribute('id');
	    //echo "REG : $id \n";
	    //$form->addElement('bancos', new f2element_hidden('reg[]', $id));
	    $valores = $reg->child_nodes();
	    
	    $regCod = '';
	    foreach($valores as $llave => $valor)
	    {
	    $regCod==''?$regCod=$valor->get_content():$regCod;
	    //echo " LLAVE : $llave => REGCOD : ".$valor->get_content()." \n";
	    $listado_array[$id][$nombres[$llave]] = $valor->get_content();
	    
	    if($listado_array[$id]['tipo_cuenta_bancaria']=='02' && trim($listado_array[$id]['codigo_banco'])=='000005')
	    {
		$mensaje = "";
	    }else{
		$mensaje = "El proveedor no tiene cuenta de DETRACCION, debe agregar una en el Banco de la Nacion.";
	    }
    
	    }
	    $i++;
	}
      }else{
        return "El proveedor no tiene cuenta de DETRACCION, debe agregar una en el Banco de la Nacion.";
      }
    }else{
        return "El proveedor no tiene cuenta de DETRACCION, debe agregar una en el Banco de la Nacion.";
    }
    return $mensaje;
  }

switch($chiches){

    case "Proveedores":
        $query = "SELECT pro_razsocial ".
                 "FROM int_proveedores ".
                 "WHERE pro_codigo = '$codigo' ";
        
	$rs1 = pg_exec($query);
	$A = pg_fetch_array($rs1,0);
	//echo "Mensaje : $resultados"; 
	?>
	<script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["pro_razsocial"];?>';
	</script>
	<?php
	if(pg_numrows($rs1)==0 || trim($codigo)=="")
	{
	?>
	    <script>
		    alert('Codigo de Proveedor Inexistente !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
    break;
    
    case "Rubros":
	
	$query = "SELECT trim(ch_codigo_rubro), ".
			"ch_descripcion AS desc_rubro, ".
			"ch_percepcion_tipo, ".
			"ch_percepcion_porcentaje, ".
			"ch_detraccion_tipo, ".
			"ch_detraccion_porcentaje ".
		 "FROM cpag_ta_rubros ".
		 "WHERE trim(ch_codigo_rubro)=trim('$codigo') ";

        //echo "$query \n";
	$rs1 = pg_exec($query);
	
	$A = pg_fetch_array($rs1,0);
	
        if(trim($A["ch_detraccion_tipo"])=="1" || trim($A["ch_detraccion_tipo"])=="2")
        {
	    $datosXml = recuperarRegistrosXml($_REQUEST['opcional']);
	    $resultados = VerificarProveedor($datosXml,$_REQUEST['opcional']);
	    //echo "MENSAJE : $resultados \n";
	    if(!empty($resultados))
	    {
	    ?>
	    <script>
		    alert('<?php echo $resultados?>');
		    parent.document.getElementById('prov_cta_detracc').value='CtaPendiente';
	            //parent.document.form1.<?php echo $campo_codigo;?>.focus();
		    parent.document.getElementsByName('cod_proveedor')[0].focus();
	    </script> 
	    <?php
	    }else{
	    ?>
	    <script>
		    parent.document.getElementById('prov_cta_detracc').value='CtaOk';
	    </script> 
	<?php  }
        }
        
	if(trim($A["ch_detraccion_tipo"])!="1" && trim($A["ch_detraccion_tipo"])!="2" && trim($A["ch_percepcion_tipo"])=="1")
	{
	   //echo "ENTRO : ".$A["ch_percepcion_porcentaje"]." \n";
	?> 
	<script>
	   parent.document.getElementById('gruposunat').style.display='block';
	   
	   parent.document.getElementById('retencion').style.display='none';
	   parent.document.getElementById('detraccion').style.display='none';
	   parent.document.getElementById('percepcion').style.display='block';
	   parent.document.getElementById('mnt_apli_percepcion').value='<?php echo trim($A["ch_percepcion_porcentaje"])?>';
	   parent.document.getElementById('mnt_apli_detraccion').value='';
	   parent.document.getElementById('mnt_apli_retencion').value='';
	   parent.document.getElementById('detraccion_tipo').value='';
	   parent.document.getElementById('prov_cta_detracc').value='';
	   parent.document.getElementsByName('percepcion')[0].disabled=false;
	   parent.document.getElementsByName('percepcion')[0].value='0.00';
	   parent.document.getElementsByName('detraccion')[0].value='';
	   parent.document.getElementsByName('detraccion')[0].disabled=true;
	   parent.document.getElementsByName('retencion')[0].value='';
	   parent.document.getElementsByName('retencion')[0].disabled=true;
	   parent.document.getElementsByName('importe_final')[0].value='0.00';
	</script>
	<?php
	}elseif(trim($A["ch_percepcion_tipo"])!="1" && trim($A["ch_detraccion_tipo"])=="1"){
	?>
	<script>
	   parent.document.getElementById('gruposunat').style.display='block';
	   
	   parent.document.getElementById('retencion').style.display='none';
	   parent.document.getElementById('percepcion').style.display='none';
	   parent.document.getElementById('detraccion').style.display='block';
	   parent.document.getElementById('mnt_apli_detraccion').value='<?php echo trim($A["ch_detraccion_porcentaje"])?>';
	   parent.document.getElementById('detraccion_tipo').value='<?php echo trim($A["ch_detraccion_tipo"])?>';
	   parent.document.getElementById('mnt_apli_percepcion').value='';
	   parent.document.getElementById('mnt_apli_retencion').value='';
	   parent.document.getElementsByName('percepcion')[0].value='';
	   parent.document.getElementsByName('percepcion')[0].disabled=true;
	   parent.document.getElementsByName('detraccion')[0].disabled=false;
	   parent.document.getElementsByName('detraccion')[0].value='0.00';
	   parent.document.getElementsByName('retencion')[0].value='';
	   parent.document.getElementsByName('retencion')[0].disabled=true;
	   parent.document.getElementsByName('importe_final')[0].value='0.00';

	</script>
	<?php
	}elseif(trim($A["ch_percepcion_tipo"])!="1" && trim($A["ch_detraccion_tipo"])=="2"){
	?>
	<script>
	   parent.document.getElementById('gruposunat').style.display='block';
	   
	   parent.document.getElementById('retencion').style.display='none';
	   parent.document.getElementById('percepcion').style.display='none';
	   parent.document.getElementById('detraccion').style.display='block';
	   parent.document.getElementById('mnt_apli_detraccion').value='<?php echo trim($A["ch_detraccion_porcentaje"])?>';
	   parent.document.getElementById('detraccion_tipo').value='<?php echo trim($A["ch_detraccion_tipo"])?>';
	   parent.document.getElementById('mnt_apli_percepcion').value='';
	   parent.document.getElementById('mnt_apli_retencion').value='';
	   parent.document.getElementsByName('percepcion')[0].value='';
	   parent.document.getElementsByName('percepcion')[0].disabled=true;
	   parent.document.getElementsByName('detraccion')[0].disabled=false;
	   parent.document.getElementsByName('detraccion')[0].value='0.00';
	   parent.document.getElementsByName('retencion')[0].value='';
	   parent.document.getElementsByName('retencion')[0].disabled=true;
	   parent.document.getElementsByName('importe_final')[0].value='0.00';
	   
	</script>
	<?php
	}else{
	?>
	<script>
	   parent.document.getElementById('gruposunat').style.display='block';
	   
	   parent.document.getElementById('percepcion').style.display='none';
	   parent.document.getElementById('detraccion').style.display='none';
	   parent.document.getElementById('retencion').style.display='block';
	   parent.document.getElementById('detraccion_tipo').value='';
	   parent.document.getElementById('prov_cta_detracc').value='';
	   parent.document.getElementById('mnt_apli_retencion').value='0.06';
	   parent.document.getElementById('mnt_apli_percepcion').value='';
	   parent.document.getElementById('mnt_apli_detraccion').value='';
	   parent.document.getElementsByName('percepcion')[0].value='';
	   parent.document.getElementsByName('percepcion')[0].disabled=true;
	   parent.document.getElementsByName('detraccion')[0].value='';
	   parent.document.getElementsByName('detraccion')[0].disabled=true;
	   parent.document.getElementsByName('retencion')[0].disabled=false;
	   parent.document.getElementsByName('retencion')[0].value='0.00';
	   parent.document.getElementsByName('importe_final')[0].value='0.00';
	</script>
	<?php
	}
	?>
	<script>
	   parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["desc_rubro"];?>';
	   parent.validarImporteTotal(parent.document.form1,parent.rubrosinv[parent.document.form1.cod_rubro.value]);
	   parent.calcularMontos2(parent.document.form1,'total'),parent.document.form1.redondeo.value='mal';
	</script>
	<?php
	
	if(pg_numrows($rs1)==0 || trim($codigo)==""){
	?>
	    <script>
		    alert('Codigo de Rubro Inexistente !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
				
    break;

    
    case "Documentos":
        $query = "SELECT trim(tab_elemento) as codigo,".
                         "tab_descripcion as desc_doc ".
                 "FROM int_tabla_general ".
	         "WHERE tab_tabla='08' ".
	         "AND tab_elemento<>'000000' ".
		 "AND trim(tab_elemento)=lpad(trim('$codigo'),6,'0') ";
	    
	$rs1 = pg_exec($query);
	$A = pg_fetch_array($rs1,0);
	?>
	<script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["desc_doc"];?>';
		parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["codigo"];?>';
	</script>
	<?php	
	
	if(pg_numrows($rs1)==0 || trim($codigo)==""){
	?>
	    <script>
		    alert('Codigo de Documento Inexistente !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
			    
    break;
    
    
    case "Documentos_Sunat":
        $query = "SELECT trim(tab_elemento) as codigo, ".
                        "tab_descripcion as desc_doc ".
                 "FROM int_tabla_general ".
	         "WHERE tab_tabla='08' ".
	         "AND tab_elemento<>'000000' ".
	         "AND trim(tab_elemento)=lpad(trim('$codigo'),6,'0') ".
	         "AND tab_car_03 is not null ";
	$rs1 = pg_exec($query);
	$A = pg_fetch_array($rs1,0);
	?>
	<script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["desc_doc"];?>';
		parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["codigo"];?>';
	</script>
	<?php	
	
	if(pg_numrows($rs1)==0 || trim($codigo)==""){
	?>
	    <script>
		    alert('Codigo de Documento Inexistente !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
			    
    break;


    case "Almacenes":
        $query = "SELECT trim(ch_almacen) as codigo, ".
                        "ch_nombre_almacen  as desc_alma ".
                 "FROM inv_ta_almacenes ".
	         "WHERE ch_clase_almacen='1' ".
	         "AND trim(ch_almacen)=lpad(trim('$codigo'),3,'0')";
	$rs1 = pg_exec($query);
	$A = pg_fetch_array($rs1,0);
	?>
	<script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["desc_alma"];?>';
		parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["codigo"];?>';
	</script>
	<?php
	
	if(pg_numrows($rs1)==0 || trim($codigo)==""){
	?>
	    <script>
		    alert('Codigo de Almacen Inexistente !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
    break;
    
    
    
    case "Vencimientos":
	$L = separarCadena($codigo,"-");
	$cod_prov   = $L[0];
	$fecha_doc  = $L[1];
	$query = "SELECT pro_dias_pago ".
	         "FROM int_proveedores ".
	         "WHERE trim(pro_codigo)=trim('$cod_prov')";
	         
	$rs1 = pg_exec($query);
	$A = pg_fetch_array($rs1,0);
	$dias_ven = $A["pro_dias_pago"];
	$rs1 = pg_exec("select to_char( (to_date('$fecha_doc','dd/mm/yyyy') + interval '$dias_ven day' ) , 'dd/mm/yyyy'  ) as fecha_ven");
	$A = pg_fetch_array($rs1,0);
	echo "select to_char( (to_date('$fecha_doc','dd/mm/yyyy') + interval '$dias_ven day' ) , 'dd/mm/yyyy'  ) as fecha_ven"; 
	?>
	<script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["fecha_ven"];?>';
	
	</script>
	<?php
	
	if(pg_numrows($rs1)==0 || trim($codigo)==""){
	?>
	    <script>
		    alert('<?php echo $fecha_doc;?>Codigo de Proveedor Inexistente o Fecha del Documento Incorrecta');
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
	    </script>
	<?php
	}
				    
    break;


    case "Tasa_cambio" : 
	echo $campo;
	$fec_docu = $codigo;
	$codigo="02";
	if($codigo=="01" || $codigo=="02") {
	    pg_exec("begin");
	    pg_exec(" select UTIL_FN_TIPOCAMBIO('ret','$fec_docu','02') ");
	    $rs1 = pg_exec("fetch all in ret");
	    pg_exec("end");
	    $tca_venta_oficial=1;
	    if(pg_numrows($rs1)>0){
	    $A = pg_fetch_array($rs1,0);
	    $tca_compra_oficial = $A["tca_compra_oficial"];
	    $tca_compra_oficial = trim($tca_compra_oficial);
	    if($tca_compra_oficial==""){$tca_compra_oficial=0;}
	    }
	    ?>
	    <script>
		parent.document.form1.<?php echo $campo;?>.value='<?php echo $tca_compra_oficial;?>';
		parent.calcularMontos(parent.document.form1);
	    </script>
	    <?php
	}else{
	    ?>
	    <script>
		//parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["tca_venta_oficial"];?>';
		alert('Codigo de Moneda inexistente !');
	    </script>
	    <?php
	}				
    break;
    
    
    case "Integridad" :
	    $L = separarCadena($codigo,"-");
	    $cod_prov = $L[0];
	    $cod_doc  = $L[1];
	    $serie_doc = $L[2];
	    $num_doc = $L[3];
	    //echo $cod_prov;
	    $q1 = "select * from cpag_ta_cabecera where 
	    pro_cab_tipdocumento =  substring(trim('$cod_doc') for 2 from length('$cod_doc')-1)
	    and trim(pro_cab_seriedocumento) = trim('$serie_doc') and 
	    trim(pro_cab_numdocumento) = trim('$num_doc') and trim(pro_codigo) = trim('$cod_prov') ";
	    $rs1 = pg_exec($q1);
	    //echo $q1;
	    if(pg_numrows($rs1)>0){
	    ?>
		<script>
		    parent.document.form1.<?php echo $campo;?>.focus();
		    alert('Este documento ya ha sido registrado');
		</script>
		<?php
	    }
	    
    break;
    
    
    case "Fecha_Documento":
	    $fec1 = date("m/Y"); 
	    echo "FECHA : ".$codigo."\n";
	    $q = " select to_date('$codigo' , 'dd/mm/yyyy') <= util_fn_fechaactual_aprosys() 
		    AND  to_date('$codigo' , 'dd/mm/yyyy')>= util_fn_fechaactual_aprosys()-interval '3 days' 
		    as valido, to_char( util_fn_fechaactual_aprosys() , 'dd/mm/yyyy') as fecha_minima ";
	    
	    $q = "select to_char(to_date('$codigo','dd/mm/yyyy'),'mm-yyyy')=to_char(current_date,'mm-yyyy')
		    OR to_date('01/$fec1','dd/mm/yyyy')+interval '4 day'>=current_date
			as valido,to_char( util_fn_fechaactual_aprosys() , 'dd/mm/yyyy') as fecha_minima";
	    //echo $q;
	    $rs1 = pg_exec($q);
	    $A = pg_fetch_array($rs1,0);
	    if($fec_docu==""){$fec_docu = date("d/m/Y");}
	    
	    $div_fe = explode("/",$fec_docu);
	    //print_r($div_fe);
	    $tm_fec_docu = mktime(0, 0, 0, $div_fe[1], $div_fe[0], $div_fe[2]);
	    
	    $div_cod = explode("/",$codigo);
	    //print_r($div_cod);
	    
	    $tm_codigo = mktime(0, 0, 0, $div_cod[1], $div_cod[0], $div_cod[2]);
	    //$tm_codigo = mktime(0, 0, 0, 12, 32, 1997);
	    //echo "FECH : $tm_fec_docu \n COD: $tm_codigo \n";
	    if($tm_codigo > $tm_fec_docu)
	    {
	       $A["valido"]="f";
	    }
	    
	    if($A["valido"]=="f"){
	    ?>
		<script>
		    alert('Fecha no permitida !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $fec_docu;?>';
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
		</script>
	    <?php
	    
	    }else{
	    print "<br>valido";
	    echo $_SESSION['almacen'];
	    $tipoAlmacen = pg_result(pg_exec("select ch_tipo_sucursal from int_ta_sucursales where ch_sucursal=trim('".$_SESSION['almacen']."')"),0,0);
	    print "xcccxxcs";
		switch($tipoAlmacen){
		
		    case "O":
			$r = pg_result(pg_exec("select cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int)
			- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) >=-1
			AND 
			cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int ) 
			- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) < 1 "),0,0);
		    break;
		    
		    case "E":
			$r = pg_result(pg_exec("select cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int)
			- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) = 0"),0,0);
		    break;
		
		}
		    
		if($r=="f"){
		    ?>
		    <script>
		    alert('Fecha no permitida !!');
		    parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["fecha_minima"];?>';
		    parent.document.form1.<?php echo $campo_codigo;?>.focus();
		    </script>
		    <?php
		}
			    
	    pg_exec("begin");
	    pg_exec(" select UTIL_FN_TIPOCAMBIO('ret','$codigo','02') ");
	    $rs = pg_exec(" fetch all in ret ");
	    pg_exec("end");
	    if(pg_numrows($rs)>0){
	    $tasa = pg_result($rs,0,"tca_compra_oficial");
	    }else{$tasa = 0;}
	    print "<script>parent.document.form1.tasa_cambio.value='$tasa';</script>";
		    
	    
	    }
    break;	
}
?>
</body>
</html>
<?php
pg_close();
?>