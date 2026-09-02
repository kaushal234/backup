<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use App\Entity\Activity\Comment;
use App\Entity\Activity\CommentFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:project_comment_files', description: 'Import TTS comment files from legacy')]
class ImportMISProjectTaskCommentFileCommand extends Command
{
    public function __construct(
        private readonly FileHelper $fileHelper,
        private readonly Connection $legacyConnection
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT tasks_comments.id, tasks_comments.parent_id, tasks_comments.filename, tasks_comments.date
            from tasks_comments
            LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
            LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
            WHERE tasks.module = 'TTS' AND mis_tts.status <> 'QUEUE' AND tasks_comments.filename <> ''
            ORDER BY tasks_comments.id DESC
            SQL;
        $result = $this->legacyConnection->executeQuery($sql);

        $this->fileHelper->import(
            $result,
            $output,
            Comment::class,
            CommentFile::class,
            'filename',
            'id',
            'tasks_comments',
            static function ($data) {
                $metadata = [];
                if ($data['date']) {
                    $metadata['created_at'] = new \DateTime($data['date']);
                }

                return $metadata;
            },
        );

        return Command::SUCCESS;
    }
}
