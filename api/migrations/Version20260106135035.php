<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106135035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'drop factory from non-conformity and non-quality-cost';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity DROP FOREIGN KEY FK_9726A49AC7AF27D2');
        $this->addSql('DROP INDEX IDX_9726A49AC7AF27D2 ON non_conformity');
        $this->addSql('ALTER TABLE non_conformity DROP factory_id');
        $this->addSql('ALTER TABLE non_quality_costs DROP FOREIGN KEY FK_26904F7CC7AF27D2');
        $this->addSql('DROP INDEX IDX_26904F7CC7AF27D2 ON non_quality_costs');
        $this->addSql('ALTER TABLE non_quality_costs DROP factory_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity ADD factory_id INT NOT NULL');
        $this->addSql('ALTER TABLE non_conformity ADD CONSTRAINT FK_9726A49AC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_9726A49AC7AF27D2 ON non_conformity (factory_id)');
        $this->addSql('ALTER TABLE non_quality_costs ADD factory_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE non_quality_costs ADD CONSTRAINT FK_26904F7CC7AF27D2 FOREIGN KEY (factory_id) REFERENCES directory_location (id)');
        $this->addSql('CREATE INDEX IDX_26904F7CC7AF27D2 ON non_quality_costs (factory_id)');
    }
}
