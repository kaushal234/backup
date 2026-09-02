<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905112003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create EstimatedGreenTagQuantityReport entity and add dayOff property on Events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE estimated_green_tag_quantity_report (id INT AUTO_INCREMENT NOT NULL, manufacturer_location_id INT DEFAULT NULL, day DATETIME NOT NULL, quantity INT NOT NULL, INDEX IDX_DD1A49D269A35B5 (manufacturer_location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_report ADD CONSTRAINT FK_DD1A49D269A35B5 FOREIGN KEY (manufacturer_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE events ADD day_off TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE estimated_green_tag_quantity_report DROP FOREIGN KEY FK_DD1A49D269A35B5');
        $this->addSql('DROP TABLE estimated_green_tag_quantity_report');
        $this->addSql('ALTER TABLE events DROP day_off');
    }
}
