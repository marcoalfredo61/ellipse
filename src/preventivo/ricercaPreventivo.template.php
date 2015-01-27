<?php

require_once 'preventivo.abstract.class.php';

class ricercaPreventivoTemplate extends preventivoAbstract {

	private static $filtri = "/preventivo/ricercaPreventivo.filtri.html";
	private static $risultatiTesta = "/preventivo/ricercaPreventivo.risultati.testata.html";
	private static $risultatiCorpo = "/preventivo/ricercaPreventivo.risultati.corpo.html";
	private static $risultatiPiede = "/preventivo/ricercaPreventivo.risultati.piede.html";

	function __construct() {
		self::$root = $_SERVER['DOCUMENT_ROOT'];
	}

	// template ------------------------------------------------
	
	public function inizializzaPagina() {}
	
	public function displayFiltri() {

		require_once 'utility.class.php';
		
		// Template ----------------------------------
		
		$utility = new utility();
		$array = $utility->getConfig();
		
		$filtri = self::$root . $array['template'] . self::$filtri;
				
		$replace = array(
				'%idPaziente%' => $this->getIdpaziente(),
				'%idListino%' => $this->getIdlistino(),
				'%cognomeRicerca%' => $this->getCognomeRicerca(),
				'%cognome%' => $this->getCognome(),
				'%nome%' => $this->getNome(),
				'%datanascita%' => $this->getDataNascita()
		);
		
		echo $utility->tailFile($utility->getTemplate($filtri), $replace);		
	}

