<?php

declare(strict_types=1);

namespace App\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Page;
use App\Sdk\Resource\Comment;
use App\Sdk\Resource\CommentFile;
use App\Sdk\Resource\Location;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use App\Sdk\Resource\NonConformity;
use App\Sdk\Resource\Person;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use App\Sdk\Resource\SupplierCorrectiveActionRequestFile;
use App\Sdk\Resource\SupplierCorrectiveActionRequestMainFile;
use App\Sdk\Resource\SupplierCorrectiveActionRequestPart;
use App\Sdk\Resource\SupplierCorrectiveActionRequestStatus;
use App\Sdk\Resource\VendorUser;
use App\Sdk\Resource\VendorWarrantyClaimInterface;
use App\Sdk\Resource\VendorWarrantyClaimStatus;
use App\Sdk\Resource\VendorWarrantyClaimType;
use App\Sdk\Resource\WCVendorWarrantyClaim;
use Psl\Collection\AccessibleCollectionInterface;
use Psl\Collection\Vector;
use Psl\Iter;
use Psl\Type;
use Psl\Vec;

/**
 * @phpstan-import-type SimplifiedSupplierCorrectiveActionRequestStructure from SupplierCorrectiveActionRequest
 *
 * @psalm-import-type SimplifiedSupplierCorrectiveActionRequestStructure from SupplierCorrectiveActionRequest
 *
 * @implements ResourceTransformerInterface<SupplierCorrectiveActionRequest>
 */
final class SupplierCorrectiveActionRequestResourceTransformer implements ResourceTransformerInterface
{
    public function supports(string $resource, mixed $data): bool
    {
        if (SupplierCorrectiveActionRequest::class !== $resource) {
            return false;
        }

        return SupplierCorrectiveActionRequest::getTypeStructure()->matches($data);
    }

