<?php
Class ConsvalesModel extends Model{

  function ObtFacturas($parametros=array()){
    global $sqlca;
    
    $Ruc = $parametros['factura'];
    $CodLiq = $parametros['cod_liq'];
  
    if(!empty($Ruc) || !empty($CodLiq)){
      $query =
"SELECT DISTINCT ".
 "f.ch_liquidacion, ".
 "f.ch_fac_tipodocumento, ".
 "f.ch_fac_seriedocumento, ".
 "f.ch_fac_numerodocumento, ".
 "f.cli_codigo, ".
 "c.cli_razsocial, ".
 "c.cli_ruc ".
"FROM ".
 "fac_ta_factura_cabecera AS f ".
 "JOIN int_clientes AS c ".
  "USING(cli_codigo) ".
"WHERE ".
 "TRIM(f.ch_liquidacion)='".$CodLiq."'
      ";
	
      if ($sqlca->query($query)<=0){
        return $sqlca->get_error();
      }
      
      while( $reg = $sqlca->fetchRow()){
        $listado[] = $reg;
      }
      return $listado;
    }else{
      return FALSE;
    }
  }//Fin de la Función -> ObtFacturas
  
  function ObtVales($parametros){
    global $sqlca;
    
    $CobLiq = $parametros['ch_liquidacion'];
    if(!empty($CobLiq)){
      $query =
"SELECT ".
 "VCD.ch_liquidacion, ".
 "VC.nu_importe, ".
 "trim(VC.ch_sucursal||VC.dt_fecha||VC.ch_documento) AS codigo, ".
 "VC.ch_cliente ".
"FROM ".
 "val_ta_cabecera AS VC ".
 "JOIN val_ta_complemento_documento AS VCD ".
  "ON(VC.ch_sucursal=VCD.ch_sucursal AND VC.dt_fecha=VCD.dt_fecha AND VC.ch_documento=VCD.ch_numeval)".
"WHERE ".
 "VCD.ch_liquidacion='".$CobLiq."'
      ";

      echo "<pre>";
      print_r($query);
      echo "</pre>";

      if($sqlca->query($query)<=0){
        return $sqlca->get_error();
      }
    
      while($reg = $sqlca->fetchRow()){
        $listado[] = $reg;
      }
      return $listado;
    }else{
      return FALSE;
    }
  }//Fin de la Función -> ObtVales
  
  function ObtProductosConsumidos($parametros){
    global $sqlca;
    
    $CobLiq = $parametros['ch_liquidacion'];
    
    if(!empty($CobLiq)){
      $query = 
"SELECT ".
 "det.ch_articulo, ".
 "art.art_descripcion, ".
 "SUM(det.nu_cantidad) AS cantidad, ".
 "SUM(det.nu_importe) AS importe, ".
 "det.nu_precio_especial ".
"FROM ".
 "val_ta_detalle AS det, ".
 "int_articulos AS art, ".
 "(SELECT ".
  "VCD.ch_liquidacion, ".
  "VC.nu_importe, ".
  "trim(VC.ch_sucursal||VC.dt_fecha||VC.ch_documento) AS codigo, ".
  "VC.ch_cliente ".
 "FROM ".
  "val_ta_cabecera AS VC ".
  "JOIN val_ta_complemento_documento AS VCD ".
   "ON(VC.ch_sucursal=VCD.ch_sucursal AND VC.dt_fecha=VCD.dt_fecha AND VC.ch_documento=VCD.ch_numeval) ".
 "WHERE ".
  "VCD.ch_liquidacion='".$CobLiq."') AS cc ".
"WHERE ".
 "det.ch_articulo=art.art_codigo ".
 "AND trim(det.ch_sucursal||det.dt_fecha||det.ch_documento)=cc.codigo ".
"GROUP BY ".
 "det.ch_articulo, ".
 "art.art_descripcion, ".
 "nu_precio_especial
      ";

      echo "<pre>";
      print_r($query);
      echo "</pre>";

      if($sqlca->query($query)<=0){
        return $sqlca->get_error();
      }

      while($reg = $sqlca->fetchRow()){
        $listado[]=$reg;
      }
      return $listado;
    }else{
      return FALSE;
    }
  }
  
  function GeneraDatosReportePDF($Parametros){
    $ResultFactura = ConsValesModel::ObtFacturas($Parametros);

    $ResultConsumos['FACTURA'] = trim($ResultFactura[0]['ch_fac_seriedocumento'])."-".$ResultFactura[0]['ch_fac_numerodocumento'];
    $ResultConsumos['DATOS ARTICULOS'] = ConsValesModel::ObtProductosConsumidos($ResultFactura[0]);

    echo "<pre>";
    print_r($ResultConsumos);
    echo "</pre>";
    return $ResultConsumos;
  }
}

