<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211020211803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create join table between features and authorized applications';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feature_authorized_application (feature_id INT NOT NULL, authorized_application_id INT NOT NULL, INDEX IDX_421D654A60E4B879 (feature_id), INDEX IDX_421D654AEE16DCEF (authorized_application_id), PRIMARY KEY(feature_id, authorized_application_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE feature_authorized_application ADD CONSTRAINT FK_421D654A60E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feature_authorized_application ADD CONSTRAINT FK_421D654AEE16DCEF FOREIGN KEY (authorized_application_id) REFERENCES authorized_application (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE feature_authorized_application');
    }
}
