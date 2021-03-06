<?php

require_once 'preventivo.abstract.class.php';

class cancellaAcconto extends preventivoAbstract {

	function __construct() {

		self::$root = $_SERVER['DOCUMENT_ROOT'];
	}

	public function start() {

		error_log("<<<<<<< Start >>>>>>> " . $_SERVER['PHP_SELF']);

		require_once 'modificaPagamento.class.php';

		if ($this->cancella()) {
			$modificaPagamento = new modificaPagamento();
			$modificaPagamento->setMessaggio('%ml.cancellaAccontoOk%' . ' - ' . '%ml.modPagamentoOk%');
			$modificaPagamento->start();				
		}
		else {
			$modificaPagamento = new modificaPagamento();
			$modificaPagamento->setMessaggio('%ml.cancellaAccontoKo%');
			$modificaPagamento->start();
		}
	}

	private function cancella() {

		require_once 'database.class.php';
		require_once 'utility.class.php';
		
		$utility = new utility();
		
		$db = new database();
		$db->beginTransaction();

		if ($this->getIdPreventivo() != "") {
			if ($this->cancellaAccontoPagamentoPreventivoPrincipale($db, $utility, $this->getIdAcconto())) {
				$db->commitTransaction();
				return TRUE;
			}
			return FALSE;
		}
		elseif ($this->getIdSottoPreventivo() != "") {
			if ($this->cancellaAccontoPagamentoPreventivoSecondario($db, $utility, $this->getIdAcconto())) {
				$db->commitTransaction();
				return TRUE;
			}
			return FALSE;
		}
	}
}

?>