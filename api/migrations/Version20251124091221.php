<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124091221 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Create contact Campaign table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_campaign (id INT AUTO_INCREMENT NOT NULL, owner INT NOT NULL, name VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_85308E4ACF60E67C (owner), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_campaign_extranet_user (contact_campaign_id INT NOT NULL, extranet_user_id INT NOT NULL, INDEX IDX_27B41D9F989548CA (contact_campaign_id), INDEX IDX_27B41D9FD2CDD54B (extranet_user_id), PRIMARY KEY(contact_campaign_id, extranet_user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_campaign_business_unit (contact_campaign_id INT NOT NULL, business_unit_id INT NOT NULL, INDEX IDX_F8EDE564989548CA (contact_campaign_id), INDEX IDX_F8EDE564A58ECB40 (business_unit_id), PRIMARY KEY(contact_campaign_id, business_unit_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE template (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX unique_template_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact_campaign ADD CONSTRAINT FK_85308E4ACF60E67C FOREIGN KEY (owner) REFERENCES user (id)');
        $this->addSql('ALTER TABLE contact_campaign_extranet_user ADD CONSTRAINT FK_27B41D9F989548CA FOREIGN KEY (contact_campaign_id) REFERENCES contact_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_campaign_extranet_user ADD CONSTRAINT FK_27B41D9FD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_campaign_business_unit ADD CONSTRAINT FK_F8EDE564989548CA FOREIGN KEY (contact_campaign_id) REFERENCES contact_campaign (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_campaign_business_unit ADD CONSTRAINT FK_F8EDE564A58ECB40 FOREIGN KEY (business_unit_id) REFERENCES directory_businessunit (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE base_task ADD template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE base_task ADD CONSTRAINT FK_C41D15D55DA0FB8 FOREIGN KEY (template_id) REFERENCES template (id)');
        $this->addSql('CREATE INDEX IDX_C41D15D55DA0FB8 ON base_task (template_id)');

        $this->addSql('ALTER TABLE extranet_user_profile ADD is_verified TINYINT(1) DEFAULT 0 NOT NULL, ADD is_gift_accepted TINYINT(1) DEFAULT 1 NOT NULL, ADD gift_refused_reason VARCHAR(255) DEFAULT NULL');

        $this->addSql('INSERT IGNORE INTO template (name) VALUES ("contact.validation.address")');

        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_EVP']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CEO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CFO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_SAM']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CIO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CPO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CMO']);

        $this->insertFeatureGroup('FEATURE_UPDATE_CONTACT_CAMPAIGN', ['ROLE_EVP']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTACT_CAMPAIGN', ['ROLE_CEO']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTACT_CAMPAIGN', ['ROLE_CFO']);
        $this->insertFeatureGroup('FEATURE_UPDATE_CONTACT_CAMPAIGN', ['ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_SAM']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CIO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CPO']);
        $this->insertFeatureGroup('FEATURE_CREATE_CONTACT_CAMPAIGN', ['ROLE_CMO']);

        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_EVP']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_CEO']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_CFO']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_COO']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_CIO']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_SAM']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_CPO']);
        $this->insertFeatureGroup('FEATURE_CREATE_TASK_VERIFIED_EXTRANET_USER', ['ROLE_CMO']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE base_task DROP FOREIGN KEY FK_C41D15D55DA0FB8');
        $this->addSql('ALTER TABLE contact_campaign DROP FOREIGN KEY FK_85308E4ACF60E67C');
        $this->addSql('ALTER TABLE contact_campaign_extranet_user DROP FOREIGN KEY FK_27B41D9F989548CA');
        $this->addSql('ALTER TABLE contact_campaign_extranet_user DROP FOREIGN KEY FK_27B41D9FD2CDD54B');
        $this->addSql('ALTER TABLE contact_campaign_business_unit DROP FOREIGN KEY FK_F8EDE564989548CA');
        $this->addSql('ALTER TABLE contact_campaign_business_unit DROP FOREIGN KEY FK_F8EDE564A58ECB40');
        $this->addSql('DROP TABLE contact_campaign');
        $this->addSql('DROP TABLE contact_campaign_extranet_user');
        $this->addSql('DROP TABLE contact_campaign_business_unit');
        $this->addSql('DROP TABLE template');
        $this->addSql('ALTER TABLE extranet_user_profile DROP is_verified, DROP is_gift_accepted, DROP gift_refused_reason');
        $this->addSql('DROP INDEX IDX_C41D15D55DA0FB8 ON base_task');
        $this->addSql('ALTER TABLE base_task DROP template_id');
    }
}
