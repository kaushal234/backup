<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Doctrine\Migration\DoctrineMigrationHelperTrait;
use App\Entity\PowerBI\Category;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829121516 extends AbstractMigration
{
    use DoctrineMigrationHelperTrait;

    public function getDescription(): string
    {
        return 'Add Power BI reports';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE power_bi_reports (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, power_bi_uuid_object BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F85429024689FFC ON power_bi_reports (power_bi_uuid_object)');

        $this->addSql('CREATE TABLE power_bi_reports_groups (report_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_47DC43BC4BD2A4C0 (report_id), INDEX IDX_47DC43BCFE54D947 (group_id), PRIMARY KEY(report_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE power_bi_reports_groups ADD CONSTRAINT FK_47DC43BC4BD2A4C0 FOREIGN KEY (report_id) REFERENCES power_bi_reports (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE power_bi_reports_groups ADD CONSTRAINT FK_47DC43BCFE54D947 FOREIGN KEY (group_id) REFERENCES user_group (id) ON DELETE CASCADE');

        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWER_BI_REPORT_CREATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWER_BI_REPORT_READ")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWER_BI_REPORT_UPDATE")');
        $this->addSql('INSERT IGNORE INTO feature (name) VALUES("FEATURE_POWER_BI_REPORT_DELETE")');

        $this->insertFeatureGroup('FEATURE_POWER_BI_REPORT_CREATE', ['ROLE_MISM']);
        $this->insertFeatureGroup('FEATURE_POWER_BI_REPORT_READ', ['ACL_AUTH_INTRANET']);
        $this->insertFeatureGroup('FEATURE_POWER_BI_REPORT_UPDATE', ['ROLE_MISM']);
        $this->insertFeatureGroup('FEATURE_POWER_BI_REPORT_DELETE', ['ROLE_MISM']);

        $values = [
            ['title' => 'BI08', 'description' => 'Inventory Forecast', 'uuid' => 'b6105b5b-3441-4cc5-8a4e-54361a9d964b', 'category' => Category::Materials],
            ['title' => 'BI37', 'description' => 'Inventory by Site', 'uuid' => '22439092-57de-4442-aee5-85c028283283', 'category' => Category::Materials],
            ['title' => 'BI41', 'description' => 'Supplier Dashboard', 'uuid' => 'a804dce6-3a72-47eb-b7f2-6b2296a95656', 'category' => Category::Materials],
            ['title' => 'BI42', 'description' => 'Distribution Orders Dashboard', 'uuid' => 'f8f7f44d-6d67-4659-919f-32257110293c', 'category' => Category::Materials],
            ['title' => 'BI58', 'description' => 'Actual Purchase lead time', 'uuid' => '9a2e3357-9e30-4107-b9e9-b758ecd29544', 'category' => Category::Materials],
            ['title' => 'BI62', 'description' => 'New part numbers creation in LN', 'uuid' => '8f5a692d-eab5-4b76-8a45-ac54783fffdc', 'category' => Category::Engineering],
            ['title' => 'BI19', 'description' => 'SSO Backlog', 'uuid' => 'a6c16e1e-46f0-407a-b126-e9b8181ba243', 'category' => Category::Finance],
            ['title' => 'BI20', 'description' => 'PPV Standard', 'uuid' => 'e9326292-dc3f-4a47-b8cd-807f57ae790a', 'category' => Category::Finance],
            ['title' => 'BI24', 'description' => 'Project Margins', 'uuid' => 'd5911aa5-f7f6-44f4-8438-d04afe79d948', 'category' => Category::Finance],
            ['title' => 'BI33', 'description' => 'SPH Margins', 'uuid' => 'ab52af83-5d15-4d64-838d-2876687bfa95', 'category' => Category::Finance],
            ['title' => 'BI38', 'description' => 'Sales Reconciliation', 'uuid' => 'c1bf7396-5fef-419d-aaef-b2a76e9e865c', 'category' => Category::Finance],
            ['title' => 'BI38', 'description' => 'Purchase Reconciliation', 'uuid' => 'aa9429f0-927e-45be-b186-bad1ae631e8b', 'category' => Category::Finance],
            ['title' => 'BI39', 'description' => 'Standard Cost', 'uuid' => '09033a2a-344c-46c1-b8ba-37833caad95f', 'category' => Category::Finance],
            ['title' => 'BI45', 'description' => 'Cycle Counting', 'uuid' => '438dbfb9-2be4-4553-b56f-33d3259416a2', 'category' => Category::Finance],
            ['title' => 'BI48', 'description' => 'Interco Reconcillation', 'uuid' => 'bde15095-fa80-4539-b25a-d46c8be2af45', 'category' => Category::Finance],
            ['title' => 'BI51', 'description' => 'Backlog Report', 'uuid' => '1b32b678-453e-4a32-9a98-59730406dfc5', 'category' => Category::Finance],
            ['title' => 'BI52', 'description' => 'Booking Report', 'uuid' => '6bd7b704-ebe1-4757-be75-c43e6ab505d9', 'category' => Category::Finance],
            ['title' => 'BI54', 'description' => 'Sales Factories', 'uuid' => '797a978c-b775-46c3-9ae4-43a997104236', 'category' => Category::Finance],
            ['title' => 'BI57', 'description' => 'AR - External Ageing Report', 'uuid' => '0610ed44-9ec0-43a6-a226-41717f49dc4b', 'category' => Category::Finance],
            ['title' => 'BI65', 'description' => 'Average days to pay', 'uuid' => '9e08864d-4e2f-42e3-b768-20343161f8c1', 'category' => Category::Finance],
            ['title' => 'BI71', 'description' => 'Production Order Report', 'uuid' => '9e033427-ad78-4b0c-9f1a-894ef76a8dc5', 'category' => Category::Finance],
            ['title' => 'BI72', 'description' => 'Anonymous Result', 'uuid' => '9d964aec-ccd6-4582-aba4-e0f37b2679ab', 'category' => Category::Finance],
            ['title' => 'BI74', 'description' => 'Suppliers third party GRNI', 'uuid' => '04f92cb4-7fa7-4aa7-8bfd-c0bb4f6c56bf', 'category' => Category::Finance],
        ];

        foreach ($values as $value) {
            $this->addSql('INSERT IGNORE INTO power_bi_reports (title, description, power_bi_uuid_object, category) VALUES(:title, :description, :uuid, :category)', [
                'title' => $value['title'],
                'description' => $value['description'],
                'uuid' => $this->uuid($value['uuid']),
                'category' => $value['category']->value,
            ]);
        }

        $this->insertReportGroups('BI58', ['SUPERUSER', 'ROLE_CFO', 'ROLE_CPO', 'ROLE_BYR', 'ROLE_MLM', 'ROLE_COO', 'ROLE_RCEO', 'ROLE_TCEO', 'ROLE_TCOO', 'ROLE_GCEO']);
        $this->insertReportGroups('BI19', ['ROLE_FC', 'SUPERUSER', 'ROLE_CFO', 'ROLE_SA', 'ROLE_SAM', 'GG_SALES', 'role_gceo', 'ROLE_GCOO', 'ROLE_RCEO']);
        $this->insertReportGroups('BI20', ['SUPERUSER', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_CPO', 'ROLE_PS', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI24', ['SUPERUSER', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_CPO', 'ROLE_PS', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI39', ['SUPERUSER', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_CPO', 'ROLE_PS', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI71', ['SUPERUSER', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_CPO', 'ROLE_PS', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroupsByDescription('Sales Reconciliation', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroupsByDescription('Purchase Reconciliation', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI48', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI57', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI65', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI72', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI74', ['SUPERUSER', 'ROLE_COO', 'ROLE_CFO', 'ROLE_AP', 'ROLE_FC', 'ROLE_CPO', 'ROLE_GCH', 'ROLE_RCEO', 'ROLE_TCOO', 'ROLE_TCEO']);
        $this->insertReportGroups('BI51', ['SUPERUSER', 'ROLE_PSM', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_PS', 'ROLE_PSE', 'ROLE_PSA']);
        $this->insertReportGroups('BI52', ['SUPERUSER', 'ROLE_PSM', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_PS', 'ROLE_PSE', 'ROLE_PSA']);
        $this->insertReportGroups('BI54', ['SUPERUSER', 'ROLE_PSM', 'ROLE_COO', 'ROLE_MLM', 'ROLE_BYR', 'ROLE_CFO', 'ROLE_PM', 'ROLE_AP', 'ROLE_FC', 'ROLE_PLANNER', 'ROLE_PS', 'ROLE_PSE', 'ROLE_PSA']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_5F85429024689FFC ON power_bi_reports');
        $this->addSql('DROP TABLE power_bi_reports');
        $this->addSql('ALTER TABLE report_group DROP FOREIGN KEY FK_47DC43BC4BD2A4C0');
        $this->addSql('ALTER TABLE report_group DROP FOREIGN KEY FK_47DC43BCFE54D947');
        $this->addSql('DROP TABLE report_group');
    }

    private function uuid(string $uuid): string
    {
        return (new Uuid($uuid))->toBinary();
    }

    private function insertReportGroups(string $reportTitle, array $groupNames): void
    {
        foreach ($groupNames as $groupName) {
            $this->addSql(<<<SQL
                INSERT INTO power_bi_reports_groups (report_id, group_id)
                SELECT (SELECT id FROM power_bi_reports WHERE title="$reportTitle"), (SELECT id FROM user_group WHERE name="$groupName")
                SQL);
        }
    }

    private function insertReportGroupsByDescription(string $reportDescription, array $groupNames): void
    {
        foreach ($groupNames as $groupName) {
            $this->addSql(<<<SQL
                INSERT INTO power_bi_reports_groups (report_id, group_id)
                SELECT (SELECT id FROM power_bi_reports WHERE description="$reportDescription"), (SELECT id FROM user_group WHERE name="$groupName")
                SQL);
        }
    }
}
