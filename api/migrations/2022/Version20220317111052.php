<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220317111052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename two columns of manual_documents and manual_parts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manual_documents ADD description LONGTEXT DEFAULT NULL, ADD other_description LONGTEXT DEFAULT NULL, DROP english_description, DROP french_description');
        $this->addSql('ALTER TABLE manual_parts ADD description LONGTEXT DEFAULT NULL, ADD other_description LONGTEXT DEFAULT NULL, DROP english_description, DROP french_description');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE manual_documents ADD english_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD french_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, DROP description, DROP other_description');
        $this->addSql('ALTER TABLE manual_parts ADD english_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, ADD french_description LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, DROP description, DROP other_description');
    }
}
