<?php

class ContinuidadModel extends Model {

	function reporte($desde, $hasta) {
		global $sqlca;

		$reporte = Array();

		$fa = explode("/",$desde);
		$fechaini = "{$fa[2]}-{$fa[1]}-{$fa[0]}";
		$fa = explode("/",$hasta);
		$fechafin = "{$fa[2]}-{$fa[1]}-{$fa[0]}";

		$sql = "	SELECT
					ctc.ch_numeroparte,
					ctc.ch_codigocombustible,
					ctc.dt_fechaparte,
					ctc.ch_surtidor,
					ctc.ch_tanque,
					ctc.nu_contometroinicialgalon,
					ctc.nu_contometrofinalgalon,
					ctc.nu_contometroinicialvalor,
					ctc.nu_contometrofinalvalor,
					comb.ch_nombrecombustible,
					cts.ch_numerolado,
					cts.nu_manguera,
					cts.ch_usuario
				FROM
					comb_ta_contometros ctc
					JOIN comb_ta_combustibles comb USING (ch_codigocombustible)
					JOIN comb_ta_surtidores cts USING (ch_surtidor)
				WHERE
					ctc.dt_fechaparte BETWEEN '{$fechaini}' AND '{$fechafin}'
				ORDER BY
					ctc.ch_surtidor ASC,
					ctc.dt_fechaparte ASC,
					ctc.ch_numeroparte ASC;";

		if ($sqlca->query($sql)<0)
			return $sqlca->get_error();

		$last = $sqlca->fetchRow();

		$c = 0;
		for ($i=1;$i<$sqlca->numrows();$i++) {
			$curr = $sqlca->fetchRow();
			if ($last['ch_surtidor'] == $curr['ch_surtidor']) {
				if ($last['nu_contometrofinalgalon'] != $curr['nu_contometroinicialgalon']) {
					$reporte[$c]['e'] = 1;
					$reporte[$c]['a'] = $last;
					$reporte[$c]['s'] = $curr;
					$c++;
				}
				if ($last['nu_contometrofinalvalor'] != $curr['nu_contometroinicialvalor']) {
					$reporte[$c]['e'] = 2;
					$reporte[$c]['a'] = $last;
					$reporte[$c]['s'] = $curr;
					$c++;
				}
			}
			$last = $curr;
		}
		return $reporte;
  	}
}
