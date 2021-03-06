<?php

require_once 'gestionePaziente.abstract.class.php';

class ricercaPaziente extends gestionePazienteAbstract {

	private static $queryRicercaPaziente = "/paziente/ricercaPaziente.sql";

	function __construct() {
		self::$root = $_SERVER['DOCUMENT_ROOT'];
	}

	public function start() {

		require_once 'ricercaPaziente.template.php';
		require_once 'utility.class.php';

		// Template
		$utility = new utility();
		$array = $utility->getConfig();

		$testata = self::$root . $array['testataPagina'];
		$piede = self::$root . $array['piedePagina'];		

		$ricercaPazienteTemplate = new ricercaPazienteTemplate();
		
		// compone la pagina
		include($testata);
		$ricercaPazienteTemplate->displayFiltri();
		include($piede);
	}
	
	public function go() {

		require_once 'ricercaPaziente.template.php';
		require_once 'utility.class.php';

		// Template
		$utility = new utility();
		$array = $utility->getConfig();

		$testata = self::$root . $array['testataPagina'];
		$piede = self::$root . $array['piedePagina'];		
		
		$ricercaPazienteTemplate = new ricercaPazienteTemplate();
	
		// cognome dalla POST
		if ($this->getCognomeRicerca() != "")
			$ricercaPazienteTemplate->setCognome($this->getCognomeRicerca());
		else
			$ricercaPazienteTemplate->setCognome(ucwords($_POST["cognome"]));

		// Il messaggio		
		$ricercaPazienteTemplate->setMessaggio($this->getMessaggio());
		
		if ($this->ricerca($ricercaPazienteTemplate)) {

			// compone la pagina
			include($testata);
			$ricercaPazienteTemplate->displayFiltri();
			$ricercaPazienteTemplate->displayRisultati();		
			include($piede);
		}	
	}
		
	private function controlli() {
	
		$esito = True;
		
		return $esito; 	
	}
	
	private function ricerca($ricercaPazienteTemplate) {

		require_once 'database.class.php';

		$cognome = $ricercaPazienteTemplate->getCognome();
		$esito = TRUE;

		// carica e ritaglia il comando sql da lanciare
		
		$replace = array('%cognome%' => $ricercaPazienteTemplate->getCognome());

		$utility = new utility();
		$array = $utility->getConfig();
		$sqlTemplate = self::$root . $array['query'] . self::$queryRicercaPaziente;

		$sql = $utility->tailFile($utility->getTemplate($sqlTemplate), $replace);

		// esegue la query
		
		$db = new database();
		$result = $db->getData($sql);
		
		$ricercaPazienteTemplate->setNumeroPazientiTrovati(pg_num_rows($result));	
		$ricercaPazienteTemplate->setPazientiTrovati($result);
		
		return $esito;	
	}
}
?>

	
