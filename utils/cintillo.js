function mostrarCintillo(num_mov,tip_mov,fecha,compra){
	var url = '/sistemaweb/inventarios/inv_cintillo.php?num_mov='+num_mov+'&tip_mov='+tip_mov+'&datec='+fecha+'&compra='+compra;
	window.open(url,'cintillo','width=1000,height=770,scrollbars=yes,menubar=no,left=100,top=20');	
}

function mostrarCintilloGuias(num_mov,tip_mov){ 
	var url = '/sistemaweb/inventarios/inv_guias_cintillo.php?num_mov='+num_mov+'&tip_mov='+tip_mov;
	window.open(url,'cintillo','width=600,height=800,scrollbars=yes,menubar=no,left=100,top=20');	
}
