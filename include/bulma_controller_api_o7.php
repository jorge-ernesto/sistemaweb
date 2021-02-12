<?php
/*
 #########################################################
#### INTEGRACIÓN FÁCIL ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# ESTE CÓDIGO FUNCIONA PARA LA VERSIÓN ONLINE
+++++++++++++++++++++++++++++++++++++++++++++++++++++++

#########################################################
#### FORMA DE TRABAJO ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# PASO 1: Conseguir un TOKEN para trabajar con OCS (enviar una solicitud a través de nuestra plataforma de soporte https://clientes.opensysperu.com o puede consultar al área de soporte (+51-1) 337-7813 opción 3).
# PASO 2: Generar un archivo en formato .JSON con una estructura que se detalla en este documento.
# PASO 3: Enviar el archivo generado a nuestra WEB SERVICE ONLINE según corresponda usando la RUTA y el TOKEN.
# PASO 4: Respuesta de OCS
+++++++++++++++++++++++++++++++++++++++++++++++++++++++

#########################################################
#### PASO 1: CONSEGUIR LA RUTA Y TOKEN ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# - Enviar una solicitud a través de nuestra plataforma de soporte en https://clientes.opensysperu.com
# - Ir la opción API (Integración).
# IMPORTANTE: Para que la opción API esté activada necesitas subir su incidencia o llámanos al teléfono: (+51-1) 337-7813 (opción 3).
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * 
 * 
 */

// RUTA para enviar items y movimientos de inventario
//$ruta = "http://172.18.8.6/sistemaweb/include/api_o7.php";//172.18.8.6 -> IP_SERVER PRODUCCION
$ruta = "http://" . $_POST['sIP'] . "/sistemaweb/include/api_o7.php";//172.18.8.6 -> IP_SERVER PRUEBAS

//TOKEN para enviar items y movimientos de inventario
$token = "5x2dhf83b96ecbcoj5l4ne67iatsvzyku7d3491wmbq50gp8r";

