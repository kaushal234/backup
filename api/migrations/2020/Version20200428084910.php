<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200428084910 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer_erp_references (id INT AUTO_INCREMENT NOT NULL, sso_id INT NOT NULL, customer_id INT NOT NULL, customer_number VARCHAR(255) NOT NULL, INDEX IDX_2998887A7843BFA4 (sso_id), INDEX IDX_2998887A9395C3F3 (customer_id), UNIQUE INDEX unique_customer_number_per_erp (customer_number, sso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customer_erp_references ADD CONSTRAINT FK_2998887A7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_erp_references ADD CONSTRAINT FK_2998887A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE directory_location ADD erp_software VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE customer_erp_references');
        $this->addSql('ALTER TABLE directory_location DROP erp_sofware');
    }
}
