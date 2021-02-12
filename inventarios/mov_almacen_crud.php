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

			$objMovimientoAlmacenModel 		= new MovimientoAlmacenCRUDModel();
			$objMovimientoAlmacenTemplate 	= new MovimientoAlmacenCRUDTemplate();

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
			?>
			</div>
   		</section>
    </body>
    <?php include "../footer.php"; ?>
    <script type="text/javascript" src="/sistemaweb/inventarios/js/mov_almacen_crud.js?ver=5.3"></script>
</html>
