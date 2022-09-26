<?php include "../header.php"; ?>
<link rel="stylesheet" href="/sistemaweb/contabilidad/css/bulma-tooltip.css">
</head>
<body class="bulma">
    <section class="section">
		<div class="bulma container">
		<?php
		date_default_timezone_set('UTC');

		include('/sistemaweb/include/mvc_sistemaweb.php');
		include('reportes/t_cuenta_contable_crud.php');
		include('reportes/m_cuenta_contable_crud.php');

		$objValeModel 		= new CuentaContableCRUDModel();
		$objValeTemplate 	= new CuentaContableCRUDTemplate();

		//Values for Template
		echo $objValeTemplate->Inicio(
			$objValeModel->ObtenerEstaciones(),
			date("d/m/Y", time()-(24*60*60))
		);
		?>
		</div>
	</section>
</body>
<?php include "../footer.php"; ?>
<script charset="utf-8" type="text/javascript" src="/sistemaweb/assets/js/init.js?ver=2.0"></script>
<script charset="utf-8" type="text/javascript" src="/sistemaweb/contabilidad/js/cuenta_contable_crud.js?ver=2.7"></script>
<script charset="utf-8" type="text/javascript" src="/sistemaweb/helper/js/autocomplete.js?ver=1.0"></script>
</html>