	public function displayRisultati() {

		require_once 'database.class.php';
		require_once 'utility.class.php';
		
		// Template ----------------------------------
		
		$utility = new utility();
		$array = $utility->getConfig();
		
		$risultatiTesta = self::$root . $array['template'] . self::$risultatiTesta;
		$risultatiCorpo = self::$root . $array['template'] . self::$risultatiCorpo;
		$risultatiPiede = self::$root . $array['template'] . self::$risultatiPiede;
		$messaggioInfo = self::$root . $array['template'] . self::$messaggioInfo;
		$messaggioErrore = self::$root . $array['template'] . self::$messaggioErrore;
		
		// Gestione del messaggio -------------------
		
		$numPreventivi = $this->getNumeroPreventiviTrovati();
		
		if ($numPreventivi > 0) {
			if ($numPreventivi > 1) {
				$text1 = "%ml.trovati% "; $text2 = " %ml.preventivi%";
			} else {
				$text1 = "%ml.trovato% "; $text2 = " %ml.preventivo%";
			}
				
			$text0 = $this->getMessaggio();
			if ($text0 != "") {$text0 = $text0 . " - ";};
		
			$replace = array('%messaggio%' => $text0 . $text1 . $numVisite . $text2);
			$template = $utility->tailFile($utility->getTemplate($messaggioInfo), $replace);
				
			echo $utility->tailTemplate($template);
		
			// Prepara la tabella dei risultati della ricerca
			echo $utility->getTemplate($risultatiTesta);
		
			$templateRiga = $utility->getTemplate($risultatiCorpo);
			$preventiviTrovati = $this->getPreventiviTrovati();
						
			$rowcounter = 0;
			$oggi = date('d/m/Y');
				
			foreach(pg_fetch_all($preventiviTrovati) as $row) {
		
				if (trim($row['tipopreventivo']) == 'P') {
					
					if ($row['stato'] == "01") $class = "class='parentAccettato'";
					else $class = "class='parent'";
					
					$id = "id='" . trim($row['idpreventivo']) . "'";
					$idpreventivo = trim($row['idpreventivo']);
					$idsottopreventivo = "";
				}
				elseif (trim($row['tipopreventivo']) == 'S') {
					$class = "class='child-" . trim($row['idpreventivo']) . "'";

					if ($row['stato'] == "01") $id = "id='childAccettato'";
					else $id = "id='child'";
					
					$idpreventivo = "";
					$idsottopreventivo = trim($row['idsottopreventivo']);
				}
		
				// BOTTONE CANCELLA -----------------------------------------------
				// nasconde il bottone cancella paziente se ha figli legati
				// solo nel caso di paziente provvisorio compare il bottone anche se ha figli  (delete cascade su db)
		
				$bottoneCancella = "<a class='tooltip' href='cancellaPreventivoFacade.class.php?modo=start&idPaziente=" . stripslashes($row['idpaziente']) . "&idListino=" . stripslashes($row['idlistino']) . "&idPreventivo=" . stripslashes($row['idpreventivo']) . "&datainserimento=" . stripslashes($row['datainserimento']) . "&stato=" . stripslashes($row['stato']) . "&cognRic=" . $this->getCognomeRicerca() . "&cognome=" . $this->getCognome() . "&nome=" . $this->getNome() . "&datanascita=" . $this->getDataNascita() . "'><li class='ui-state-default ui-corner-all' title='Cancella'><span class='ui-icon ui-icon-trash'></span></li></a>";
		
				if ($row['stato'] != "00") {
					$bottoneCancella = "";
				}
		
				// BOTTONE SPLIT -----------------------------------------------
				// nasconde il bottone split per i preventivi secondari
				
				$bottoneSplit = "<a class='tooltip' href='cancellaPreventivoFacade.class.php?modo=start&idPaziente=" . stripslashes($row['idpaziente']) . "&idListino=" . stripslashes($row['idlistino']) . "&idPreventivo=" . stripslashes($row['idpreventivo']) . "&datainserimento=" . stripslashes($row['datainserimento']) . "&stato=" . stripslashes($row['stato']) . "&cognRic=" . $this->getCognomeRicerca() . "&cognome=" . $this->getCognome() . "&nome=" . $this->getNome() . "&datanascita=" . $this->getDataNascita() . "'><li class='ui-state-default ui-corner-all' title='Crea un preventivo secondario'><span class='ui-icon ui-icon-newwin'></span></li></a>";

				if (trim($row['tipopreventivo']) == 'S') {
					$bottoneSplit = "";
				}				
				
				// Stati ---------------------------------------------------------
				if (trim($row['stato']) == '00') $stato = '%ml.proposto%';
				if (trim($row['stato']) == '01') $stato = '%ml.accettato%';
				if (trim($row['stato']) == '02') $stato = '%ml.parzialmenteaccettato%';
				if (trim($row['stato']) == '03') $stato = '%ml.splittato%';
				
				++$rowcounter;
		
				$replace = array(
						'%class%' => $class,
						'%id%' => $id,
						'%idpreventivo%' => $idpreventivo,
						'%idsottopreventivo%' => $idsottopreventivo,
						'%idpaziente%' => $this->getIdpaziente(),
						'%idlistino%' => $this->getIdlistino(),
						'%cognome%' => $this->getCognome(),
						'%nome%' => $this->getNome(),
						'%datanascita%' => $this->getDataNascita(),
						'%cognomeRicerca%' => $this->getCognomeRicerca(),
						'%datainserimento%' => stripslashes($row['datainserimento']),
						'%datamodifica%' => stripslashes($row['datamodifica']),
						'%bottoneCancella%' => $bottoneCancella,
						'%bottoneSplit%' => $bottoneSplit,
						'%stato%' => $stato
				);
		
				$riga = $templateRiga;				
				echo $utility->tailTemplate($utility->tailFile($riga, $replace));
			}
		}
		else {
		
			$text0 = $this->getMessaggio();
			if ($text0 != "") {$text0 = $text0 . " - ";};
		
			$replace = array('%messaggio%' => $text0 . '%ml.norisultati%');
			$template = $utility->tailFile($utility->getTemplate($messaggioErrore), $replace);
				
			echo $utility->tailTemplate($template);		
		}
		echo $utility->getTemplate($risultatiPiede);
	}
}

?>