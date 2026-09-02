<?php

declare(strict_types=1);

namespace App\Postman\Command;

use App\Postman\Builder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'api:postman',
    description: 'Generate postman collection JSON file',
)]
class ApiPostmanCommand extends Command
{
    public const FILENAME = 'TLD.postman_collection.json';

    public function __construct(
        private readonly Builder $builder,
        private readonly SerializerInterface $serializer,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $collection = $this->builder->build();

        try {
            $this->filesystem->dumpFile($this->projectDir.'/'.self::FILENAME, $this->serializer->serialize($collection, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]));

            $io->success('Postman JSON File created');
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
