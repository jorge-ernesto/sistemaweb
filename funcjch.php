<?php
	function lee_cont_turno($fecha,$turno)
	{
		$v_sql="select * from pos_contometros where dia='".$funcion->date_format($fecha,'YYYY-MM-DD')."' and turno='".$turno."' ";
		$v_xsql=pg_exec($conector_id,$v_sql);
		return $v_xsql;
	}
