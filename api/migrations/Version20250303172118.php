<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303172118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update faq plan item description to long text';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items CHANGE description description LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items CHANGE description description VARCHAR(255) NOT NULL');
    }
}
