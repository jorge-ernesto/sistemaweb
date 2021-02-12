<?php
session_start();
include("../functions.php");
require("../clases/funciones.php");
$funcion = new class_funciones;
// crea la clase para controlar errores
//$clase_error = new OpensoftError;
// conectar con la base de datos
$coneccion=$funcion->conectar("","","","","");

	switch($chiche) {
	
		case "Proveedores":
			echo $chiche;
			$rs1 = pg_exec(" select pro_razsocial from int_proveedores where pro_codigo='$codigo' ");			
			$A = pg_fetch_array($rs1,0);
			echo $A["pro_razsocial"];
			?>
			<script>
				parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["pro_razsocial"];?>';
				parent.document.all("despro").innerText = '<?php echo $A["pro_razsocial"];?>';
				
			</script>
			
			<?php
			if(pg_numrows($rs1)==0 || trim($codigo)==""){
				?>
				<script>
					parent.document.form1.<?php echo $campo_codigo;?>.focus();
					alert('Codigo de Proveedor Inexistente !!');
				</script>
			<?php
			}
		
			break;
			
			
	case "Documentos":
			echo $chiche;
			$rs1 = pg_exec(" select trim(tab_elemento) as codigo,tab_descripcion as desc_doc from int_tabla_general 
			where tab_tabla='08' and tab_elemento<>'000000'
			and trim(tab_elemento)=lpad(trim('$codigo'),6,'0')");			
			$A = pg_fetch_array($rs1,0);
			echo $A["desc_doc"];
			?>
			<script>
				parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["desc_doc"];?>';
				parent.document.all("desdoc").innerText = '<?php echo $A["desc_doc"];?>';
				
			</script>
			
			<?php
			if(pg_numrows($rs1)==0 || trim($codigo)==""){
				?>
				<script>
					parent.document.form1.<?php echo $campo_codigo;?>.focus();
					alert('Codigo de Documento Inexistente !!');
				</script>
			<?php
			}
		
			break;
			
		
	case "Articulos":

			echo $chiche;

			$almacen_interno = "";

			if($c_naturaleza==1 || $c_naturaleza==2){
    
	    			$almacen_interno = $c_alma_des;

			}else{
				$almacen_interno = $c_alma_ori;
			} 
			
			$rs1 = pg_exec("select to_char(util_fn_fechaactual_aprosys(),'mm') as mes, to_char(util_fn_fechaactual_aprosys(),'yyyy') as periodo");

			$A = pg_fetch_array($rs1,0);
			$mes = $A["mes"];
			$periodo = $A["periodo"];
			
			$rs1 = pg_exec(" select util_fn_stock ('$periodo','$mes','$codigo','$almacen') as stock");
			$A = pg_fetch_array($rs1,0);
			$stock = $A["stock"];
			
			//$rs1 = pg_exec("select art_costoreposicion from int_articulos where lpad(trim(art_codigo),13,'0')=lpad('$codigo',13,'0') ");//costo reposicion de int_articulos 07/06/2012
			$rs1 = pg_exec("select rec_precio as art_costoreposicion from com_rec_pre_proveedor where lpad(trim(art_codigo),13,'0')=lpad('$codigo',13,'0') ORDER BY rec_fecha_ultima_compra DESC LIMIT 1;");//de com_rec_pre_proveedor
			$costo_uni = 0;
			if(pg_numrows($rs1)>0){
				$A = pg_fetch_array($rs1,0);
				$costo_uni = $A["art_costoreposicion"];
			
			}

			/*echo "costo_uni Alexis:  " . $costo_uni;
			echo "valor Alexis:  " . $valor;*/

			if($c_valor=="N"){
				$rs1 = pg_exec("select util_fn_costo_promedio('$periodo','$mes',lpad('$codigo',13,'0'),'$almacen_interno') as art_costo_promedio");
				$costo_uni = 0;
				if(pg_numrows($rs1)>0){
					$A = pg_fetch_array($rs1,0);
					$costo_uni = $A["art_costo_promedio"];
					
				}	
			}

			//echo "costo_uni se modifico Alexis:  " . $costo_uni;
			
			if ($codigo == '11620301' || $codigo == '11620302' || $codigo == '11620303' || $codigo == '11620304' || $codigo == '11620305' || $codigo == '11620307'){
				
				$rs1 = pg_exec("
				SELECT
					art_descripcion as desc,
					art_codigo as cod,
					art_estado as est
				FROM
					int_articulos 
				WHERE
					art_codigo = '$codigo'
				");	

			} else  {

				$rs1 = pg_exec("
				SELECT
					art_descripcion as desc,
					art_codigo as cod,
					art_estado as est
				FROM
					int_articulos 
				WHERE
					art_codigo ='" . addslashes($codigo) . "'
				");
	
			}

			$A = pg_fetch_array($rs1,0);
//			echo $A["desc"];
			$desc = $A["desc"];
			$desc = str_replace("'"," ",$desc);
			//echo $desc;
			if ($A['est'] == "0") {
			?>
			<script>
				parent.document.form1.<?php echo $campo;?>.value='<?php echo $desc; ?>';
				parent.document.form1.art_stock.value='<?php echo $stock; ?>';
				parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["cod"]; ?>';
				parent.document.form1.art_costo_uni.value='<?php echo $costo_uni; ?>';
				//alert(parent.document.all("des_articulo").innerText);
				parent.document.all("des_articulo").innerHTML = '<?php echo $desc; ?>';
				//alert('<?php echo $desc; ?>');
			</script>
			
			<?php
			} else {
			?>
			<script>
				parent.document.form1.<?php echo $campo_codigo;?>.focus();
				parent.document.form1.<?php echo $campo;?>.value='';
				parent.document.form1.art_stock.value='';
				parent.document.form1.<?php echo $campo_codigo;?>.value='';
				parent.document.form1.art_costo_uni.value='';
				parent.document.all("des_articulo").innerHTML='';
				alert('El articulo esta desactivado');
			</script>
			<?php
			}
			if(pg_numrows($rs1)==0 || trim($codigo)==""){
				?>
				<script>
					parent.document.form1.<?php echo $campo_codigo;?>.focus();
					alert('Codigo de Articulo Incorrecto !!');
				</script>
			<?php
			}
		
			break;
		
	
			case "Almacenes":
				echo "Chiche ".$chiche."<br>";
				echo "Codigo ".$codigo."<br>";
				echo "Campo ".$campo."<br>";
				echo "Campo ".$campo_codigo."<br>";
				
				$rs = pg_exec(" select ch_almacen,ch_nombre_almacen from inv_ta_almacenes 
				where ch_almacen=lpad(trim('$codigo'),3,'0') ");
				
				if(pg_numrows($rs)>0){
					$A = pg_fetch_array($rs,0);
					$cod  = $A[0];
					$desc = $A[1];
					print "<script>
					parent.document.form1.$campo_codigo.value = '$cod';
					parent.document.all(\"$campo\").innerText = '$desc';
					</script>";
				}else{
					?>
						<script>
						parent.document.form1.<?php echo $campo_codigo;?>.focus();
						alert('Codigo de Almacen <?php echo $campo_codigo;?> Incorrecto !!');
						</script>
					<?php
				}
				
				
			break;

	}

pg_close();

echo $chiche;
