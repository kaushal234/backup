<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716151303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reduce CRAB codes from 53 to 27 per TTS#3632 (QAM-validated list)';
    }

    public function up(Schema $schema): void
    {
        // 1. Redirect every crab to its final code_id (ids 1-27 already exist, their
        // content is overwritten below). Mapping: old crab_code.id => new code number,
        // from the TTS#3632 correspondence file.
        $this->addSql('UPDATE crab SET code_id = CASE code_id
            WHEN 1 THEN 9 WHEN 2 THEN 8 WHEN 3 THEN 7 WHEN 4 THEN 7 WHEN 5 THEN 3
            WHEN 6 THEN 6 WHEN 7 THEN 6 WHEN 8 THEN 6 WHEN 9 THEN 6 WHEN 10 THEN 1
            WHEN 11 THEN 11 WHEN 12 THEN 1 WHEN 13 THEN 17 WHEN 14 THEN 6 WHEN 15 THEN 11
            WHEN 16 THEN 2 WHEN 17 THEN 2 WHEN 18 THEN 11 WHEN 19 THEN 9 WHEN 20 THEN 11
            WHEN 21 THEN 12 WHEN 22 THEN 1 WHEN 23 THEN 1 WHEN 24 THEN 6 WHEN 25 THEN 11
            WHEN 26 THEN 10 WHEN 27 THEN 11 WHEN 28 THEN 3 WHEN 29 THEN 6 WHEN 30 THEN 15
            WHEN 31 THEN 4 WHEN 32 THEN 6 WHEN 33 THEN 1 WHEN 34 THEN 5 WHEN 35 THEN 11
            WHEN 36 THEN 1 WHEN 37 THEN 11 WHEN 38 THEN 20 WHEN 39 THEN 21 WHEN 40 THEN 22
            WHEN 41 THEN 23 WHEN 42 THEN 24 WHEN 43 THEN 25 WHEN 44 THEN 26 WHEN 45 THEN 27
            WHEN 46 THEN 11 WHEN 47 THEN 17 WHEN 48 THEN 11 WHEN 49 THEN 6 WHEN 50 THEN 6
            WHEN 51 THEN 13 WHEN 52 THEN 19 WHEN 53 THEN 14
            ELSE code_id END
            WHERE code_id BETWEEN 1 AND 53');

        // 2. Ids above 27 are now unreferenced by construction (every old id 1-53 was
        // remapped to a target in 1-27 above), safe to delete.
        $this->addSql('DELETE FROM crab_code WHERE id > 27');

        // 3. Overwrite the surviving 27 rows with their final code and description.
        $this->addSql("UPDATE crab_code SET code = 1, description = 'Not well adjusted (interference, routing, adjustment ...)' WHERE id = 1");
        $this->addSql("UPDATE crab_code SET code = 2, description = 'Visual issue (paint issue, rust, sticker, colour, other)' WHERE id = 2");
        $this->addSql("UPDATE crab_code SET code = 3, description = 'Internal request or reminder' WHERE id = 3");
        $this->addSql("UPDATE crab_code SET code = 4, description = 'Something missing vs SOL (Sales Order Line)' WHERE id = 4");
        $this->addSql("UPDATE crab_code SET code = 5, description = 'FAQ (First Article Qualification)' WHERE id = 5");
        $this->addSql("UPDATE crab_code SET code = 6, description = 'Function in error (electrical, hydraulical, pneumatical, other)' WHERE id = 6");
        $this->addSql("UPDATE crab_code SET code = 7, description = 'leak (oil, cooling liquid, refrigerant, other)' WHERE id = 7");
        $this->addSql("UPDATE crab_code SET code = 8, description = 'Not greased' WHERE id = 8");
        $this->addSql("UPDATE crab_code SET code = 9, description = 'Not clean / not well prepared before painting' WHERE id = 9");
        $this->addSql("UPDATE crab_code SET code = 10, description = 'Performances out of tolerances' WHERE id = 10");
        $this->addSql("UPDATE crab_code SET code = 11, description = 'Missing/false informations or parts' WHERE id = 11");
        $this->addSql("UPDATE crab_code SET code = 12, description = 'Software, (all issues)' WHERE id = 12");
        $this->addSql("UPDATE crab_code SET code = 13, description = 'Requested added Tests following Yellow Tag' WHERE id = 13");
        $this->addSql("UPDATE crab_code SET code = 14, description = 'Issue with PIO' WHERE id = 14");
        $this->addSql("UPDATE crab_code SET code = 15, description = 'Foreign objects and debris' WHERE id = 15");
        $this->addSql("UPDATE crab_code SET code = 16, description = 'Lose parts' WHERE id = 16");
        $this->addSql("UPDATE crab_code SET code = 17, description = 'Miswiring' WHERE id = 17");
        $this->addSql("UPDATE crab_code SET code = 18, description = 'Missing customer supplied parts' WHERE id = 18");
        $this->addSql("UPDATE crab_code SET code = 19, description = 'Service Bulletin' WHERE id = 19");
        $this->addSql("UPDATE crab_code SET code = 20, description = 'Rotolock Connection Leak' WHERE id = 20");
        $this->addSql("UPDATE crab_code SET code = 21, description = 'Rotolock valve leak' WHERE id = 21");
        $this->addSql("UPDATE crab_code SET code = 22, description = 'Steel-Copper Braze Joint Leak' WHERE id = 22");
        $this->addSql("UPDATE crab_code SET code = 23, description = 'Copper-Copper Braze Joint Leak' WHERE id = 23");
        $this->addSql("UPDATE crab_code SET code = 24, description = 'Refrigerant fluid Flare Leak' WHERE id = 24");
        $this->addSql("UPDATE crab_code SET code = 25, description = 'Refrigerant fluid Gasket/Flange Leak' WHERE id = 25");
        $this->addSql("UPDATE crab_code SET code = 26, description = 'Refrigerant fluid hose Crimp Leak' WHERE id = 26");
        $this->addSql("UPDATE crab_code SET code = 27, description = 'Refrigerant fluid O-ring Leak' WHERE id = 27");
    }

    public function down(Schema $schema): void
    {
        // Not reversible: consolidating 53 codes into 27 is a lossy many-to-one merge,
        // the original per-crab old code cannot be reconstructed.
        throw new \RuntimeException('This migration is not reversible (CRAB codes consolidation, TTS#3632).');
    }
}
