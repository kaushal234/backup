<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160523114658 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE feature (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1FD775665E237E06 ON feature (name);');
        $this->addSql('CREATE TABLE feature_group (feature_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_A49E4ECC60E4B879 (feature_id), INDEX IDX_A49E4ECCFE54D947 (group_id), PRIMARY KEY(feature_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE feature_group ADD CONSTRAINT FK_A49E4ECC60E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE feature_group ADD CONSTRAINT FK_A49E4ECCFE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE feature_group DROP FOREIGN KEY FK_A49E4ECC60E4B879');
        $this->addSql('DROP TABLE feature');
        $this->addSql('DROP TABLE feature_group');
    }
}
