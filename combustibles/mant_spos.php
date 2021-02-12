<?php
/*
  Fecha de creacion     : Feb 24, 2012, 4:17:52 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :
  Mantenimiento de la tabla s_pos
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Puntos de venta</title>
        <!--  <link rel="stylesheet" href="/sistemaweb/css/sistemaweb.css" type="text/css">-->
        <link rel="stylesheet" href="/sistemaweb/css/mantenimientos.css" type="text/css" />
        <script type="text/javascript" src="/sistemaweb/images/stm31.js"></script>
        <script type="text/javascript"  src="/sistemaweb/combustibles/js/mant_spos.js"></script>
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
        <iframe id="control" name="control" scrolling="no" src="control.php?rqst=MAESTROS.NEW_POS_PUNTO_VENTA" frameborder="1" width="10" height="10"></iframe>
    </body>
</html>

