<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200401195658 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_part_number (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, factory_id INT NOT NULL, part_number VARCHAR(30) NOT NULL, INDEX IDX_32F41DE4584665A (product_id), INDEX IDX_32F41DEC7AF27D2 (factory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_part_number ADD CONSTRAINT FK_32F41DE4584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_part_number ADD CONSTRAINT FK_32F41DEC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_part_number');
    }
}
