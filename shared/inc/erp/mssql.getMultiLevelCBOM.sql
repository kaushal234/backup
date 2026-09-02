CREATE PROCEDURE getMultiLevelCBOM (@pid int, @erp int, @prno char(20), @dt char(10)) as	--This is a non-recursive preorder traversal.
SET NOCOUNT ON

DECLARE @topLevelItem char(30), @date char(10), @pono char(10), @csig char(10), @dsca char(100), @revi char(10)

IF @erp=400
BEGIN
	DECLARE c1 CURSOR READ_ONLY FOR
			SELECT RTRIM(PCS.t_sitm) AS t_sitm, PCS.t_pono, RTRIM(t_csig), t_dsca
			FROM ttipcs022400 AS PCS, ttiitm001400 AS ITM
			WHERE PCS.t_sitm=ITM.t_item
			AND t_cprj=@prno
			ORDER BY CAST(PCS.t_pono AS integer) DESC
	OPEN c1
END
IF @erp=420
BEGIN
	DECLARE c1 CURSOR READ_ONLY FOR
			SELECT RTRIM(PCS.t_sitm) AS t_sitm, PCS.t_pono, RTRIM(t_csig), t_dsca
			FROM ttipcs022420 AS PCS, ttiitm001420 AS ITM
			WHERE PCS.t_sitm=ITM.t_item
			AND t_cprj=@prno
			ORDER BY CAST(PCS.t_pono AS integer) DESC
	OPEN c1
END
IF @erp=640
BEGIN
	DECLARE c1 CURSOR READ_ONLY FOR
			SELECT RTRIM(PCS.t_sitm) AS t_sitm, PCS.t_pono, RTRIM(t_csig), t_dsca
			FROM ttipcs022640 AS PCS, ttiitm001640 AS ITM
			WHERE PCS.t_sitm=ITM.t_item
			AND t_cprj=@prno
			ORDER BY CAST(PCS.t_pono AS integer) DESC
	OPEN c1
END

FETCH NEXT FROM c1
INTO @topLevelItem, @pono, @csig, @dsca

WHILE @@FETCH_STATUS = 0
BEGIN

	PRINT @topLevelItem

		INSERT INTO tld..temp_bom 
		SELECT 
			@pid,
			@prno,
			null,
			@pono,
			@topLevelItem,
			null,
			null,
			@dsca,
			@csig,
			null, 
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			0

	EXEC getMultiLevelBOM @pid, @erp, @prno, @topLevelItem, @dt

	FETCH NEXT FROM c1
	INTO @topLevelItem, @pono, @csig, @dsca

END

CLOSE c1
DEALLOCATE c1
GO
