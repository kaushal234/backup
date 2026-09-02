<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107103036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix unsynchronize entities/database mapping.';
    }

    public function up(Schema $schema): void
    {
        // Index on double keys is missing.
        $this->addSql('CREATE UNIQUE INDEX unique_category_per_business_unit ON directory_position_classification (business_unit_id, position_category_id)');

        // Doctrine drop and recreate FK and Index, don't know why but let him do.
        $this->addSql('ALTER TABLE evendors_news DROP FOREIGN KEY FK_41A3C7E6B03A8386');
        $this->addSql('DROP INDEX idx_41a3c7e6b03a8386 ON evendors_news');
        $this->addSql('CREATE INDEX IDX_41A3C7E6DE12AB56 ON evendors_news (created_by)');
        $this->addSql('ALTER TABLE evendors_news ADD CONSTRAINT FK_41A3C7E6B03A8386 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE non_conformity_main_files DROP FOREIGN KEY FK_C9220AC78EA30491');
        $this->addSql('DROP INDEX idx_c9220ac78ea30491 ON non_conformity_main_files');
        $this->addSql('CREATE INDEX IDX_898746DD8EA30491 ON non_conformity_main_files (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_main_files ADD CONSTRAINT FK_C9220AC78EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE non_conformity_parts DROP FOREIGN KEY FK_61A451DF8EA30491');
        $this->addSql('DROP INDEX idx_61a451df8ea30491 ON non_conformity_parts');
        $this->addSql('CREATE INDEX IDX_A72719868EA30491 ON non_conformity_parts (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_parts ADD CONSTRAINT FK_61A451DF8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');

        // DELETE old migrations version. Migrations files was moved on archives folders.
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2015%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2016%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2017%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2018%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2019%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2020%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2021%"');
        $this->addSql('DELETE FROM migration_versions WHERE version LIKE "%Version2022%"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE non_conformity_parts DROP FOREIGN KEY FK_A72719868EA30491');
        $this->addSql('DROP INDEX idx_a72719868ea30491 ON non_conformity_parts');
        $this->addSql('CREATE INDEX IDX_61A451DF8EA30491 ON non_conformity_parts (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_parts ADD CONSTRAINT FK_A72719868EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
        $this->addSql('ALTER TABLE evendors_news DROP FOREIGN KEY FK_41A3C7E6DE12AB56');
        $this->addSql('DROP INDEX idx_41a3c7e6de12ab56 ON evendors_news');
        $this->addSql('CREATE INDEX IDX_41A3C7E6B03A8386 ON evendors_news (created_by)');
        $this->addSql('ALTER TABLE evendors_news ADD CONSTRAINT FK_41A3C7E6DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('DROP INDEX unique_category_per_business_unit ON directory_position_classification');
        $this->addSql('ALTER TABLE non_conformity_main_files DROP FOREIGN KEY FK_898746DD8EA30491');
        $this->addSql('DROP INDEX idx_898746dd8ea30491 ON non_conformity_main_files');
        $this->addSql('CREATE INDEX IDX_C9220AC78EA30491 ON non_conformity_main_files (non_conformity_id)');
        $this->addSql('ALTER TABLE non_conformity_main_files ADD CONSTRAINT FK_898746DD8EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id)');
    }
}
