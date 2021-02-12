<?php
  // Modelo para Tarjetas Magneticas
include("store_procedures.php");

Class ConsistenciaValesModel extends Model
{
 //$rs = REPORTE_VALES_X_CENTRO_COSTO($c_est, $c_fec_desde,$c_fec_hasta);
 	function ModelReportePDF($filtro=array())
	{
	 	print_r($filtro);
	}
}
