--
-- STEP 0 - REQUIREMENTS
--

-- Make sure TO CLEAN SB3 BETA data first
-- Make sure SB3 table auto increment is 4000

--
-- STEP 1 - INSERT SB1 into SB3
--

-- Lock status in SB1
UPDATE sbs SET status='LOCKED' WHERE status IN('PENDING','APPROVAL','ER_SELECTION');

-- IMPORT SB1 data into SB3
INSERT INTO sb (
	id,
	parent_id,
	dt,
	poster_id,
	bu_id,
	status,
	category,
	type,
	ifactor,
	confidential,
	title,
	description,
	dt_ssd_approval,
	dt_ssd_decision,
	dt_closed,
	labour_hours,
	nb_tech_needed
) 
SELECT
	id,
	parent_id,
	entered_date,
	entered_by,
	(SELECT id FROM locations WHERE location=sbs.factory) AS bu_id,
	'PENDING' AS status,
	CASE
		WHEN urgency IN('IB:IMPROVEMENT','IB:MAINTENANCE','IB:OPERATION') THEN 'INFORMATION'
		WHEN urgency IN('SB:RECOMMENDED','SB:RESTRICTED') THEN 'RECOMMENDED'
		WHEN urgency LIKE 'SB:COMPULSORY' THEN 'COMPULSORY'
		ELSE ''
	END AS category,
	CASE
		WHEN urgency LIKE 'IB:IMPROVEMENT' THEN 'IMPROVEMENT'
		WHEN urgency LIKE 'IB:MAINTENANCE' THEN 'MAINTENANCE'
		WHEN urgency LIKE 'IB:OPERATION' THEN 'OPERATION'
		ELSE ''
	END AS type,
	CASE
		WHEN urgency IN('IB:IMPROVEMENT','IB:MAINTENANCE','IB:OPERATION') THEN 10
		WHEN urgency IN('SB:RECOMMENDED','SB:RESTRICTED') THEN 100
		WHEN urgency LIKE 'SB:COMPULSORY' THEN 1000
	END AS ifactor,
	'N',
	title,
	description,
	NULL,
	NULL,
	dt_closed,
	0,
	1 AS nb_tech_needed
FROM
	sbs
WHERE
	status LIKE 'LOCKED';

--
-- STEP 2 - COPY PARTS
--

INSERT INTO mod_parts (
	parent_id,
	module,
	pn,
	dsc,
	qty,
	um
)
SELECT
	parent_id,
	'SB3',
	pn,
	dsca,
	qty,
	um
FROM
	sbs_parts
WHERE
	parent_id IN(SELECT id FROM sbs WHERE status LIKE 'LOCKED');

--
-- STEP 3 - COPY COVERAGE
--

INSERT INTO sb_coverage (
	parent_id,
	model,
	sn_from,
	sn_to,
	sn_list
)
SELECT
	parent_id,
	model,
	sn_from,
	sn_to,
	sn_list
FROM
	sbs_lines
WHERE
	parent_id IN(SELECT id FROM sbs WHERE status LIKE 'LOCKED');

--
-- STEP 3 - Transfer Task
--

UPDATE tasks SET module='SB3' 
WHERE module LIKE 'SB' 
AND parent_id IN(SELECT id FROM sbs WHERE status LIKE 'LOCKED');