<?php

require_once 'preventivo.abstract.class.php';

class modificaVocePreventivo extends preventivoAbstract {

	public static $azioneModificaVocePreventivo = "../preventivo/modificaVocePreventivoFacade.class.php?modo=go";

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

	// ------------------------------------------------

	public function start() {

		error_log("<<<<<<< Start >>>>>>> " . $_SERVER['PHP_SELF']);
		
		require_once 'vocePreventivo.template.php';
		require_once 'utility.class.php';

		$vocePreventivoTemplate = new vocePreventivoTemplate();
		$this->preparaPagina($vocePreventivoTemplate);

		// Compone la pagina
		include(self::$testata);
		$vocePreventivoTemplate->displayPagina();
		include(self::$piede);
	}


	public function go() {
	
		error_log("<<<<<<< Go >>>>>>> " . $_SERVER['PHP_SELF']);
	
		require_once 'vocePreventivo.template.php';
		require_once 'dettaglioPreventivo.class.php';

		$vocePreventivoTemplate = new vocePreventivoTemplate();
		
		if ($vocePreventivoTemplate->controlliLogici()) {
			
			$dettaglioPreventivo = new dettaglioPreventivo();
				
			if ($this->getIdPreventivo() != "") {

				if ($this->modificaPreventivoPrincipale()) $dettaglioPreventivo->setMessaggio("%ml.modificaVoceOk%");
				else $dettaglioPreventivo->setMessaggio("%ml.modificaVoceKo%");
			}
			elseif ($this->getIdSottoPreventivo() != "") {

				if ($this->modificaPreventivoSecondario()) $dettaglioPreventivo->setMessaggio("%ml.modificaVoceOk%");
				else $dettaglioPreventivo->setMessaggio("%ml.modificaVoceKo%");
			}

			$dettaglioPreventivo->start();
		}
		else {
			include(self::$testata);
			$vocePreventivoTemplate->displayPagina();
			include(self::$piede);
		}
	}


	public function preparaPagina($vocePreventivoTemplate) {
	
		$vocePreventivoTemplate->setAzione(self::$azioneModificaVocePreventivo);
		$vocePreventivoTemplate->setTestoAzione("%ml.modificaVocePreventivoTip%");
		$vocePreventivoTemplate->setTitoloPagina("%ml.modificaVocePreventivo" . $this->getTabella() . "%");
		
		$vocePreventivoTemplate->setTipDescrizioneVoce("%ml.descrizioneVoceTip%");
		$vocePreventivoTemplate->setTipPrezzo("%ml.prezzoTip%");	

		// Preleva i dati della voce selezionata
		
		require_once 'database.class.php';
		
		$db = new database();
		$db->beginTransaction();
		
		if ($this->getIdPreventivo() != "") {
			$vocePreventivoTrovata = $this->leggiVocePreventivoPrincipale($db, $this->getIdVocePreventivo());
		}
		elseif ($this->getIdSottoPreventivo() != "") {
			$vocePreventivoTrovata = $this->leggiVocePreventivoSecondario($db, $this->getIdVoceSottoPreventivo());
		}
		$db->commitTransaction();
		
		$vocePreventivoTemplate->setCodiceVoce($vocePreventivoTrovata[0]['codicevocelistino']);
		$vocePreventivoTemplate->setDescrizioneVoce(trim($vocePreventivoTrovata[0]['descrizione']));
		$vocePreventivoTemplate->setDescrizioneVoceListino(trim($vocePreventivoTrovata[0]['descrizionevocelistino']));
		$vocePreventivoTemplate->setPrezzo($vocePreventivoTrovata[0]['prezzo']);
	}
	
	private function modificaPreventivoPrincipale() {
	
		require_once 'utility.class.php';
		require_once 'database.class.php';
	
		$utility = new utility();
		$db = new database();
		$db->beginTransaction();
		
		if ($this->getDescrizioneVoce() != "") {
			if ($this->getDescrizioneVoce() != $this->getDescrizioneVoceListino()) $descrizione = "'" . $this->getDescrizioneVoce() . "'";
			else $descrizione = "null";
		}
		else $descrizione = "null";
		
		
		if ($this->aggiornaVocePreventivoPrincipale($db, $utility, $descrizione, $this->getPrezzo(), $this->getIdVocePreventivo())) {
			$db->commitTransaction();
			return TRUE;
		}
		return FALSE;
	}

	private function modificaPreventivoSecondario() {
	
		require_once 'utility.class.php';
		require_once 'database.class.php';
	
		$utility = new utility();
		$db = new database();
		$db->beginTransaction();
	
		if ($this->getDescrizioneVoce() != "") {
			if ($this->getDescrizioneVoce() != $this->getDescrizioneVoceListino()) $descrizione = "'" . $this->getDescrizioneVoce() . "'";
			else $descrizione = "null";
		}
		else $descrizione = "null";
	
	
		if ($this->aggiornaVocePreventivoSecondario($db, $utility, $descrizione, $this->getPrezzo(), $this->getIdVoceSottoPreventivo())) {
			$db->commitTransaction();
			return TRUE;
		}
		return FALSE;
	}
}
	
?>