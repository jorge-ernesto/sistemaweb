<?php include "../header.php"; ?>
	</head>
    <body class="bulma">
    	<section class="section">
    		<div class="bulma container">
			<?php
			date_default_timezone_set('America/Lima');

			include('/sistemaweb/include/mvc_sistemaweb.php');
			include('reportes/t_mov_almacen_crud.php');
			include('reportes/m_mov_almacen_crud.php');

			include('/sistemaweb/assets/jgridpaginador.php');
			$objjqGridModel = new jqGridModel();

			$objMovimientoAlmacenModel 		= new MovimientoAlmacenCRUDModel();
			$objMovimientoAlmacenTemplate 	= new MovimientoAlmacenCRUDTemplate();

			/*var_dump($objMovimientoAlmacenModel->getTest());
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->ObtenerEstaciones());
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getFechaSistema());
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getTipoMovimientoInventario(trim(strip_tags($_GET['fm']))));
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getDocumentosRef());

			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getIgv());
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getCierreInventario());
			echo '<hr>';
			var_dump($objMovimientoAlmacenModel->getFechaSistemaInicio());

			echo '<hr>';
			var_dump(trim(strip_tags($_GET['fm'])));
			echo '<hr>';
			var_dump(trim(strip_tags($_GET['flg'])));*/


			var_dump($objMovimientoAlmacenModel->getTipoMovimientoInventario(trim(strip_tags($_GET['fm']))));
			echo '<hr>';
			//Values for Template
			echo $objMovimientoAlmacenTemplate->Inicio(
				$objMovimientoAlmacenModel->ObtenerEstaciones(),
				$objMovimientoAlmacenModel->getFechaSistema(),
				$objMovimientoAlmacenModel->getTipoMovimientoInventario(trim(strip_tags($_GET['fm']))),
				$objMovimientoAlmacenModel->getDocumentosRef(),
				$objMovimientoAlmacenModel->getIgv(),
				$objMovimientoAlmacenModel->getCierreInventario(),
				$objMovimientoAlmacenModel->getFechaSistemaInicio(),
				date("01/m/Y"),
				date("d/m/Y"),
				trim(strip_tags($_GET['fm'])),
				trim(strip_tags($_GET['flg'])),
				$save=false
			);

			var_dump($objMovimientoAlmacenModel->getTest());
			echo '<hr>';

			error_log('PRE Ejuecuta getListAll.');
			$response = $objMovimientoAlmacenModel->getListAll(
			array(
				'Nu_Almacen' => '',
				'Fe_Inicial' => '01/01/2018',
				'Fe_Final' => '09/01/2018',
				'Nu_Documento' => '',
				'No_Producto' => '',
				'No_Proveedor' => '',
				'Nu_Tipo_Movimiento_Inventario' => '01',
			)
			, $objjqGridModel);
			var_dump($response);

			$objMovimientoAlmacenTemplate->gridView($response);

			?>
			</div>
   		</section>
    </body>
    <?php include "../footer.php"; ?>
    <script type="text/javascript" src="/sistemaweb/inventarios/js/mov_almacen_crud.js"></script>
</html>
