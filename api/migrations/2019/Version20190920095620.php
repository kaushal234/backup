<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190920095620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_manufacturing (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, factory_id INT NOT NULL, industrial_incorporation_parameter INT NOT NULL, factory_standard_efficiency INT NOT NULL, effective_at DATETIME NOT NULL, INDEX IDX_BB0C37A44584665A (product_id), INDEX IDX_BB0C37A4C7AF27D2 (factory_id), UNIQUE INDEX unique_product_by_factory_by_year (product_id, factory_id, effective_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_manufacturing ADD CONSTRAINT FK_BB0C37A44584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        $this->addSql('ALTER TABLE product_manufacturing ADD CONSTRAINT FK_BB0C37A4C7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_manufacturing');
    }
}
