<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210607140205 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tool ADD status_updated_at DATETIME');
        $this->addSql('UPDATE tool
            SET status_updated_at = (SELECT MAX(created_at) FROM activity
                                        WHERE discr = \'log\'
                                        AND resource = CONCAT(\'/quality/calibrated_tools/tools/\', tool.id)
                                        AND change_set LIKE \'%"status":%\');');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tool DROP status_updated_at');
    }
}
