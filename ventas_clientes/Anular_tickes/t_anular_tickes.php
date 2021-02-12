<?php

class AnularTickesTemplate extends Template {

	function titulo() {
        	return '<h2 align="center"><b>Anular Ticket</b></h2>';
	}

	function FormularioPrincipal($estaciones) { ?>

	<div style="text-align: center;position: relative;" id="id_nuevo_registro_view">
        <div><h3 style="color: #336699; text-align: center;">Anular Ticket</h3></div>
	<table style="text-align: left;position: relative;margin: 5px auto;">
	    	<tr>
			<td align="right">Almacen: </td>
		    	<td>
			    <select id="nualmacen">
				    <option value="T">Seleccionar...</option>
				    <?php
					foreach($estaciones as $value){
						echo "<option value='" . $value['nualmacen'] . "'>". $value['noalmacen'] . "</option>";
					}
				    ?>
			    </select>
			</td>
		</tr>
                <tr>

			<td align="right">Fecha:</td>
			<td><input maxlength="10" size="12" type="text" name="fecha_inicial" id="fecha_inicial" class="fecha_formato" placeholder="Ingresar Fecha" required/></td>

		</tr>

                <tr>

                    	<td align="right">Nro. Ticket: </td>
                    	<td><input type="text" id="txtnum_tickes" name="txtnum_tickes" maxlength="14" size="14" placeholder="Ingresar # Ticket" required/> </td>

                </tr>

                <tr>
               		<td align="right">Nro. Caja: </td>
			<td id="cajas">
				<select id="txtnum_caja">
				</select>
				<div id="tab_cajas" style="font-size:1.2em; color:red;"></div>
			</td>

		<tr>

        	       	<td align="right">Turno: </td>
			<td id="turno_final">
				<select id="txtnum_turno">
				</select>
				<div id="tab_turnos" style="font-size:1.2em; color:red;"></div>
			</td>

                </tr>

		<tr>
			<td align="right">Tipo Movimiento: </td>
			<td colspan="3" align="left">
			<select id="txtnum_tm">
				<option value='V'>Venta</option>
				<option value='A'>Extorno</option>
				<option value='D'>Devolucion</option>
			</select>
			</td>
		</tr>

		<tr>
			<td align="right">Tipo Documento: </td>
			<td colspan="3" align="left">
			<select id="txtnum_td">
				<option value='B'>Ticket - Boleta</option>
				<option value='F'>Ticket - Factura</option>
				<option value='N'>Ticket - Nota de Despacho</option>
				<option value='A'>Ticket - Afericion</option>
			</select>
			</td>
		</tr>

		<tr>
			<td align="right">Tipo Venta: </td>
			<td colspan="3" align="left">
			<select id="txtnum_tv">
				<option value='C'>Combustible</option>
				<option value='M'>Market</option>
			</select>
			</td>
		</tr>

                <tr>
                    <td>&nbsp;</td>
                    
                     </td>
                </tr>
                <tr>
                    <td colspan="3" style='text-align: center;'><button id="btnseleccionar"><img align="right" src="/sistemaweb/icons/gbuscar.png"/>Buscar</button> </td>
                    
                     </td>
                </tr>
            </table>
            <table id="table_fill" style="text-align: left;position: relative;margin: 5px auto;">
                
            </table>
            <table id="table_anular_final" style="text-align: left;position: relative;margin: 5px auto;">
                
            </table>




        </div>
    



        <?php
    }

    function FormularioPrincipalSegundario($estaciones, $caja, $operacion, $almacen, $fecha_actual, $serie,$tipo_cambio) {
        ?>

        <div ><h3 style="color: #336699; text-align: center;">Nuevo Recibo de Ingreso.</h3></div>
        <div id="cargardor" style="position: absolute;display: inline;z-index: 999;"><img src="movimientos/cg.gif" /></div>
        <table style="text-align: left;position: relative;margin: 5px auto;">
            <tr>
                <td>Sucursal: </td>
                <td> <select id="cmnsucursal_id" onchange="MostarNumeroRecibo(this)">
                        <option value="-1">Seleccione ..</option>
                        <?php
                        foreach ($estaciones as $key => $valor) {
                            if (strcmp($almacen, $valor[0]) == 0) {
                                echo "<option value='$valor[0]' selected>$valor[1]</option>";
                            } else {
                                echo "<option value='$valor[0]'>$valor[1]</option>";
                            }
                        }
                        ?>
                    </select> </td>
            </tr>
            <tr>
                <td>Recibo Nro  </td>
                <td> <input type='text' id='recibe_nro' class='fecha_formato' value="<?php echo $serie; ?>" /> </td>
            </tr>
            <tr>
                <td>Fecha  <input type="hidden" id="fecha_tmp" value="<?php echo $fecha_actual; ?>"/></td>
                <td> <input type='text' id='fecha_mostar' class='fecha_formato' /> </td>
            </tr>
            <tr>
                <td>Tipo Cambio  </td>
                <td> <input type='text' id='txttipo_cambio' class='fecha_formato' value="<?php echo $tipo_cambio;?>"/> </td>
            </tr>



            <tr>
                <td>Caja:  </td>
                <td id="idcaja"> <select id="cmncaja_id">
                        <?php
                        foreach ($caja as $key => $valor) {
                            echo "<option value='$valor[0]'>$valor[1]</option>";
                        }
                        ?>
                    </select></td>
            </tr>

            <tr>
                <td>Operacion:  </td>
                <td id="idoperaion">  <select id="cmnoperacion_id" onchange="SelecionararAyuda(this)">
                        <option value='-' selected>Seleccione..</option>
                        <?php
                        foreach ($operacion as $key => $valor) {
                            echo "<option value='$valor[0]' mostarayuda='$valor[2]'>$valor[1]</option>";
                        }
                        ?>
                    </select></td>
            </tr>


            <tr>
                <td>Observacion  </td>
                <td> <input type='text' id='id_observacion' class='fecha_formato' style="width: 250px;"/></td>
            </tr>
            <tr class="ayuda_clientes_id" style="display: none;">
                <td>Cliente  </td>
                <td> <input type='text' id='id_cliente_auto' class='fecha_formato' style="width: 250px;"></td>
            </tr>
            <tr class="ayuda_clientes_id" style="display: none;">
                <td>Ruc cliente  </td>
                <td> <input type='text' id='id_cliente_auto_ruc' class='fecha_formato' /></td>
            </tr>

            <tr >
                <td><input type="hidden" id="tipo_accion" value="-" /> 
                    <button id="btnbuscar"><img align="right" src="/sistemaweb/images/search.png"/>Documentos</button></td>
                <td><button  onclick="irhome()"><img align="right" src="/sistemaweb/icons/atra.gif">Regresar</button> </td>
            </tr>



        </table>


        <?php
    }

 

}
?>

