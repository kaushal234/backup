<?php

declare(strict_types=1);

namespace App\Controller\Finance\AccountReceivable;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Message\Finance\AccountReceivableImportFromFile;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class AccountReceivableImportFileController extends AbstractController
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly Security $security,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapEntity(id: 'ssoId')]
        Location $location
    ) {
        /** @var File $file */
        $file = $request->files->get('file');

        if (XlsxEncoder::FORMAT !== $file->guessExtension()) {
            throw new BadRequestHttpException('File must be Xlsx type.');
        }

        /** @var People $user */
        $user = $this->security->getUser();
        $locationIri = $this->iriConverter->getIriFromResource($location);
        $file->move(sys_get_temp_dir(), $file->getFilename());

        $this->messageBus->dispatch(new AccountReceivableImportFromFile(\sprintf('%s/%s', sys_get_temp_dir(), $file->getFilename()), $user->getEmail(), $locationIri));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
