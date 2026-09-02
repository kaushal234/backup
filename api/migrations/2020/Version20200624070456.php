<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200624070456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE shipping_quotation_request_files (id INT NOT NULL, shipping_quotation_request_id INT DEFAULT NULL, INDEX IDX_4834B4F81AF83651 (shipping_quotation_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shipping_quotation_request_files ADD CONSTRAINT FK_4834B4F81AF83651 FOREIGN KEY (shipping_quotation_request_id) REFERENCES shipping_quotation_requests (id)');
        $this->addSql('ALTER TABLE shipping_quotation_request_files ADD CONSTRAINT FK_4834B4F8BF396750 FOREIGN KEY (id) REFERENCES files (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE shipping_quotation_request_files');
    }
}
