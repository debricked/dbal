<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Driver\PgSQL;

use Doctrine\DBAL\SQL\Parser\Visitor;

use function count;
use function implode;
use function ltrim;
use function str_starts_with;

final class ConvertParameters implements Visitor
{
    /** @var list<string> */
    private array $buffer = [];

    /** @var array<array-key, int> */
    private array $parameterMap = [];

    public function acceptPositionalParameter(string $sql): void
    {
        if (str_starts_with($sql, '$')) {
            $position                      = (int) ltrim($sql, '$');
            $this->parameterMap[$position] = $position;
            $this->buffer[]                = $sql;

            return;
        }

        $position                      = count($this->parameterMap) + 1;
        $this->parameterMap[$position] = $position;
        $this->buffer[]                = '$' . $position;
    }

    public function acceptNamedParameter(string $sql): void
    {
        $position                 = count($this->parameterMap) + 1;
        $this->parameterMap[$sql] = $position;
        $this->buffer[]           = '$' . $position;
    }

    public function acceptOther(string $sql): void
    {
        $this->buffer[] = $sql;
    }

    public function getSQL(): string
    {
        return implode('', $this->buffer);
    }

    /** @return array<array-key, int> */
    public function getParameterMap(): array
    {
        return $this->parameterMap;
    }
}
