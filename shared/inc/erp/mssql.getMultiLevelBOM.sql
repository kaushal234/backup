CREATE PROCEDURE getMultiLevelBOM (@pid int, @erp int, @prno char(20), @current char(20), @date datetime) as	--This is a non-recursive preorder traversal.
 SET NOCOUNT ON
 DECLARE @lvl int, @line char(50)

 CREATE TABLE #stack (item char(20), lvl int)	--Create a tempory stack.

 INSERT INTO #stack VALUES (@current,  1)	--Insert current node to the stack.

 SELECT @lvl = 1				

 WHILE @lvl > 0					--From the top level going down.
	BEGIN
	    IF EXISTS (SELECT * FROM #stack WHERE lvl = @lvl)
	        BEGIN
	            SELECT @current = item	--Find the first node that matches current node's name.
	            FROM #stack
	            WHERE lvl = @lvl

	            SELECT @line = space((@lvl - 1)*2) + @current	--@lvl - 1 s spaces before the node name.
	            PRINT @line					--Print it.

		INSERT INTO tld..temp_bom 
		SELECT 
			@pid,
			@prno,
			RTRIM(BOM.t_mitm) AS t_mitm,
			BOM.t_pono,
			RTRIM(BOM.t_sitm) AS t_sitm,
			BOM.t_qana,
			ITM.t_cuni,
			ITM.t_dsca,
			RTRIM(ITM.t_csig) AS t_csig,
			EDM.t_revi, 
			SUBSTRING(convert(varchar,EDM.t_indt,120), 0, 11) AS t_indt,
			SUBSTRING(convert(varchar,EDM.t_exdt,120), 0, 11) AS t_exdt,
			'change'=CASE
			  WHEN (EDM.t_exdt <>'1753-01-01') THEN '1'
			  ELSE '0'
			END,
			(SELECT CAST(SUBSTRING(t_dscb, 2, 2) AS tinyint) FROM ttiedm010400 AS T2 WHERE T2.t_eitm=ITM.t_item) AS P,
			(SELECT CAST(SUBSTRING(t_dscb, 5, 2) AS tinyint) FROM ttiedm010400 AS T2 WHERE T2.t_eitm=ITM.t_item) AS M,
			(SELECT CAST(SUBSTRING(t_dscb, 8, 2) AS tinyint) FROM ttiedm010400 AS T2 WHERE T2.t_eitm=ITM.t_item) AS O,
			(SELECT CAST(SUBSTRING(t_dscb, 11, 2) AS tinyint) FROM ttiedm010400 AS T2 WHERE T2.t_eitm=ITM.t_item) AS C,
			@lvl
		FROM
		  ttibom010400 AS BOM left join ttiitm001400 AS ITM on BOM.t_sitm=ITM.t_item 
		  left join ttiedm100400 AS EDM on BOM.t_sitm=EDM.t_eitm
		WHERE BOM.t_mitm=@current AND 
		((@date >= BOM.t_indt AND @date < BOM.t_exdt) 
			or (@date >= BOM.t_indt and BOM.t_exdt='1753-01-01'))
		 AND ((@date >= EDM.t_indt AND @date < EDM.t_exdt)
			 or (@date >= EDM.t_indt and EDM.t_exdt='1753-01-01'))
		AND EDM.t_rele=1
		ORDER BY CAST(BOM.t_pono AS integer)

	            DELETE FROM #stack
	            WHERE lvl = @lvl
	                AND item = @current	--Remove the current node from the stack.

	            INSERT #stack		--Insert the childnodes of the current node into the stack.
	                SELECT BOM.t_sitm, @lvl + 1
		FROM
		  ttibom010400 AS BOM left join ttiitm001400 AS ITM on BOM.t_sitm=ITM.t_item 
		  left join ttiedm100400 AS EDM on BOM.t_sitm=EDM.t_eitm
		WHERE BOM.t_mitm=@current AND 
		((@date >= BOM.t_indt AND @date < BOM.t_exdt) 
			or (@date >= BOM.t_indt and BOM.t_exdt='1753-01-01'))
		 AND ((@date >= EDM.t_indt AND @date < EDM.t_exdt)
			 or (@date >= EDM.t_indt and EDM.t_exdt='1753-01-01'))
		AND EDM.t_rele=1

	            IF @@ROWCOUNT > 0		--If the previous statement added one or more nodes, go down for its first child.
                        SELECT @lvl = @lvl + 1	--If no nodes are added, check its brother nodes.
		END
    	    ELSE
	      	SELECT @lvl = @lvl - 1		--Back to the level immediately above.
       	
END
GO
