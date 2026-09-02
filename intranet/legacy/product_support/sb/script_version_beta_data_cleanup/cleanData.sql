--
-- STEP 1 - DELETE CSR2 DATA
--

DELETE FROM mod_logs WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM tasks WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM mod_files WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM mod_parts WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM mod_costs WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM mod_links WHERE (module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines)) OR (type LIKE 'CSR2' AND item IN(SELECT csr_id FROM sb_lines));
DELETE FROM mod_faq WHERE module LIKE 'CSR2' AND parent_id IN(SELECT csr_id FROM sb_lines);
DELETE FROM csr2 WHERE id IN(SELECT csr_id FROM sb_lines);

--
-- STEP 2 - DELETE SPR DATA
--

DELETE FROM mod_logs WHERE module LIKE 'SPR' AND parent_id IN(SELECT spr_id FROM sb_lines);
DELETE FROM tasks WHERE module LIKE 'SPR' AND parent_id IN(SELECT spr_id FROM sb_lines);
DELETE FROM mod_files WHERE module LIKE 'SPR' AND parent_id IN(SELECT spr_id FROM sb_lines);
DELETE FROM mod_links WHERE (module LIKE 'SPR' AND parent_id IN(SELECT spr_id FROM sb_lines)) OR (type LIKE 'SPR' AND item IN(SELECT spr_id FROM sb_lines));
DELETE FROM spr_lines WHERE parent_id IN(SELECT spr_id FROM sb_lines);
DELETE FROM spr WHERE id IN(SELECT spr_id FROM sb_lines);

--
-- STEP 3 - DELETE SB3 DATA
--

DELETE FROM mod_logs WHERE module LIKE 'SB3' AND parent_id IN(SELECT id FROM sb);
DELETE FROM tasks WHERE module LIKE 'SB3' AND parent_id IN(SELECT id FROM sb);
DELETE FROM mod_files WHERE module LIKE 'SB3' AND parent_id IN(SELECT id FROM sb);
DELETE FROM mod_links WHERE (module LIKE 'SB3' AND parent_id IN(SELECT id FROM sb)) OR (type LIKE 'SB3' AND item IN(SELECT id FROM sb));
DELETE FROM mod_parts WHERE module LIKE 'SB3' AND parent_id IN(SELECT id FROM sb);
DELETE FROM sb_coverage WHERE parent_id IN(SELECT id FROM sb);
DELETE FROM sb_signature WHERE parent_id IN(SELECT id FROM sb);
DELETE FROM sb_lines WHERE parent_id IN(SELECT id FROM sb);
DELETE FROM mod_logs WHERE module LIKE 'SBL';
DELETE FROM sb;

--
-- STEP 4 - CONFIGURATION
--

ALTER TABLE sb AUTO_INCREMENT = 4000;
ALTER TABLE sb_coverage AUTO_INCREMENT = 0;
ALTER TABLE sb_signature AUTO_INCREMENT = 0;
