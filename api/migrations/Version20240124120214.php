<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240124120214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update user photo to public as it should be';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE files SET public = 1 WHERE discr='people_file' and public = 0");
    }

    public function down(Schema $schema): void
    {
    }
}
