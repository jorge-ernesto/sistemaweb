<?php
session_start();
include("../../functions.php");
require("../../clases/funciones.php");
include("inv_enlace_cpagar_support.php");
$funcion = new class_funciones;

$coneccion=$funcion->conectar("","","","","");

switch($chiche){

	case "Rubros":
			$rs1 = pg_exec(" select trim(tab_elemento) as cod,tab_descripcion as desc_rubro from int_tabla_general 
			where tab_tabla='RCPG' and trim(tab_elemento)!='000000'
			 and trim(tab_elemento)=trim('$codigo') ");
			$A = pg_fetch_array($rs1,0);
			echo $campo."<br>";
			echo $campo_codigo."<br>";
			
			?>
			<script>
				parent.document.form1.<?php echo $campo;?>.value='<?php echo $A["cod"];?>';
				parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["cod"];?>';
				parent.document.all("descripcion_rubro").innerText = '<?php echo $A["desc_rubro"];?>';
			</script>
			<?php
			
			if(pg_numrows($rs1)==0 || trim($codigo)==""){
				?>
				<script>
					alert('Codigo de Rubro Inexistente !!');
					//parent.document.form1.<?php echo $campo_codigo;?>.focus();
					parent.document.form1.<?php echo $campo;?>.focus();
				</script>
				<?php
			}
						
		break;
		
		
		case "Documentos_Sunat":
			$rs1 = pg_exec(" select trim(tab_elemento) as codigo,tab_descripcion as desc_doc from int_tabla_general 
			where tab_tabla='08' and tab_elemento<>'000000'
			 and trim(tab_elemento)=lpad(trim('$codigo'),6,'0')
			 and tab_car_03 is not null ");
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
	

		case "Fecha_Documento":
			$tipoAlmacen = getTipoAlmacen($_SESSION['almacen']);
				echo $tipoAlmacen;
					switch($tipoAlmacen){
					
						case "O":
							$r = pg_result(pg_exec("select cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int)
							- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) >=-1
							AND 
							cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int ) 
							- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) < 1 "),0,0);
							echo "select cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int)
							- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) >=-1
							AND 
							cast(to_char(to_date('$codigo','dd/mm/yyyy'),'yyyy') as int ) 
							- cast(to_char(util_fn_fechaactual_aprosys(),'yyyy') as int) < 1 ";
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
						//parent.document.form1.<?php echo $campo_codigo;?>.value='<?php echo $A["fecha_minima"];?>';
						parent.document.form1.<?php echo $campo_codigo;?>.focus();
						</script>
						<?php
					}else{
						$rs = pg_exec(" select tca_compra_oficial from int_tipo_cambio where tca_fecha=to_date('$codigo','dd/mm/yyyy') ");
						if(pg_numrows($rs)>0){
							$tasa = pg_result( $rs,0,0);
						}else{
							$tasa = 0;
						}
						print "<script>parent.document.form1.tasa_cambio.value='$tasa';</script>";		
					}
		
		
		break;

}

