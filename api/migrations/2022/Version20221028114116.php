<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221028114116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add property public on files, add feature FEATURE_NON_CONFORMITY_FILE_CHANGE_VISIBILITY';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE files ADD public TINYINT(1) DEFAULT 0');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES ("FEATURE_NON_CONFORMITY_FILE_CHANGE_VISIBILITY")');

        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
               SELECT user_group.id, feature.id
                 FROM user_group, feature
                WHERE feature.name = "FEATURE_NON_CONFORMITY_FILE_CHANGE_VISIBILITY"
                  AND user_group.name IN ("SUPERUSER", "ROLE_QAM", "ROLE_QE", "ROLE_QA")');

        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'comment_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'manual_document_files'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'news_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'non_conformity_main_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'people_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'supplier_corrective_action_request_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'supplier_corrective_action_request_main_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'support_accident_file'");
        $this->addSql("UPDATE files SET public=1 WHERE discr LIKE 'vendor_warranty_claim_file'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE files DROP public');
    }
}
