<?php
session_start();
include("guias/config.php");
include("inv_add_guia_support.php");
include("/sistemaweb/functions.php");
include("store_procedures.php");

echo $_SESSION["almacen"];
switch($accion){
	
	case "Filtrar":
	
		$tipo_transa = $codigo;
		$q = "select tran_entidad from inv_tipotransa where tran_codigo='$tipo_transa' ";
		echo $q;
		$rs = pg_exec($q);
		$A = pg_fetch_array($rs,0);
		$entidad = trim($A["tran_entidad"]); 
		switch ($entidad){
		
			case "C":
				
					?>
						<script language="JavaScript">
								parent.hacerDesaparecer('fila_proveedor');
								parent.hacerAparecer('fila_cliente');
								parent.document.form1.tipo_entidad.value='<?php echo $entidad;?>';		
						</script>
					<?php
			
			break;
			
			case "P":
					?>
						<script language="JavaScript">
								parent.hacerAparecer('fila_proveedor');
								parent.hacerDesaparecer('fila_cliente');
								parent.document.form1.tipo_entidad.value='<?php echo $entidad;?>';
						</script>
					<?php	
			break;
			
			case "N":
					?>
						<script language="JavaScript">
								parent.hacerDesaparecer('fila_proveedor');
								parent.hacerDesaparecer('fila_cliente');
								parent.document.form1.tipo_entidad.value='<?php echo $entidad;?>';
						</script>
					<?php
			break;
			
			
		}
		
	
		$almacen = $_SESSION["almacen"];
			
			echo $tipo_transa."<br>";
			echo $almacen."<br>";
			
			$F = inicializarVariables($tipo_transa,trim($almacen));
			$mov_numero = $F["nro_mov"];
			$mov_naturaleza = $F["natu"];
			
			$DEF = almacenesDefault($tipo_transa);
			$alma_ori = $DEF["tran_origen"];
			$alma_des = $DEF["tran_destino"];
			$alma_ori_campo = $DEF["tran_origen_campo"];
			$alma_des_campo = $DEF["tran_destino_campo"];
		
			echo $alma_ori_campo."<br>";
			echo $alma_des_campo."<br>";
	
			if($alma_ori!=""){$read_ori="readonly='yes'";}
			if($alma_des!=""){$read_des="readonly='yes'";}
			
			if(trim($alma_ori)!=""){
				?>
					<script language="JavaScript">
						parent.hacerDesaparecer('fila_ayuda_almaori');
						parent.document.form1.alma_ori.value='<?php echo $alma_ori;?>';
						parent.document.form1.alma_ori_campo.value='<?php echo $alma_ori;?>';
						parent.document.all("almaori").innerText='<?php echo $alma_ori_campo;?>';
						parent.document.form1.alma_ori_campo.readOnly = true;
					</script>
				<?php
			}
			
			if(trim($alma_des)!=""){
				$rs1 = pg_exec(" select ch_almacen,ch_nombre_almacen as alma_des_desc from inv_ta_almacenes 
				where ch_almacen ='$alma_des' ");
				$A = pg_fetch_array($rs1,0);
				$alma_des_campo = $A["alma_des_desc"];
			
				?>
					<script language="JavaScript">
						parent.hacerDesaparecer('fila_ayuda_almades');
						parent.document.form1.alma_des.value='<?php echo $alma_des;?>';
						parent.document.form1.alma_des_campo.value='<?php echo $alma_des;?>';
						parent.document.all("almades").innerText='<?php echo $alma_des_campo;?>';
						
					</script>
				<?php
			}
			
	
			?>
				<script language="JavaScript">
					parent.document.form1.mov_numero.value = "<?php echo $mov_numero;?>";
					parent.document.form1.mov_naturaleza.value = "<?php echo $mov_naturaleza;?>";
					parent.document.form1.valorizado.value = "<?php echo $valorizado;?>";
				</script>
			<?php
			
	break;
	
	
	
	
	case "Destino":
	
		if($tipo_ayuda=="clientes"){
			$q = "select cli_direccion from int_clientes where cli_codigo='$cod_cliente'";
			echo $q;
			$rs = pg_exec($q);
			$A = pg_fetch_array($rs,0);
			$destino = $A[0];
		}
		
		if($tipo_ayuda=="proveedores"){
			$q = "select pro_direccion from int_proveedores where pro_codigo='$cod_proveedor'";
			echo $q;
			$rs = pg_exec($q);
			$A = pg_fetch_array($rs,0);
			$destino = $A[0];
		}
	
		$destino = str_replace("\""," ",$destino);
	
					?>
						<script language="JavaScript">
								parent.document.form1.destino.value='<?php echo $destino;?>';
						</script>
					<?php
	
	break;
	
	
	case "Correlativo_Guia":
	
		$correlativo = correlativo_documento('09',$serie,'select');
		$correlativo = str_pad($correlativo,10,"0",STR_PAD_LEFT);
		
		echo $correlativo;
		?>
			<script language="JavaScript">
				parent.document.form1.num_guia.value='<?php echo $correlativo;?>';
			</script>
		<?php
	
	break;
	
	case "completar_items":
		$q1 = "x";
		$q2 = "x";
		
		$codigo = trim($codigo);
		if($tipo_guia=="I"){
			$q1 = "select art_codigo as cod_item,art_descripcion as des_item from int_articulos 
			where art_codigo=lpad('$codigo',13,'0') ";
			$q2 = "select art_codigo as cod_item,art_descripcion as des_item from int_articulos 
			where art_codigo='$codigo' ";		
		}
		if($tipo_guia=="A"){
			$q1 = "select ch_codigo_activo  as cod_item ,ch_nombre_activo as des_item 
			from acf_ta_maestro_activo where ch_codigo_activo=lpad('$codigo',13,'0')";
			$q2 = "select ch_codigo_activo  as cod_item ,ch_nombre_activo as des_item 
			from acf_ta_maestro_activo where ch_codigo_activo='$codigo'";
		}
		
		$rs = pg_exec($q1); 
		$filas = pg_numrows($rs);
		if($filas==0){
			$rs = pg_exec($q2);
		}
	
		$filas = pg_numrows($rs);
		if($filas==0){
			?>
				<script language="JavaScript">
					alert("Codigo incorrecto :(!!!");
					parent.document.form1.cod_item.value="";
					parent.document.form1.cod_item.focus();
					parent.document.all("celda_item").innerText = '';
				</script>
			<?php
		}else{
			$A = pg_fetch_array($rs,0);
			
			?>
				<script language="JavaScript">
					parent.document.form1.cod_item.value='<?php echo $A["cod_item"];?>';
					parent.document.form1.des_item_campo.value='<?php echo $A["des_item"];?>';
					parent.document.all("celda_item").innerText = '<?php echo $A["des_item"];?>';
					parent.document.form1.cantidad_item.focus();
				</script>
			<?php
			
			
		}
	
	break;
	
}

pg_close();
