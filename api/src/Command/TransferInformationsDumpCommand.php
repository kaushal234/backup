<?php

declare(strict_types=1);

namespace App\Command;

use App\Doctrine\EntityOwnerFinder;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Doctrine\OwnerReflectionBag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:transfer:dump')]
class TransferInformationsDumpCommand extends Command
{
    private readonly EntityOwnerFinder $entityOwnerFinder;

    public function __construct(EntityOwnerFinder $entityOwnerFinder)
    {
        parent::__construct();
        $this
            ->setDescription('Get all transfer information of an entity')
            ->addArgument('entity')
        ;
        $this->entityOwnerFinder = $entityOwnerFinder;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $entity */
        $entity = $input->getArgument('entity');

        if (false !== mb_strpos($entity, ':')) {
            throw new \InvalidArgumentException('Only FQCN are allowed.');
        }

        $owners = $this->entityOwnerFinder->getOwners($entity);

        $table = new Table($output);
        $table
            ->setHeaders(['Entity', 'Property', 'Handler'])
            ->setRows(array_reduce($owners, static function ($memo, OwnerReflectionBag $owner) {
                $propertyAttributes = $owner->getReflectionProperty()->getAttributes(Transferable::class);
                foreach ($propertyAttributes as $propertyAttribute) {
                    $transferable = $propertyAttribute->newInstance();
                    $memo[] = [
                        $owner->getReflectionClass()->getName(),
                        $owner->getReflectionProperty()->getName(),
                        $transferable->handler,
                    ];
                }

                return $memo;
            }, []))
        ;
        $table->render();

        return 0;
    }
}
