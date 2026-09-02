<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260807063429 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Consolidate guest positions into a single GUEST position (rename 128 to GUEST, move 129 users to 128, delete 129).';
    }

    public function up(Schema $schema): void
    {
        // Move all GUEST ERP (129) users to the kept guest position (128).
        $this->addSql('UPDATE `user` SET position_id = 128 WHERE position_id = 129');

        // Rename the kept position from GUEST AZURE to GUEST.
        $this->addSql("UPDATE directory_position SET code = 'GUEST', description = 'GUEST' WHERE id = 128");

        // Remove the now-unused GUEST ERP position.
        $this->addSql('DELETE FROM directory_position WHERE id = 129');
    }

    public function down(Schema $schema): void
    {
        // Recreate the GUEST ERP position.
        $this->addSql("INSERT INTO directory_position (id, level_id, code, description) VALUES (129, 5, 'GUEST ERP', 'GUEST ERP')");

        // Restore the kept position's original name.
        $this->addSql("UPDATE directory_position SET code = 'GUEST AZURE', description = 'GUEST AZURE' WHERE id = 128");

        // Note: original user->position (129) assignments cannot be restored — all guest users remain on 128.
    }
}
