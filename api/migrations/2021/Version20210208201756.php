<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210208201756 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos ADD emission_rating_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demos ADD CONSTRAINT FK_CD0E2E3C15428FA8 FOREIGN KEY (emission_rating_id) REFERENCES emission_ratings (id)');
        $this->addSql('CREATE INDEX IDX_CD0E2E3C15428FA8 ON demos (emission_rating_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demos DROP FOREIGN KEY FK_CD0E2E3C15428FA8');
        $this->addSql('DROP INDEX IDX_CD0E2E3C15428FA8 ON demos');
        $this->addSql('ALTER TABLE demos DROP emission_rating_id');
    }
}
