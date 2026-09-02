<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722073107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deliverables_due_date in first_article_qualifications';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications ADD deliverables_due_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications DROP deliverables_due_date');
    }
}
