-- MySQL function to get number of days suspended
-- @param int toc ID
-- @return int nb days suspended

DROP FUNCTION IF EXISTS fct_tld_mod_toc_getDaysSuspendedByID;

DELIMITER |

CREATE FUNCTION fct_tld_mod_toc_getDaysSuspendedByID(tocid INT)
RETURNS INT
READS SQL DATA
BEGIN
    RETURN (
    	SELECT 
    		IF(COUNT(log1.id),
                SUM(DATEDIFF(
                    IF(
                    	(SELECT TO_DAYS(log2.date) FROM mod_logs AS log2 WHERE log2.date>log1.date 
                            AND log2.parent_id=log1.parent_id AND log2.module LIKE 'TOC' 
                            AND log2.log_num=10 ORDER BY log2.date LIMIT 1
                        ) IS NOT NULL,
                        (SELECT log2.date FROM mod_logs AS log2 WHERE log2.date>log1.date 
                            AND log2.parent_id=log1.parent_id AND log2.module LIKE 'TOC' 
                            AND log2.log_num=10 ORDER BY log2.date LIMIT 1),
                        NOW()
                    ),
                    log1.date)
                ),
                0
        	)
        FROM
        	mod_logs AS log1
        WHERE
        	log1.parent_id=tocid AND log1.module LIKE 'TOC'
        	AND log1.comment LIKE 'SUSPENDED' AND log1.log_num=10
    );
END |

DELIMITER ;