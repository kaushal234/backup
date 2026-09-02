<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160322050632 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_businessunit ADD domain VARCHAR(255) DEFAULT NULL');
        $domain = [
            '@tld-america.com' => [
                'TLD AME',
                'TLD LAAJ',
                'TLD SHE',
                'TLD WIN',
            ],
            '@tld-asia.com' => [
                'TLD ASI',
                'TLD CHI',
                'TLD GST',
                'TLD SHA',
                'TLD SIN',
                'TLD WUX',
            ],
            '@tld-europe.com' => [
                'TLD EUR',
                'TLD DTV',
                'TLD MTL',
                'TLD STL',
            ],
            '@tld-group.com' => [
                'TLD GRP',
            ],
            '@tld-meai.com' => [
                'TLD MEAI',
            ],
        ];
        foreach ($domain as $email => $bu) {
            $this->addSql('UPDATE directory_businessunit SET domain="'.$email.'" WHERE name IN ("'.implode('","', $bu).'") ');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE directory_businessunit DROP domain');
    }
}
