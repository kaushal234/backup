<?php

declare(strict_types=1);

namespace App\Controller\Finance;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Factory\ManufacturingMarginBatchFactory;
use App\Serializer\Encoder\XlsxEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ManufacturingMarginBatchController extends AbstractController
{
    public function __construct(
        private readonly ManufacturingMarginBatchFactory $factory,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request)
    {
        /** @var File $file */
        $file = $request->files->get('file');

        if (XlsxEncoder::FORMAT !== $file->guessExtension()) {
            throw new BadRequestHttpException('File must be Xlsx type.');
        }

        $object = $this->factory->createManufacturingMarginBatch($file);

        $violations = $this->validator->validate($object);

        $invalidLines = [];
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $matches = [];
            if (!preg_match_all('#^margins\[(?P<id>.+)].+#', $violation->getPropertyPath(), $matches, \PREG_SET_ORDER)) {
                continue;
            }
            $invalidLines[] = (int) $matches[0]['id'];
        }

        foreach ($object->getMargins() as $index => $margin) {
            if (!\in_array($index, $invalidLines, true)) {
                $this->entityManager->persist($margin);
                continue;
            }

            $object->removeMargin($margin);
        }

        $this->entityManager->flush();

        if (0 !== $violations->count()) {
            throw new ValidationException($violations);
        }

        return $object;
    }
}
