<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.efacturas.ovh/pe/send/push/c2XHgMe6PpXwXpPIkd_W3R62aNBGuk7h',
  CURLOPT_POST => 1,
  // CURLOPT_TIMEOUT => 5,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/plain'
  ),
  CURLOPT_POSTFIELDS =>'09|T001|00000013|2021-06-28|-|1796|01|02|6|20602325831|ADELIT COMERCIALIZADORA Y SERVICIOS S.R.L.|6|20515229745|OPEN COMB SYSTEMS E.I.R.L.|2021-06-28|5.0000|KGM|1|7377777|BHQ-544
L|884116337911|1.0000|NIU|SERVIDOR DELL POWEREDGE T40, INTEL XEON E-2224G, 3.50 GHZ, 8GB DDR4, 1TB SATA
X|X0013|030101|CAR. PANAMERICANA KM. 15.5 APURIMAC - ABANCAY - ABANCAY
X|X0014|150136|JR. PROLONGACIÓN AYACUCHO 177 URB. SANTA EULALIA - SAN MIGUEL - LIMA - LIMA',
  CURLOPT_RETURNTRANSFER => true,
));

//recogemos la respuesta
$respuesta = curl_exec ($curl);
 
//o el error, por si falla
$error = curl_error($curl);
 
//y finalmente cerramos curl
curl_close($curl);

echo "<pre>";
print_r( array($respuesta, $error) );
echo "</pre>";

error_log(json_encode( array($respuesta, $error) ));

/*********************************************************************************************************/

/*
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.efacturas.ovh/pe/send/push/c2XHgMe6PpXwXpPIkd_W3R62aNBGuk7h',
  CURLOPT_POST => 1,
  // CURLOPT_TIMEOUT => 5,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: text/plain'
  ),
  CURLOPT_POSTFIELDS =>'09|T001|00000010|2021-06-21|-|1793|01|02|6|20506151547|ENERGIGAS S.A.C.|6|20100010721|AERO TRANSPORTE S A|2021-06-21|2.0000|KGM|1|7377777|123455
L|000069|1.0000|MTR|CABLE DE CONTROL 01 TWISTED PAIR 18AWG 300V PROTEGIDO PARA COMUNICACION DE TELEMEDICION OPW X MT
L|000038|1.0000|NIU|CABLE SERIAL DE IMPRESION PARA TERMINAL DE VENTA NEW POS TECH
X|X0013|150131|AV. REP ARGENTINA NRO. 1858 (AV REPUBLICA DE ARGENTINA N. 1858) LIMA - LIMA - LIMA
X|X0014|150136|JR. PROLONGACIÓN AYACUCHO 177 URB. SANTA EULALIA - SAN MIGUEL - LIMA - LIMA',
  CURLOPT_RETURNTRANSFER => true,
));

//recogemos la respuesta
$respuesta = curl_exec ($curl);
 
//o el error, por si falla
$error = curl_error($curl);
 
//y finalmente cerramos curl
curl_close($curl);

echo "<pre>";
print_r( array($respuesta, $error) );
echo "</pre>";

error_log(json_encode( array($respuesta, $error) ));
*/
