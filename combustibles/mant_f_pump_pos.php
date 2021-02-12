
<?php
/*
  Fecha de creacion     : Marzo 6, 2012, 11:04 AM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :
  Mantenimiento de la tabla f_pump_pos
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <title>Lados por punto de venta</title>  
        <link rel="stylesheet" href="/sistemaweb/css/mantenimientos.css" type="text/css" />
        <script type="text/javascript" src="/sistemaweb/images/stm31.js"></script>
        <script type="text/javascript"  src="/sistemaweb/combustibles/js/mant_f_pump_pos.js"></script>  
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
        <iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.NEW_POS_LADOS" frameborder="1" width="10" height="10"></iframe>
    </body>
</html>
