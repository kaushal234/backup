<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251016042100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add property formality to documentTranslation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_translation ADD formality VARCHAR(11) NOT NULL');
        $this->addSql("UPDATE document_translation SET formality='default'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document_translation DROP formality');
    }
}
