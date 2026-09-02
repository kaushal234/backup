<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606072443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update account review of third party app.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE third_party_app_account_review ADD count_end_admin_users INT DEFAULT NULL, ADD admins_comment LONGTEXT DEFAULT NULL, CHANGE count_admin_users count_start_admin_users INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE third_party_app_account_review ADD count_admin_users INT DEFAULT NULL, DROP count_start_admin_users, DROP count_end_admin_users, DROP admins_comment');
    }
}
