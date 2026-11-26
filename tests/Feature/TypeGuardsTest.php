<?php

declare(strict_types=1);

use Pollora\Metabox\CustomTable\TableName;
use Pollora\Metabox\Transformer\EnumTransformer;

it('reports a model whose getTable() gives no table name', function (): void {
    $model = new class
    {
        public function getTable(): ?string
        {
            return null;
        }
    };

    expect(fn (): string => (new TableName($model::class))->resolve())
        ->toThrow(LogicException::class, 'must return a table name');
});

it('rejects a class that is not a backed enum', function (): void {
    expect(fn (): string => EnumTransformer::convertStringToEnumValue('a', stdClass::class))
        ->toThrow(InvalidArgumentException::class, 'Invalid enum class');
});
