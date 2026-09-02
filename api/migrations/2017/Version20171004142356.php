<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171004142356 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023A34BAA6FC');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023A411AB');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023A437031EE');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023A8B54B08B');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023A9395C3F3');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023AAE98149B');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_FFB1023AE03B0789');
        $this->addSql('DROP INDEX idx_ffb1023a9395c3f3 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D309395C3F3 ON customer_relationship_teams (customer_id)');
        $this->addSql('DROP INDEX idx_ffb1023a8b54b08b ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D308B54B08B ON customer_relationship_teams (sales_representative_id)');
        $this->addSql('DROP INDEX idx_ffb1023ae03b0789 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D30E03B0789 ON customer_relationship_teams (parts_representative_id)');
        $this->addSql('DROP INDEX idx_ffb1023a411ab ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D30962895D3 ON customer_relationship_teams (service_representative_id)');
        $this->addSql('DROP INDEX idx_ffb1023a437031ee ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D30437031EE ON customer_relationship_teams (parts_location_id)');
        $this->addSql('DROP INDEX idx_ffb1023aae98149b ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D30CAD70722 ON customer_relationship_teams (service_location_id)');
        $this->addSql('DROP INDEX idx_ffb1023a34baa6fc ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_7CA88D3034BAA6FC ON customer_relationship_teams (erp_location_id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A34BAA6FC FOREIGN KEY (erp_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A411AB FOREIGN KEY (service_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A437031EE FOREIGN KEY (parts_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A8B54B08B FOREIGN KEY (sales_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023AAE98149B FOREIGN KEY (service_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_FFB1023AE03B0789 FOREIGN KEY (parts_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customers ADD country_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21F92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id)');
        $this->addSql('CREATE INDEX IDX_62534E21F92F3E70 ON customers (country_id)');
        $this->addSql('ALTER TABLE acl CHANGE legacy_id legacy_id INT NOT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE spq_quotations CHANGE delivery_address_baan_id delivery_address_baan_id VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\', CHANGE billing_address_baan_id billing_address_baan_id VARCHAR(3) NOT NULL COMMENT \'(DC2Type:string)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE acl CHANGE legacy_id legacy_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\'');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D309395C3F3');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D308B54B08B');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D30E03B0789');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D30962895D3');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D30437031EE');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D30CAD70722');
        $this->addSql('ALTER TABLE customer_relationship_teams DROP FOREIGN KEY FK_7CA88D3034BAA6FC');
        $this->addSql('DROP INDEX idx_7ca88d309395c3f3 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023A9395C3F3 ON customer_relationship_teams (customer_id)');
        $this->addSql('DROP INDEX idx_7ca88d308b54b08b ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023A8B54B08B ON customer_relationship_teams (sales_representative_id)');
        $this->addSql('DROP INDEX idx_7ca88d30e03b0789 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023AE03B0789 ON customer_relationship_teams (parts_representative_id)');
        $this->addSql('DROP INDEX idx_7ca88d30962895d3 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023A411AB ON customer_relationship_teams (service_representative_id)');
        $this->addSql('DROP INDEX idx_7ca88d30437031ee ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023A437031EE ON customer_relationship_teams (parts_location_id)');
        $this->addSql('DROP INDEX idx_7ca88d30cad70722 ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023AAE98149B ON customer_relationship_teams (service_location_id)');
        $this->addSql('DROP INDEX idx_7ca88d3034baa6fc ON customer_relationship_teams');
        $this->addSql('CREATE INDEX IDX_FFB1023A34BAA6FC ON customer_relationship_teams (erp_location_id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D309395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D308B54B08B FOREIGN KEY (sales_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D30E03B0789 FOREIGN KEY (parts_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D30962895D3 FOREIGN KEY (service_representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D30437031EE FOREIGN KEY (parts_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D30CAD70722 FOREIGN KEY (service_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customer_relationship_teams ADD CONSTRAINT FK_7CA88D3034BAA6FC FOREIGN KEY (erp_location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21F92F3E70');
        $this->addSql('DROP INDEX IDX_62534E21F92F3E70 ON customers');
        $this->addSql('ALTER TABLE customers DROP country_id');
        $this->addSql('ALTER TABLE spq_quotations CHANGE delivery_address_baan_id delivery_address_baan_id VARCHAR(3) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\', CHANGE billing_address_baan_id billing_address_baan_id VARCHAR(3) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:string)\'');
    }
}
