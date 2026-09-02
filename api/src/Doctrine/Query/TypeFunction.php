<?php

declare(strict_types=1);

namespace App\Doctrine\Query;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

class TypeFunction extends FunctionNode
{
    public $field;

    public function getSql(SqlWalker $sqlWalker): string
    {
        $qComp = $sqlWalker->getQueryComponent($this->field);
        /** @var ClassMetadata $class */
        $class = $qComp['metadata'];

        $tableAlias = $sqlWalker->getSQLTableAlias($class->getTableName(), $this->field);

        if (!isset($class->discriminatorColumn['name'])) {
            throw QueryException::semanticalError('TYPE() only supports entities with a discriminator column.');
        }

        return $tableAlias.'.'.$class->discriminatorColumn['name'];
    }

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);

        $this->field = $parser->IdentificationVariable();

        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }
}
