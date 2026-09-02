<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create trouble_ticket_support_level table (Atlassian IT support levels), seed it
 * and add the support_level_id column on trouble_ticket.
 */
final class Version20260629132735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add supportLevel to trouble tickets (new trouble_ticket_support_level resource)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trouble_ticket_support_level (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql("INSERT INTO trouble_ticket_support_level (id, level, name, description) VALUES
            (1, 0, 'Self-service', 'Users resolve issues themselves through FAQs, knowledge bases, service catalogs, how-to guides and AI chatbots (lost passwords, outdated profiles, basic application errors).'),
            (2, 1, 'Basic help desk', 'Agents handle minor, low-severity issues: password resets, profile updates and basic software or hardware glitches such as reconnecting to the network or restarting a device.'),
            (3, 2, 'Technical support', 'Agents dig into technical issues — system outages, update errors, hardware malfunctions and permissions flaws — using remote access tools and stricter documentation standards.'),
            (4, 3, 'Expert support', 'Highest in-house tier handling severe or complex incidents: software/API integration, server maintenance and creating or updating standard operating procedures.'),
            (5, 4, 'External support', 'Vendors and highly skilled outside experts handle vendor-specific or complex problems beyond the team scope, such as proprietary hardware repairs and product bugs.')");

        $this->addSql('ALTER TABLE trouble_ticket ADD support_level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trouble_ticket ADD CONSTRAINT FK_29D21CE2C0DDCFC6 FOREIGN KEY (support_level_id) REFERENCES trouble_ticket_support_level (id)');
        $this->addSql('CREATE INDEX IDX_29D21CE2C0DDCFC6 ON trouble_ticket (support_level_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trouble_ticket DROP FOREIGN KEY FK_29D21CE2C0DDCFC6');
        $this->addSql('DROP INDEX IDX_29D21CE2C0DDCFC6 ON trouble_ticket');
        $this->addSql('ALTER TABLE trouble_ticket DROP support_level_id');
        $this->addSql('DROP TABLE trouble_ticket_support_level');
    }
}
