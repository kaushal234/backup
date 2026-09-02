<?php

declare(strict_types=1);

namespace Application\tocMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512144903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TOC mainContact and contacts properties done';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE technician_on_call_extranet_user (technician_on_call_id INT NOT NULL, extranet_user_id INT NOT NULL, INDEX IDX_2BEA67B1EC02A7D0 (technician_on_call_id), INDEX IDX_2BEA67B1D2CDD54B (extranet_user_id), PRIMARY KEY(technician_on_call_id, extranet_user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user ADD CONSTRAINT FK_2BEA67B1EC02A7D0 FOREIGN KEY (technician_on_call_id) REFERENCES technician_on_call (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user ADD CONSTRAINT FK_2BEA67B1D2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE technician_on_call ADD main_contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE technician_on_call ADD CONSTRAINT FK_3BD0B5C6DF595129 FOREIGN KEY (main_contact_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3BD0B5C6DF595129 ON technician_on_call (main_contact_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE technician_on_call_extranet_user DROP FOREIGN KEY FK_2BEA67B1EC02A7D0');
        $this->addSql('ALTER TABLE technician_on_call_extranet_user DROP FOREIGN KEY FK_2BEA67B1D2CDD54B');
        $this->addSql('DROP TABLE technician_on_call_extranet_user');
        $this->addSql('ALTER TABLE technician_on_call DROP FOREIGN KEY FK_3BD0B5C6DF595129');
        $this->addSql('DROP INDEX IDX_3BD0B5C6DF595129 ON technician_on_call');
        $this->addSql('ALTER TABLE technician_on_call DROP main_contact_id');
    }
}
