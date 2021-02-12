<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Service OpenSoft</title>
    <link rel="stylesheet" href="/sistemaweb/assets/css/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.7.2/css/bulma.min.css">
    <script type="text/javascript" src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-1.12.4.js"></script>
    <script type="text/javascript" src="/sistemaweb/assets/js/jquery/jquery-ui.js"></script>
  </head>
  <body>
    <section class="section">
      <div class="container">
        <h1 class="title" align="center">Web Service ERP O7</h1>
        <h2 class="subtitle" align="center">Entorno de pruebas</h2>

        <div class="columns">
          <div class="column" align="center">
            <div class="field">                          
              <div class="select">
                <select id="cbo-ip">
                  <option value="172.18.8.6">Local</option>
                  <option value="192.168.0.191">Copetrol Central</option>
                  <option value="192.168.2.1">Copetrol Breña</option>
                  <option value="192.168.4.1">Copetrol Los Olivos</option>
                  <option value="192.168.5.1">Copetrol Trujillo 2</option>
                  <option value="192.168.6.1">Copetrol Chincha</option>
                  <option value="192.168.7.1">Copetrol Trujillo 1</option>
                  <option value="192.168.8.1">Copetrol Chimbote 1</option>
                  <option value="192.168.9.1">Copetrol Chimbote 2</option>
                  <option value="192.168.10.1">Copetrol Ate</option>
                  <option value="192.168.11.1">Copetrol SJL</option>
                  <option value="192.168.12.1">Copetrol VMT</option>
                  <option value="192.168.13.1">Copetrol Chiclayo 2</option>
                  <option value="192.168.14.1">Copetrol Bolivar</option>
                  <option value="192.168.15.1">Copetrol Chiclayo 1</option>
                  <option value="192.168.16.1">Copetrol Ica</option>
                  <option value="192.168.17.1">Copetrol Trujillo 3</option>
                  <option value="192.168.18.1">Copetrol Trujillo 4</option>
                  <option value="192.168.19.1">Copetrol Trujillo 5</option>
                  <option value="192.168.20.1">Copetrol Cañete</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="columns">
          <div class="column is-6">
            <nav class="panel">
              <p class="panel-heading">Lista de opciones</p>
              <a class="panel-block a-actions" data-action="item">
                <span class="panel-icon"><i class="fa fa-book" aria-hidden="true"></i></span>Items
              </a>
              <a class="panel-block a-actions" data-action="movimiento_inventario">
                <span class="panel-icon"><i class="fa fa-book" aria-hidden="true"></i></span>Movimientos de inventario
              </a>
              <a class="panel-block a-actions" data-action="precio">
                <span class="panel-icon"><i class="fa fa-book" aria-hidden="true"></i></span>Listas de precio
              </a>
            </nav>
          </div><!-- column is-6 left -->

          <div class="column is-6" id="div-action-item">
            <div class="tabs is-toggle is-fullwidth">
              <ul>
                <li class="is-active is-link li-add">
                  <a class="a-table-action-add">
                    <span class="icon is-small"><i class="fas fa-save" aria-hidden="true"></i></span>
                    <span>Insertar</span>
                  </a>
                </li>
                <li class="li-view">
                  <a class="a-table-action-view">
                    <span class="icon is-small"><i class="far fa-file-alt" aria-hidden="true"></i></span>
                    <span>Ver</span>
                  </a>
                </li>
              </ul>
            </div>

            <nav id="panel-add-table-item" class="panel">
              <p class="panel-heading text-center" align="center">Generar Item</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Tipo item" name="tipo_item">
                        </div>
                      </div>
                    </div>

                    <div class="column is-4">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" maxlength="13" placeholder="ID Item" name="id_item">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns">
                    <div class="column is-7">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Descripcion" name="descripcion">
                        </div>
                      </div>
                    </div>

                    <div class="column is-5">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Descripcion breve" name="descripcion_breve">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns">
                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Tipo" name="id_tipo">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Linea" name="id_linea">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Marca" name="id_marca">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID SKU" name="id_sku">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns">
                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Unidad" name="id_unidad">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Presentacion" name="id_presentacion">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Ubicacion" name="id_ubicacion">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Impuesto" name="id_impuesto">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns">
                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Estado" name="estado">
                        </div>
                      </div>
                    </div>

                    <div class="column is-4">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Usuario" name="usuario">
                        </div>
                      </div>
                    </div>

                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Lista precio" name="id_lista_precio">
                        </div>
                      </div>
                    </div>

                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Moneda" name="id_moneda">
                        </div>
                      </div>
                    </div>

                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Precio venta" name="precio_venta">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns div-log">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-save-action" data-action="generar_item">Save</a>
                    </div>
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-save-action" data-action="modificar_item">Update</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-add-item -->

            <nav id="panel-view-table-item" class="panel">
              <p class="panel-heading text-center">Items</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Item" name="id_item_view">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns div-log-view">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-view-action" data-action="consultar_item">Consultar</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-view-item -->
          </div><!-- /. column is-6 right -->

          <div class="column is-6" id="div-action-movimiento_inventario">
            <div class="tabs is-toggle is-fullwidth">
              <ul>
                <li class="is-active is-link li-add">
                  <a class="a-table-action-add">
                    <span class="icon is-small"><i class="fas fa-save" aria-hidden="true"></i></span>
                    <span>Insertar</span>
                  </a>
                </li>
                <li class="li-view">
                  <a class="a-table-action-view">
                    <span class="icon is-small"><i class="far fa-file-alt" aria-hidden="true"></i></span>
                    <span>Ver</span>
                  </a>
                </li>
              </ul>
            </div>

            <nav id="panel-add-table-movimiento_inventario" class="panel">
              <p class="panel-heading text-center" align="center">Generar Movimiento de Inventario</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Tipo item" name="tipo_item">
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="columns div-log">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-save-action" data-action="generar_movimiento_inventario">Save</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-add-movimiento_inventario -->

            <nav id="panel-view-table-movimiento_inventario" class="panel">
              <p class="panel-heading text-center">Movimientos de Inventario</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column">
                      <div class="field">
                        <div class="control">
                          <input type="text" class="input" id="txt-fe_emision" placeholder="Fecha Emisión">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns div-log-view">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-view-action" data-action="consultar_movimiento_inventario">Consultar</a>
                    </div>
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-view-action" data-action="generar_movimiento_inventario">Generar</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-view-movimiento_inventario -->
          </div><!-- /. div-movimiento_inventario column is-6 right -->

          <!-- /. div-precio column is-6 right -->
          <div class="column is-6" id="div-action-precio">
            <div class="tabs is-toggle is-fullwidth">
              <ul>
                <li class="is-active is-link li-upd">
                  <a class="a-table-action-upd">
                    <span class="icon is-small"><i class="fas fa-save" aria-hidden="true"></i></span>
                    <span>Insertar</span>
                  </a>
                </li>
                <li class="li-view">
                  <a class="a-table-action-view">
                    <span class="icon is-small"><i class="far fa-file-alt" aria-hidden="true"></i></span>
                    <span>Ver</span>
                  </a>
                </li>
              </ul>
            </div>

            <nav id="panel-upd-table-precio" class="panel">
              <p class="panel-heading text-center" align="center">Modificar Lista de precio</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column is-5">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" maxlength="13" placeholder="ID Item" name="id_item_modificar_lista_precio">
                        </div>
                      </div>
                    </div>

                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Lista precio" name="id_lista_precio_modificar_lista_precio">
                        </div>
                      </div>
                    </div>

                    <div class="column is-2">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="ID Moneda" name="id_moneda_modificar_lista_precio">
                        </div>
                      </div>
                    </div>

                    <div class="column is-3">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Precio venta" name="precio_venta_modificar_lista_precio">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns">
                    <div class="column is-12">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" placeholder="Usuario" name="usuario_modificar_lista_precio">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns div-log">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-save-action" data-action="modificar_lista_precio">Save</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-upd-precio -->

            <nav id="panel-view-table-precio" class="panel">
              <p class="panel-heading text-center">Listas de precios</p>
              <div class="panel-block">
                <div class="container">
                  <div class="columns">
                    <div class="column">
                      <div class="field">
                        <div class="control">
                          <input class="input" type="text" maxlength="13" placeholder="ID Item" name="id_precio_view_lista_precio">
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="columns div-log-view">
                    <div class="column">
                      <a class="button is-link is-fullwidth btn-view-action" data-action="consultar_lista_precio">Consultar</a>
                    </div>
                  </div>
                </div>
              </div>
            </nav><!-- /. nav-view-precio -->
          </div><!-- /. div-precio column is-6 right -->
        </div><!-- /. Class column -->
      </div><!-- /. Class container -->
    </section>
    <script charset="utf8" type="text/javascript">
      var $path_web_service = '/sistemaweb/include/bulma_controller_api_o7.php';
      var sStatus='', div_log='', div_data='';

      $(initPage);
      function initPage(){
        ocultarDiv();

        var fToday = new Date();
        var fYear = fToday.getFullYear();
        var fMonth = fToday.getMonth() + 1; //hoy es 0!
        var fDay = fToday.getDate();

        $.datepicker.regional['es'] = {
          closeText: 'Cerrar',
          prevText: '<Ant',
          nextText: 'Sig>',
          currentText: 'Hoy',
          monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
          monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
          dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
          dayNamesShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
          dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
          weekHeader: 'Sm',
          dateFormat: 'dd/mm/yy',
          firstDay: 1,
          isRTL: false,
          showMonthAfterYear: false,
          yearSuffix: ''
        };

        $.datepicker.setDefaults( $.datepicker.regional[ "es" ] );
        
        if (fMonth<10)
          fMonth = '0' + fMonth;
        var dActual = fDay + '/' + fMonth + '/' + fYear;

        $( "#txt-fe_emision" ).val(dActual);
        $( "#txt-fe_emision" ).datepicker({
          changeMonth: true,
          changeYear: true,
          maxDate: dActual,
        });
      }

      function ocultarDiv(){
        $( '#div-action-item' ).hide();
        $( '#div-action-movimiento_inventario' ).hide();
        $( '#div-action-precio' ).hide();
      }

      $( '.a-actions' ).on('click', actionAllProcess);
      function actionAllProcess(){
        ocultarDiv();

        // Remove before class
        $(this).addClass( 'is-active' ).siblings( '.is-active' ).removeClass( 'is-active' );

        if ( $( this ).data('action') == 'item' ) {
          $( '#div-action-item' ).show();
          $( '#div-action-item > div > ul > li.li-add > a' ).attr('data-action-add', 'generar_item');
          $( '#div-action-item > div > ul > li.li-view > a' ).attr('data-action-view', 'consultar_item');

          $( '#panel-view-table-item' ).hide();
        }

        if ( $( this ).data('action') == 'movimiento_inventario' ) {
          $( '#div-action-movimiento_inventario' ).show();
          $( '#div-action-movimiento_inventario > div > ul > li.li-add > a' ).attr('data-action-add', 'generar_movimiento_inventario');
          $( '#div-action-movimiento_inventario > div > ul > li.li-view > a' ).attr('data-action-view', 'consultar_movimiento_inventario');

          $( '#panel-view-table-movimiento_inventario' ).hide();
        }

        if ( $( this ).data('action') == 'precio' ) {
          $( '#div-action-precio' ).show();
          $( '#div-action-precio > div > ul > li.li-upd > a' ).attr('data-action-upd', 'modificar_lista_precio');
          $( '#div-action-precio > div > ul > li.li-view > a' ).attr('data-action-view', 'consultar_lista_precio');

          $( '#panel-view-table-precio' ).hide();
        }
      }

      $( '.a-table-action-add' ).on('click', actionAddProcess);
      function actionAddProcess(){
        $( '.li-add' ).addClass( 'is-active' );
        $( '.li-view' ).removeClass( 'is-active' );

        $( '.a-table-action-add' ).attr('disabled', true);
        $( '.a-table-action-add' ).addClass('is-loading')

        $( '#notification-add' ).remove();
        $( '#notification-view' ).remove();

        if ( $( this ).data('action-add') == 'generar_item' ) {
          $( '#panel-add-table-item' ).show();
          $( '#panel-view-table-item' ).hide();
        }

        if ( $( this ).data('action-add') == 'generar_movimiento_inventario' ) {
          $( '#panel-add-table-movimiento_inventario' ).show();
          $( '#panel-view-table-movimiento_inventario' ).hide();
        }
      }

      $( '.a-table-action-upd' ).on('click', actionUpdProcess);
      function actionUpdProcess(){
        $( '.li-upd' ).addClass( 'is-active' );
        $( '.li-add' ).removeClass( 'is-active' );
        $( '.li-view' ).removeClass( 'is-active' );

        $( '.a-table-action-upd' ).attr('disabled', true);
        $( '.a-table-action-upd' ).addClass('is-loading')

        $( '#notification-upd' ).remove();
        $( '#notification-add' ).remove();
        $( '#notification-view' ).remove();

        if ( $( this ).data('action-upd') == 'modificar_lista_precio' ) {
          $( '#panel-upd-table-precio' ).show();
          $( '#panel-view-table-precio' ).hide();
        }
      }

      $( '.btn-save-action' ).on('click', saveData)
      function saveData(){
        $( '.btn-save-action' ).attr('disabled', true);
        $( '.btn-save-action' ).addClass('is-loading');

        var sIP = $( '#cbo-ip' ).val();

        if ( $( this ).data('action') == 'generar_item' ) {
          var $_arrParameterPOST = {
            sIP: sIP,
            sOperation: "generar_item",
            arrDataItem: {
              tipo_item: $( '[name="tipo_item"]' ).val(),
              id_item: $( '[name="id_item"]' ).val(),
              descripcion: $( '[name="descripcion"]' ).val(),
              descripcion_breve: $( '[name="descripcion_breve"]' ).val(),
              id_tipo: $( '[name="id_tipo"]' ).val(),
              id_linea: $( '[name="id_linea"]' ).val(),
              id_marca: $( '[name="id_marca"]' ).val(),
              id_sku: $( '[name="id_sku"]' ).val(),
              id_unidad: $( '[name="id_unidad"]' ).val(),
              id_presentacion: $( '[name="id_presentacion"]' ).val(),
              id_ubicacion: $( '[name="id_ubicacion"]' ).val(),
              id_impuesto: $( '[name="id_impuesto"]' ).val(),
              estado: $( '[name="estado"]' ).val(),
              usuario: $( '[name="usuario"]' ).val(),
            },
            arrDataListaPrecio : {
              id_lista_precio: $( '[name="id_lista_precio"]' ).val(),
              id_moneda: $( '[name="id_moneda"]' ).val(),
              precio_venta: $( '[name="precio_venta"]' ).val(),
            }
          };
        } else if ( $( this ).data('action') == 'modificar_lista_precio' ) {
          var $_arrParameterPOST = {
            sIP: sIP,
            sOperation: "modificar_lista_precio",
            arrDataListaPrecio : {
              sOperation: "modificar_lista_precio",
              id_lista_precio: $( '[name="id_lista_precio_modificar_lista_precio"]' ).val(),
              id_item: $( '[name="id_item_modificar_lista_precio"]' ).val(),
              id_moneda: $( '[name="id_moneda_modificar_lista_precio"]' ).val(),
              precio_venta: $( '[name="precio_venta_modificar_lista_precio"]' ).val(),
              usuario: $( '[name="usuario_modificar_lista_precio"]' ).val(),
            }
          };
        } else if ( $( this ).data('action') == 'modificar_item' ) {
          var $_arrParameterPOST = {
            sIP: sIP,
            sOperation: "modificar_item",
            arrDataItem: {
              tipo_item: $( '[name="tipo_item"]' ).val(),
              id_item: $( '[name="id_item"]' ).val(),
              descripcion: $( '[name="descripcion"]' ).val(),
              descripcion_breve: $( '[name="descripcion_breve"]' ).val(),
              id_tipo: $( '[name="id_tipo"]' ).val(),
              id_linea: $( '[name="id_linea"]' ).val(),
              id_marca: $( '[name="id_marca"]' ).val(),
              id_sku: $( '[name="id_sku"]' ).val(),
              id_unidad: $( '[name="id_unidad"]' ).val(),
              id_presentacion: $( '[name="id_presentacion"]' ).val(),
              id_ubicacion: $( '[name="id_ubicacion"]' ).val(),
              id_impuesto: $( '[name="id_impuesto"]' ).val(),
              estado: $( '[name="estado"]' ).val(),
              usuario: $( '[name="usuario"]' ).val(),
            }
          };
        } else if ( $( this ).data('action') == 'generar_movimiento_inventario' ) {
          var $_arrParameterPOST = {
            sIP: sIP,
            sOperation: "generar_movimiento_inventario",
          }
        }
       
        $arrParameterPOST = $_arrParameterPOST;

        $url = 'http://' + sIP + $path_web_service;
        $.post( $url, $arrParameterPOST, function( response ) {
          sStatus = response.sStatus;
          if (response.sStatus == 'success'){
            sStatus = 'link';
          }

          $( '#notification-add' ).remove();

          div_log = '';
          div_log +=
          '<div id="notification-add" class="columns">'
            +'<div class="column text-center">'
             +'<div class="notification is-' + sStatus + '">' + response.sMessage + '</div>'
            +'</div>'
          +'</div>';

          $( '.btn-save-action' ).attr('disabled', false);
          $( '.btn-save-action' ).removeClass('is-loading');

          $( ".div-log" ).after( div_log );
        }, 'json')
        .fail(function() {
          $( '.btn-save-action' ).attr('disabled', false);
          $( '.btn-save-action' ).removeClass('is-loading');
        });
      }

      $( '.a-table-action-view' ).on('click', actionViewProcess);
      function actionViewProcess(){
        $( '.li-upd' ).removeClass( 'is-active' );
        $( '.li-add' ).removeClass( 'is-active' );
        $( '.li-view' ).addClass( 'is-active' );

        $( '.a-table-action-view' ).attr('disabled', true);
        $( '.a-table-action-view' ).addClass('is-loading');

        $( '#notification-upd' ).remove();
        $( '#notification-add' ).remove();
        $( '#notification-view' ).remove();

        if ( $( this ).data('action-view') == 'consultar_item' ) {
          $( '#panel-add-table-item' ).hide();
          $( '#panel-view-table-item' ).show();
        }

        if ( $( this ).data('action-view') == 'consultar_movimiento_inventario' ) {
          $( '#panel-add-table-movimiento_inventario' ).hide();
          $( '#panel-view-table-movimiento_inventario' ).show();
        }

        if ( $( this ).data('action-view') == 'consultar_lista_precio' ) {
          $( '#panel-upd-table-precio' ).hide();
          $( '#panel-view-table-precio' ).show();
        }
      };

      $( '.btn-view-action' ).on('click', viewData)
      function viewData(){
        $( '.btn-view-action' ).attr('disabled', true);
        $( '.btn-view-action' ).addClass('is-loading');


        if ( $( this ).data('action') == 'consultar_item' ) {
          listAll( $( '#cbo-ip' ).val(), $( this ).data('action'), $( '[name="id_item_view"]' ).val());
        }

        if ( $( this ).data('action') == 'consultar_movimiento_inventario' ) {
          listAll( $( '#cbo-ip' ).val(), $( this ).data('action'), $( '#txt-fe_emision' ).val());
        }

        if ( $( this ).data('action') == 'generar_movimiento_inventario' ) {
          listAll( $( '#cbo-ip' ).val(), $( this ).data('action'), $( '#txt-fe_emision' ).val());
        }

        if ( $( this ).data('action') == 'consultar_lista_precio' ) {
          listAll( $( '#cbo-ip' ).val(), $( this ).data('action'), $( '[name="id_precio_view_lista_precio"]' ).val());
        }
      }

      function listAll( sIP, sOperation, sIDRegister ){
        $( "#div-table" ).remove();

        var $arrParameterPOST = {
          sIP: sIP,
          sOperation: sOperation,
          sIDRegister: sIDRegister,
        };
        
        $url = 'http://' + sIP + $path_web_service;
        div_log = '';
        $.post( $url, $arrParameterPOST, function( response ) {
          sStatus = response.sStatus;
          if (response.sStatus == 'success'){
            sStatus = 'link';
          }

          div_log +=
          '<div id="notification-view" class="columns">'
            +'<div class="column text-center">'
             +'<div class="notification is-' + sStatus + '">' + response.sMessage;
              if (response.sStatus == 'success'){
                div_log += '<br>';
                for (key in response.arrData) {
                  div_log += '<br>Registros Nro. '+key+': <br>';
                  for (var i in Object.values(response.arrData[key])){
                    div_log +='<p>' + Object.values(response.arrData[key])[i] + '</p>'
                  }
                }
              }
             div_log +='</div>'
            +'</div>'
          +'</div>';

          $( '.a-table-action-view' ).attr('disabled', false);
          $( '.a-table-action-view' ).removeClass('is-loading');

          $( '.btn-view-action' ).attr('disabled', false);
          $( '.btn-view-action' ).removeClass('is-loading');

          $( ".div-log-view" ).after( div_log );
        }, 'json')
        .fail(function() {
          $( '.a-table-action-view' ).attr('disabled', false);
          $( '.a-table-action-view' ).removeClass('is-loading');

          $( '.btn-view-action' ).attr('disabled', false);
          $( '.btn-view-action' ).removeClass('is-loading');
        })
      }
    </script>
  </body>
</html>