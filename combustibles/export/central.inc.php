<?php
require_once("dbsqlca.php");

$db_host = "localhost";
$db_name = "opensoft";
$db_user = "postgres";
$db_password = "postgres";
$sqlca = new pgsqlDB($db_host,$db_user,$db_password,$db_name);

$COWMap = Array();

/*
 * Consideraciones para el proceso de migracion:
 *
 *  - El Business Partner Generico debe tener TaxID 00000000000
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */

function exportProcess() {
}

function getCentralizedData($url) {
	$fh = fopen($url,"rb");
	if ($fh===FALSE)
		return FALSE;

	$res = '';
	while (!feof($fh)) {
		$res .= fread($fh, 8192);
	}

	fclose($fh);
	return explode("\n",$res);
}

function insertCentralizedArray($TableName,$FieldList,$RawRows) {
	global $sqlca;

	$dbfl = implode(",",$FieldList);

	foreach ($RawRows as $rr) {
		$ffvl = explode("|",$rr);
		$sql = "INSERT INTO {$TableName} ({$FieldList}) VALUES (";
		for ($i = 1;$i <= count($dbfl);$i++)
			$sql .= (($i > 1) ? "," : "") . "\${$i}";
		$sql .= ");";
		if ($sqlca->query_params($sql,Array($ffvl)) < 0)
			return FALSE;
	}

	return TRUE;
}

function importProcess($cturl,$dt) {
	global $sqlca,$COWMap;

	$sql = "
SELECT
	m.ch_almacen,
	m.c_client_id,
	m.c_org_id,
	m.i_warehouse_id
FROM
	mig_cowmap m;";

	if ($sqlca->query($sql)<0) {
		$migerr = "Cannot initialize COWMap";
		return FALSE;
	}

	for ($i = 0;$i < $sqlca->numrows();$i++) {
		$rR = $sqlca->fetchRow();
		$COWMap[$rR[0]] = Array($rR[1],$rR[2],$rR[3]);
	}

	$ctdata = getCentralizedData($cturl . "?mod=IH&from={$dt}&to={$dt}");
	if (!is_array($ctdata) || importIH($ctdata,$cturl) == FALSE)
		return FALSE;

	$ctdata = getCentralizedData($cturl . "?mod=ID&from={$dt}&to={$dt}");
	if (!is_array($ctdata) || importID($ctdata,$cturl) == FALSE)
		return FALSE;

	$ctdata = getCentralizedData($cturl . "?mod=IT&from={$dt}&to={$dt}");
	if (!is_array($ctdata) || importIT($ctdata,$cturl) == FALSE)
		return FALSE;

	$ctdata = getCentralizedData($cturl . "?mod=MH&from={$dt}&to={$dt}");
	if (!is_array($ctdata) || importMH($ctdata,$cturl) == FALSE)
		return FALSE;

	$ctdata = getCentralizedData($cturl . "?mod=MD&from={$dt}&to={$dt}");
	if (!is_array($ctdata) || importMD($ctdata,$cturl) == FALSE)
		return FALSE;

	return TRUE;
}

function importBP($bpval,$cturl,$c_client_id) {
	global $sqlca,$COWMap,$migerr;

	$ctdata = getCentralizedData($cturl . "?mod=BI&sk={$bpval}");
	if (!is_array($ctdata)) {
		$migerr = "Cannot get centralized data for Business Partner '{$bpval}'";
		return FALSE;
	}

	if (count($ctdata) == 0) {
		$migerr = "Centralized data is not valid for Business Partner '{$bpval}'";
		return FALSE;
	}

	$cr = explode("|",$ctdata[0]);

	$sql = "
INSERT INTO
	C_BPartner
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	C_Client_ID,
	Name,
	Description,
	TaxID,
	Value,
	IsWorker
) VALUES (
	now(),
	0,
	now(),
	0,
	1,
	{$c_client_id},
	'" . addslashes($cr[1]) . "',
	'',
	'{$cr[0]}',
	'{$cr[0]}',
	0
);";
	if ($sqlca->query($sql) < 0) {
		$migerr = "Database error creating Business Partner '{$cr[0][0]}'";
		return FALSE;
	}

	$sqlca->query("SELECT lastval();");
	$lr = $sqlca->fetchRow();

	return $lr[0];
}

