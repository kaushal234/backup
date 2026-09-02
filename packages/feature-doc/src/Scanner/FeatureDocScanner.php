<?php

declare(strict_types=1);

namespace Alvest\FeatureDoc\Scanner;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;

final class FeatureDocScanner
{
    /**
     * @return list<array{file:string,line:int,path:string}>
     */
    public function scan(string $srcDir): array
    {
        $finder = (new Finder())
            ->files()
            ->in($srcDir)
            ->name('*.php');

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        $hits = [];

        foreach ($finder as $file) {
            $code = $file->getContents();

            try {
                $ast = $parser->parse($code);
            } catch (Error) {
                continue;
            }

            if (null === $ast) {
                continue;
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new class($file->getRealPath(), $hits) extends NodeVisitorAbstract {
                /** @var list<array{file:string,line:int,path:string}> */
                private array $hitsRef;

                public function __construct(
                    private readonly string $filename,
                    array &$hitsRef,
                ) {
                    $this->hitsRef = &$hitsRef;
                }

                public function enterNode(Node $node)
                {
                    if (!\property_exists($node, 'attrGroups') || empty($node->attrGroups)) {
                        return null;
                    }

                    foreach ($node->attrGroups as $group) {
                        foreach ($group->attrs as $attr) {
                            $name = $attr->name->toString();

                            if (!\str_ends_with($name, 'FeatureDoc')) {
                                continue;
                            }

                            $arg0 = $attr->args[0]->value ?? null;

                            if (!$arg0 instanceof Node\Scalar\String_) {
                                continue;
                            }

                            $this->hitsRef[] = [
                                'file' => $this->filename,
                                'line' => $attr->getStartLine(),
                                'path' => $arg0->value,
                            ];
                        }
                    }

                    return null;
                }
            });

            $traverser->traverse($ast);
        }

        return $hits;
    }
}
