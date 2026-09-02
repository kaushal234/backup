<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210601122351 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extranet_user_profile ADD airport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE extranet_user_profile ADD CONSTRAINT FK_4382446B289F53C8 FOREIGN KEY (airport_id) REFERENCES iata_codes (id)');
        $this->addSql('CREATE INDEX IDX_4382446B289F53C8 ON extranet_user_profile (airport_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE extranet_user_profile DROP FOREIGN KEY FK_4382446B289F53C8');
        $this->addSql('DROP INDEX IDX_4382446B289F53C8 ON extranet_user_profile');
        $this->addSql('ALTER TABLE extranet_user_profile DROP airport_id');
    }
}
