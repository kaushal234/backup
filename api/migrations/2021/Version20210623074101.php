<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210623074101 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records ADD maintainer_id INT DEFAULT NULL, CHANGE date_shipped date_shipped DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_records ADD CONSTRAINT FK_EAE697A985D19953 FOREIGN KEY (maintainer_id) REFERENCES customers (id)');
        $this->addSql('CREATE INDEX IDX_EAE697A985D19953 ON equipment_records (maintainer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_records DROP FOREIGN KEY FK_EAE697A985D19953');
        $this->addSql('DROP INDEX IDX_EAE697A985D19953 ON equipment_records');
        $this->addSql('ALTER TABLE equipment_records DROP maintainer_id, CHANGE date_shipped date_shipped VARCHAR(255) DEFAULT NULL');
    }
}
