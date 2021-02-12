<?php
require("central.inc.php");

$migerr = NULL;

if (isset($_REQUEST['do']) && $_REQUEST['do'] == "export") {
	$sql = "BEGIN;";
	if ($sqlca->query($sql) < 0)
		$migerr = "Error interno (1)";
	else {
		if (exportProcess()) {
			$sql = "COMMIT;";
			$sqlca->query($sql);
		} else {
			if ($migerr == NULL)
				$migerr = "Error en la exportacion";
			$sql = "ROLLBACK;";
			$sqlca->query($sql);
		}
	}
}

$cterr = NULL;

$sql = "
SELECT
	to_char(max(x.created),'DD/MM/YYYY HH24:MI:SS'),
	to_char(max(x.systemdate),'DD/MM/YYYY'),
	to_char(max(x.systemdate + interval '1 day'),'DD/MM/YYYY')
FROM
	mig_export x;";

if ($sqlca->query($sql) <= 0)
	$cterr = "Error interno (1)";
else {
	$rR = $sqlca->fetchRow();
	$systemdate = $rR[1];
	$exportdate = $rR[0];
	$nextexport = $rR[2];
}

require("header.inc.php");

if (isset($_REQUEST['do']) && $_REQUEST['do'] == "export") {
?><table style="width:100%;">
<tr>
<td class="borderedcell <?php if ($migerr == NULL) echo "greencell"; else echo "highvaluecell"; ?>" style="text-align: center; font-weight: bold;"><?php if ($migerr == NULL) echo "Exportaci&oacute;n satisfactoria"; else echo htmlentities($migerr); ?></td>
</tr>
</table><?php
}

if ($cterr == NULL) {
?><table style="width:330px;">
<tr>
<td class="borderedcell" style="text-align: center; width: 50%; font-weight: bold;">&Uacute;ltimo d&iacute;a exportado</td>
<td class="borderedcell" style="text-align: center; width: 50%;"><?php echo $systemdate; ?></td>
</tr>
<tr>
<td class="borderedcell" style="text-align: center; font-weight: bold;">Fecha de exportaci&oacute;n</td>
<td class="borderedcell" style="text-align: center;"><?php echo $exportdate; ?></td>
</tr>
<tr>
<td class="borderedcell" style="text-align: center; font-weight: bold;">Siguiente exportaci&oacute;n</td>
<td class="borderedcell" style="text-align: center;"><?php echo $nextexport; ?></td>
</tr>
<tr>
<td class="borderedcell" style="text-align: center;" colspan="2"><a href="export_control.php?do=export">Exportar</a></td>
</tr>
</table><?php
}

require("footer.inc.php");
