<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240718141002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove express property for FAQ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications DROP express');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications ADD express TINYINT(1) NOT NULL');
    }
}
