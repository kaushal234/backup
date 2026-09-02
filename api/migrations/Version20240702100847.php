<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702100847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update description from "All users cannot proceed" to "Major impact on business activity" on type table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE type SET description='i_cannot_proceed' WHERE description= 'I cannot proceed'");
        $this->addSql("UPDATE type SET description='security_high_attention' WHERE description= 'Security - High Attention'");
        $this->addSql("UPDATE type SET description='security_not_urgent' WHERE description= 'Security - Not Urgent'");
        $this->addSql("UPDATE type SET description='annoying_but_i_can_proceed' WHERE description= 'Annoying but I can proceed'");
        $this->addSql("UPDATE type SET description='multiple_users_cannot_proceed' WHERE description= 'Multiple users cannot proceed'");
        $this->addSql("UPDATE type SET description='more_permission_needed' WHERE description= 'More permission needed'");
        $this->addSql("UPDATE type SET description='more_training_needed' WHERE description= 'More training needed'");
        $this->addSql("UPDATE type SET description='new_feature_proposal' WHERE description= 'New feature proposal'");
        $this->addSql("UPDATE type SET description='other' WHERE description= 'Other'");
        $this->addSql("UPDATE type SET description='it_purchase_request' WHERE description= 'IT Purchase request'");
        $this->addSql("UPDATE type SET description='major_impact_on_business_activity' WHERE description= 'All users cannot proceed'");
    }

    public function down(Schema $schema): void
    {
    }
}
