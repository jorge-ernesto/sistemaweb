<?php

$dbstr = "
INTERFAZO7=
(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=192.168.0.4)(PORT=1521))
(CONNECT_DATA=
(SERVER=dedicated)
(SERVICE_NAME=INTERFAZO7)
))";

$conn = oci_connect("OPENSOFT", "OPENSOFT", "192.168.0.4:1521/INTERFAZO7");
var_dump($conn);

if (!$conn) {
    $e = oci_error();
    var_dump($e);
}


/*
  array(18) {
  ["PACCODCIA"]=> string(3) "001"
  ["PACCODSUC"]=> string(3) "001"
  ["PACNROPTF"]=> string(5) "12345"
  ["PACCODLOC"]=> string(3) "001"
  ["PACTIPCLI"]=> string(2) "1 "
  ["PACNRORUC"]=> string(8) "12345567"
  ["PACRAZONS"]=> string(6) "prueba"
  ["PACNRODNI"]=> NULL ["PACAPEPAT"]=> NULL
  ["PACAPEMAT"]=> NULL ["PACNOMBRE"]=> NULL
  ["PACNROHC"]=> NULL ["PACFECAFI"]=> NULL
  ["PACDIRECC"]=> NULL ["PACDIRREF"]=> NULL
  ["PACTELFIJ"]=> NULL ["PACMOVIL"]=> NULL ["PACEMAIL"]=> NULL } */

/*$stmt = oci_parse($conn, "DELETE FROM  I_TMPCLIENTES");
oci_execute($stmt, OCI_DEFAULT);
$stmt = oci_parse($conn, "DELETE FROM  I_TMPFACCAB");
oci_execute($stmt, OCI_DEFAULT);
$stmt = oci_parse($conn, "DELETE FROM  I_TMPFACDET");
oci_execute($stmt, OCI_DEFAULT);
$stmt = oci_parse($conn, "DELETE FROM  I_TMPCOBRANZA");
oci_execute($stmt, OCI_DEFAULT);
$stmt = oci_parse($conn, "
  INSERT INTO I_TMPCOBRANZA 
  ( 
  CCFCODCIA,CCFCODSUC,CCFCODLOC,
  CCFNROCAJ,CCFNROTURNO, CCFFECING,
  CCFTIPDOC,CCFSERDOCE,CCFNRODOCE,
  
  CCFITMCOB, CCFCODCBR,CLCCFECEMI,
  CCFCODTIPC,CCFCODMON,CCFIMPCOB,
  CCFNROTAR,CCFNROVCH

   )
  VALUES ( 
  '008','24','25',
  'FFFF100327','1', to_date('11/01/2013','mm/dd/yyyy'),
  'BLC','000',129161,
  1, '00000000',to_date('11/01/2013','mm/dd/yyyy'),
  'EFE','S/.',10.2,'dd','ff'
  )
 "); //'12.7119','2.2881','15.0000',CCFNROTAR='1230'


$error = oci_error($stmt);
echo "Error encontrado =>" . $error . "\n";
oci_execute($stmt, OCI_DEFAULT);*/
/* $cade = "SELECT count(*) as cliente FROM I_TMPCLIENTES WHERE PACNROPTF='6839'";

  $stmt = oci_parse($conn, $cade);
  oci_execute($stmt, OCI_DEFAULT);

  $row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS);
  echo $row['cliente'] . "---";
  echo $row['CLIENTE'] ;
  var_dump($row);


 */

  echo "************************************";
  echo "</table>\n";
  oci_commit($conn);
  echo "********CLIENTE*****************<br/>";
  $stmt = oci_parse($conn, ' SELECT * FROM I_TMPCLIENTES ');
  oci_execute($stmt, OCI_DEFAULT);
  echo "<table border='1'>\n";
  $i = 1;
  while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
  echo $i . "=>" . $row['PACNRORUC'] . "--" . $row['PACRAZONS'] . "--" . $row['PACFECAFI'] ;
  $i++;
  }
  echo "********CABECERA*****************<br/>";
  $stmt = oci_parse($conn, ' SELECT * FROM I_TMPFACCAB ');
  oci_execute($stmt, OCI_DEFAULT);
  echo "<table border='1'>\n";
  $i = 1;
  while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
  echo $i . "=>" . $row['CCFCODCIA'] . "--" . $row['CCFNROCAJ'] . "--" . $row['CCFTIPDOC'] . "--" . $row['CCFCODANT'] . "--" . $row['CCFCODMON'] . "--" . $row['CCFIMPBAS'] . "--" . $row['CCFIMPIGV'] . "--" . $row['CCFIMPTOT'] . "<br/>";
  $i++;
  }
  echo "********DETALLE*****************<br/>";
  $stmt = oci_parse($conn, ' SELECT * FROM I_TMPFACDET ');
  oci_execute($stmt, OCI_DEFAULT);
  echo "<table border='1'>\n";
  $i = 1;
  while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
  echo "<br/>" . $i . "=>" . $row['CCFCODSUC'] . "--" . $row['CCFNROCAJ'] . "--" . $row['CCFTIPDOC'] . "--" . $row['CCFSERDOCE'] . "--" . $row['CCFNRODOCE'] . "--" . $row['CDFNROITM'] . "--" . $row['CDFMOTIVO'] . "--" . $row['CDFCANTID'] . "--" . $row['CDFUNIMED'] . "--" . $row['CDFPRCUNI'] . "--" . $row['CCFIMPBAS'] . "--" . $row['CCFIMPIGV'] . "--" . $row['CCFIMPTOT'] . "<br/>";
  $i++;
  }
 
echo "********COBRANZA*****************<br/>";
$stmt = oci_parse($conn, ' SELECT * FROM I_TMPCOBRANZA ');
oci_execute($stmt, OCI_DEFAULT);
echo "<table border='1'>\n";
$i = 1;
while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
    echo "<br/>" . $i . "=>" . $row['CCFSERDOCE'] . "--" . $row['CCFNROCAJ'] . "--*". $row['CCFCODTIPC'] . "*--". $row['CCFCODMON'] . "--". $row['CCFIMPCOB'] . "--";

    $i++;
}

/* * ****** */
/*echo "********DESCRIBE*****************<br/>";
$stmt = oci_parse($conn, "  select column_name , data_type , 
   data_length  
from all_tab_columns 
where table_name = 'I_TMPCOBRANZA' ");
oci_execute($stmt, OCI_DEFAULT);
echo "<table border='1'>\n";
$i = 1;

while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
    var_dump($row);

    $i++;
}*/