function importProduct($pval,$cturl,$c_client_id) {
	global $sqlca,$COWMap,$migerr;

	$ctdata = getCentralizedData($cturl . "?mod=PI&sk={$pval}");
	if (!is_array($ctdata)) {
		$migerr = "Cannot get centralized data for Product '{$pval}'";
		return FALSE;
	}

	if (count($ctdata) == 0) {
		$migerr = "Centralized data is not valid for Product '{$pval}'";
		return FALSE;
	}

	$cr = explode("|",$ctdata[0]);

	$sql = "
SELECT
	C_TaxGroup_ID
FROM
	C_TaxGroup
WHERE
	C_Client_ID = {$c_client_id};";
	if ($sqlca->query($sql) <= 0) {
		$migerr = "Cannot get Tax Group for new Product '{$pval}'";
		return FALSE;
	}

	$bpr = $sqlca->fetchRow();
	$c_taxgroup_id = $bpr[0];

	$sql = "
SELECT
	f.C_ProductFamily_ID
FROM
	C_ProductFamily f
	JOIN C_ProductType t USING (C_ProductType_ID)
WHERE
	t.C_Client_ID = {$c_client_id};";
	if ($sqlca->query($sql) <= 0) {
		$migerr = "Cannot get Product Family for new Product '{$pval}'";
		return FALSE;
	}

	$bpr = $sqlca->fetchRow();
	$c_productfamily_id = $bpr[0];

	$sql = "
SELECT
	C_ProductUOM_ID
FROM
	C_ProductUOM
WHERE
	C_Client_ID = {$c_client_id};";
	if ($sqlca->query($sql) <= 0) {
		$migerr = "Cannot get Product UOM for new Product '{$pval}'";
		return FALSE;
	}

	$bpr = $sqlca->fetchRow();
	$c_productuom_id = $bpr[0];

	$sql = "
SELECT
	C_ProductGroup_ID
FROM
	C_ProductGroup
WHERE
	C_Client_ID = {$c_client_id};";
	if ($sqlca->query($sql) <= 0) {
		$migerr = "Cannot get Product Group for new Product '{$pval}'";
		return FALSE;
	}

	$bpr = $sqlca->fetchRow();
	$c_productgroup_id = $bpr[0];

	$sql = "
INSERT INTO
	C_Product
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	C_ProductFamily_ID,
	C_ProductUOM_ID,
	C_ProductGroup_ID,
	C_TaxGroup_ID,
	Name,
	IsComposite,
	IsSellable,
	IsInventory,
	Value
) VALUES (
	now(),
	0,
	now(),
	0,
	1,
	{$c_productfamily_id},
	{$c_productuom_id},
	{$c_productgroup_id},
	{$c_taxgroup_id},
	'" . addslashes($cr[1]) . "',
	0,
	1,
	1,
	'{$cr[0]}'
);";
	if ($sqlca->query($sql) < 0) {
		$migerr = "Database error creating Product '{$cr[0][0]}'";
		return FALSE;
	}

	$sqlca->query("SELECT lastval();");
	$lr = $sqlca->fetchRow();

	return $lr[0];
}

