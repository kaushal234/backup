<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241216193801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add FAQ property on CRAB';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crab ADD first_article_qualification_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE crab ADD CONSTRAINT FK_80F8615DC9C3E626 FOREIGN KEY (first_article_qualification_id) REFERENCES first_article_qualifications (id)');
        $this->addSql('CREATE INDEX IDX_80F8615DC9C3E626 ON crab (first_article_qualification_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE crab DROP FOREIGN KEY FK_80F8615DC9C3E626');
        $this->addSql('DROP INDEX IDX_80F8615DC9C3E626 ON crab');
        $this->addSql('ALTER TABLE crab DROP first_article_qualification_id');
    }
}
