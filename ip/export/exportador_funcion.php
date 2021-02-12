<?php

$org_cade = array("1" => "Grifo Molina", "2" => "Market Molina", "3" => "Grifo Ica", "4" => "Market Barranco", "5" => "GASOCENTROS DEL PERU S.A.C.",
    "6" => "Grifo Carabayllo", "6" => "Grifo Carabayllo", "7" => "Market Venezuela", "8" => "Grifo Venezuela",
    "9" => "Market Marina", "10" => "Grifo Marina", "11" => "Grifo Argentina", "12" => "Grifo Victoria",
    "13" => "Grifo Chicayo", "14" => "Grifo Ica2"
);
$areglo_global_irium = array();

function exportProcessPrecio() {
    global $sqlca, $resultado_exe, $array_c_org, $mssql, $estadoActu, $areglo_global_irium;
    $resultado_exe = array();

    try {


        mssql_query("begin transaction", $mssql);
        $fecha_bus = trim($ddesde);

        $areglo_global_irium = llenar_areglo_todo($mssql);



        Insertar_Producto($mssql);

        mssql_query("commit transaction", $mssql);

        echo "<strong>Detalle de la exportacion</strong><br/>";
        if (count($resultado_exe) > 0) {
            echo "<ul>";
            foreach ($resultado_exe as $value) {
                echo "<li>$value</li>";
            }
            echo "</ul>";
        } else {
            echo "<ul>No hay niguna notificacion</ul>";
        }
        if ($estadoActu == TRUE) {
            echo "<br/><strong>Estado de la exportacion :<span style='color:blue'>Exitosa<span></strong><br/>";
        } else {
            echo "<br/><strong>Estado de la exportacion :<span style='color:#F30100'>No se encontraron ningun registro en el recorrido de todas las tablas con criterio de busqueda de  fecha($fecha_bus)<span></strong><br/>";
        }



    } catch (Exception $e) {
        mssql_query("rollback transaction", $mssql);
        echo "<strong>" . $e->getMessage() . "  Linea :  " . $e->getLine() . "</strong>";
        echo "<br/><strong>Estado de la exportacion :<span style='color:red'>Fallida<span></strong><br/>";
        throw $e;
    }
}

function exportProcess($fech) {
    global $sqlca, $resultado_exe, $array_c_org, $mssql, $estadoActu, $areglo_global_irium;
    $resultado_exe = array();
    $ddesde = substr($fech, 6, 4) . '-' . substr($fech, 3, 2) . '-' . substr($fech, 0, 2);
    $diadesde = substr($fech, 6, 4) . '/' . substr($fech, 3, 2) . '/' . substr($fech, 0, 2);
    $cond2 = substr($fech, 0, 2) + 1;
    $diadesde2 = substr($fech, 6, 4) . "/" . substr($fech, 3, 2) . "/" . $cond2;
    try {


        mssql_query("begin transaction", $mssql);
        $fecha_bus = trim($ddesde);

        $areglo_global_irium = llenar_areglo_todo($mssql);



        if (export_Ventas($mssql, $fecha_bus, $fecha_bus)) {
            export_D_Ventas($mssql, $fecha_bus, $fecha_bus);
        }


        if (export_Compras($mssql, $fecha_bus, $fecha_bus)) {
            export_D_Compras($mssql, $fecha_bus, $fecha_bus);
        }
        export_Varillaje($mssql, $fecha_bus, $fecha_bus);
        export_Vales($mssql, $fecha_bus, $fecha_bus);


        export_ZZ_producto($mssql, $fecha_bus, $fecha_bus);
        export_ZZ_Factura($mssql, $fecha_bus, $fecha_bus);
        export_ZZ($mssql, $fecha_bus, $fecha_bus);

        export_ZZ_Parte_Diario($mssql, $fecha_bus, $fecha_bus);
       // Insertar_Producto($mssql);

        mssql_query("commit transaction", $mssql);

        echo "<strong>Detalle de la exportacion</strong><br/>";
        if (count($resultado_exe) > 0) {
            echo "<ul>";
            foreach ($resultado_exe as $value) {
                echo "<li>$value</li>";
            }
            echo "</ul>";
        } else {
            echo "<ul>No hay niguna notificacion</ul>";
        }
        if ($estadoActu == TRUE) {
            echo "<br/><strong>Estado de la exportacion :<span style='color:blue'>Exitosa<span></strong><br/>";
        } else {
            echo "<br/><strong>Estado de la exportacion :<span style='color:#F30100'>No se encontraron ningun registro en el recorrido de todas las tablas con criterio de busqueda de  fecha($fecha_bus)<span></strong><br/>";
        }





        /* if (!actualiza_fecha($ddesde)) {
          echo "<br/><strong>Estado de actualizacion :<span style='color:red'>Fallida<span></strong>";
          } else {
          echo "<br/><strong>Estado de actualizacion :<span style='color:blue'>Exitosa<span></strong>";
          } */
    } catch (Exception $e) {
        mssql_query("rollback transaction", $mssql);
        echo "<strong>" . $e->getMessage() . "  Linea :  " . $e->getLine() . "</strong>";
        echo "<br/><strong>Estado de la exportacion :<span style='color:red'>Fallida<span></strong><br/>";
        throw $e;
    }
}

function export_Ventas(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {
        echo "--" . $c_org_id;
        $sql = "SELECT
			h.C_InvoiceHeader_ID,
			h.c_org_id,
			h.created,
			h.c_bpartner_id,
			h.documentserial,
			h.c_doctype_id,
			t.baseamount neto,
			0 descuento,
			t.baseamount subtotal,
			t.taxamount impuesto,
			SUM(d.LineTotal) linetotal
		FROM
			C_InvoiceHeader h
			INNER JOIN C_InvoiceDetail d ON (h.C_InvoiceHeader_ID = d.C_InvoiceHeader_ID) 
			INNER JOIN C_InvoiceTax t ON (h.C_InvoiceHeader_ID = t.C_InvoiceHeader_ID)
		WHERE
			 h.IsSale=1 AND
			 h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                         AND h.c_org_id={$c_org_id}
		GROUP BY
			h.C_InvoiceHeader_ID,
			h.c_org_id,
			h.created,
			h.c_bpartner_id,
			h.documentserial,
			h.c_doctype_id,
			subtotal,
			impuesto";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_VENTAS: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $fecha_ins = substr($row[2], 0, 19);
            $datos[$i][0] = $row[0];
            $datos[$i][1] = $row[1];
            $datos[$i][2] = $fecha_ins;
            $datos[$i][3] = $row[3];
            $datos[$i][4] = $row[4];
            $datos[$i][5] = $row[5];
            $datos[$i][6] = $row[6];
            $datos[$i][7] = $row[7];
            $datos[$i][8] = $row[8];
            $datos[$i][9] = $row[9];
            $datos[$i][10] = $row[10];
            $i++;
        }

        for ($i = 0; $i < count($datos); $i++) {
            $c_bpartner_id = to_taxid($datos[$i][3]);
            $c_org_id_irium = aux_almacen($mssql, $datos[$i][1]); // codigo de irisium
            $sql = "INSERT INTO
                            aux_energigas_e_ventas(
                                                    op_venta,
                                                    op_local,
                                                    fecha,
                                                    codigo,
                                                    serie,
                                                    numero,
                                                    documento,
                                                    neto,
                                                    descuento,
                                                    subtotal,
                                                    impuesto,
                                                    total
					) VALUES (
							{$datos[$i][0]},
							{$c_org_id_irium},
							'" . $datos[$i][2] . "',
							'" . $c_bpartner_id . "',
							'" . $datos[$i][4] . "',
							{$datos[$i][5]},
							{$datos[$i][6]},
							{$datos[$i][8]},
							0,
							{$datos[$i][8]},
							{$datos[$i][9]},
							{$datos[$i][10]}
					);";

            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_e_ventas, se Termino el proceso de exportacion <BR/>$sql");
            }
            Insertar_Client($mssql, $c_org_id_irium, $c_bpartner_id, $datos[$i][3]); ///$datos[$i][3]
            $estadoActu = TRUE;
        }
    }
    return true;
}

