<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230313122931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add equipment records to Non Conformity entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE non_conformity_equipment_record (non_conformity_id INT NOT NULL, equipment_record_id INT NOT NULL, INDEX IDX_BF4A51858EA30491 (non_conformity_id), INDEX IDX_BF4A51859FC03375 (equipment_record_id), PRIMARY KEY(non_conformity_id, equipment_record_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE non_conformity_equipment_record ADD CONSTRAINT FK_BF4A51858EA30491 FOREIGN KEY (non_conformity_id) REFERENCES non_conformity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE non_conformity_equipment_record ADD CONSTRAINT FK_BF4A51859FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity_equipment_record DROP FOREIGN KEY FK_BF4A51858EA30491');
        $this->addSql('ALTER TABLE non_conformity_equipment_record DROP FOREIGN KEY FK_BF4A51859FC03375');
        $this->addSql('DROP TABLE non_conformity_equipment_record');
    }
}
