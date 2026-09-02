<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200211212752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove previous columns for single files associated to a resource';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP photo');
        $this->addSql('ALTER TABLE calibration_log DROP certificate_file_name');
        $this->addSql('ALTER TABLE customers DROP logo');
        $this->addSql('ALTER TABLE competitors DROP logo');
        $this->addSql('ALTER TABLE news DROP picture');
    }

    public function down(Schema $schema): void
    {
    }
}
