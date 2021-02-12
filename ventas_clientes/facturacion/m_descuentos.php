<?php
  // Modelo para Eliminacion de Cuentas por cobrar

Class DescuentosModel extends Model{
  
  //Otras funciones para consultar la DB

 function listadoSoloenEspera($criterio = array()){
		global $sqlca;
		$cond = '';
		$cond=" and int_clientes.cli_estado_desc=0 AND int_clientes.cli_tipo='AC' ";
		if ($criterio['codigo']!=''){
			$cond .= " and int_clientes.cli_codigo='".$criterio['codigo']."'";
		}
		$cond .= " order by CLI_CODIGO";
		$query = "SELECT 
				  int_clientes.cli_codigo,
				  int_clientes.cli_razsocial,
				  int_clientes.cli_descuento,
				  round((int_tabla_general.tab_num_01/100),4) AS porc_descuento,
				  int_clientes.cli_estado_desc
				  FROM
				  int_clientes, int_tabla_general where
				  int_clientes.cli_descuento=substring(int_tabla_general.tab_elemento for 2 from length(int_tabla_general.tab_elemento)-1)
				  and int_tabla_general.tab_tabla= 'DESC' AND int_tabla_general.tab_elemento<>'000000' ".$cond;
		if ($sqlca->query($query)<=0){
		    return $sqlca->get_error();
		}
    
		$listado[] = array();
		    
		while( $reg = $sqlca->fetchRow()){
		        $listado['datos'][] = $reg;
		}    
   
    	return $listado;
	}
	
function autorizarRegistros($valor){
		global $sqlca;
		$cond = '';
		$query=" update int_clientes set cli_estado_desc=1 
		where cli_codigo='".$valor."'";
		print_r($query);
		$sqlca->query($query);
		return true;
	}
function cambiarDescuento($codigo, $valor){
	global $sqlca;
		$cond = '';
		$query=" update int_clientes set cli_descuento='".trim($valor)."' 
		where cli_codigo='".trim($codigo)."'";
		print_r($query);
		$sqlca->query($query);
		return true;
}
function DescuentosCBArray($condicion=''){
    global $sqlca;
    $cbArray = array();
    $query = "SELECT ".
                    "substring(tab.tab_elemento for 2 from length(tab_elemento)-1 ) AS cod_descuento, ".
                    "tab.tab_descripcion AS des_descuento, ".
                    "round((tab_num_01/100),6) AS porc_descuento ".
                    "FROM  int_tabla_general tab ".
                    "WHERE tab_tabla= 'DESC' ".
                    "AND tab_elemento<>'000000' ".
    
    $query .= ($condicion!=''?' AND '.$condicion:'').' ORDER BY des_descuento DESC';
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0)
      return $cbArray;
    while($result = $sqlca->fetchRow()){
      $cbArray['Datos'][trim($result["cod_descuento"])] = $result["cod_descuento"].' ('.$result["des_descuento"].')';
      //$cbArray['Desc'][trim($result["cod_descuento"])] = $result["porc_descuento"];
    }
    ksort($cbArray);
    return $cbArray;
  }
}