function importIH($ctdata,$cturl) {
	global $sqlca,$COWMap,$migerr;

	foreach ($ctdata as $crv) {
		$cr = explode("|",$crv);
		if (count($cr) <= 1)
			break;

		if (!isset($COWMap[$cr[1]])) {
		//	echo "Row with invalid COWMap: $crv\n";
			continue;
		}

		$c_client_id = $COWMap[$cr[1]][0];
		$c_org_id = $COWMap[$cr[1]][1];
		$i_warehouse_id = $COWMap[$cr[1]][2];

		if ($cr[8] == "GENERIC" || substr($cr[8],0,4) == "9999")
			$cr[8] = "00000000000";
		$sql = "
SELECT
	C_BPartner_ID
FROM
	C_BPartner
WHERE
	C_Client_ID = {$c_client_id}
	AND TaxID = '{$cr[8]}';";
		if ($sqlca->query($sql) <= 0) {
			$c_bpartner_id = importBP($cr[8],$cturl,$c_client_id);
			if ($c_bpartner_id === FALSE) {
				if ($migerr == NULL)
					$migerr = "Cannot create non-existant Business Partner '{$cr[9]}'";
				return FALSE;
			}
		} else {
			$bpr = $sqlca->fetchRow();
			$c_bpartner_id = $bpr[0];
		}

		$sql = "
SELECT
	C_TenderType_ID
FROM
	C_TenderType
WHERE
	C_Client_ID = {$c_client_id}
	AND IsCredit = " . (($cr[9]=="0") ? 0 : 1) . ";";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Invalid Tender Type '{$cr[9]}'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_tendertype_id = $bpr[0];

		if ($cr[6] == "B")
			$cr[6] = "35";
		else if ($cr[6] == "F")
			$cr[6] = "10";
		$sql = "
SELECT
	C_DocType_ID
FROM
	C_DocType
WHERE
	Value = '{$cr[6]}';";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Invalid Document Type '{$cr[6]}'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_doctype_id = $bpr[0];

		$sql = "
INSERT INTO
	C_InvoiceHeader
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	C_Org_ID,
	C_BPartner_ID,
	C_Currency_ID,
	IsSale,
	C_TenderType_ID,
	Status,
	DocumentNo,
	DocumentSerial,
	C_DocType_ID
) VALUES (
	'{$cr[3]}',
	0,
	'{$cr[3]}',
	0,
	1,
	{$c_client_id},
	{$c_bpartner_id},
	1,
	{$cr[7]},
	{$c_tendertype_id},
	2,
	'" . addslashes($cr[4]) . "',
	'" . addslashes($cr[5]) . "',
	{$c_doctype_id}
);";

		if ($sqlca->query($sql) < 0) {
			$migerr = "Database error inserting document '" . $cr[5] . "-" . $cr[4] . "'";
			return FALSE;
		}
	}

	return TRUE;
}

function importID($ctdata,$cturl) {
	global $sqlca,$COWMap,$migerr;

	foreach ($ctdata as $crv) {
		$cr = explode("|",$crv);
		if (count($cr) <= 1)
			break;

		if (!isset($COWMap[$cr[1]])) {
		//	echo "Row with invalid COWMap: $crv\n";
			continue;
		}

		$c_client_id = $COWMap[$cr[1]][0];
		$c_org_id = $COWMap[$cr[1]][1];
		$i_warehouse_id = $COWMap[$cr[1]][2];

		$sql = "
SELECT
	h.C_InvoiceHeader_ID
FROM
	C_InvoiceHeader h
	JOIN C_Org o USING (C_Org_ID)
WHERE
	o.C_Client_ID = {$c_client_id}
	AND h.DocumentNo = '" . addslashes($cr[2]) . "'
	AND h.DocumentSerial = '" . addslashes($cr[3]) . "';";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Cannot get IHID for ID '" . $cr[3] . "-" . $cr[2] . "'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_invoiceheader_id = $bpr[0];

		$sql = "
SELECT
	p.C_Product_ID
FROM
	C_Product p
	JOIN C_TaxGroup t USING (C_TaxGroup_ID)
WHERE
	t.C_Client_ID = {$c_client_id}
	AND p.Value = '{$cr[5]}';";
		if ($sqlca->query($sql) <= 0) {
			$c_product_id = importProduct($cr[5],$cturl,$c_client_id);
			if ($c_product_id === FALSE) {
				if ($migerr == NULL)
					$migerr = "Cannot create non-existant Product '{$cr[5]}'";
				return FALSE;
			}
		} else {
			$bpr = $sqlca->fetchRow();
			$c_product_id = $bpr[0];
		}

		$sql = "
INSERT INTO
	C_InvoiceDetail
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	C_InvoiceHeader_ID,
	C_Product_ID,
	UnitPrice,
	LineTotal,
	Quantity
) VALUES (
	now(),
	0,
	now(),
	0,
	1,
	{$c_invoiceheader_id},
	{$c_product_id},
	{$cr[6]},
	{$cr[8]},
	{$cr[7]}
);";

		if ($sqlca->query($sql) < 0)
			return FALSE;
	}

	return TRUE;
}

