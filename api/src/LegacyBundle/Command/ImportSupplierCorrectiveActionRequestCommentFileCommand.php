<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Activity\Comment;
use App\Entity\Activity\CommentFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:scar_comment_files')]
class ImportSupplierCorrectiveActionRequestCommentFileCommand extends Command
{
    private readonly FileHelper $fileHelper;
    private readonly Connection $legacyConnection;

    public function __construct(FileHelper $fileHelper, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Import SCAR comment files from legacy');
        $this->fileHelper = $fileHelper;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT sc.id, f.filename, f.dt
            FROM scar_comments sc
            INNER JOIN file f ON sc.file_id = f.id
            WHERE sc.file_id != 0
            AND f.filename <> ''
            SQL;
        $result = $this->legacyConnection->executeQuery($sql);

        $this->fileHelper->import(
            $result,
            $output,
            Comment::class,
            CommentFile::class,
            'filename',
            'id',
            'mod_files',
            static function ($data) {
                $metadata = [];
                if ($data['dt']) {
                    $metadata['created_at'] = new \DateTime($data['dt']);
                }

                return $metadata;
            },
            'legacyId',
            ['discriminator' => 'scar_conversation']
        );

        return Command::SUCCESS;
    }
}
