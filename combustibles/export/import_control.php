<?php
require("central.inc.php");

$migerr = NULL;

if (isset($_REQUEST['do']) && $_REQUEST['do'] == "migrate") {
	if (!isset($_REQUEST['remote']) || !is_numeric($_REQUEST['remote']))
		$migerr = "Nodo remoto no v&aacute;lido";
	else {
		$MrI = $_REQUEST['remote'];
		$sql = "BEGIN;";
		if ($sqlca->query($sql) < 0)
			$migerr = "Error interno (1)";
		else {
			$sql = "
SELECT
	first(r.ip),
	to_char(max(p.systemdate),'YYYYMMDD'),
	to_char(max(p.systemdate) + interval '1 day','YYYYMMDD'),
	to_char(max(p.systemdate) + interval '1 day','YYYY-MM-DD')
FROM
	mig_process p
	JOIN mig_remote r USING (mig_remote_id)
WHERE
	r.mig_remote_id = {$MrI};";
			if ($sqlca->query($sql) <= 0)
				$migerr = "Nodo remoto desconocido";
			else {
				$rR = $sqlca->fetchRow();

				if (importProcess("http://{$rR[0]}/sistemaweb/centralizer.php",$rR[2])) {
					$sql = "INSERT INTO mig_process (mig_remote_id,created,systemdate) VALUES ({$MrI},now(),'{$rR[3]}');";
					$sqlca->query($sql);

					$sql = "COMMIT;";
					$sqlca->query($sql);
				} else {
					if ($migerr == NULL)
						$migerr = "Error en la migracion";
					$sql = "ROLLBACK;";
					$sqlca->query($sql);
				}
			}
		}
	}
}

$cterr = NULL;
$crlist = Array();

$sql = "
SELECT
	first(r.mig_remote_id),
	first(r.name),
	max(p.mig_process_id),
	to_char(max(p.created),'DD/MM/YYYY HH24:MI:SS'),
	to_char(max(p.systemdate),'DD/MM/YYYY')
FROM
	mig_process p
	JOIN mig_remote r USING (mig_remote_id)
GROUP BY
	p.mig_remote_id
ORDER BY
	p.mig_remote_id;";

if ($sqlca->query($sql) <= 0)
	$cterr = "Error interno (1)";
else {
	for ($i = 0;$i < $sqlca->numrows();$i++) {
		$rR = $sqlca->fetchRow();
		$crlist[] = Array($rR[0],$rR[1],$rR[2],$rR[3],$rR[4]);
	}
}

require("header.inc.php");

if (isset($_REQUEST['do']) && $_REQUEST['do'] == "migrate") {
?><table style="width:100%;">
<tr>
<td class="borderedcell <?php if ($migerr == NULL) echo "greencell"; else echo "highvaluecell"; ?>" style="text-align: center; font-weight: bold;"><?php if ($migerr == NULL) echo "Migraci&oacute;n satisfactoria"; else echo htmlentities($migerr); ?></td>
</tr>
</table><?php
}

if ($cterr == NULL) {
?><table style="width:100%;">
<tr>
<td class="borderedcell" style="text-align: center; font-weight: bold; width: 10%;">ID</td>
<td class="borderedcell" style="text-align: center; font-weight: bold; width: 30%;">Estaci&oacute;n</td>
<td class="borderedcell" style="text-align: center; font-weight: bold; width: 30%;">&Uacute;ltima migraci&oacute;n</td>
<td class="borderedcell" style="text-align: center; font-weight: bold; width: 20%;">&Uacute;ltimo proceso</td>
<td class="borderedcell" style="text-align: center; font-weight: bold; width: 10%;">Acci&oacute;n</td>
</tr><?php
	foreach ($crlist as $rV) {
		echo "<tr>";
		echo "<td class=\"borderedcell\">{$rV[0]}</td>";
		echo "<td class=\"borderedcell\">" . htmlentities($rV[1]) . "</td>";
		echo "<td class=\"borderedcell\">{$rV[4]}</td>";
		echo "<td class=\"borderedcell\">{$rV[2]} - {$rV[3]}</td>";
		echo "<td class=\"borderedcell\"><a href=\"import_control.php?do=migrate&remote={$rV[0]}\">Migrar</a></td>";
		echo "</tr>\n";
	}
?></table><?php
}

require("footer.inc.php");
