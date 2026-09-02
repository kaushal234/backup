-- MySQL function to get TOC Weight Factor
-- @param int toc ID
-- @param double DF Diligence Factor
-- @return double WF

DROP FUNCTION IF EXISTS fct_tld_mod_toc_getWFByID;

DELIMITER |

CREATE FUNCTION fct_tld_mod_toc_getWFByID(tocid INT, df DOUBLE) 
RETURNS DOUBLE
READS SQL DATA
BEGIN
    RETURN (
    	SELECT ROUND(
            ifactor * POW(
                df,
                (
                    IF(TO_DAYS(dt_closed) IS NULL,
                        DATEDIFF(NOW(),dt),
                        DATEDIFF(dt_closed,dt)
                    )-(SELECT fct_tld_mod_toc_getDaysSuspendedByID(tocid))
    			)
            ),2
        )
        FROM
    		toc
        WHERE
    		id=tocid
    );
END |

DELIMITER ;