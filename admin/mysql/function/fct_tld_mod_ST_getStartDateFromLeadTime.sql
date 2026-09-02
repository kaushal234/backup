-- MySQL function to get start date from Lead Time
-- @param date start date
-- @param varchar unit lead time
-- @param int lead time value
-- @return date new start date

DROP FUNCTION IF EXISTS fct_tld_mod_ST_getStartDateFromLeadTime;

DELIMITER |

CREATE FUNCTION fct_tld_mod_ST_getStartDateFromLeadTime(startDate DATE, leadTimeUnit VARCHAR(10), leadTimeVal INT(1))
RETURNS DATE
READS SQL DATA
BEGIN
    RETURN (
        SELECT
        	CASE
        		WHEN (leadTimeVal=0 OR leadTimeVal IS NULL) THEN DATE(startDate)
                WHEN leadTimeUnit LIKE 'YEAR' THEN DATE_SUB(startDate, INTERVAL leadTimeVal YEAR)
        		WHEN leadTimeUnit LIKE 'MONTH' THEN DATE_SUB(startDate, INTERVAL leadTimeVal MONTH)
             	WHEN leadTimeUnit LIKE 'WEEK' THEN DATE_SUB(startDate, INTERVAL leadTimeVal WEEK)
             	WHEN leadTimeUnit LIKE 'DAY' THEN DATE_SUB(startDate, INTERVAL leadTimeVal DAY)
            	ELSE startDate
        	END
    );
END |

DELIMITER ;