<?php
/**
 * METODO 1 - DICIEMBER:
- Genere los inserts en base de datos, también genere los archivos txt con el formato multilinea
- Se desactivo el servicio de fe
- Ellos completaron y verificaron descargo de stock en ATK
- Luego te pase los archivos txt para el envio de las boletas
- Arreglamos correlativo de sequencias

 * METODO 2 - JUNIO:
- Genere los inserts en base de datos, también genere los archivos txt con el formato multilinea y los inserts a ebi_queue
- Insertamos directo en ebi_queue, esperamos a que se procese (status = 1) y luego completamos. Se volverá a insertar en ebi_queue, pero será rechazado (status = 2), así que sólo cambias estado en act_invoice a "Completado - Enviado"
- Arreglamos correlativo de sequencias
*/

$linea = 0;

//Preparamos Llaves (Siempre ver en BD antes de correr el script)
$act_invoice_id = 21147;
$act_invoiceline_id = 13393;
$act_invoicetax_id = 23242;
$subdiario_ventas_locales = 111; //En el listado de facturas, el ultimo elemento generado
$cnf_bpartner_id = 2303;
$ebi_queue_id = 7754;

$act_invoice_id++;
$act_invoiceline_id++;
$act_invoicetax_id++;
$subdiario_ventas_locales++;
$ebi_queue_id++;

//Abrimos nuestro archivo
$archivo = fopen("script_redher.csv", "r");

//Lo recorremos
while (($datos = fgetcsv($archivo, ",")) == true) {
   //Mostramos datos en array
   // echo "<pre>";
   // print_r($datos);
   // echo "<pre>";
    
   //Recorremos las columnas de esa linea
   // $num = count($datos);
   // for ($columna=0; $columna<$num; $columna++) {
      // echo $datos[$columna] . "|";
   // }

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

   //Inserts
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

   // echo "<br>";
   // echo "select * from act_invoice where documentno = '$datos[2]' and act_serialnumber_id = '21';";

   // echo "<br>";
   // echo "UPDATE act_invoice SET created = '$datos[0]', dateacct = '$datos[0]', duedate = '$datos[0]', documentdate = '$datos[0]', description = '$datos[9]' WHERE documentno = '$datos[2]' AND act_serialnumber_id = '21';";

   // echo "<br>";
   // echo "UPDATE act_invoice SET status = '3' WHERE documentno = '$datos[2]' AND act_serialnumber_id = '21';";

   $callback      =<<<EOT
   {
      "1":"update act_invoice set status = 3  WHERE act_invoice_id = {$act_invoice_id}",
      "2":"update act_invoice set status = 4  WHERE act_invoice_id = {$act_invoice_id}"
   }
EOT;

   $texto = <<<_END
03|$datos[1]|$datos[2]|PEN|$datos[0]|0.00|0|0|CLIENTES VARIOS|-|-|-|0
L|$datos[3]|1|NIU|0|$datos[5]|$datos[4]|0.00|14$datos[7]|-|-|0|0
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
   $act_invoice_id++;
   $act_invoiceline_id++;
   $act_invoicetax_id++;
   $subdiario_ventas_locales++;
   $ebi_queue_id++;

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

   //Guardar archivos en txt
   $fh = fopen("/var/www/html/sistemaweb/script/ebi_queue/$datos[1]-$datos[2].txt", 'w') or die("Se produjo un error al crear el archivo");

   $texto = <<<_END
03|$datos[1]|$datos[2]|PEN|$datos[0]|0.00|0|0|CLIENTES VARIOS|-|-|-|0
L|$datos[3]|1|NIU|0|$datos[5]|$datos[4]|0.00|14$datos[7]|-|-|0|0
T|VAT|$datos[7]|18.00
O|1001|0.00
O|1004|$datos[8]
E|1002|TRANSFERENCIA GRATUITA
X|X0009|$datos[9]
_END;

  fwrite($fh, $texto) or die("No se pudo escribir en el archivo");
  fclose($fh);
}

//Cerramos el archivo
fclose($archivo);
die();
