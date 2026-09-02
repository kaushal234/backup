<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200915082922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add training subscription';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE training_subscription (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, type_id INT DEFAULT NULL, subscriber_id INT NOT NULL, INDEX IDX_BBD548DEB62DE735 (category_id), INDEX IDX_BBD548DE18721C9D (type_id), INDEX IDX_BBD548DE7808B1AD (subscriber_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DEB62DE735 FOREIGN KEY (category_id) REFERENCES customers (id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DE18721C9D FOREIGN KEY (type_id) REFERENCES trainings_types (id)');
        $this->addSql('ALTER TABLE training_subscription ADD CONSTRAINT FK_BBD548DE7808B1AD FOREIGN KEY (subscriber_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX unique_subscription_by_type ON training_subscription (subscriber_id, category_id, type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_subscription_by_type ON training_subscription');
        $this->addSql('DROP TABLE training_subscription');
    }
}