function importIT($ctdata) {
	global $sqlca,$COWMap,$migerr;

	foreach ($ctdata as $crv) {
		$cr = explode("|",$crv);
		if (count($cr) <= 1)
			break;

		if (!isset($COWMap[$cr[1]])) {
		//	echo "Row with invalid COWMap: $crv\n";
			continue;
		}

		$c_client_id = $COWMap[$cr[1]][0];
		$c_org_id = $COWMap[$cr[1]][1];
		$i_warehouse_id = $COWMap[$cr[1]][2];

		$sql = "
SELECT
	h.C_InvoiceHeader_ID
FROM
	C_InvoiceHeader h
	JOIN C_Org o USING (C_Org_ID)
WHERE
	o.C_Client_ID = {$c_client_id}
	AND DocumentNo = '" . addslashes($cr[2]) . "'
	AND DocumentSerial = '" . addslashes($cr[3]) . "';";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Cannot get IHID for IT '" . $cr[3] . "-" . $cr[2] . "'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_invoiceheader_id = $bpr[0];

		$sql = "
SELECT
	C_Tax_ID
FROM
	C_Tax
WHERE
	C_Client_ID = {$c_client_id};";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Cannot get tax for client $c_client_id";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_tax_id = $bpr[0];

		$sql = "
INSERT INTO
	C_InvoiceTax
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	C_InvoiceHeader_ID,
	C_Tax_ID,
	BaseAmount,
	TaxAmount
) VALUES (
	now(),
	0,
	now(),
	0,
	1,
	{$c_invoiceheader_id},
	{$c_tax_id},
	{$cr[6]},
	{$cr[7]}
);";

		if ($sqlca->query($sql) < 0)
			return FALSE;
	}

	return TRUE;
}

function importMH($ctdata,$cturl) {
	global $sqlca,$COWMap,$migerr;

	foreach ($ctdata as $crv) {
		$cr = explode("|",$crv);
		if (count($cr) <= 1)
			break;

		if (!isset($COWMap[$cr[1]])) {
//			echo "Row with invalid COWMap: $crv\n";
			continue;
		}

		$c_client_id = $COWMap[$cr[1]][0];
		$c_org_id = $COWMap[$cr[1]][1];
		$i_warehouse_id = $COWMap[$cr[1]][2];

		if ($cr[4] == "GENERIC" || substr($cr[4],0,4) == "9999")
			$cr[8] = "00000000000";
		$sql = "
SELECT
	C_BPartner_ID
FROM
	C_BPartner
WHERE
	C_Client_ID = {$c_client_id}
	AND TaxID = '{$cr[4]}';";
		if ($sqlca->query($sql) <= 0) {
			$c_bpartner_id = importBP($cr[4],$cturl,$c_client_id);
			if ($c_bpartner_id === FALSE) {
				if ($migerr == NULL)
					$migerr = "Cannot create non-existant Business Partner '{$cr[4]}'";
				return FALSE;
			}
		} else {
			$bpr = $sqlca->fetchRow();
			$c_bpartner_id = $bpr[0];
		}

		$sql = "
SELECT
	C_DocType_ID
FROM
	C_DocType
WHERE
	Value = '{$cr[9]}';";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Invalid Document Type '{$cr[9]}'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$c_doctype_id = $bpr[0];

		$sql = "
SELECT
	w.I_Warehouse_ID
FROM
	I_Warehouse w
	JOIN C_Org o USING (C_Org_ID)
WHERE
	o.C_Client_ID = {$c_client_id}
	AND w.Description = '{$cr[3]};'";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Invalid Destination Warehouse '{$cr[3]}'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$destination_i_warehouse_id = $bpr[0];

		$sql = "
SELECT
	w.I_Warehouse_ID
FROM
	I_Warehouse w
	JOIN C_Org o USING (C_Org_ID)
WHERE
	o.C_Client_ID = {$c_client_id}
	AND w.Description = '{$cr[2]};'";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Invalid Source Warehouse '{$cr[2]}'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$source_i_warehouse_id = $bpr[0];

		$sql = "
INSERT INTO
	I_MovementHeader
(
	Created,
	CreatedBy,
	Updated,
	UpdatedBy,
	IsActive,
	Source_I_Warehouse_ID,
	Destination_I_Warehouse_ID,
	C_BPartner_ID,
	C_DocType_ID,
	DocumentSerial,
	DocumentNo,
	C_InvoiceHeader_ID,
	Status
) VALUES (
	'{$cr[8]}',
	0,
	'{$cr[8]}',
	0,
	1,
	{$source_i_warehouse_id},
	{$destination_i_warehouse_id},
	{$c_bpartner_id},
	{$c_doctype_id},
	'" . addslashes($cr[10]) . "',
	'" . addslashes($cr[11]) . "',
	NULL,
	1
);";

		if ($sqlca->query($sql) < 0)
			return FALSE;
	}

	return TRUE;
}

