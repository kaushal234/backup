<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsCommand(name: 'api:charset:chinese')]
class ReEncodeChineseCommand extends Command
{
    final public const DEFAULT_FILTER = ['Í', 'Ò', 'ý'];

    private readonly EntityManagerInterface $entityManager;
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, PropertyAccessorInterface $propertyAccessor)
    {
        parent::__construct();
        $this->setDescription("Re-encode bad-encoded text to chinese.\r\n
  This command get first 10 rows searching on data bad-encoded. You can check data conversion, validate and go to next 10 rows.
  Ex command for SCAR comment:
  \tmake api-console CMD=\"api:charset:chinese 'App\Entity\Activity\Comment' message\"
  Ex command for NCR:
  \tmake api-console CMD=\"api:charset:chinese 'App\Entity\Quality\NonConformity' problem\"");

        $this->addArgument('entity', InputArgument::REQUIRED, 'Entity class');
        $this->addArgument('attribute', InputArgument::REQUIRED, 'Attribute of entity to perform conversion');
        $this->addOption('filter', 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Add filter to retrieve bad-encoded chinese text (separate by coma)', self::DEFAULT_FILTER);
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit to treat data', 10);

        $this->entityManager = $entityManager;
        $this->propertyAccessor = $propertyAccessor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityClass = $input->getArgument('entity');
        $attribute = $input->getArgument('attribute');
        $filters = $input->getOption('filter');
        $limit = $input->getOption('limit');
        $noInteraction = $input->getOption('no-interaction');

        if (!class_exists($entityClass)) {
            throw new \LogicException(\sprintf('Class %s does not exist', $entityClass));
        }

        if (!property_exists($entityClass, $attribute)) {
            throw new \LogicException(\sprintf('Property %s not found in class %s', $attribute, $entityClass));
        }

        $property = new \ReflectionProperty($entityClass, $attribute);
        if (!$property->getType() instanceof \ReflectionNamedType || 'string' !== $property->getType()->getName()) {
            throw new \LogicException(\sprintf('Property %s must be a type string', $attribute));
        }

        // BINARY is used here to use sensitive case of LIKE condition.
        $this->entityManager->getConfiguration()->addCustomStringFunction('binary', 'App\\Doctrine\\ORM\\Query\\Functions\\Binary');
        $repository = $this->entityManager->getRepository($entityClass);
        $queryBuilder = $repository->createQueryBuilder('rootAlias')
            ->select('rootAlias')
            ->setMaxResults($limit);

        foreach ($filters as $key => $filter) {
            $queryBuilder->orWhere(\sprintf('rootAlias.%s LIKE binary(:value_%s)', $attribute, $key));
            $queryBuilder->setParameter('value_'.$key, \sprintf('%%%s%%', $filter));
        }
        $result = $queryBuilder->getQuery()->getResult();

        if (0 === \count($result)) {
            $output->writeln('No result found.');

            return (int) Command::SUCCESS;
        }

        $table = new Table($output);
        foreach ($result as $row) {
            $originalText = $this->propertyAccessor->getValue($row, $attribute);
            $reencodedText = $this->reencodeChineseString($originalText);
            $this->propertyAccessor->setValue($row, $attribute, $reencodedText);

            // Styling to verify result before updating.
            $table->addRow([\sprintf('<error>%s</error>', wordwrap(preg_replace('/\s+/', ' ', (string) $originalText), 300, "\n"))]);
            $table->addRow([\sprintf('<info>%s</info>', wordwrap(preg_replace('/\s+/', ' ', (string) $reencodedText), 300, "\n"))]);
            $table->addRow([new TableSeparator()]);
        }

        if ($noInteraction) {
            $this->entityManager->flush();

            return Command::SUCCESS;
        }

        $table->render();

        // Interactive shell to ask validation
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Validate conversion? <comment>[no]</comment>', false);
        if ($helper->ask($input, $output, $question)) {
            $this->entityManager->flush();
            $this->execute($input, $output);
        }

        return Command::SUCCESS;
    }

    protected function reencodeChineseString(string $text)
    {
        return mb_convert_encoding(mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8'), 'UTF-8', 'GBK');
    }
}
