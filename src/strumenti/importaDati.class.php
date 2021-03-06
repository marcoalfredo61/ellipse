<?php

require_once 'strumenti.abstract.class.php';

class importaDati extends strumentiAbstract {

	public static $azione = "../strumenti/importaDatiFacade.class.php?modo=go";	
	public static $sourceFolder = "/ellipse/src/strumenti/";
	
	function __construct() {

		self::$root = $_SERVER['DOCUMENT_ROOT'];
		
		require_once 'utility.class.php';
		
		$utility = new utility();
		$array = $utility->getConfig();
		
		self::$testata = self::$root . $array['testataPagina'];
		self::$piede = self::$root . $array['piedePagina'];
		self::$messaggioErrore = self::$root . $array['messaggioErrore'];
		self::$messaggioInfo = self::$root . $array['messaggioInfo'];
		
	}
	
	public function start() {

		error_log("<<<<<<< Start >>>>>>> " . $_SERVER['PHP_SELF']);
		
		require_once 'importaDati.template.php';
		require_once 'utility.class.php';
		
		// Template
		$utility = new utility();
		$array = $utility->getConfig();
		
		$importaTemplate = new importaTemplate();
		$importaTemplate->setImportaTemplate($this->preparaPagina($importaTemplate));
				
		// compone la pagina
		include(self::$testata);
		$importaTemplate->displayPagina();
		include(self::$piede);
	}
	
	public function go() {

		error_log("<<<<<<< Go >>>>>>> " . $_SERVER['PHP_SELF']);
		
		require_once 'database.class.php';
		require_once 'importaDati.template.php';
		require_once 'utility.class.php';
		
		// Template
		$utility = new utility();
		$array = $utility->getConfig();
		
		$importaTemplate = new importaTemplate();
		$importaTemplate->setImportaTemplate($this->preparaPagina($importaTemplate));
		
		include(self::$testata);
		
		$db = new database();
		$mess = array();
		
		$configs = $this->leggiConfigurazioni($db,$utility);
		if ($configs) {
			
			$rows = pg_fetch_all($configs);
			array_push($mess, "Configurazioni caricate, inizio a importare ..." . "<br>");
			$esito = TRUE;
			
			$this->setMessaggi($mess);
			
			foreach($rows as $row) {
				if ($row['stato'] == '00') {
					if (!$this->importa($db, $utility, $importaTemplate, $row)) {
						$esito = FALSE;
					}
				}
				else {
					array_push($mess, "Configurazione '" . $row['progressivo'] . "' classe '" . $row['classe'] . "' gi&agrave; elaborata, salto e proseguo ..." . "<br>");						
					$this->setMessaggi($mess);
				}				
			}
			
			$mess = $this->getMessaggi();
			array_push($mess, "Fine importazione dati!" . "<br>");									
			$this->setMessaggi($mess);
				
			$importaTemplate->setMessaggi($this->getMessaggi());
			
			if ($esito) {					
				$importaTemplate->displayPagina();
				$replace = array('%messaggio%' => '%ml.importaDatiOk%');
				$template = $utility->tailFile($utility->getTemplate(self::$messaggioInfo), $replace);
				echo $utility->tailTemplate($template);
			}
			else {
				$importaTemplate->displayPagina();
				$replace = array('%messaggio%' => '%ml.importaDatiKo%');
				$template = $utility->tailFile($utility->getTemplate(self::$messaggioErrore), $replace);
				echo $utility->tailTemplate($template);
			}				
		}
		else {
			$importaTemplate->displayPagina();
			$replace = array('%messaggio%' => '%ml.configurazioniKo%');
			$template = $utility->tailFile($utility->getTemplate(self::$messaggioErrore), $replace);
			echo $utility->tailTemplate($template);
		}		
		include(self::$piede);
	}
	
	public function leggiConfigurazioni($db, $utility) {
		
		$array = $utility->getConfig();
		$sqlTemplate = self::$root . $array['query'] . self::$queryConfigurazioni;
		
		$sql = $utility->getTemplate($sqlTemplate);
		$result = $db->getData($sql);
		
		return $result;
	}

	public function importa($db, $utility, $importaTemplate, $row) {

		$mess = $this->getMessaggi();
		
		array_push($mess, "Instanzio la classe '" . $row['classe'] . "' per importare il file '" . self::$root . $row['filepath'] . "'<br>");
		$this->setMessaggi($mess);
		
		$className = trim($row['classe']);
		$fileClass = self::$root . self::$sourceFolder . $className . '.class.php';
		
		if (file_exists($fileClass)) {
			
			require_once $className . '.class.php';
			
			if (class_exists($className)) {
				$instance = new $className();
				if ($instance->start($db, $utility, $row)) {
					return TRUE;
				}
				else {
					return FALSE;
				}
			}
			else {
				array_push($mess, "Il nome classe '" . $row['classe'] . "' non &egrave; definito, salto il passo e proseguo" . "'<br>");
				$this->setMessaggi($mess);
				return FALSE;
			}	
		}
		else {
			array_push($mess, "Il file '" . $row['classe'] . ".class.php' non esiste, salto il passo e proseguo" . "'<br>");
			$this->setMessaggi($mess);
			return FALSE;
		}	
	}
	
	public function preparaPagina($importaTemplate) {
		
		$importaTemplate->setAzione(self::$azione);		
		$importaTemplate->setTestoAzione("%ml.importaTip%");		
		$importaTemplate->setTitoloPagina("%ml.importaDati%");
		
		return $importaTemplate;		
	}
}

?>
