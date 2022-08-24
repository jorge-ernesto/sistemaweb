<?php
class jqGridModel {
	public $page     = 0;
	public $total    = 0;
	public $records  = 0;
	public $start    = 0;
	public $limit    = 0;

	public $sord     = '';
	public $rows     = array();

	public function Config($count, $rows = 30, $pagelimit = 3){

		$total_pages		= $count > 0 ? ceil($count / $rows) : 0;
		$this->start		= $rows * (empty($_REQUEST['page']) ? $rows : $_REQUEST['page']) - $rows;
		$this->limit		= $rows;
		$this->Pagelimit	= $pagelimit;//De cuanto en cuanto se van a mostrar las paginas en el paginador
		
		$this->page    = (empty($_REQUEST['page']) ? $rows : $_REQUEST['page']);
		$this->total   = $total_pages;
		$this->records = $count;

		return array(
			'start' => $this->start,
			'limit' => $this->limit,
			'Pagelimit' => $this->Pagelimit
		);
	}

	public function DataSource($data){
		$this->rows = $data;
		return array(
			'rows' => $this->rows,
		);	
	}
}