/*
#########################################################
####   PASO 2: GENERAR EL ARCHIVO PARA ENVIAR A OCS  ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# - MANUAL para archivo JSON solicitarlo por nuestra plataforma de soporte en https://clientes.opensysperu.com
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

if (
    $_POST['sOperation'] != 'generar_item'
    && $_POST['sOperation'] != 'consultar_item'
    && $_POST['sOperation'] != 'modificar_item'
    && $_POST['sOperation'] != 'modificar_lista_precio'
    && $_POST['sOperation'] != 'consultar_lista_precio'
    && $_POST['sOperation'] != 'generar_movimiento_inventario'
    && $_POST['sOperation'] != 'consultar_movimiento_inventario'
) {
    echo json_encode(array(
        'sStatus' => 'danger',
        'sMessage' => 'Operacion ' . $_POST['sOperation'] . ' no definida (controller)',
    ));
    exit();
}

if ( $_POST['sOperation'] == 'generar_item') {
    $arrData = array(
        "operacion"         => $_POST['sOperation'],
        "tipo_item"         => $_POST['arrDataItem']['tipo_item'],
        "id_item"           => $_POST['arrDataItem']['id_item'],
        "descripcion"       => $_POST['arrDataItem']['descripcion'],
        "descripcion_breve" => $_POST['arrDataItem']['descripcion_breve'],
        "id_tipo"           => $_POST['arrDataItem']['id_tipo'],
        "id_linea"          => $_POST['arrDataItem']['id_linea'],
        "id_marca"          => $_POST['arrDataItem']['id_marca'],
        "id_sku"            => $_POST['arrDataItem']['id_sku'],
        "id_unidad"         => $_POST['arrDataItem']['id_unidad'],
        "id_presentacion"   => $_POST['arrDataItem']['id_presentacion'],
        "id_ubicacion"      => $_POST['arrDataItem']['id_ubicacion'],
        "id_impuesto"       => $_POST['arrDataItem']['id_impuesto'],
        "estado"            => $_POST['arrDataItem']['estado'],
        "usuario"           => $_POST['arrDataItem']['usuario'],
        "lista_precios"     => array(
            array(
                "operacion"         => "generar_lista_precio",
                "id_lista_precio"   => $_POST['arrDataListaPrecio']['id_lista_precio'],
                "id_moneda"         => $_POST['arrDataListaPrecio']['id_moneda'],
                "precio_venta"      => $_POST['arrDataListaPrecio']['precio_venta'],
                "usuario"           => $_POST['arrDataListaPrecio']['usuario'],
            )
        ),
    );
}

if ( $_POST['sOperation'] == 'modificar_item') {
    $arrData = array(
        "operacion"         => $_POST['sOperation'],
        "tipo_item"         => $_POST['arrDataItem']['tipo_item'],
        "id_item"           => $_POST['arrDataItem']['id_item'],
        "descripcion"       => $_POST['arrDataItem']['descripcion'],
        "descripcion_breve" => $_POST['arrDataItem']['descripcion_breve'],
        "id_tipo"           => $_POST['arrDataItem']['id_tipo'],
        "id_linea"          => $_POST['arrDataItem']['id_linea'],
        "id_marca"          => $_POST['arrDataItem']['id_marca'],
        "id_sku"            => $_POST['arrDataItem']['id_sku'],
        "id_unidad"         => $_POST['arrDataItem']['id_unidad'],
        "id_presentacion"   => $_POST['arrDataItem']['id_presentacion'],
        "id_ubicacion"      => $_POST['arrDataItem']['id_ubicacion'],
        "id_impuesto"       => $_POST['arrDataItem']['id_impuesto'],
        "estado"            => $_POST['arrDataItem']['estado'],
        "usuario"           => $_POST['arrDataItem']['usuario'],
    );
}

if ( $_POST['sOperation'] == 'consultar_item') {
    $arrData = array(
        "operacion" => $_POST['sOperation'],
        "id_item"   => $_POST['sIDRegister'],
    );
}

if ( $_POST['sOperation'] == 'modificar_lista_precio') {
    $arrData = array(
        "operacion" => "modificar_lista_precio",
        "lista_precios" => array(
            array(
                "id_lista_precio"   => $_POST['arrDataListaPrecio']['id_lista_precio'],
                "id_item"           => $_POST['arrDataListaPrecio']['id_item'],
                "id_moneda"         => $_POST['arrDataListaPrecio']['id_moneda'],
                "precio_venta"      => $_POST['arrDataListaPrecio']['precio_venta'],
                "usuario"           => $_POST['arrDataListaPrecio']['usuario'],
            )
        )
    );
}

if ( $_POST['sOperation'] == 'consultar_lista_precio') {
    $arrData = array(
        "operacion" => $_POST['sOperation'],
        "id_item"   => $_POST['sIDRegister'],
    );
}

if ( $_POST['sOperation'] == 'generar_movimiento_inventario') {
    $arrData = array(
        "operacion" => "generar_movimiento_inventario",
        "id_tipo_movimiento" => "21",
        "tipo_naturaleza" => "1",
        "id_almacen_origen" => "420",
        "id_almacen_destino" => "001",
        "fecha_emision" => "2019-03-14 23:59:59",
        "tipo_identidad" => "P",
        "id_entidad" => "20445604373",
        "tipo_comprobante" => "10",
        "serie" => "F001",
        "numero" => "00000001",
        "usuario" => "ADMIN",
        "id_item" => '11620307',
        "cantidad" => 5732.1600,
        "costo_unitario" => 1.108198,
        "costo_promedio" => 1.108198,
        "total" => 6352.37,
        "conversion_glp" => array(
            array(
                "operacion" => "generar_conversion_glp",
                "kilos" => 5732.16,
                "gravedad_especifica" => 1.0000,
                "galones" => 0,
            )
        )
    );

    /*
    $arrData = array(
        "operacion" => "generar_movimiento_inventario",
        "id_tipo_movimiento" => "21",
        "tipo_naturaleza" => "1",
        "id_almacen_origen" => "420",
        "id_almacen_destino" => "001",
        "fecha_emision" => "2019-03-14 23:59:59",
        "tipo_identidad" => "P",
        "id_entidad" => "20445604373",
        "tipo_comprobante" => "10",
        "serie" => "F001",
        "numero" => "00000001",
        "usuario" => "ADMIN",
        "items" => array(
            array(
                "id_item" => '11620307',
                "cantidad" => 5732.1600,
                "costo_unitario" => 1.108198,
                "costo_promedio" => 1.108198,
                "total" => 6352.37,
            ),
            array(
                "id_item" => '11620302',
                "cantidad" => 5732.1600,
                "costo_unitario" => 1.108198,
                "costo_promedio" => 1.108198,
                "total" => 6352.37,
            ),
            array(
                "id_item" => '11620303',
                "cantidad" => 5732.1600,
                "costo_unitario" => 1.108198,
                "costo_promedio" => 1.108198,
                "total" => 6352.37,
            ),
        ),
        "conversion_glp" => array(
            array(
                "operacion" => "generar_conversion_glp",
                "kilos" => 5732.16,
                "gravedad_especifica" => 1.0000,
                "galones" => 0,
            )
        )
    );
    */
}

if ( $_POST['sOperation'] == 'consultar_movimiento_inventario') {
    $arrData = array(
        "operacion" => $_POST['sOperation'],
        "id"   => $_POST['sIDRegister'],
    );
}

/*
#########################################################
#### PASO 3: ENVIAR EL ARCHIVO A OCS ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# SI ESTÁS TRABAJANDO CON ARCHIVO JSON
# - Debes enviar en el HEADER de tu solicitud la siguiente lo siguiente:
# Authorization = Token token="9d8c7c1f6402687720eab85cd57a54f5a7a3fa163476bbcf3"
# Content-Type = application/json
# - Adjuntar en el CUERPO o BODY el archivo JSON
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
*/

$data_json = json_encode($arrData);

//Invocamos el servicio de OCS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ruta);
curl_setopt(
    $ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Token token="'.$token.'"',
    'Content-Type: application/json',
    )
);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$respuesta  = curl_exec($ch);
curl_close($ch);

/*
 #########################################################
#### PASO 4: LEER RESPUESTA DE OCS ####
+++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Recibirás una respuesta de OCS inmediatamente lo cual se debe leer, verificando que no haya errores.
# Escríbenos por nuestra plataforma de soporte en https://clientes.opensysperu.com o llámanos al teléfono: (+51-1) 337-7813 (opción 3)
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 */

echo $respuesta;

