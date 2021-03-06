SELECT
     'P' AS tipopreventivo ,
     preventivo.idpreventivo ,
     NULL AS idsottopreventivo ,
     to_char(preventivo.datainserimento , 'DD/MM/YYYY') AS datainserimento ,
     to_char(preventivo.datamodifica , 'DD/MM/YYYY') AS datamodifica ,
     preventivo.stato ,
     COALESCE(totalipreventivipaziente.totalepreventivo , 0) AS totalepreventivo
     
 FROM paziente.preventivo AS preventivo 
 
 LEFT OUTER JOIN (
        SELECT
            preventivo.idpreventivo ,
            SUM(vocepreventivo.prezzo) AS totalepreventivo    
          FROM paziente.preventivo AS preventivo  
            INNER JOIN paziente.vocepreventivo AS vocepreventivo 
            	ON vocepreventivo.idpreventivo = preventivo.idpreventivo             
         WHERE preventivo.idpaziente = %idpaziente%
         GROUP BY preventivo.idpreventivo
        
    	) AS totalipreventivipaziente
    
    	ON totalipreventivipaziente.idpreventivo = preventivo.idpreventivo
    	
    WHERE preventivo.idpaziente = %idpaziente%
    
UNION ALL
    
SELECT
	'S' AS tipopreventivo ,
    sottopreventivo.idpreventivo ,
    sottopreventivo.idsottopreventivo ,
    to_char(sottopreventivo.datainserimento ,'DD/MM/YYYY') AS datainserimento ,
    to_char(sottopreventivo.datamodifica , 'DD/MM/YYYY') AS datamodifica ,
    sottopreventivo.stato ,
    COALESCE(totalisottopreventivipaziente.totalesottopreventivo , 0) AS totalepreventivo
     
  FROM paziente.preventivo AS preventivo
    
	    INNER JOIN paziente.sottopreventivo AS sottopreventivo 
	    	ON sottopreventivo.idpreventivo = preventivo.idpreventivo
    
	    LEFT OUTER JOIN (
	        SELECT
	            sottopreventivo.idsottopreventivo ,
	            SUM(vocesottopreventivo.prezzo) AS totalesottopreventivo
	        FROM paziente.preventivo AS preventivo 
	            INNER JOIN paziente.sottopreventivo AS sottopreventivo 
	            	ON sottopreventivo.idpreventivo = preventivo.idpreventivo 
	            INNER JOIN paziente.vocesottopreventivo AS vocesottopreventivo 
	            	ON vocesottopreventivo.idsottopreventivo = sottopreventivo.idsottopreventivo 
	        WHERE preventivo.idpaziente = %idpaziente%
	        GROUP BY sottopreventivo.idsottopreventivo
	    	) AS totalisottopreventivipaziente
	    
		    ON totalisottopreventivipaziente.idsottopreventivo = sottopreventivo.idsottopreventivo

 WHERE preventivo.idpaziente = %idpaziente%
ORDER BY idpreventivo , tipopreventivo , idsottopreventivo , datainserimento



