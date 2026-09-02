<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->skip([
        'src/Driver/Connection.php', // false positive, will remove the query content
        'src/Migrations/*',
        'tests/Manager/Kiriba/KiribaEncrypterTest.php',
    ]);
    $rectorConfig->paths(['src', 'tests']);
    $rectorConfig->importNames();
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::PHP_81);
    $rectorConfig->import(SymfonySetList::SYMFONY_44);
    $rectorConfig->import(SymfonySetList::SYMFONY_50);
    $rectorConfig->import(SymfonySetList::SYMFONY_50_TYPES);
    $rectorConfig->import(SymfonySetList::SYMFONY_52);
    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $rectorConfig->import(DoctrineSetList::DOCTRINE_DBAL_30);
    $rectorConfig->sets([
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
            new AnnotationToAttribute('Symfony\Component\Serializer\Annotation\Groups'),
            new AnnotationToAttribute('ApiPlatform\Core\Annotation\ApiResource'),
            new AnnotationToAttribute('ApiPlatform\Core\Annotation\ApiProperty'),
            new AnnotationToAttribute('ApiPlatform\Core\Annotation\ApiFilter'),
            new AnnotationToAttribute('App\Doctrine\Mapping\Attributes\Exclude'),
            new AnnotationToAttribute('App\Doctrine\Mapping\Attributes\Loggable'),
            new AnnotationToAttribute('App\Doctrine\Mapping\Attributes\LoggedDate'),
            new AnnotationToAttribute('App\Doctrine\Mapping\Attributes\LoggedName'),
            new AnnotationToAttribute('App\Doctrine\Mapping\Attributes\Transferable'),
            new AnnotationToAttribute('App\Validator\Constraints\OpenTasks'),
            new AnnotationToAttribute('App\Validator\Constraints\ResourceExists'),
            new AnnotationToAttribute('App\Validator\Constraints\PremiseArchived'),
            new AnnotationToAttribute('App\Validator\Constraints\PersistableTrainingAttendee'),
            new AnnotationToAttribute('App\Validator\Constraints\TimeZone'),
            new AnnotationToAttribute('App\Validator\Constraints\EmailList'),
            new AnnotationToAttribute('App\Validator\Constraints\ErpCustomer'),
            new AnnotationToAttribute('App\Validator\Constraints\EquipmentHourmeterTotalizer'),
            new AnnotationToAttribute('App\Validator\Constraints\QuotationTransferable'),
            new AnnotationToAttribute('App\Validator\Constraints\UniqueBaanCustomerPurchaseOrder'),
            new AnnotationToAttribute('App\Validator\Constraints\Password'),
            new AnnotationToAttribute('App\Validator\Constraints\PeopleRoles'),
            new AnnotationToAttribute('App\Validator\Constraints\SalesOrder'),
            new AnnotationToAttribute('App\Validator\Constraints\ManualDocumentFile'),
            new AnnotationToAttribute('App\Validator\Constraints\LockedValue'),
            new AnnotationToAttribute('App\Validator\Constraints\Location'),
            new AnnotationToAttribute('App\Validator\Constraints\Currency'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\Column'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\Copy'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\EmbeddedColumn'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\ExtraColumn'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\ExtraTable'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\ExtraTableColumn'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\Id'),
            new AnnotationToAttribute('LegacyBundle\Doctrine\Mapping\Attributes\Synchronize'),
            new AnnotationToAttribute('Gedmo\Mapping\Annotation\Timestampable'),
            new AnnotationToAttribute('Gedmo\Mapping\Annotation\Translatable'),
            new AnnotationToAttribute('Gedmo\Mapping\Annotation\SoftDeleteable'),
            new AnnotationToAttribute('Gedmo\Mapping\Annotation\Blameable'),
        ]
    );
};
