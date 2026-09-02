<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220527201405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add metadata on activity and tries to have the diff empty once and for all!';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE activity ADD metadata LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('CREATE INDEX general_translations_lookup_idx ON ext_translations (object_class, foreign_key)');
        $this->addSql('ALTER TABLE outbound_requests DROP FOREIGN KEY FK_2DF1F99264D218E');
        $this->addSql('DROP INDEX idx_2df1f99264d218e ON outbound_requests');
        $this->addSql('CREATE INDEX IDX_C4AC680664D218E ON outbound_requests (location_id)');
        $this->addSql('ALTER TABLE outbound_requests ADD CONSTRAINT FK_2DF1F99264D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE user CHANGE gender gender VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP metadata');
        $this->addSql('DROP INDEX general_translations_lookup_idx ON ext_translations');
        $this->addSql('ALTER TABLE outbound_requests DROP FOREIGN KEY FK_C4AC680664D218E');
        $this->addSql('DROP INDEX idx_c4ac680664d218e ON outbound_requests');
        $this->addSql('CREATE INDEX IDX_2DF1F99264D218E ON outbound_requests (location_id)');
        $this->addSql('ALTER TABLE outbound_requests ADD CONSTRAINT FK_C4AC680664D218E FOREIGN KEY (location_id) REFERENCES directory_location (id)');
        $this->addSql('ALTER TABLE user CHANGE gender gender VARCHAR(3) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
