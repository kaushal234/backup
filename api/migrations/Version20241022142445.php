<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022142445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update after Doctrine upgrade';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B56089BF');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B56089BF');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B56089BF FOREIGN KEY (extranet_user_profile_id) REFERENCES extranet_user_profile (id)');
    }
}