    public function transform(mixed $data): SupplierCorrectiveActionRequest
    {
        try {
            $structure = SupplierCorrectiveActionRequest::getTypeStructure()->assert($data);
        } catch (Type\Exception\ExceptionInterface $exception) {
            throw new FailedTransformationException('Failed to transform the given data into a SCAR structure.', previous: $exception);
        }

        $factory = new Location(
            iri: $structure['factory']['@id'],
            name: $structure['factory']['name'],
            erp: $structure['factory']['erp'],
        );
        $poster = null === $structure['poster'] ? null : new Person(
            iri: $structure['poster']['@id'],
            username: $structure['poster']['username'],
            email: $structure['poster']['email'],
            firstname: $structure['poster']['firstname'],
            lastname: $structure['poster']['lastname'],
        );
        $leader = null === $structure['leader'] ? null : new Person(
            iri: $structure['leader']['@id'],
            username: $structure['leader']['username'],
            email: $structure['leader']['email'],
            firstname: $structure['leader']['firstname'],
            lastname: $structure['leader']['lastname'],
        );

        return new SupplierCorrectiveActionRequest(
            iri: $structure['@id'],
            id: $structure['id'],
            poster: $poster,
            leader: $leader,
            iFactor: $structure['iFactor'],
            factory: $factory,
            supplierErp: $structure['supplierErp'] ?? null,
            supplierNumber: $structure['supplierNumber'],
            supplierName: $structure['supplierName'],
            status: SupplierCorrectiveActionRequestStatus::from($structure['status']),
            createdAt: $structure['createdAt'],
            approvedAt: $structure['approvedAt'],
            closedAt: $structure['closedAt'],
            description: $structure['description'],
            shortDescription: $structure['shortDescription'],
            issueOrigin: $structure['issueOrigin'],
            correctiveAction: $structure['correctiveAction'],
            commercialAgreement: $structure['commercialAgreement'],
            representative: null === $structure['representative'] ? null : new Person(
                iri: $structure['representative']['@id'],
                username: $structure['representative']['username'],
                email: $structure['representative']['email'],
                firstname: $structure['representative']['firstname'],
                lastname: $structure['representative']['lastname'],
            ),
            supplierRepresentative: null === $structure['supplierRepresentative'] ? null : new VendorUser(
                iri: $structure['supplierRepresentative']['@id'],
                username: $structure['supplierRepresentative']['username'],
                email: $structure['supplierRepresentative']['email'],
                firstname: $structure['supplierRepresentative']['firstname'],
                lastname: $structure['supplierRepresentative']['lastname'],
            ),
            verificationDescription: $structure['verificationDescription'],
            preventiveAction: $structure['preventiveAction'],
            conclusion: $structure['conclusion'],
            vendorWarrantyClaims: Vec\map(
                $structure['vendorWarrantyClaims'],
                static function (array $claim): ?VendorWarrantyClaimInterface {
                    $status = VendorWarrantyClaimStatus::from(
                        Type\string()->matches($claim['status']) ? $claim['status'] : $claim['status']['name']
                    );

                    if ('NcrVendorWarrantyClaim' === $claim['@type']) {
                        return new NCRVendorWarrantyClaim(
                            iri: $claim['@id'],
                            id: $claim['id'],
                            nonConformity: new NonConformity(
                                iri: $claim['nonConformity']['@id'],
                                id: $claim['nonConformity']['id'],
                                problem: $claim['nonConformity']['problem'],
                            ),
                            type: new VendorWarrantyClaimType(
                                iri: $claim['type']['@id'],
                                name: $claim['type']['name'],
                                description: $claim['type']['description'],
                            ),
                            status: $status,
                        );
                    }

                    if ('WcVendorWarrantyClaim' === $claim['@type']) {
                        return new WCVendorWarrantyClaim(
                            iri: $claim['@id'],
                            id: $claim['id'],
                            status: $status,
                        );
                    }

                    return null;
                },
            ),
            parts: Vec\map($structure['parts'], static fn ($part) => new SupplierCorrectiveActionRequestPart(
                iri: $part['@id'],
                partNumber: $part['partNumber'],
                description: $part['description'],
                quantity: $part['quantity'],
                unitOfMeasure: $part['unitOfMeasure'] ?? null,
            )),
            files: Vec\map($structure['files'], static fn ($file) => new SupplierCorrectiveActionRequestFile(
                iri: $file['@id'],
                id: $file['id'],
                filePath: $file['filePath'],
                poster: (null === $file['poster']) ? null : new Person(
                    iri: $file['poster']['@id'],
                    username: $file['poster']['username'],
                    email: $file['poster']['email'],
                    firstname: $file['poster']['firstname'],
                    lastname: $file['poster']['lastname'],
                ),
                createdAt: $file['createdAt'],
                description: $file['description'],
                sha: $file['sha'],
                mimeType: $file['mimeType'],
                extension: $file['extension'],
                size: $file['size'],
            )),
            mainFile: ($structure['mainFile'] ?? null) === null ? null : new SupplierCorrectiveActionRequestMainFile(
                iri: $structure['mainFile']['@id'],
                id: $structure['mainFile']['id'],
                filePath: $structure['mainFile']['filePath'],
                poster: (null === $structure['mainFile']['poster']) ? null : new Person(
                    iri: $structure['mainFile']['poster']['@id'],
                    username: $structure['mainFile']['poster']['username'],
                    email: $structure['mainFile']['poster']['email'],
                    firstname: $structure['mainFile']['poster']['firstname'],
                    lastname: $structure['mainFile']['poster']['lastname'],
                ),
                createdAt: $structure['mainFile']['createdAt'],
                description: $structure['mainFile']['description'],
                sha: $structure['mainFile']['sha'],
                mimeType: $structure['mainFile']['mimeType'],
                extension: $structure['mainFile']['extension'],
                size: $structure['mainFile']['size']
            ),
            activities: Vec\map($structure['activity'], static fn ($comment) => new Comment(
                iri: $comment['@id'],
                resource: $comment['resource'],
                message: $comment['message'],
                user: (null === $comment['user']) ? null : new Person(
                    iri: $comment['user']['@id'],
                    username: $comment['user']['username'],
                    email: $comment['user']['email'],
                    firstname: $comment['user']['firstname'],
                    lastname: $comment['user']['lastname'],
                ),
                createdAt: $comment['createdAt'],
                updatedAt: $comment['updatedAt'],
                public: $comment['public'],
                metadata: $comment['metadata'],
                files: Vec\map($comment['files'], static fn ($file) => new CommentFile(
                    iri: $file['@id'],
                    id: $file['id'],
                    filePath: $file['filePath'],
                    createdAt: $file['createdAt'],
                )),
            )),
        );
    }

