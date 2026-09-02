<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115084509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates the close_airport_id column in the user table with value from the premises table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            UPDATE user
            INNER JOIN premises ON user.premise_id = premises.id
            SET user.closest_airport_id = premises.airport_id
            WHERE premises.airport_id IS NOT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            UPDATE user
            SET closest_airport_id = NULL
        ');
    }
}
