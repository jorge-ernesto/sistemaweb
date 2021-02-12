<?php

require('/sistemaweb/combustibles/reportes/m_descuentos_especiales.php');

if (!isset($_GET['keyword'])) {
	die();
}

$keyword = $_GET['keyword'];
$data = searchForKeyword($keyword);
echo json_encode($data);
