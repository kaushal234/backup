<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908074510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update NCR photo table as there are not photos anymore';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE non_conformity_photo_files RENAME non_conformity_main_files;');
        $this->addSql("UPDATE files SET discr='non_conformity_main_file' WHERE discr LIKE 'non_conformity_photo_file'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
