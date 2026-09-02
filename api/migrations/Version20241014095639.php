<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241014095639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Inspection entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inspection (id INT AUTO_INCREMENT NOT NULL, created_by INT DEFAULT NULL, equipment_record_id INT DEFAULT NULL, created_at DATETIME NOT NULL, planned_at DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_F9F13485DE12AB56 (created_by), INDEX IDX_F9F134859FC03375 (equipment_record_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inspection ADD CONSTRAINT FK_F9F13485DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inspection ADD CONSTRAINT FK_F9F134859FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inspection DROP FOREIGN KEY FK_F9F13485DE12AB56');
        $this->addSql('ALTER TABLE inspection DROP FOREIGN KEY FK_F9F134859FC03375');
        $this->addSql('DROP TABLE inspection');
    }
}
