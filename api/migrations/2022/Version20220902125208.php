<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220902125208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update length of supplier number for migration';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications CHANGE supplier_number supplier_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE first_article_qualifications CHANGE supplier_number supplier_number VARCHAR(6) DEFAULT NULL');
    }
}
