<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180816121053 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE first_article_qualifications_plan_items ADD description VARCHAR(140) DEFAULT NULL');

        $this->addSql('DELETE IGNORE FROM first_article_qualifications_tags_xref WHERE first_article_qualification_tag_id IN (SELECT id FROM tags WHERE name IN(\'test1\',\'test2\',\'tag\'))');
        $this->addSql('DELETE IGNORE FROM first_article_qualifications_tags WHERE id IN (SELECT id FROM tags WHERE name IN(\'test1\',\'test2\',\'tag\'))');
        $this->addSql('DELETE IGNORE FROM tags WHERE name IN (\'test1\',\'test2\',\'tag\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE first_article_qualifications_plan_items DROP description');
    }
}
