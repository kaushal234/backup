<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201224080907 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP model_base_hours');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD model_base_hours INT DEFAULT NULL');
    }
}
