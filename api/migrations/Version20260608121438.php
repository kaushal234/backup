<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608121438 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to user table and unique constraint to user_group';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX user_discr_hidden_disabled_index ON user (discr, hidden, disabled)');
        $this->addSql('CREATE INDEX user_lastname_firstname_index ON user (lastname, firstname)');

        // Remove duplicate entry 'SEQ_outbound_not_WO' not used.
        $this->addSql('DELETE FROM user_group WHERE id = 175 and name="SEQ_outbound_not_WO"');
        // Remove duplicate entry 'pi_PILOT_TAXIBOT' not used.
        $this->addSql('DELETE FROM user_group WHERE id = 164 and name="pi_PILOT_TAXIBOT"');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F02BF9D5E237E06 ON user_group (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8F02BF9D5E237E06 ON user_group');
        $this->addSql('DROP INDEX user_discr_hidden_disabled_index ON user');
        $this->addSql('DROP INDEX user_lastname_firstname_index ON user');
    }
}
