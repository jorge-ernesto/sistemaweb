<?php

header('Content-type: application/force-download');

// Se llamará downloaded.pdf
header('Content-Disposition: attachment; filename="PDF_documento_a_imprimir.pdf"');
 header("Content-Transfer-Encoding: binary");

// La fuente de PDF se encuentra en original.pdf
readfile('/sistemaweb/ventas_clientes/reportes/pdf/reporte_documento.pdf');

