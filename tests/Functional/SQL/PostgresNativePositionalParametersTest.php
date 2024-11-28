<?php

declare(strict_types=1);

namespace Doctrine\DBAL\Tests\Functional\SQL;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Tests\FunctionalTestCase;
use Doctrine\DBAL\Tests\TestUtil;
use Doctrine\DBAL\Types\Types;

final class PostgresNativePositionalParametersTest extends FunctionalTestCase
{
    public function testPostgresNativePositionalParameters(): void
    {
        if (! TestUtil::isDriverOneOf('pgsql')) {
            self::markTestSkipped('This test requires the pgsql driver.');
        }

        $table = new Table('dummy_table');
        $table->addColumn('a_number', Types::SMALLINT);
        $table->addColumn('a_number_2', Types::SMALLINT);
        $table->addColumn('b_number', Types::SMALLINT);
        $table->addColumn('c_number', Types::SMALLINT);
        $table->addColumn('a_number_3', Types::SMALLINT);
        $this->dropAndCreateTable($table);
        $this->connection->executeStatement(
            'INSERT INTO dummy_table (a_number, a_number_2, b_number, c_number, a_number_3)' .
            ' VALUES ($1, $1, $2, $3, $1)',
            [1, 2, 3],
            [ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::INTEGER],
        );
        $result = $this->connection->executeQuery('SELECT * FROM dummy_table')->fetchAllAssociative();
        self::assertCount(1, $result);
        self::assertEquals(1, $result[0]['a_number']);
        self::assertEquals(1, $result[0]['a_number_2']);
        self::assertEquals(2, $result[0]['b_number']);
        self::assertEquals(3, $result[0]['c_number']);
        self::assertEquals(1, $result[0]['a_number_3']);
    }
}
