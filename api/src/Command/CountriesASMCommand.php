<?php

declare(strict_types=1);

namespace App\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Continent;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesArea;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(name: 'tld:countries:asm:add')]
class CountriesASMCommand extends Command
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly ValidatorInterface $validator;

    /**
     * CountriesASMCommand constructor.
     */
    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        parent::__construct();
        $this
            ->setDescription('Link ASM to countries')
            ->addArgument('asm', InputArgument::REQUIRED, 'iri of ASM')
            ->addArgument('sso', InputArgument::REQUIRED, 'iri of SSO')
            ->addArgument('continent', InputArgument::REQUIRED, 'iri of Continent')
        ;

        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $asmIri */
        $asmIri = $input->getArgument('asm');
        /** @var string $ssoIri */
        $ssoIri = $input->getArgument('sso');
        /** @var string $continentIri */
        $continentIri = $input->getArgument('continent');

        /** @var People $asm */
        $asm = $this->iriConverter->getResourceFromIri($asmIri);
        /** @var Location $sso */
        $sso = $this->iriConverter->getResourceFromIri($ssoIri);
        /** @var Continent $continent */
        $continent = $this->iriConverter->getResourceFromIri($continentIri);

        foreach ($continent->getCountries() as $country) {
            $salesArea = new SalesArea();
            $salesArea
                ->setAsm($asm)
                ->setSso($sso)
                ->setCountry($country)
            ;
            $violations = $this->validator->validate($salesArea);
            if (0 === $violations->count()) {
                $this->entityManager->persist($salesArea);
            }

            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $output->writeln(\sprintf('<info>Validation failed for country %s (%s)</info>', $country->getName(), (string) $violation->getMessage()));
            }
        }
        $this->entityManager->flush();

        return 0;
    }
}
