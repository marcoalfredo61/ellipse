SELECT DISTINCT

	substring(vocesottopreventivo.nomecampoform, 0, position(';' in vocesottopreventivo.nomecampoform)) as nomecomboform,
	vocesottopreventivo.codicevocelistino

FROM paziente.preventivo as preventivo

	INNER JOIN paziente.paziente as paziente
		ON paziente.idpaziente = preventivo.idpaziente
	
	LEFT OUTER JOIN paziente.sottopreventivo as sottopreventivo
		on sottopreventivo.idpreventivo = preventivo.idpreventivo
	
	LEFT OUTER JOIN paziente.vocesottopreventivo as vocesottopreventivo
		on vocesottopreventivo.idsottopreventivo = sottopreventivo.idsottopreventivo
	
	INNER JOIN paziente.vocelistino as vocelistino
		ON  vocelistino.idlistino = paziente.idlistino
		
	INNER JOIN paziente.voce as voce
		ON  voce.idvoce = vocelistino.idvocelistino
		AND voce.codice = vocesottopreventivo.codicevocelistino
	
WHERE paziente.idpaziente = %idpaziente%
  AND vocesottopreventivo.idsottopreventivo = %idsottopreventivo%
  AND vocesottopreventivo.nomeform = 'gruppi'
