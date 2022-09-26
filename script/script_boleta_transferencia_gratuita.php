<?php
/**
* METODO PARA GENERAR BOLETAS TRANSFERENCIA GRATUITA:
1. Indicamos correlativos de secuencias en el script de las tablas: act_invoice, act_invoiceline, act_invoicetax, ebi_queue 
2. Generar los inserts de base de datos de las tablas act_invoice, act_invoiceline, act_invoicetax, ebi_queue 
3. Insertamos act_invoice, act_invoiceline, act_invoicetax
4. Insertamos ebi_queue, esperamos a que se procese (status = 1) esto cambia el estado a "Completado - Enviado" en Facturas de Ventas. 
5. Completamos manualmente para esto debemos cambiar el estado a "Registrado" para completar y esto hara que descargue STOCK y realice el movimiento de inventario, esto hara que se vuelva a insertar en ebi_queue, pero será rechazado (status = 2), así que sólo cambias estado en act_invoice a "Completado - Enviado"
6. Arreglamos correlativo de sequencias de las tablas: act_invoice, act_invoiceline, act_invoicetax, ebi_queue
7. Actualizamos el Numero de registro (registerno) del Subdiario (act_subbook_id) de Facturas de Ventas (act_invoice)
*/

$linea = 0;

//Preparamos Llaves (Siempre ver en BD antes de correr el script)
$act_invoice_id = 21177;
$act_invoiceline_id = 13416;
$act_invoicetax_id = 23273;
$ebi_queue_id = 7795;
$subdiario_ventas_locales = 133; //En el listado de Facturas de Ventas, es la columna "Numero de Registro"
$cnf_bpartner_id = 2303; //Dejar 2303 por defecto

//Abrimos nuestro archivo
$archivo = fopen("script_boleta_transferencia_gratuita.csv", "r");

//Lo recorremos
while (($datos = fgetcsv($archivo, ",")) == true) {
   //Mostramos datos en array
   // echo "<pre>";
   // print_r($datos);
   // echo "<pre>";

   //Validamos que si es la primera fila no lo considere
   if(TRIM($datos[0]) == 'Fecha'){
      continue;
   }
    
   //A partir de aqui no es la primera fila
   $act_invoice_id++;
   $act_invoiceline_id++;
   $act_invoicetax_id++;
   $ebi_queue_id++;
   $subdiario_ventas_locales++;

   /**
    * 
    Array
   (
      [0] => 2012-12-09
      [1] => B001
      [2] => 00000009
      [3] => 000209
      [4] => TARJETAS NAVIDEÑAS - PAVOS
      [5] => 118.0000
      [6] => 1.0000
      [7] => 18.0000
      [8] => 100.0000
      [9] => OBSERVACION/GLOSA
   )
    */

   //Inserts act_invoice, act_invoiceline, act_invoicetax
   echo "<pre>";
   echo "INSERT INTO public.act_invoice(
               act_invoice_id, documentserial, documentno, cnf_org_id, act_doctype_id, created, dateacct, act_currency_id, cnf_bpartner_id, cnf_bpartner_location_id, issale, isinventory, inv_warehouse_id, refinvoice_id, status, totalamt, act_serialnumber_id, grandtotal, duedate, description, registerno, documentdate, act_order_id, act_customs_id, act_subbook_id, actionacct, act_tendertype_id)
               VALUES ($act_invoice_id, 'NULL', '$datos[2]', 100, 1, '$datos[0]', '$datos[0]', '1', '$cnf_bpartner_id', NULL, 1, 1, 2, NULL, 0, 0.0000, 21, 0.0000, '$datos[0]', '$datos[9]', '$subdiario_ventas_locales', '$datos[0]', NULL, NULL, 1, 1, 1);";

   echo "<br>";
   echo "INSERT INTO public.act_invoiceline(
               act_invoiceline_id, act_invoice_id, cnf_org_id, inv_product_id, quantity, linetotal, unitprice, taxtype)
               VALUES ($act_invoiceline_id, $act_invoice_id, 100, $datos[10], $datos[6], 0.0000, $datos[5], 7);";

   echo "<br>";
   echo "INSERT INTO public.act_invoicetax(
               act_invoicetax_id, act_invoice_id, cnf_org_id, act_tax_id, baseamt, taxamt)
               VALUES ($act_invoicetax_id, $act_invoice_id, 100, 1, 0.0000, $datos[7]);";

   //Insert a ebi_queue
   $callback      =<<<EOT
   {
      "1":"update act_invoice set status = 3  WHERE act_invoice_id = {$act_invoice_id}",
      "2":"update act_invoice set status = 4  WHERE act_invoice_id = {$act_invoice_id}"
   }
EOT;

   $texto = <<<_END
03|$datos[1]|$datos[2]|PEN|$datos[0]|0.00|0|0|CLIENTES VARIOS|-|-|-|0
L|$datos[3]|$datos[6]|NIU|0|$datos[5]|$datos[4]|0.00|14$datos[7]|-|-|0|0
T|VAT|$datos[7]|18.00
O|1001|0.00
O|1004|$datos[8]
E|1002|TRANSFERENCIA GRATUITA
X|X0009|$datos[9]
_END;

   echo "<br><br>";
   echo "INSERT INTO ebi_queue (_id, created, taxid, optype, status, callback, content) VALUES ($ebi_queue_id, NOW(), '20515229745', '0', '0', '$callback', '$texto');";
   echo "<br><br>";
   echo "</pre>";
}

//Update de numero de registro
echo "<pre>";
echo "select * from act_subbook;";
echo "<br>";
echo "select * from act_registernumber where act_subbook_id = 1 order by act_day_id; /*act_subbook_id = 1 es '30 - VENTAS LOCALES (14)'*/";
echo "<br>";
echo "select * from act_preseq order by act_registernumber_id";
echo "<br>";
echo "</pre>";

//Cerramos el archivo
fclose($archivo);
die();