    public function supportsCollection(string $resource, mixed $data): bool
    {
        if (SupplierCorrectiveActionRequest::class !== $resource) {
            return false;
        }

        return SupplierCorrectiveActionRequest::getCollectionTypeStructure()->matches($data);
    }

    /**
     * @return Vector<SupplierCorrectiveActionRequest>
     */
    public function transformCollection(mixed $data): AccessibleCollectionInterface
    {
        try {
            $collection = SupplierCorrectiveActionRequest::getCollectionTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list SCAR structures.', previous: $e);
        }

        $result = $this->getCollectionMembers($collection['hydra:member']);

        return Vector::fromArray($result);
    }

    public function supportsPage(string $resource, mixed $data): bool
    {
        if (SupplierCorrectiveActionRequest::class !== $resource) {
            return false;
        }

        return SupplierCorrectiveActionRequest::getPageTypeStructure()->matches($data);
    }

    /**
     * @return Page<SupplierCorrectiveActionRequest>
     */
    public function transformPage(mixed $data, int $page, int $itemsPerPage): Page
    {
        try {
            $collection = SupplierCorrectiveActionRequest::getPageTypeStructure()->assert($data);
        } catch (Type\Exception\AssertException $e) {
            throw new FailedTransformationException('Failed to transform the given data into a list of SCAR structures.', previous: $e);
        }

        $result = $this->getCollectionMembers($collection['hydra:member']);

        $items = Vector::fromArray($result);

        $totalItems = $data['hydra:totalItems'];
        $hasNext = Iter\contains_key($data['hydra:view'], 'hydra:next');
        $hasPrevious = Iter\contains_key($data['hydra:view'], 'hydra:previous');

        return new Page($page, $itemsPerPage, $totalItems, $hasNext, $hasPrevious, $items);
    }

    /**
     * @param list<SimplifiedSupplierCorrectiveActionRequestStructure> $members
     *
     * @return list<SupplierCorrectiveActionRequest>
     */
    public function getCollectionMembers(array $members): array
    {
        $result = [];
        foreach ($members as $structure) {
            /** @var SimplifiedSupplierCorrectiveActionRequestStructure $structure */
            $result[] = new SupplierCorrectiveActionRequest(
                iri: $structure['@id'],
                id: $structure['id'],
                poster: null === $structure['poster'] ? null : new Person(
                    iri: $structure['poster']['@id'],
                    username: $structure['poster']['username'],
                    email: $structure['poster']['email'],
                    firstname: $structure['poster']['firstname'],
                    lastname: $structure['poster']['lastname'],
                ),
                leader: null === $structure['leader'] ? null : new Person(
                    iri: $structure['leader']['@id'],
                    username: $structure['leader']['username'],
                    email: $structure['leader']['email'],
                    firstname: $structure['leader']['firstname'],
                    lastname: $structure['leader']['lastname'],
                ),
                iFactor: $structure['iFactor'],
                factory: new Location(
                    iri: $structure['factory']['@id'],
                    name: $structure['factory']['name'],
                    erp: $structure['factory']['erp'],
                ),
                supplierErp: $structure['supplierErp'],
                supplierNumber: $structure['supplierNumber'],
                supplierName: $structure['supplierName'],
                status: SupplierCorrectiveActionRequestStatus::from($structure['status']),
                createdAt: $structure['createdAt'],
            );
        }

        return $result;
    }
}
