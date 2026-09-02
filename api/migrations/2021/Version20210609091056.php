<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609091056 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E219C54D4BF');
        $this->addSql('DROP INDEX IDX_62534E219C54D4BF ON customers');
        $this->addSql('ALTER TABLE customers DROP asm_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customers ADD asm_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E219C54D4BF FOREIGN KEY (asm_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_62534E219C54D4BF ON customers (asm_id)');
    }
}
