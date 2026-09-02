<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630081520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add completion rate with validate date and comment to FAQ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD completion_rate INT DEFAULT 0 NOT NULL, ADD comment LONGTEXT DEFAULT "" NOT NULL');
        $this->addSql('UPDATE first_article_qualifications_plan_items SET completion_rate = 100 WHERE validated = 1');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP validated');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP completion_rate, DROP comment');
        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD validated TINYINT(1) DEFAULT NULL');
    }
}
