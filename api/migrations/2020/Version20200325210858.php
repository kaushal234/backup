<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200325210858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE manufacturing_families (id INT NOT NULL, test_duration INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE products ADD manufacturing_family_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A58D77FA8 FOREIGN KEY (manufacturing_family_id) REFERENCES manufacturing_families (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE manufacturing_families');
        $this->addSql('ALTER TABLE products DROP manufacturing_family_id, CHANGE erp_location_id erp_location_id INT DEFAULT NULL, CHANGE finance_family_id finance_family_id INT DEFAULT NULL, CHANGE light light TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE model_base_hours model_base_hours INT DEFAULT NULL');
    }
}
