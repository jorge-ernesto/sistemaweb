<?php
  // Modelo s

Class InterfaceMovModel extends Model{
  
  function ListadoAlmacenes($codigo)
  {
  //SELECT ch_almacen, ch_nombre_almacen 
  //FROM inv_ta_almacenes 
  //WHERE trim(ch_clase_almacen)='1' AND ch_sucursal='005'
    global $sqlca;
    $cond = '';
    if ($codigo != ""){
      $cond = "AND trim(ch_sucursal) = '".pg_escape_string($codigo)."' ";
    }
    $query = "SELECT ch_almacen ".
             "FROM inv_ta_almacenes ".
             "WHERE trim(ch_clase_almacen)='1' ".
             " ".$cond." ".
	     "ORDER BY ch_almacen";
    //echo "QUERY : $query \n";
    if ($sqlca->query($query)<=0){
      return $sqlca->get_error();
    }
    $numrows = $sqlca->numrows();
    //echo "NUMERO DE REG : $numrows \n";
    $x = 0;
    while( $reg = $sqlca->fetchRow())
    {
        if($numrows>1)
        {
            if($x < $numrows-1)
            {
                $conc = ".";
            }else{
                $conc = "";
            }
        }

        $listado[''.$codigo.''] .= $reg[0].$conc;
    $x++;
    }
    //print_r($listado);
    return $listado;
  }
  
  //Otras funciones para consultar la DB
  
  function ActualizarInterfaces($Fecha,$CodAlmacen,$CodModulo){
    global $sqlca;
    $FechaDiv = explode("/", $Fecha);
    $Fecha = $FechaDiv[2]."/".$FechaDiv[1]."/".$FechaDiv[0];
	
   $result = $sqlca->functionDB("interface_central_fn_movimientos_consulta('".$Fecha."','".$CodAlmacen."','".$CodModulo."')");
		

    return $result;
  }
  function ActualizarInterfaces_vales($Fechai,$Fechaf,$CodAlmacen,$CodModulo){
    global $sqlca;
    $FechaDiv = explode("/", $Fechai);
    $Fechai = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
	$FechaDiv = explode("/", $Fechaf);	
    $Fechaf = $FechaDiv[2]."-".$FechaDiv[1]."-".$FechaDiv[0];
	
    $result = $sqlca->functionDB("interface_central_fn_movimientos_consulta_vales('".$Fechai."','".$Fechaf."','".$CodAlmacen."','".$CodModulo."')");
    return $result;
  }

}
?>
