<?php

class InterfaceController extends Controller {
	function Init(){
		$this->visor = new Visor();
		isset($_REQUEST['action'])?$this->action = $_REQUEST['action']:$this->action="";
	}

	function Run() {
		$this->Init();
		$result = '';

		include('m_interface.php');
		include('t_interface.php');
		include('m_sisvarios.php');

		switch ($this->action) {
			case 'Procesar':

			$res = InterfaceModel::interface_fn_opensoft_copetrol($_REQUEST['desde'],$_REQUEST['hasta'],$_REQUEST['sucursal']);

			if (file_exists("/tmp/$res")){
				unlink("/tmp/$res");
			}

			$cmd = "zip -j -m /tmp/$res /home/data/*";							
							
			exec($cmd);

			header("Content-Type: application/x-zip-compressed");
			header('Content-Disposition: attachment; filename="' . $res . '"');
			readfile("/tmp/$res");
			break;
		}
	}

	function ObtenerDocumentoReferencia($anio, $mes, $numDocumeto, $numSerieDocumento, $tipoDocumento) {    
        global $sqlca;

        $fecha_postrans = $anio . "" . $mes;

        $sql1 = "
			SELECT
					SUBSTR(TRIM(venta_tickes.fe), 6)||'*'||SUBSTR(TRIM(venta_tickes.fe), 0, 5)||'*'||(CASE WHEN SUBSTR(TRIM(venta_tickes.fe), 1)='F' then '01' else '03' END) as ch_fac_observacion2,
					TO_CHAR(venta_tickes.diatickes::DATE,'DD/MM/YYYY') as ch_fac_observacion3
			FROM
				(SELECT 
					(p.trans||'-'||p.caja) as tickes_refe,
					p.trans,
					p.usr as fe,
					extorno.trans as trans_ext,
					extorno.registro,
					extorno.trans1,
					p.fecha,
					p.dia as diatickes
				FROM
					pos_trans$fecha_postrans p
					INNER JOIN (
						SELECT 
							(dia|| caja || td ||turno ||codigo ||tipo || pump || fpago ||  abs(cantidad) ||abs(precio)|| abs(igv) || abs(importe) ||ruc) as registro,
							fecha,
							trans||'-'||caja as trans,
							trans as trans1,
							usr
						FROM
							pos_trans$fecha_postrans
						WHERE
							tm = 'A'
							AND td IN ('B','F')
						) as extorno ON (p.dia|| p.caja || p.td ||p.turno ||p.codigo ||p.tipo || p.pump || p.fpago ||  abs(p.cantidad) ||abs(p.precio)|| abs(p.igv) || abs(p.importe) ||ruc) = extorno.registro
						AND td IN ('B','F')
						AND tm = 'V'
						AND p.trans < extorno.trans1
						AND SUBSTR(TRIM(extorno.usr), 6) = '$numDocumeto'
						AND SUBSTR(TRIM(extorno.usr), 0, 5) = '$numSerieDocumento'
				ORDER BY
					p.fecha asc
				) AS venta_tickes
			GROUP BY
				venta_tickes.registro,
				venta_tickes.trans_ext,
				venta_tickes.fe,
				venta_tickes.diatickes
			;
				
				";

	//echo $sql1;

        if ($sqlca->query($sql1) < 0) {
            return array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
        }

        $info_documento_referencia = $sqlca->fetchRow();

        if (isset($info_documento_referencia[0]) && isset($info_documento_referencia[1])) {
            $datos_integrado = explode("*", $info_documento_referencia[0]);
            if (count($datos_integrado) == 3) {
                $numDocumeto_Original = trim($datos_integrado[0]);
                $numSerieDocumento_Original = trim($datos_integrado[1]);
                $tipoDocumento_original = trim($datos_integrado[2]);
                if (strlen(trim($numSerieDocumento_Original)) < 4) {
                    $numSerieDocumento_Original = trim($numSerieDocumento_Original);
                    $numSerieDocumento_Original = str_pad($numSerieDocumento_Original, 4, "0", STR_PAD_LEFT);
                }

                $fecha_original = trim($info_documento_referencia[1]);

                if ($tipoDocumento_original == 12) {//quiere decir que la serie sera la de la maquina
                    if (strlen($numSerieDocumento_Original) == 10) {
                        $numSerieDocumento_Original = substr($numSerieDocumento_Original, 4, 6);
                    }
                }

                return array(
                    "fecha_emision_original" => $fecha_original,
                    "tipo_docu_original" => $tipoDocumento_original,
                    "num_serie_original" => $numSerieDocumento_Original,
                    "num_docu_original" => $numDocumeto_Original
                );
            } else {

                return array(
                    "fecha_emision_original" => "01/01/0001",
                    "tipo_docu_original" => "00",
                    "num_serie_original" => "-",
                    "num_docu_original" => "-"
                );
            }
        } else {

            return array(
                "fecha_emision_original" => "01/01/0001",
                "tipo_docu_original" => "00",
                "num_serie_original" => "-",
                "num_docu_original" => "-"
            );
        }
    }

}
