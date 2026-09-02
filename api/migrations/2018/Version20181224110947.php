<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181224110947 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE finance_family_pricing (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', finance_family_id INT NOT NULL COMMENT \'(DC2Type:integer)\', sso_id INT NOT NULL COMMENT \'(DC2Type:integer)\', factory_id INT NOT NULL COMMENT \'(DC2Type:integer)\', average_price INT NOT NULL COMMENT \'(DC2Type:integer)\', average_margin DOUBLE PRECISION NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_D554B99EA0896C46 (finance_family_id), INDEX IDX_D554B99E7843BFA4 (sso_id), INDEX IDX_D554B99EC7AF27D2 (factory_id), UNIQUE INDEX unique_pricing_per_family_sso_factory (finance_family_id, sso_id, factory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_family_pricing ADD CONSTRAINT FK_D554B99EA0896C46 FOREIGN KEY (finance_family_id) REFERENCES finance_families (id)');
        $this->addSql('ALTER TABLE finance_family_pricing ADD CONSTRAINT FK_D554B99E7843BFA4 FOREIGN KEY (sso_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE finance_family_pricing ADD CONSTRAINT FK_D554B99EC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE finance_family_pricing');
    }
}
