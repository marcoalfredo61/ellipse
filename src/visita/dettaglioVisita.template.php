<?php

require_once 'visita.abstract.class.php';

class riepilogoVociVisita extends visitaAbstract {

	private static $pagina = "/visita/visita.dettaglio.form.html";
	private static $paginaSingoli = "/visita/visita.dettagliosingoli.form.html";
	private static $paginaGruppi = "/visita/visita.dettagliogruppi.form.html";
	private static $paginaCure = "/visita/visita.dettagliocure.form.html";
	public static $vociVisitaDentiSingoli;	
	public static $vociVisitaGruppi;	
	public static $vociVisitaCureTab;	
	public static $vociVisitaCure;	
	public static $dettaglioVisita;

	public static $azione;
	public static $azioneTip;
	public static $labelBottone;
	
	//-----------------------------------------------------------------------------

	function __construct() {
		self::$root = $_SERVER['DOCUMENT_ROOT'];
	}

	// Setters --------------------------------------------------------------------

	public function setVociVisitaDentiSingoli($vociVisitaDentiSingoli) {
		self::$vociVisitaDentiSingoli = $vociVisitaDentiSingoli;
	}
	public function setVociVisitaGruppi($vociVisitaGruppi) {
		self::$vociVisitaGruppi = $vociVisitaGruppi;
	}
	public function setVociVisitaCure($vociVisitaCure) {
		self::$vociVisitaCure = $vociVisitaCure;
	}
	public function setDettaglioVisita($dettaglioVisita) {
		self::$dettaglioVisita = $dettaglioVisita;
	}
	public function setAzione($azione) {
		self::$azione = $azione;
	}
	public function setAzioneTip($azioneTip) {
		self::$azione = $azioneTip;
	}
	public function setLabelBottone($labelBottone) {
		self::$labelBottone = $labelBottone;
	}
	
	// Getters --------------------------------------------------------------------

	public function getVociVisitaDentiSingoli() {
		return self::$vociVisitaDentiSingoli;
	}
	public function getVociVisitaGruppi() {
		return self::$vociVisitaGruppi;
	}
	public function getVociVisitaCure() {
		return self::$vociVisitaCure;
	}
	public function getDettaglioVisita() {
		return self::$dettaglioVisita;
	}
	public function getAzione() {
		return self::$azione;
	}
	public function getAzioneTip() {
		return self::$azioneTip;
	}
	public function getLabelBottone() {
		return self::$labelBottone;
	}
	
	// ----------------------------------------------------------------------------
	
