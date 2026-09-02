<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180131133449 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE leasing_contracts (id INT AUTO_INCREMENT NOT NULL COMMENT \'(DC2Type:integer)\', representative_id INT DEFAULT NULL COMMENT \'(DC2Type:integer)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', start_date DATE NOT NULL, expiration_date DATE NOT NULL, description VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string)\', INDEX IDX_976D1AE5783E3463 (representative_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leasing_contracts_equipment_records (leasing_contract_id INT NOT NULL COMMENT \'(DC2Type:integer)\', equipment_record_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_E9B09FA15005B16A (leasing_contract_id), INDEX IDX_E9B09FA19FC03375 (equipment_record_id), PRIMARY KEY(leasing_contract_id, equipment_record_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leasing_contracts_user_xus (leasing_contract_id INT NOT NULL COMMENT \'(DC2Type:integer)\', extranet_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_8CE98BBD5005B16A (leasing_contract_id), INDEX IDX_8CE98BBDD2CDD54B (extranet_user_id), PRIMARY KEY(leasing_contract_id, extranet_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leasing_contracts_buyer_xus (leasing_contract_id INT NOT NULL COMMENT \'(DC2Type:integer)\', extranet_user_id INT NOT NULL COMMENT \'(DC2Type:integer)\', INDEX IDX_440743F05005B16A (leasing_contract_id), INDEX IDX_440743F0D2CDD54B (extranet_user_id), PRIMARY KEY(leasing_contract_id, extranet_user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE leasing_contracts ADD CONSTRAINT FK_976D1AE5783E3463 FOREIGN KEY (representative_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE leasing_contracts_equipment_records ADD CONSTRAINT FK_E9B09FA15005B16A FOREIGN KEY (leasing_contract_id) REFERENCES leasing_contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leasing_contracts_equipment_records ADD CONSTRAINT FK_E9B09FA19FC03375 FOREIGN KEY (equipment_record_id) REFERENCES equipment_records (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leasing_contracts_user_xus ADD CONSTRAINT FK_8CE98BBD5005B16A FOREIGN KEY (leasing_contract_id) REFERENCES leasing_contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leasing_contracts_user_xus ADD CONSTRAINT FK_8CE98BBDD2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leasing_contracts_buyer_xus ADD CONSTRAINT FK_440743F05005B16A FOREIGN KEY (leasing_contract_id) REFERENCES leasing_contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leasing_contracts_buyer_xus ADD CONSTRAINT FK_440743F0D2CDD54B FOREIGN KEY (extranet_user_id) REFERENCES user (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_LEASING_CONTRACT_WRITE")');
        $this->addSql('INSERT IGNORE INTO feature_group (group_id, feature_id)
                       SELECT user_group.id, feature.id
                         FROM user_group, feature
                        WHERE feature.name = "FEATURE_LEASING_CONTRACT_WRITE"
                          AND user_group.name = "SUPERUSER"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE leasing_contracts_equipment_records DROP FOREIGN KEY FK_E9B09FA15005B16A');
        $this->addSql('ALTER TABLE leasing_contracts_user_xus DROP FOREIGN KEY FK_8CE98BBD5005B16A');
        $this->addSql('ALTER TABLE leasing_contracts_buyer_xus DROP FOREIGN KEY FK_440743F05005B16A');
        $this->addSql('DROP TABLE leasing_contracts');
        $this->addSql('DROP TABLE leasing_contracts_equipment_records');
        $this->addSql('DROP TABLE leasing_contracts_user_xus');
        $this->addSql('DROP TABLE leasing_contracts_buyer_xus');
    }
}