function importMD($ctdata,$cturl) {
	global $sqlca,$COWMap;

	global $sqlca,$COWMap,$migerr;

	foreach ($ctdata as $crv) {
		$cr = explode("|",$crv);
		if (count($cr) <= 1)
			break;

		if (!isset($COWMap[$cr[1]])) {
//			echo "Row with invalid COWMap: $crv\n";
			continue;
		}

		$c_client_id = $COWMap[$cr[1]][0];
		$c_org_id = $COWMap[$cr[1]][1];
		$i_warehouse_id = $COWMap[$cr[1]][2];

		$sql = "
SELECT
	h.I_MovementHeader_ID
FROM
	I_MovementHeader h
	JOIN C_BPartner p USING (C_BPartner_ID)
WHERE
	p.C_Client_ID = {$c_client_id}
	AND h.DocumentNo = '" . addslashes($cr[5]) . "'
	AND h.DocumentSerial = '" . addslashes($cr[7]) . "';";
		if ($sqlca->query($sql) <= 0) {
			$migerr = "Cannot get MHID for ID '" . $cr[7] . "-" . $cr[5] . "'";
			return FALSE;
		}

		$bpr = $sqlca->fetchRow();
		$i_movementheader_id = $bpr[0];

		$sql = "
SELECT
	p.C_Product_ID
FROM
	C_Product p
	JOIN C_TaxGroup t USING (C_TaxGroup_ID)
WHERE
	t.C_Client_ID = {$c_client_id}
	AND p.Value = '{$cr[8]}';";
		if ($sqlca->query($sql) <= 0) {
			$c_product_id = importProduct($cr[8],$cturl,$c_client_id);
			if ($c_product_id === FALSE) {
				if ($migerr == NULL)
					$migerr = "Cannot create non-existant Product '{$cr[8]}'";
				return FALSE;
			}
		} else {
			$bpr = $sqlca->fetchRow();
			$c_product_id = $bpr[0];
		}

		$sql = "
INSERT INTO
	I_MovementDetail
(
	Created
	CreatedBy
	Updated
	UpdatedBy
	IsActive
	I_MovementHeader_ID
	C_Product_ID
	Quantity
	UnitPrice
	LineTotal
) VALUES (
	now(),
	0,
	now(),
	0,
	1,
	{$i_movementheader_id},
	{$c_product_id},
	{$cr[10]},
	{$cr[9]},
	{$cr[11]}
);";

		if ($sqlca->query($sql) < 0)
			return FALSE;
	}

	return TRUE;
}