function export_D_Ventas(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {

        $sql = "
        SELECT 
                h.c_invoiceheader_id,
                d.c_product_id,
                d.unitprice,
                d.quantity,
                d.linetotal 
        FROM 
                C_InvoiceHeader h
                INNER JOIN C_InvoiceDetail d ON (h.C_InvoiceHeader_ID = d.C_InvoiceHeader_ID) 
        WHERE
                h.IsSale=1 AND
                h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                AND h.c_org_id={$c_org_id}
	";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_D_VENTAS: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $datos[$i][0] = $row [0];
            $datos[$i][1] = $row [1];
            $datos[$i][2] = $row [2];
            $datos[$i][3] = $row [3];
            $datos[$i][4] = $row [4];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {
            $codigo_producto = to_producto($datos[$i][1]);
            $sql = "INSERT INTO
                                aux_energigas_d_ventas(
                                                op_venta,
                                                producto,
                                                cantidad,
                                                precio,
                                                dcto,
                                                importe
					)VALUES(
							" . $datos[$i][0] . ",
							'" . $codigo_producto . "',
							" . $datos[$i][3] . ",
							" . $datos[$i][2] . ",
							0,
							" . $datos[$i][4] . "
					);";

            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_d_ventas, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_Compras(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {

        $sql = "SELECT
			h.C_InvoiceHeader_ID,
			h.c_org_id,
			h.created,
			h.c_bpartner_id,
			h.documentserial,
			h.c_doctype_id,
			t.baseamount neto,
			0 descuento,
			t.baseamount subtotal,
			t.taxamount impuesto,
			SUM(d.LineTotal) linetotal
		FROM
			C_InvoiceHeader h
			INNER JOIN C_InvoiceDetail d ON (h.C_InvoiceHeader_ID = d.C_InvoiceHeader_ID) 
			INNER JOIN C_InvoiceTax t ON (h.C_InvoiceHeader_ID = t.C_InvoiceHeader_ID)
		WHERE
			 h.IsSale=0 AND
			 h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                         AND h.c_org_id={$c_org_id}
		GROUP BY
			h.C_InvoiceHeader_ID,
			h.c_org_id,
			h.created,
			h.c_bpartner_id,
			h.documentserial,
			h.c_doctype_id,
			subtotal,
			impuesto";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_COMPRAS: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $fecha_ins = substr($row[2], 0, 19);
            $datos[$i][0] = $row[0];
            $datos[$i][1] = $row[1];
            $datos[$i][2] = $fecha_ins;
            $datos[$i][3] = $row[3];
            $datos[$i][4] = $row[4];
            $datos[$i][5] = $row[5];
            $datos[$i][6] = $row[6];
            $datos[$i][7] = $row[7];
            $datos[$i][8] = $row[8];
            $datos[$i][9] = $row[9];
            $datos[$i][10] = $row[10];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {

            $sql = "INSERT INTO
                            aux_energigas_e_compras(
                                                    op_compras,
                                                    op_local,
                                                    fecha,
                                                    codigo,
                                                    serie,
                                                    numero,
                                                    neto,
                                                    descuento,
                                                    subtotal,
                                                    impuestos,
                                                    total
					) VALUES (
							{$datos[$i][0]},
							{$datos[$i][1]},
							'" . $datos[$i][2] . "',
							'" . $datos[$i][3] . "',
							'" . $datos[$i][4] . "',
							{$datos[$i][5]},

							{$datos[$i][8]},
							0,
							{$datos[$i][8]},
							{$datos[$i][9]},
							{$datos[$i][10]}
					);";


            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_e_compras, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
        $datos = array();
    }
    return TRUE;
}

function export_D_Compras(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {

        $sql = "SELECT 
                    h.c_invoiceheader_id,
                    d.c_product_id,
                    d.unitprice,
                    d.quantity,
                    d.linetotal 
            FROM 
                    C_InvoiceHeader h
                    INNER JOIN C_InvoiceDetail d ON (h.C_InvoiceHeader_ID = d.C_InvoiceHeader_ID) 
            WHERE
                    h.IsSale=0 AND
                    h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                    AND h.c_org_id={$c_org_id}
	";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_D_COMPRAS: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $datos[$i][0] = $row [0];
            $datos[$i][1] = $row [1];
            $datos[$i][2] = $row [2];
            $datos[$i][3] = $row [3];
            $datos[$i][4] = $row [4];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {

            $sql = "INSERT INTO
					aux_energigas_d_compras(
							op_compra,
							producto,
							cantidad,
							precio,
							dcto,
							importe
					)VALUES(
							" . $datos[$i][0] . ",
							'" . $datos[$i][1] . "',
							" . $datos[$i][3] . ",
							" . $datos[$i][2] . ",
							0,
							" . $datos[$i][4] . "
					);";

            if (mssql_query($sql, $mssql) === FALSE) {

                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_d_compras, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_Vales(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {

        $sql = "
            SELECT 
                    h.c_org_id,
                    h.created,
                    h.documentserial,
                    h.documentno,
                    h.c_bpartner_id,
                    p.taxid,
                    p.name,
                    d.c_product_id,
                    d.quantity,
                    d.unitprice,
                    d.linetotal,
                    h.f_fleetvehicle_id
	 
            FROM    i_movementheader h
                    INNER JOIN i_movementdetail d ON h.i_movementheader_id=d.i_movementheader_id
                    INNER JOIN c_bpartner p ON h.c_bpartner_id=p.c_bpartner_id
            WHERE 	
                    h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                    AND   h.c_org_id={$c_org_id}
	";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_VALES: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $datos[$i][0] = $row [0];
            $datos[$i][1] = $row [1];
            $datos[$i][2] = $row [2];
            $datos[$i][3] = $row [3];
            $datos[$i][4] = $row [4];
            $datos[$i][5] = $row [5];
            $datos[$i][6] = $row [6];
            $datos[$i][7] = $row [7];
            $datos[$i][8] = $row [8];
            $datos[$i][9] = $row [9];
            $datos[$i][10] = $row [10];
            $datos[$i][11] = $row [11];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {
            $placa = (strlen($datos[$i][11]) == 0) ? '-' : $datos[$i][11];
            // $date_created = date("d-m-Y H:i:s", strtotime($datos[$i][1]));
            //$date_created = substr($datos[$i][1], 0, strpos($datos[$i][1], "."));
            $date_created = trim($datos[$i][1]);
            $c_bpartner_id = to_taxid($datos[$i][4]);
            $codigo_producto = to_producto($datos[$i][7]);
            $c_org_id_irium = aux_almacen($mssql, $datos[$i][0]);

            //  echo $date_created."---";
            $sql = "INSERT INTO
					aux_energigas_vales(
							op_local,
                                                        fecha,
                                                        serie,
                                                        numero,
                                                        cliente,
                                                        ruc,
                                                        nombre,
                                                        producto,
                                                        cantidad,
                                                        precio,
                                                        total,
                                                        fecha_consumo,
                                                        placa
					)VALUES(
							" . $c_org_id_irium . ",
							'" . $date_created . "',
                                                        '" . $datos[$i][2] . "',
                                                         {$datos[$i][3]},
                                                        '" . $c_bpartner_id . "',
                                                        '" . $datos[$i][5] . "',
                                                        '" . str_replace("'", " ", $datos[$i][6]) . "',
                                                        '" . $codigo_producto . "',
                                                        '" . $datos[$i][8] . "',
                                                        '" . $datos[$i][9] . "',
                                                        '" . $datos[$i][10] . "',
                                                        '" . $date_created . "',
                                                        '" . $placa . "'
							
							
					);";

            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_vales, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_Varillaje(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $org_cade, $array_c_org, $estadoActu;
    foreach ($array_c_org as $c_org_id) {

        $sql = " SELECT 
                    h.i_warehouselocation_id,
                    h.created,
                    d.c_product_id,
                    d.quantity,
                    iwh.c_org_id
            FROM 
                    i_inventoryheader h
                    inner join 
                    i_inventorydetail d
                    on h.i_inventoryheader_id=d.i_inventoryheader_id  
                    inner join i_warehouselocation iw on h.i_warehouselocation_id=iw.i_warehouselocation_id
                    inner join i_warehouse iwh on iw.i_warehouse_id=iwh.i_warehouse_id
            WHERE 	
                    h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                    AND iwh.c_org_id={$c_org_id};	
                    
                        
	";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_VARILLAJE: no se obtuvo datos en la fecha $fecha_bus0 :: $fecha_bus0 y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $datos[$i][0] = $row [0];
            $datos[$i][1] = $row [1];
            $datos[$i][2] = $row [2];
            $datos[$i][3] = $row [3];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {
            $codigo_producto = to_producto($datos[$i][2]);
            $c_org_id_irium = aux_almacen($mssql, $c_org_id);
            $sql = "INSERT INTO
					aux_energigas_varillaje(
							op_local,
                                                        fecha,
                                                        producto,
                                                        varillaje
					)VALUES(
							" . $c_org_id_irium . ",
							'" . $datos[$i][1] . "',
                                                          {$codigo_producto},
                                                          {$datos[$i][3]}
							
							
					);";
            if (mssql_query($sql, $mssql) === FALSE) {

                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_varillaje, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_ZZ_Parte_Diario(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $array_c_org, $array_close_period, $org_cade, $estadoActu;


    $date_system = substr($fecha_bus0, 0, 10);

    $date_prev = date("Y-m-d", strtotime("$date_system -1 day"));
    foreach ($array_c_org as $c_org_id) {
        $array_day_cod = array();
        $estatusday_previus = true;
        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM c_daycontrol cd
        INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE  cd.systemdate ='$date_prev'  AND cd.c_org_id=$c_org_id
        ORDER BY pc.updated desc limit 1";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_PARTE_DIARIO: no encontro los contometros del dia anterior $date_prev y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            $estatusday_previus = FALSE;

            /* continue; */
        }
        if ($estatusday_previus == true) {
            while ($row = $sqlca->fetchRow()) {
                $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
            }
        }
        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM    c_daycontrol cd
                INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE   cd.systemdate ='$date_system'  AND cd.c_org_id=$c_org_id
        ORDER BY pc.updated asc"; //desc limit 1
        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_PARTE_DIARIO: no se encontro el ultimo cierro de turno del dia $date_system y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }
        while ($row = $sqlca->fetchRow()) {
            $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
        }
// de captura los periodos del dia siguiente
//inicio del select del dia anretio
        $datos_day_prev = Array();
        if ($estatusday_previus == true) {
            $sql = "
                SELECT 
                        fpn.c_org_id,                        
                        ft.created,
                        ft.f_grade_id,
                        fg.c_product_id,
                        ft.amount,
                        ft.volume,
                        ft.c_periodcontrol_id
                        
                FROM    f_totalizer  ft
                        INNER JOIN f_grade fg ON ft.f_grade_id=fg.f_grade_id
                        INNER JOIN f_pump fp ON fg.f_pump_id=fp.f_pump_id
                        INNER JOIN f_pumpnetwork fpn ON fp.f_pumpnetwork_id=fp.f_pumpnetwork_id
                WHERE   c_periodcontrol_id='" . $array_day_cod[0]['c_periodcontrol_id'] . "' and fpn.c_org_id=$c_org_id
                
                GROUP BY
                        ft.created,
                        ft.f_grade_id,
                        ft.volume,
                        ft.amount,
                        ft.c_periodcontrol_id,
                        fpn.c_org_id,
                        fg.c_product_id
                ORDER BY ft.f_grade_id
                 ";


            if ($sqlca->query($sql) <= 0) {
                $menx = "AVISO-EXPORT_PARTE_DIARIO: no se encontro datos en f_totalizer en el periodo(" . $array_day_cod[0]['c_periodcontrol_id'] . ") y la ORG(" . $org_cade[$c_org_id] . ")";
                $resultado_exe[] = $menx;
                continue;
            }


            $i = 0;
            while ($row = $sqlca->fetchRow()) {

                $datos_day_prev[$i][0] = $row ['c_org_id'];
                $datos_day_prev[$i][1] = $row ['created'];
                $datos_day_prev[$i][2] = 0; //turno
                $datos_day_prev[$i][3] = $row ['f_grade_id'];
                $datos_day_prev[$i][4] = $row ['c_product_id'];
                $datos_day_prev[$i][5] = $row ['amount'];
                $datos_day_prev[$i][6] = $row ['volume'];
                $i++;
            }
        } else {
            $datos_day_prev[$i][0] = "0";
            $datos_day_prev[$i][1] = "1970-01-01";
            $datos_day_prev[$i][2] = 0; //turno
            $datos_day_prev[$i][3] = 0;
            $datos_day_prev[$i][4] = 0;
            $datos_day_prev[$i][5] = 0;
            $datos_day_prev[$i][6] = 0;
        }

        $sql = "
                SELECT 
                        fpn.c_org_id,                        
                        ft.created,
                        ft.f_grade_id,
                        fg.c_product_id,
                        ft.amount,
                        ft.volume,
                        ft.c_periodcontrol_id,
                        fp.value
                        
                FROM    f_totalizer  ft
                        INNER JOIN f_grade fg ON ft.f_grade_id=fg.f_grade_id
                        INNER JOIN f_pump fp ON fg.f_pump_id=fp.f_pump_id
                        INNER JOIN f_pumpnetwork fpn ON fp.f_pumpnetwork_id=fp.f_pumpnetwork_id
                WHERE   c_periodcontrol_id='" . $array_day_cod[count($array_day_cod) - 1]['c_periodcontrol_id'] . "' and fpn.c_org_id=$c_org_id
                
                GROUP BY
                        ft.created,
                        ft.f_grade_id,
                        ft.volume,
                        ft.amount,
                        ft.c_periodcontrol_id,
                        fpn.c_org_id,
                        fg.c_product_id,
                        fp.value
                ORDER BY ft.f_grade_id
                 ";


        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_PARTE_DIARIO: no se encontro datos en f_totalizer en el periodo(" . $array_day_cod[count($array_day_cod) - 1]['c_periodcontrol_id'] . ") y la ORG(" . $org_cade[$c_org_id] . ")";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos_day_now = Array();
        $i = 0;
        while ($row = $sqlca->fetchRow()) {
            $datos_day_now[$i][0] = $row ['c_org_id'];
            $datos_day_now[$i][1] = $row ['created'];
            $datos_day_now[$i][2] = 0; //turno
            $datos_day_now[$i][3] = $row ['f_grade_id'];
            $datos_day_now[$i][4] = $row ['c_product_id'];
            $datos_day_now[$i][5] = $row ['amount'];
            $datos_day_now[$i][6] = $row ['volume'];
            $datos_day_now[$i][7] = $row ['value'];
            $i++;
        }
        $sql = "
                SELECT 
                         im.f_pump_id ,
                         id.c_product_id,
                         sum(id.linetotal) as credito
                 FROM   i_movementheader im
                        INNER JOIN i_movementdetail id on im.i_movementheader_id=id.i_movementheader_id
                 WHERE im.created between '$date_system' and '$date_system'  and  im.c_org_id=$c_org_id
                 
                GROUP BY f_pump_id,id.c_product_id;";

        $array_movement = array();
        if ($sqlca->query($sql) <= 0) {
            
        }

        while ($row = $sqlca->fetchRow()) {
            $array_movement[$row ['f_pump_id']][$row ['c_product_id']] = $row ['credito']; //la del value
        }


        for ($i = 0; $i < count($datos_day_now); $i++) {
            $diff_soles = round($datos_day_now[$i][5] - $datos_day_prev[$i][5], 4);
            $diff_galon = round($datos_day_now[$i][6] - $datos_day_prev[$i][6], 4);
            $price_unit = round($diff_soles / $diff_galon, 2);
            $date_format_mssql = substr($datos_day_now[$i][1], 0, strpos($datos_day_now[$i][1], "."));
            $credito = (isset($array_movement[$datos_day_now[$i][7]][$datos_day_now[$i][4]])) ? $array_movement[$datos_day_now[$i][7]][$datos_day_now[$i][4]] : 0;
            $creditogalon = ($credito == 0) ? 0 : round(($credito / $price_unit), 4);
            $totalventasolescontado = round(($diff_soles - $credito), 4);
            $totalventagaloncontado = ($credito == 0) ? $diff_galon : round(($totalventasolescontado / $price_unit), 4);

            /* echo "--$/  lado :".$datos_day_now[$i][7]."=>". $datos_day_prev[$i][5] . "--" . $datos_day_now[$i][5] . "--" . $diff_soles ."----".$credito. "<br/>";
              echo "--GA " . $datos_day_prev[$i][6] . "--" . $datos_day_now[$i][6] . "--" . $diff_galon . "--pricer" . $price_unit . "--" . "<br/>";
             */
            $codigo_producto = to_producto($datos_day_now[$i][4]);
            $c_org_id_irium = aux_almacen($mssql, $c_org_id); //$datos_day_now[$i][0] 
            $sql = "INSERT INTO 
                                aux_energigas_parte_diario

                                (op_local,
                                fecha,
                                turno,
                                manguera,
                                grifero,
                                producto,
                                precio,
                                contometrosalidasoles,
                                contometrosalidaglns,
                                totalsalidasoles,
                                totalsalidaglns,
                                totalcontadosoles,
                                totalcontadoglns,
                                totalcreditosoles,
                                totalcreditoglns,
                                totalaferimientosoles,
                                totalaferimientoglns)
                 VALUES(
                                " . $c_org_id_irium . ",
                                '" . $date_format_mssql . "',
                                '" . (count($array_day_cod) - 1) . "',
                                '" . $datos_day_now[$i][3] . "',
                                1,
                                '" . $codigo_producto . "',
                                '" . $price_unit . "',
                                '" . $datos_day_now[$i][5] . "',
                                '" . $datos_day_now[$i][6] . "',
                                '" . $diff_soles . "',
                                '" . $diff_galon . "',
                                '" . $totalventasolescontado . "',
                                '" . $totalventagaloncontado . "',
                                '" . $credito . "',
                                '" . $creditogalon . "',
                                '0',
                                '0'

                                );";



            if (mssql_query($sql, $mssql) === FALSE) {

                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_parte_diario, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_ZZ_producto(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $array_c_org, $array_close_period, $org_cade, $estadoActu;


    $date_system = substr($fecha_bus0, 0, 10);

    $date_prev = date("Y-m-d", strtotime("$date_system -1 day"));
    foreach ($array_c_org as $c_org_id) {
        $array_day_cod = array();
        $whiledefaul = true;
        $sql = "
                SELECT
                       pc.c_periodcontrol_id,
                       pc.updated
               FROM    c_daycontrol cd
                       INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
               WHERE   cd.systemdate ='$date_prev'  AND cd.c_org_id=$array_close_period[$c_org_id]
                
               ORDER BY pc.updated desc limit 1";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_ZZ_PRODUCT: no encontro  Cierre del dia anterior($date_prev) y la ORG(" . $org_cade[$c_org_id] . ").";

            $array_day_cod[] = array("c_periodcontrol_id" => "0000", "day_period" => $date_system . " 00:00:00");
            $whiledefaul = false;
        }
        if ($whiledefaul == true) {
            while ($row = $sqlca->fetchRow()) {
                $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
            }
        }

        $sql = "
            SELECT
                    pc.c_periodcontrol_id,
                    pc.updated
            FROM    c_daycontrol cd
                    INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
            WHERE   cd.systemdate ='$date_system'  AND cd.c_org_id=$array_close_period[$c_org_id]
            ORDER BY pc.updated";


        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_ZZ_PRODUCT: no encontro los cierres correspondiente de la fecha $date_system  y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }
        while ($row = $sqlca->fetchRow()) {
            $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
        }

        for ($countDay = 0; $countDay < count($array_day_cod) - 1; $countDay++) {


            $sql = "
            SELECT
                        c.documentserial,
                        c.c_org_id,
                        d.c_product_id,
                        p.name,
                        sum(d.quantity) cantidad,
                        d.unitprice,
                        sum(round(d.linetotal / 1.18,2)) valorventa,
                        sum(round((d.linetotal - (d.linetotal / 1.18)),2)) igv,
                        sum(d.linetotal) total
            FROM
                        c_invoiceheader c
                            INNER JOIN c_invoicedetail d ON (c.c_invoiceheader_id = d.c_invoiceheader_id)
                            INNER JOIN c_product p ON (p.c_product_id = d.c_product_id)
            WHERE
                        (c.updated >'" . $array_day_cod[$countDay]['day_period'] . "' and c.updated <= '" . $array_day_cod[$countDay + 1]['day_period'] . "')
                         AND (c.c_org_id=$c_org_id AND c.issale=1)



            GROUP BY 
                        c.documentserial,
                        c.c_org_id,
                        d.c_product_id,
                        p.name,
                        d.unitprice
                        ORDER BY d.c_product_id 
                        ;
                                ";
            if ($sqlca->query($sql) <= 0) {
                $menx = "AVISO-EXPORT_ZZ_PRODUCT: no se encontro productos en el turno => " . $array_day_cod[$countDay]['day_period'] . " :: " . $array_day_cod[$countDay + 1]['day_period'] . "  y la ORG(" . $org_cade[$c_org_id] . ").";
                $resultado_exe[] = $menx;
                continue;
            }

            $datos = Array();
            $i = 0;
            $c = $sqlca->numrows();


            while ($row = $sqlca->fetchRow()) {

                $datos[$i][0] = $row ['documentserial'];
                $datos[$i][1] = $row ['c_org_id'];
                $datos[$i][2] = $row ['c_product_id'];
                $datos[$i][3] = $row ['name'];
                $datos[$i][4] = $row ['cantidad'];
                $datos[$i][5] = $row ['unitprice'];
                $datos[$i][6] = $row ['valorventa'];
                $datos[$i][7] = $row ['igv'];
                $datos[$i][8] = $row ['total'];
                $i++;
            }


            for ($i = 0; $i < count($datos); $i++) {
                if (strlen(trim($datos[$i][0])) > 0) {//el numero de serie no debe ser espacio en blanco por que son compras
                    $sales_shift = dev_cash_number_and_number_zz($datos[$i][0], $array_day_cod[$countDay + 1]['c_periodcontrol_id'], $c_org_id);


                    $date = explode("-", substr($array_day_cod[$countDay + 1]['day_period'], 0, 10));
                    $date_format_mssql = $date[0] . "-" . $date[2] . "-" . $date[1];


                    $date_format_mssql = substr($array_day_cod[$countDay + 1]['day_period'], 0, strpos($array_day_cod[$countDay + 1]['day_period'], "."));

                    $codigo_producto = to_producto($datos[$i][2]);
                    $c_org_id_irium = aux_almacen($mssql, $datos[$i][1]);
                    $sql = "INSERT INTO
                      aux_energigas_zz_producto(
                      op_local,
                      cajaregistradora,
                      zz,
                      fecha,
                      producto,
                      descripcion,
                      cantidad,
                      precio,
                      importesinigv,
                      importeigv,
                      timporte
                      )VALUES(
                      " . $c_org_id_irium . ",
                      " . $sales_shift['c_cash_number'] . ",
                      " . $sales_shift['c_number_zz'] . ",
                      '$date_format_mssql',
                      '" . $codigo_producto . "',
                      '" . str_replace("'", " ", $datos[$i][3]) . "',
                      '" . $datos[$i][4] . "',
                      '" . $datos[$i][5] . "',
                      '" . $datos[$i][6] . "',
                      '" . $datos[$i][7] . "',
                      '" . $datos[$i][8] . "'



                      );";

                    if (mssql_query($sql, $mssql) === FALSE) {
                        throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_zz_producto, se Termino el proceso de exportacion <BR/>$sql");
                    }
                    $estadoActu = TRUE;
                }
            }
        }
    }
}

function export_ZZ(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $array_c_org, $array_close_period, $org_cade, $estadoActu;


    $date_system = substr($fecha_bus0, 0, 10);

    $date_prev = date("Y-m-d", strtotime("$date_system -1 day"));

    foreach ($array_c_org as $c_org_id) {
        $array_day_cod = array();
        $whiledefaul = true;

        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM c_daycontrol cd
        INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE  cd.systemdate ='$date_prev'  AND cd.c_org_id=$array_close_period[$c_org_id]
        ORDER BY pc.updated desc limit 1";
        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_ZZ: no encontro  Cierre del dia anterior($date_prev) y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            /* continue; */
            $array_day_cod[] = array("c_periodcontrol_id" => "0000", "day_period" => $date_system . " 07:01:01.6565");
            $whiledefaul = false;
        }
        if ($whiledefaul == true) {
            while ($row = $sqlca->fetchRow()) {
                $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
            }
        }

        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM    c_daycontrol cd
                INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE   cd.systemdate ='$date_system'  AND cd.c_org_id=$array_close_period[$c_org_id]
        ORDER BY pc.updated";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_ZZ: no encontro los cierres correspondiente de la fecha $date_system  y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        while ($row = $sqlca->fetchRow()) {
            $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
        }

        $array_cod_period = array();
        for ($countDay = 0; $countDay < count($array_day_cod) - 1; $countDay++) {
            $array_cod_period[$array_day_cod[$countDay + 1]['c_periodcontrol_id']] = array("date_start" => $array_day_cod[$countDay]['day_period'], "date_end" => $array_day_cod[$countDay + 1]['day_period']);
            //echo  $array_day_cod[$countDay]['c_periodcontrol_id']."::".$array_day_cod[$countDay]['day_period'] . "-----'" .$array_day_cod[$countDay+1]['c_periodcontrol_id']."::". $array_day_cod[$countDay + 1]['day_period']."<br/>";
        }

        $sql = "
           SELECT
                cs.created,
                cpos.c_org_id,
                cs.c_periodcontrol_id,
                cs.cash_number,
                cs.number_zz,
                cpos.c_pos_id,
                cpos.c_postype_id,
                cs.ticket_transactions,
                (cs.ticket_total-cs.ticket_tax) as afectoboleta,
                cs.ticket_tax ,
                cs.ticket_total ,
                cs.invoice_transactions,
                (cs.invoice_total-cs.invoice_tax) as afectofactura,
                cs.invoice_tax ,
                cs.invoice_total ,
                (cs.total_total-cs.total_tax) as afecto,
                cs.total_tax ,
                cs.total_total 
         FROM   c_sale_shift cs
                INNER JOIN c_pos cpos ON  cs.c_pos_id=cpos.c_pos_id
         WHERE   (cs.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus1 . " 23:59:59')
                AND cpos.c_org_id=$c_org_id
                 ";


        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_ZZ: no se encontro la ventas por turno  y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $date_range = $array_cod_period[$row ['c_periodcontrol_id']];
            $datos[$i][0] = $row ['c_org_id'];
            $datos[$i][1] = $row ['cash_number'];
            $datos[$i][2] = $row ['number_zz'];
            $datos[$i][3] = $row ['c_postype_id'];

            $datos[$i][4] = substr($date_range["date_start"], 0, strpos($date_range["date_start"], "."));
            $datos[$i][5] = substr($date_range["date_start"], 0, strpos($date_range["date_start"], "."));
            $datos[$i][6] = substr($date_range["date_end"], 0, strpos($date_range["date_end"], "."));
            $datos[$i][7] = substr($date_range["date_end"], 0, strpos($date_range["date_end"], "."));

            $datos[$i][8] = $row ['ticket_transactions'];
            $datos[$i][9] = $row ['afectoboleta'];
            $datos[$i][10] = $row ['ticket_tax'];
            $datos[$i][11] = $row ['ticket_total'];
            $datos[$i][12] = $row ['invoice_transactions'];
            $datos[$i][13] = (isset($row ['afectofactura'])) ? $row ['afectofactura'] : "0";
            $datos[$i][14] = (isset($row ['invoice_tax'])) ? $row ['invoice_tax'] : "0";
            $datos[$i][15] = (isset($row ['invoice_total'])) ? $row ['invoice_total'] : "0";
            $datos[$i][16] = $row ['afecto'];
            $datos[$i][17] = $row ['total_tax'];
            $datos[$i][18] = $row ['total_total'];
            $i++;
        }


        for ($i = 0; $i < count($datos); $i++) {

            $origen = ($datos[$i][3] == 1) ? "Grifo" : "Market";
            $c_org_id_irium = aux_almacen($mssql, $datos[$i][0]);
            $sql = "INSERT INTO
					aux_energigas_zz(
							 op_local,
                                                         cajaregistradora,
                                                         zz,
                                                         origen,
                                                         aperturafecha,
                                                         aperturahora,
                                                         cierrefecha,
                                                         cierrehora,
                                                         totalticketsboletas,
                                                         afectoboletas,
                                                         igvboletas,
                                                         totalboletas,
                                                         totalticketsfacturas,
                                                         afectofacturas,
                                                         igvfacturas,
                                                         totalfacturas,
                                                         afecto,
                                                         igv,
                                                         total
					)VALUES( 
							" . $c_org_id_irium . ",
							" . $datos[$i][1] . ",
                                                        " . $datos[$i][2] . ",
                                                        '" . $origen . "',
                                                        '" . $datos[$i][4] . "',
                                                        '" . $datos[$i][5] . "',
                                                        '" . $datos[$i][6] . "',
                                                        '" . $datos[$i][7] . "',
                                                        '" . $datos[$i][8] . "',
                                                        '" . $datos[$i][9] . "',
                                                        '" . $datos[$i][10] . "',
                                                        '" . $datos[$i][11] . "',
                                                        '" . $datos[$i][12] . "',
                                                        '" . $datos[$i][13] . "',
                                                        '" . $datos[$i][14] . "',
                                                        '" . $datos[$i][15] . "',
                                                        '" . $datos[$i][16] . "',
                                                        '" . $datos[$i][17] . "',
                                                        '" . $datos[$i][18] . "'
                                                      );";


            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_zz, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function export_ZZ_Factura(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $array_c_org, $array_close_period, $org_cade, $estadoActu;

    foreach ($array_c_org as $c_org_id) {
        $periodoZZ = array();

        $sql = "
            SELECT
			h.c_org_id,
                        '0',
			'0',
			h.created,
			h.documentserial,
			h.documentno,
			h.c_bpartner_id,
			bp.name,
			bp.taxid,
			h.created,
			h.created,
			t.baseamount,
			t.taxamount,
			sum(d.linetotal) total
			
		FROM
			C_InvoiceHeader h
			INNER JOIN C_InvoiceDetail d ON h.C_InvoiceHeader_ID = d.C_InvoiceHeader_ID
			INNER JOIN C_InvoiceTax t ON (h.C_InvoiceHeader_ID = t.C_InvoiceHeader_ID)
			INNER JOIN c_bpartner bp ON (h.c_bpartner_id = bp.c_bpartner_id)
			
		WHERE   h.issale=1 AND  h.c_doctype_id=2
                       AND  h.created BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59'
                       AND  h.c_org_id=$c_org_id
		GROUP BY 
			h.c_org_id,
			h.created,
			h.documentserial,
			h.documentno,
			h.c_bpartner_id,
			bp.name,
			bp.taxid,
			h.created,
			h.created,
			t.baseamount,
			t.taxamount  
	";

        if ($sqlca->query($sql) <= 0) {
            $menx = "AVISO-EXPORT_FACTURAS: no encontro las facturas de  fecha $fecha_bus0  y la ORG(" . $org_cade[$c_org_id] . ").";
            $resultado_exe[] = $menx;
            continue;
        }

        $datos = Array();
        $i = 0;
        $c = $sqlca->numrows();

        while ($row = $sqlca->fetchRow()) {

            $datos[$i][0] = $row [0];
            $datos[$i][1] = $row [1];
            $datos[$i][2] = $row [2];
            $datos[$i][3] = $row [3];
            $datos[$i][4] = $row [4];
            $datos[$i][5] = $row [5];
            $datos[$i][6] = $row [6];
            $datos[$i][7] = $row [7];
            $datos[$i][8] = $row [8];
            $datos[$i][9] = $row [9];
            $datos[$i][10] = $row [10];
            $datos[$i][11] = $row [11];
            $datos[$i][12] = $row [12];
            $datos[$i][13] = $row [13];
            $i++;
        }

        $_c_sale_datos = array('c_cash_number' => '', 'number_zz' => '');
        for ($i = 0; $i < count($datos); $i++) {

            if (strlen(trim($datos[$i][4])) == 0)
                continue;
            $sql = "
                    SELECT 
                            c.cash_number,
                            c.number_zz,
                            c.c_periodcontrol_id,
                            cp.c_postype_id,
                            perdc.created,
                            perdc.updated
                    FROM    c_sale_shift c 
                            INNER JOIN c_pos cp ON c.c_pos_id=cp.c_pos_id
                            INNER JOIN c_documentserial d ON cp.c_documentserial_id=d.c_documentserial_id  
                            INNER JOIN c_periodcontrol perdc ON c.c_periodcontrol_id=perdc.c_periodcontrol_id

                    WHERE  c.created  
                           BETWEEN '" . $fecha_bus0 . " 00:00:00' AND '" . $fecha_bus0 . " 23:59:59' AND
                           d.documentserial=trim('" . $datos[$i][4] . "') AND d.c_org_id=$c_org_id  
                   ORDER BY perdc.updated";


            if ($sqlca->query($sql) <= 0) {
                continue;
            }
            while ($period = $sqlca->fetchRow()) {
                $periodoZZ[] = array($period[0], $period[1], $period[5], strtotime($period[5]));
            }

            $timestamp = strtotime($datos[$i][3]);
            foreach ($periodoZZ as $value) {
                if ($timestamp <= $value[3]) {
                    $_c_sale_datos['c_cash_number'] = $value[0];
                    $_c_sale_datos['number_zz'] = $value[1];
                    break;
                }
            }
            unset($periodoZZ);


            //$date_created = substr(trim($datos[$i][3]), 0, strpos($datos[$i][3], "."));
            $date_created = $datos[$i][3];
            $c_bpartner_id = to_taxid($datos[$i][6]);
            $c_org_id_irium = aux_almacen($mssql, $datos[$i][0]);

            $sql = "INSERT
					aux_energigas_zz_facturas(
							op_local,
                                                        cajaregistradora,
                                                        zz,
                                                        fecha,
                                                        serie,
                                                        ticket,
                                                        cliente,
                                                        nombre,
                                                        ruc,
                                                        fecha2,
                                                        hora,
                                                        afecto,
                                                        igv,
                                                        total
					)VALUES(
							" . $c_org_id_irium . ",
                                                        " . $_c_sale_datos['c_cash_number'] . ",
                                                        " . $_c_sale_datos['number_zz'] . ",
                                                        '$date_created',
                                                        '" . $datos[$i][4] . "',
                                                        {$datos[$i][5]},
                                                        '" . $c_bpartner_id . "',
                                                        '" . str_replace("'", " ", $datos[$i][7]) . "',
                                                        '" . $datos[$i][8] . "',
                                                        '$date_created',
                                                        '$date_created',
                                                        '" . $datos[$i][11] . "',
                                                        '" . $datos[$i][12] . "',
                                                        '" . $datos[$i][13] . "'
                                                        
							
							
					);";


            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("Problemas al insertar datos en la tabla ->  aux_energigas_zz_facturas, se Termino el proceso de exportacion <BR/>$sql");
            }
            $estadoActu = TRUE;
        }
    }
}

function dev_cash_number_and_number_zz($documentserial, $periodcontrol_id, $c_org_id) {
    global $sqlca;

    $sql = "SELECT 
                    c.cash_number,
                    c.number_zz,
                    c.c_periodcontrol_id,
                    cp.c_postype_id,
                    perdc.created,
                    perdc.updated
            FROM    c_sale_shift c inner join c_pos cp on c.c_pos_id=cp.c_pos_id
                    INNER JOIN c_documentserial d on cp.c_documentserial_id=d.c_documentserial_id  
                    INNER JOIN c_periodcontrol perdc on c.c_periodcontrol_id=perdc.c_periodcontrol_id
            WHERE c.c_periodcontrol_id=$periodcontrol_id and
                    d.documentserial=trim('$documentserial') and d.c_org_id=$c_org_id  
            ORDER BY c.c_periodcontrol_id limit 1";


    if ($sqlca->query($sql) <= 0) {
        throw new Exception("Error no se encontro Caja registrado ni numero de cierre con la serie : $documentserial, periodo : $periodcontrol_id y centro de costo : $c_org_id");
    }
    while ($row = $sqlca->fetchRow()) {
        $array_sales_shift = array("c_cash_number" => $row['cash_number'], "c_number_zz" => $row['number_zz']);
    }
    return $array_sales_shift;
}

function to_taxid($c_bpartner_id) {
    global $sqlca;
    $id_cliente = trim($c_bpartner_id);
    $sql = "SELECT 
                taxid 
            FROM c_bpartner 
            WHERE c_bpartner_id=$id_cliente
            limit 1";

    $taxid = '';
    if ($sqlca->query($sql) <= 0) {
        $taxid = "GENERICO";
    }
    while ($row = $sqlca->fetchRow()) {
        $taxid = $row['taxid'];
    }
    return $taxid;
}

function to_producto($id_producto) {
    global $sqlca;
    $id_produ = trim($id_producto);
    $sql = "SELECT 
                value 
            FROM c_product 
            WHERE c_product_id=$id_produ
            limit 1";

    $value = '';
    if ($sqlca->query($sql) <= 0) {
        $value = "----";
    }
    while ($row = $sqlca->fetchRow()) {
        $value = $row['value'];
    }
    return $value;
}

function aux_almacen(&$mssql, $id_org) {
    global $areglo_global_irium;
    $resul = $areglo_global_irium[$id_org];
    if ($resul == null) {
        $resul = '-1';
    } else {
        
    }

    return $resul;
}

function llenar_areglo_todo(&$mssql) {
    $areglo_global_irium;
    $sql = "SELECT cod_alm_irin,cod_alm_open FROM aux_almacenes ";
    $resul = mssql_query($sql, $mssql);
    if ($resul === FALSE) {
        return "-1";
    }
    while ($data = mssql_fetch_array($resul)) {
        $areglo_global_irium[$data['cod_alm_open']] = $data['cod_alm_irin'];
    }
    return $areglo_global_irium;
}

function Insertar_Client(&$mssql, $op_local, $identi, $c_bpartner_id) {
    global $sqlca;
    $op_local = trim($op_local);
    $identi = trim($identi);
    try {
        $sql = "select count(*)as can from Aux_Energigas_Clientes where Op_local=$op_local and (RUC='$identi' or DNI='$identi');";
        $resul = mssql_query($sql, $mssql);
        if ($resul === FALSE) {

            throw new Exception("---");
        }
        $data = mssql_fetch_array($resul);
        if ($data['can'] == 0) {
            $fecha = date('d-m-Y');
            $dni = "";
            $ruc = "";
            if (strlen($identi) == 11) {
                $ruc = $identi;
            } else if (strlen($identi) == 8) {
                $dni = $identi;
            } else if (strlen($identi) > 1) {
                $ruc = $identi;
                $dni = $identi;
            } else {
                $ruc = '-';
                $dni = '-';
            }
            $sqlpos = "select name from c_bpartner where c_bpartner_id='$c_bpartner_id'";
            if ($sqlca->query($sqlpos) <= 0) {
                throw new Exception("---");
            }

            $nombre = "--";
            while ($row = $sqlca->fetchRow()) {
                $nombre = $row['name'];
            }

            $sql = "insert into Aux_Energigas_Clientes (Op_local,RUC,DNI,Nombre_Razon_Social,Fecha_Creacion)values('$op_local','$ruc','$dni','$nombre','$fecha')";
            if (mssql_query($sql, $mssql) === FALSE) {
                throw new Exception("---");
            }
        }
    } catch (Exception $r) {
        return;
    }
}

function Insertar_Producto(&$mssql) {
    global $sqlca, $resultado_exe,$estadoActu;

    $sql = "
            SELECT 
                cp.value,
                ph.name,
                pd.maxprice,
                ph.created
        FROM  c_pricelistheader ph 
        INNER JOIN c_pricelistdetail pd on ph.c_pricelistheader_id=pd.c_pricelistheader_id
        INNER JOIN  c_product cp on pd.c_product_id=cp.c_product_id;";

    if ($sqlca->query($sql) <= 0) {
        $menx = "Lista de Precio vacia.<br/>.$sql";
        $resultado_exe[] = $menx;
    }

    $datos = Array();
    $x = 0;

    while ($row = $sqlca->fetchRow()) {
        $sql = "select count(*) as cant from Aux_Energigas_Productos where Producto='" . $row ['value'] . "';";
        $resul = mssql_query($sql, $mssql);
        if ($resul === FALSE) {
            continue;
        }
        $fila = mssql_fetch_array($resul);
        if ($fila['cant'] == 0) {
            $datos[$x][0] = '9999996';
            $datos[$x][1] = $row ['value'];
            $datos[$x][2] = $row ['name'];
            $datos[$x][3] = $row ['maxprice'];
            $datos[$x][4] = $row ['created'];
            $x++;
        }
    }

    for ($i = 0; $i < count($datos); $i++) {
        $sql = "INSERT  INTO Aux_Energigas_Productos(
						Op_Local,
                                                Producto,
                                                Descripcion,
                                                Precio,
                                                Fecha_Creacion
					)VALUES(
							'" . $datos[$i][0] . "',
                                                        '" . $datos[$i][1] . "',
                                                        '" . $datos[$i][2] . "',
                                                        '" . $datos[$i][3] . "',
                                                        '" . $datos[$i][4] . "'
					);";
        if (mssql_query($sql, $mssql) === FALSE) {
            throw new Exception("Error Al Insertar Producto <BR/>$sql");
        }
    }
    $estadoActu=TRUE;
}

function export_ZZ_Parte_Diario_backup(&$mssql, $fecha_bus0, $fecha_bus1) {
    global $sqlca, $resultado_exe, $array_c_org, $array_close_period;


    $date_system = substr($fecha_bus0, 0, 10);

    $date_prev = date("Y-m-d", strtotime("$date_system -1 day"));
    foreach ($array_c_org as $c_org_id) {
        $array_day_cod = array();
        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM c_daycontrol cd
        INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE  cd.systemdate ='$date_prev'  AND cd.c_org_id=$c_org_id
        ORDER BY pc.updated desc limit 1";
        if ($sqlca->query($sql) <= 0) {
            $menx = "Error al obtener datos de la funcion  export_ZZ_Parte_Diario(). no encontro  Contometros del dia anterior $date_prev : CS $c_org_id -AVISO";
            $resultado_exe["export_zz_producto_" . $c_org_id] = $menx;
            continue;
        }
        while ($row = $sqlca->fetchRow()) {
            $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
        }

        $sql = "
         SELECT
                pc.c_periodcontrol_id,
                pc.updated
        FROM    c_daycontrol cd
                INNER JOIN c_periodcontrol pc on cd.c_daycontrol_id=pc.c_daycontrol_id
        WHERE   cd.systemdate ='$date_system'  AND cd.c_org_id=$c_org_id
        ORDER BY pc.updated desc limit 1";
        if ($sqlca->query($sql) <= 0) {
            $menx = "Error al obtener datos de la funcion  export_ZZ_producto. no encontro  el ultimo cierro de turno del dia : $date_system   CS : $c_org_id  -AVISO";
            $resultado_exe["export_zz_producto_" . $c_org_id] = $menx;
            continue;
        }
        while ($row = $sqlca->fetchRow()) {
            $array_day_cod[] = array("c_periodcontrol_id" => $row['c_periodcontrol_id'], "day_period" => $row['updated']);
        }


        $sql = "
                SELECT 
                        fpn.c_org_id,                        
                        ft.created,
                        ft.f_grade_id,
                        fg.c_product_id,
                        ft.amount,
                        ft.volume,
                        ft.c_periodcontrol_id
                        
                FROM    f_totalizer  ft
                        INNER JOIN f_grade fg ON ft.f_grade_id=fg.f_grade_id
                        INNER JOIN f_pump fp ON fg.f_pump_id=fp.f_pump_id
                        INNER JOIN f_pumpnetwork fpn ON fp.f_pumpnetwork_id=fp.f_pumpnetwork_id
                WHERE   c_periodcontrol_id='" . $array_day_cod[0]['c_periodcontrol_id'] . "' and fpn.c_org_id=$c_org_id
                
                GROUP BY
                        ft.created,
                        ft.f_grade_id,
                        ft.volume,
                        ft.amount,
                        ft.c_periodcontrol_id,
                        fpn.c_org_id,
                        fg.c_product_id
                ORDER BY ft.f_grade_id
                 ";


        if ($sqlca->query($sql) <= 0) {
            $menx = "Error al obtener datos de la funcion export_ZZ_Parte_Diario   AVISO";
            $resultado_exe["export_ZZ_Parte_Diario" . $c_org_id] = $menx;
            continue;
        }

        $datos_day_prev = Array();
        $i = 0;

        while ($row = $sqlca->fetchRow()) {

            $datos_day_prev[$i][0] = $row ['c_org_id'];
            $datos_day_prev[$i][1] = $row ['created'];
            $datos_day_prev[$i][2] = 0; //turno
            $datos_day_prev[$i][3] = $row ['f_grade_id'];
            $datos_day_prev[$i][4] = $row ['c_product_id'];
            $datos_day_prev[$i][5] = $row ['amount'];
            $datos_day_prev[$i][6] = $row ['volume'];
            $i++;
        }

        $sql = "
                SELECT 
                        fpn.c_org_id,                        
                        ft.created,
                        ft.f_grade_id,
                        fg.c_product_id,
                        ft.amount,
                        ft.volume,
                        ft.c_periodcontrol_id,
                        fp.value
                        
                FROM    f_totalizer  ft
                        INNER JOIN f_grade fg ON ft.f_grade_id=fg.f_grade_id
                        INNER JOIN f_pump fp ON fg.f_pump_id=fp.f_pump_id
                        INNER JOIN f_pumpnetwork fpn ON fp.f_pumpnetwork_id=fp.f_pumpnetwork_id
                WHERE   c_periodcontrol_id='" . $array_day_cod[1]['c_periodcontrol_id'] . "' and fpn.c_org_id=$c_org_id
                
                GROUP BY
                        ft.created,
                        ft.f_grade_id,
                        ft.volume,
                        ft.amount,
                        ft.c_periodcontrol_id,
                        fpn.c_org_id,
                        fg.c_product_id,
                        fp.value
                ORDER BY ft.f_grade_id
                 ";


        if ($sqlca->query($sql) <= 0) {
            $menx = "Error al obtener datos de la funcion export_ZZ_Parte_Diario   AVISO";
            $resultado_exe["export_ZZ_Parte_Diario" . $c_org_id] = $menx;
            continue;
        }

        $datos_day_now = Array();
        $i = 0;
        while ($row = $sqlca->fetchRow()) {
            $datos_day_now[$i][0] = $row ['c_org_id'];
            $datos_day_now[$i][1] = $row ['created'];
            $datos_day_now[$i][2] = 0; //turno
            $datos_day_now[$i][3] = $row ['f_grade_id'];
            $datos_day_now[$i][4] = $row ['c_product_id'];
            $datos_day_now[$i][5] = $row ['amount'];
            $datos_day_now[$i][6] = $row ['volume'];
            $datos_day_now[$i][7] = $row ['value'];
            $i++;
        }

        //obtener los creditos o vales
        $sql = "
                SELECT 
                         im.f_pump_id ,
                         id.c_product_id,
                         sum(id.linetotal) as credito
                 FROM   i_movementheader im
                        INNER JOIN i_movementdetail id on im.i_movementheader_id=id.i_movementheader_id
                 WHERE im.created between '$date_system' and '$date_system'  and  im.c_org_id=$c_org_id
                 
                GROUP BY f_pump_id,id.c_product_id;";
        echo $sql;
        $array_movement = array();
        if ($sqlca->query($sql) <= 0) {
            
        }

        while ($row = $sqlca->fetchRow()) {
            $array_movement[$row ['f_pump_id']][$row ['c_product_id']] = $row ['credito']; //la del value
        }


        for ($i = 0; $i < count($datos_day_now); $i++) {
            $diff_soles = round($datos_day_now[$i][5] - $datos_day_prev[$i][5], 4);
            $diff_galon = round($datos_day_now[$i][6] - $datos_day_prev[$i][6], 4);
            $price_unit = round($diff_soles / $diff_galon, 2);
            $date_format_mssql = substr($datos_day_now[$i][1], 0, strpos($datos_day_now[$i][1], "."));
            $credito = (isset($array_movement[$datos_day_now[$i][7]][$datos_day_now[$i][4]])) ? $array_movement[$datos_day_now[$i][7]][$datos_day_now[$i][4]] : 0;
            $creditogalon = ($credito == 0) ? 0 : round(($credito / $price_unit), 4);
            $totalventasolescontado = round(($diff_soles - $credito), 4);
            $totalventagaloncontado = ($credito == 0) ? $diff_galon : round(($totalventasolescontado / $price_unit), 4);

            /* echo "--$/  lado :".$datos_day_now[$i][7]."=>". $datos_day_prev[$i][5] . "--" . $datos_day_now[$i][5] . "--" . $diff_soles ."----".$credito. "<br/>";
              echo "--GA " . $datos_day_prev[$i][6] . "--" . $datos_day_now[$i][6] . "--" . $diff_galon . "--pricer" . $price_unit . "--" . "<br/>";
             */
            /* $sql = "INSERT INTO 
              aux_energigas_parte_diario

              (op_local,
              fecha,
              turno,
              manguera,
              grifero,
              producto,
              precio,
              contometrosalidasoles,
              contometrosalidaglns,
              totalsalidasoles,
              totalsalidaglns,
              totalcontadosoles,
              totalcontadoglns,
              totalcreditosoles,
              totalcreditoglns,
              totalaferimientosoles,
              totalaferimientoglns)
              VALUES(
              " . $datos_day_now[$i][0] . ",
              '" . $date_format_mssql . "',
              '" . $datos_day_now[$i][2] . "',
              '" . $datos_day_now[$i][3] . "',
              1,
              '" . $datos_day_now[$i][4] . "',
              '" . $price_unit . "',
              '" . $datos_day_now[$i][5] . "',
              '" . $datos_day_now[$i][6] . "',
              '" . $diff_soles . "',
              '" . $diff_galon . "',
              '" . $totalventasolescontado . "',
              '" . $totalventagaloncontado . "',
              '" . $credito . "',
              '" . $creditogalon . "',
              '0',
              '0'

              );";



              if (mssql_query($sql, $mssql) === FALSE) {
              throw new Exception("Error al insertar en la tabla aux_energigas_zz.  -PELIGRO");
              } */
        }
    }
}

