<?php
/*
  Fecha de creacion     : Marzo 8, 2012, 4: 00 PM
  Autor                 : Nestor Hernandez Loli
  Fecha de modificacion :
  Modificado por        :

  Ventana para buscar un artículo
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Buscar articulo</title>
        <script type="text/javascript">
            
            /*Permite pasar el valor seleccionado del artículo a la ventana anterior
             *que invocó esta ventana modal
             **/
            function pasarValor(){
                var combo = document.getElementById("articulos");
                var valor = combo.value;
                var descrip = combo.options[combo.selectedIndex].text;

                var form = document.getElementsByTagName("form")[0];
                opener.document.getElementById("art_codigo").value = valor;
                opener.document.getElementById("art_descrip").innerHTML = descrip;
              
                //form.submit();
                window.close();
            }
        </script>
        <style type="text/css">
            .fila {
                margin: .3em 0;   
            }
            .separador {
                margin-right: 4px;
            }
            .form2 {
                font-size: 12px;
                font-family: Arial, helvetica, sans-serif;
            }
            .combo {
                width: 350px;
            }
        </style>
    </head>
    <body>

        <?php
        include_once '/sistemaweb/start.php';
        include_once 'm_pos_descuento_ruc.php';
        $model = new PosDescuentoRucModel();
        $checkTipoCodigo = "";
        $checkTipoDescripcion = "";
        $combo = "";
        if (isset($_REQUEST["buscar"])) {
            $checkTipoCodigo = ($_REQUEST["tipoBuscar"] == "0") ? " checked = 'checked' " : "";
            $checkTipoDescripcion = ($_REQUEST["tipoBuscar"] == "1") ? " checked = 'checked' " : "";
            $array = array();
            if ($_REQUEST["tipoBuscar"] == "0") {
                $array = $model->buscarArticulosPorCodigo($_REQUEST["valor"]);
            } else {
                $array = $model->buscarArticulosPorDescripcion($_REQUEST["valor"]);
            }
            foreach ($array as $articulo) {
                $combo .= "<option value = '" . $articulo["art_codigo"] . "'>" . trim($articulo["art_codigo"]) . " - " . htmlentities($articulo["art_descripcion"])
                        . "</option>";
            }
        } else {
            //Si es la primera vez que se ejecuta el script entonces el radio 
            //descripcion esta seleccionado
            $checkTipoDescripcion = " checked = 'checked'";
        }
        ?>
        <form class ="form2" method="post">
            <div class="fila">
                Buscar articulo por:
            </div>
            <div class="fila">
                <input type="radio" name ="tipoBuscar"  <?php echo $checkTipoCodigo; ?> class ="separador" value="0"/>
                Codigo
                <input type="radio" name ="tipoBuscar"  <?php echo $checkTipoDescripcion; ?> class ="separador" value="1"/>
                Descripcion
            </div>
            <div class="fila">
                <label class="separador">Ingresar</label>
                <input type="text" name="valor"  value ="<?php echo $_REQUEST["valor"]; ?>"/>
                <input type="submit" value="Buscar" name="buscar" />
            </div>
            <div class="fila">
                <select name="articulos" id="articulos" size="5" class="combo">
                    <?php echo $combo; ?>
                </select>
            </div>
            <div class="fila">
                <input type="button" onclick="pasarValor()" value="Seleccionar" />
            </div>
        </form>
    </body>
</html>
