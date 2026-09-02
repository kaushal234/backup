<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use App\Entity\MIS\Project\Project;
use LegacyBundle\Command\Helper\CommentImportHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:projects_comments', description: 'Import Projects comments from legacy')]
class ImportProjectCommentsCommand extends Command
{
    public function __construct(
        private readonly CommentImportHelper $commentImportHelper,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commentImportHelper->progressiveImport($output, 'TTS', Project::class, 'mis_tts');

        return Command::SUCCESS;
    }
}