	public function displayPagina() {

		require_once 'database.class.php';
		require_once 'utility.class.php';
		
		// Template --------------------------------------------------------------

		$utility = new utility();
		$array = $utility->getConfig();

		$form = self::$root . $array['template'] . self::$pagina;
		$formSingoli = self::$root . $array['template'] . self::$paginaSingoli;
		$formGruppi = self::$root . $array['template'] . self::$paginaGruppi;
		$formCure = self::$root . $array['template'] . self::$paginaCure;
		
		$replace = array(
			'%titoloPagina%' => $this->getTitoloPagina(),
			'%bottonePreventivo%' => $this->preparaBottonePreventivo($this->getStato()),	
			'%cognomeRicerca%' => $this->getCognomeRicerca(),
			'%cognome%' => $this->getCognome(),
			'%nome%' => $this->getNome(),
			'%datanascita%' => $this->getDataNascita(),
			'%idVisita%' => $this->getIdVisita(),
			'%datainserimento%' => $this->getDataInserimento(),
			'%stato%' => $this->getStato(),
			'%idPaziente%' => $this->getIdPaziente(),
			'%idListino%' => $this->getIdListino()
		);

		// Preparo la tab per le voci riferite ai denti singoli -----------------------------------------------------
		
		if ($this->getVociVisitaDentiSingoli()) {	

			$riepilogoVociVisitaDentiSingoli = "";
			$replace['%riepilogoDentiSingoliTab%'] = "<li><a href='#tabs-1'>%ml.dentiSingoli%</a></li>"; 
			
			$denteBreak = "";
			foreach ($this->getVociVisitaDentiSingoli() as $row) {
				$dente = split("_", $row['nomecampoform']);
				if ($dente[1] != $denteBreak) {
					$riepilogoVociVisitaDentiSingoli .= "<tr><td>" . $dente[1] . "</td><td>" . $row['codicevocelistino'] . "</td><td>" . $row['descrizionevoce'] . "</td></tr>";
					$denteBreak = $dente[1];
				}
				else {
					$riepilogoVociVisitaDentiSingoli .= "<tr><td></td><td>" . $row['codicevocelistino'] . "</td><td>" . $row['descrizionevoce'] . "</td></tr>";
				}
			}
			
			$replace['%riepilogoDentiSingoli%'] = $riepilogoVociVisitaDentiSingoli;			
			$template = $utility->tailFile($utility->getTemplate($formSingoli), $replace);
			$replace['%riepilogoDentiSingoliDiv%'] = $template;
	
		}
		else {
			$replace['%riepilogoDentiSingoliTab%'] = ""; 
			$replace['%riepilogoDentiSingoliDiv%'] = ""; 
			$replace['%riepilogoDentiSingoli%'] = "";
		}

		// Preparo la tab per le voci riferite ai gruppi di denti --------------------------------------------------
		
		if ($this->getVociVisitaGruppi()) {	

			$riepilogoVociVisitaGruppi = "";
			$replace['%riepilogoGruppiTab%'] = "<li><a href='#tabs-2'>%ml.gruppi%</a></li>"; 

			$voceListinoBreak = "";
			
			foreach ($this->getVociVisitaGruppi() as $row) {

				$dente = split("_", $row['nomecampoform']);

				if (trim($row['codicevocelistino']) != trim($voceListinoBreak)) {
					$riepilogoVociVisitaGruppi .= "<tr><td>" . $row['codicevocelistino'] . "</td><td>" . $row['descrizionevoce'] . "</td><td>" . $dente[1] . "</td></tr>";
					$voceListinoBreak = $row['codicevocelistino'];
				}
				else {
					$riepilogoVociVisitaGruppi .= "<tr><td></td><td></td><td>" . $dente[1] . "</td></tr>";
				}
			}
			
			$replace['%riepilogoGruppi%'] = $riepilogoVociVisitaGruppi;			
			$template = $utility->tailFile($utility->getTemplate($formGruppi), $replace);
			$replace['%riepilogoGruppiDiv%'] = $template;
		}
		else {
			$replace['%riepilogoGruppiTab%'] = "";
			$replace['%riepilogoGruppiDiv%'] = "";
			$replace['%riepilogoGruppi%'] = "";
		}

		// Preparo la tab per le voci riferite alle cure generiche  --------------------------------------------------
		
		if ($this->getVociVisitaCure()) {	

			$riepilogoVociVisitaCure = "";
			$replace['%riepilogoCureTab%'] = "<li><a href='#tabs-3'>%ml.cure%</a></li>"; 
			
			foreach ($this->getVociVisitaCure() as $row) {
				$riepilogoVociVisitaCure .= "<tr><td>" . $row['codicevocelistino'] . "</td><td>" . $row['descrizionevoce'] . "</td></tr>";
			}
			
			$replace['%riepilogoCure%'] = $riepilogoVociVisitaCure;
			$template = $utility->tailFile($utility->getTemplate($formCure), $replace);
			$replace['%riepilogoCureDiv%'] = $template;
		}
		else {
			$replace['%riepilogoCureTab%'] = "";
			$replace['%riepilogoCureDiv%'] = "";
			$replace['%riepilogoCure%'] = "";
		}
		
		// display della pagina completata ------------------------------------------------------------------------
		$template = $utility->tailFile($utility->getTemplate($form), $replace);
		echo $utility->tailTemplate($template);
	}
	
	public function preparaBottonePreventivo($statoVisita) {

		$bottonePreventivo = "";

		/**
		 * SB 9/2/2015 : lascio la possibilità di preventivare la visita più volte
		 */
//		if ($statoVisita == 'In corso') {

			$bottonePreventivo = "<td>";
			$bottonePreventivo .= "<form class='tooltip' method='post' action='" . $this->getAzione() . "'>";
			$bottonePreventivo .= "<button class='button' title='" . $this->getAzioneTip() . "'>" . $this->getLabelBottone() . "</button>";
			$bottonePreventivo .= "<input type='hidden' name='cognRic' value='" . $this->getCognomeRicerca() . "'/>";			
			$bottonePreventivo .= "<input type='hidden' name='idPaziente' value='" . $this->getIdPaziente() . "'/>";
			$bottonePreventivo .= "<input type='hidden' name='idListino' value='" . $this->getIdListino() . "'/>";
			$bottonePreventivo .= "<input type='hidden' name='idVisita' value='" . $this->getIdVisita() . "'/>";
			$bottonePreventivo .= "<input type='hidden' name='cognome' value='" . $this->getCognome() . "'/>";
			$bottonePreventivo .= "<input type='hidden' name='nome' value='" . $this->getNome() . "'/>";
			$bottonePreventivo .= "<input type='hidden' name='datanascita' value='" . $this->getDataNascita() . "'/>";
			$bottonePreventivo .= "</form></td>";
//		}
		
		return $bottonePreventivo;
	}
}

?>
