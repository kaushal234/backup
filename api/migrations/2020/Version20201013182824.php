<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201013182824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account_receivable_overview (id INT NOT NULL, transaction_type_reference_id INT DEFAULT NULL, customer_erp_reference_id INT DEFAULT NULL, currency VARCHAR(255) NOT NULL, total_value DOUBLE PRECISION NOT NULL, not_past_due DOUBLE PRECISION NOT NULL, past_due_one_month DOUBLE PRECISION NOT NULL, past_due_two_months DOUBLE PRECISION NOT NULL, past_due_three_months DOUBLE PRECISION NOT NULL, past_due_six_months DOUBLE PRECISION NOT NULL, past_due_more_than_six_months DOUBLE PRECISION NOT NULL, past_due_percentage INT NOT NULL, past_due_two_months_percentage INT NOT NULL, INDEX IDX_4E5AB0FADE219473 (transaction_type_reference_id), INDEX IDX_4E5AB0FAB8DD6B22 (customer_erp_reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_division (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, legacy_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE directory_sub_division (id INT AUTO_INCREMENT NOT NULL, division_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, legacy_id INT NOT NULL, INDEX IDX_877CF77A41859289 (division_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory_sub_division ADD CONSTRAINT FK_877CF77A41859289 FOREIGN KEY (division_id) REFERENCES directory_division (id)');
        $this->addSql('ALTER TABLE directory_businessunit DROP FOREIGN KEY FK_22677AEE98260155');
        $this->addSql('DROP INDEX fk_22677aee98260155 ON directory_businessunit');
        $this->addSql('CREATE INDEX IDX_22677AEE98260155 ON directory_businessunit (region_id)');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_22677AEE98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');
        $this->addSql('ALTER TABLE directory_region DROP FOREIGN KEY FK_14AF72F9FC3FF006');
        $this->addSql('ALTER TABLE directory_region ADD sub_division_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE directory_region ADD CONSTRAINT FK_1AF6AE6BA47CE717 FOREIGN KEY (sub_division_id) REFERENCES directory_sub_division (id)');
        $this->addSql('CREATE INDEX IDX_1AF6AE6BA47CE717 ON directory_region (sub_division_id)');
        $this->addSql('DROP INDEX uniq_14af72f95e237e06 ON directory_region');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1AF6AE6B5E237E06 ON directory_region (name)');
        $this->addSql('DROP INDEX idx_14af72f9fc3ff006 ON directory_region');
        $this->addSql('CREATE INDEX IDX_1AF6AE6BFC3FF006 ON directory_region (representative_id)');
        $this->addSql('ALTER TABLE directory_region ADD CONSTRAINT FK_14AF72F9FC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory_sub_division DROP FOREIGN KEY FK_877CF77A41859289');
        $this->addSql('ALTER TABLE directory_region DROP FOREIGN KEY FK_1AF6AE6BA47CE717');
        $this->addSql('DROP TABLE directory_division');
        $this->addSql('DROP TABLE directory_sub_division');
        $this->addSql('ALTER TABLE directory_businessunit DROP FOREIGN KEY FK_22677AEE98260155');
        $this->addSql('DROP INDEX idx_22677aee98260155 ON directory_businessunit');
        $this->addSql('CREATE INDEX FK_22677AEE98260155 ON directory_businessunit (region_id)');
        $this->addSql('ALTER TABLE directory_businessunit ADD CONSTRAINT FK_22677AEE98260155 FOREIGN KEY (region_id) REFERENCES directory_region (id)');
        $this->addSql('DROP INDEX IDX_1AF6AE6BA47CE717 ON directory_region');
        $this->addSql('ALTER TABLE directory_region DROP FOREIGN KEY FK_1AF6AE6BFC3FF006');
        $this->addSql('ALTER TABLE directory_region DROP sub_division_id');
        $this->addSql('DROP INDEX uniq_1af6ae6b5e237e06 ON directory_region');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14AF72F95E237E06 ON directory_region (name)');
        $this->addSql('DROP INDEX idx_1af6ae6bfc3ff006 ON directory_region');
        $this->addSql('CREATE INDEX IDX_14AF72F9FC3FF006 ON directory_region (representative_id)');
        $this->addSql('ALTER TABLE directory_region ADD CONSTRAINT FK_1AF6AE6BFC3FF006 FOREIGN KEY (representative_id) REFERENCES user (id)');
    }
}
