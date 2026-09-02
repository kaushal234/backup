<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);
    //    $rectorConfig->importNames();
    //    $rectorConfig->import(SetList::CODE_QUALITY);
    //    $rectorConfig->import(SetList::PHP_74);
    //    $rectorConfig->import(SetList::PHP_81);
    //    $rectorConfig->import(SymfonySetList::SYMFONY_44);
    //    $rectorConfig->import(SymfonySetList::SYMFONY_CODE_QUALITY);

    //     register a single rule
    //    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    //
    //    $rectorConfig->rules([
    //        ReturnTypeFromStrictNativeCallRector::class,
    //        ReturnTypeFromStrictScalarReturnExprRector::class,
    //    ]);
    //
    //    $rectorConfig->sets([
    //        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    //    ]);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            //                        new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Template'),
            //            new AnnotationToAttribute('AppBundle\Configuration\ApiParamConverter'),
            new AnnotationToAttribute('Sensio\Bundle\FrameworkExtraBundle\Configuration\Security'),
            //            new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route'),
        ]
    );
};
