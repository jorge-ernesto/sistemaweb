
<?php
/*
  Fecha de creacion     : Marzo 7, 2012, 5:00 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :
  Mantenimiento de la tabla pos_descuento_ruc
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <title>Descuentos de RUC para facturas</title>
        <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">
        <script type="text/javascript" src="/sistemaweb/images/stm31.js"></script>
        <link rel="stylesheet" href="/sistemaweb/css/mantenimientos.css" type="text/css" />
        <script type="text/javascript"  src="/sistemaweb/combustibles/js/mant_pos_descuento_ruc.js"></script>  
        <script type="text/javascript" language="JavaScript1.2" src="/sistemaweb/ventas_clientes/js/validacion.js"></script>
        <style type="text/css">
            .fila {
                margin: .3em 0;   
            }
            .separador {
                margin-right: 4px;
            }

        </style>
    </head>
    <body>
        <?php include "../menu_princ.php"; ?>
        <div id="content">
            <script  type="text/javascript" src="/sistemaweb/js/calendario.js"></script>
            <script  type="text/javascript" src="/sistemaweb/js/overlib_mini.js"></script>
            <div id="content_title">&nbsp;</div>
            <div id="content_body">&nbsp;</div>
            <div id="content_footer">&nbsp;</div>
        </div>
        <div id="footer">&nbsp;</div>
        <iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.POS_DESCUENTO_RUC" frameborder="1" width="10" height="10"></iframe>
    </body>
</html>
