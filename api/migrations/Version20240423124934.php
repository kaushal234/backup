<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423124934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add solution proposed at filed on Trouble Ticket';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket ADD solution_proposed_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE type ADD displayed_order INT DEFAULT NULL');

        $this->addSql('UPDATE type SET description= "Security - High Attention",  displayed_order=1 WHERE id=2');
        $this->addSql('UPDATE type SET description= "Security - Not Urgent",  displayed_order=2 WHERE id=3');
        $this->addSql('UPDATE type SET description= "I cannot proceed",  displayed_order=3 WHERE id=1');
        $this->addSql('UPDATE type SET description= "Multiple users cannot proceed",  displayed_order=4 WHERE id=5');
        $this->addSql('UPDATE type SET description= "Annoying but I can proceed",  displayed_order=5 WHERE id=4');

        $this->addSql('UPDATE type SET description= "More permission needed",  displayed_order=6 WHERE id=6');
        $this->addSql('UPDATE type SET description= "More training needed",  displayed_order=7 WHERE id=7');
        $this->addSql('UPDATE type SET description= "New feature proposal",  displayed_order=8 WHERE id=8');
        $this->addSql('UPDATE type SET description= "Other",  displayed_order=10 WHERE id=9');
        $this->addSql('UPDATE type SET description= "IT Purchase request",  displayed_order=9  WHERE id=10');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_TROUBLE_TICKET_OPEN_ON_BEHALF")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
          SELECT user_group.id, feature.id
          FROM user_group, feature
          WHERE feature.name = "FEATURE_TROUBLE_TICKET_OPEN_ON_BEHALF"
          AND user_group.name ="GG_MIS"'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trouble_ticket DROP solution_proposed_at');
        $this->addSql('ALTER TABLE type DROP displayed_order');
    }
}
