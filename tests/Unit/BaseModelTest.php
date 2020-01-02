<?php

namespace Tests\Unit;

use Tests\TestCase;

use App;

class BaseModelTest extends TestCase {

    /**
     * SCENARIO: get a list of ENUM values associated with a database column, when the column is of type enum
     *  GIVEN:   a database table and column (combines) are of type ENUM
     *  WHEN:    we call DatabaseHelper::get_enum_values()
     *  THEN:    the method should return a non-empty array
     */
    public function testGetEnumValuesWhenColumnIsTypeEnum(){
        // GIVEN
        // as of the migration 2015_09_07_002949_create_account_types_table
        // the database column account_types.type exists and is of type ENUM
        $column = 'type';

        // WHEN
        $enum_values = App\AccountType::get_enum_values($column);

        // THEN
        $this->assertNotEmpty($enum_values);
        $this->assertTrue(is_array($enum_values));
    }

    /**
     * SCENARIO: get a list of ENUM values associated with a database column, when the column is NOT of type enum
     *  GIVEN:   a database table and column (combined) are NOT of type ENUM
     *  WHEN:    we call DatabaseHelper::get_enum_values()
     *  THEN:    an empty array is returned
     */
    public function testGetEnumValuesFromANonEnumColumn(){
        // GIVEN
        // as of the migration 2015_09_07_002949_create_account_types_table
        // the database column account_types.id exists and is of type INT
        $column = 'id';

        // WHEN
        $enum_values = App\AccountType::get_enum_values($column);

        // THEN
        $this->assertEmpty($enum_values);
        $this->assertTrue(is_array($enum_values));
    }

    /**
     * SCENARIO: get a list of ENUM values associated with a database column, when the column doesn't exist
     *  GIVEN:   a database table and column (combined) that does NOT exist
     *  WHEN:    we call DatabaseHelper::get_enum_values()
     *  THEN:    InvalidArgumentException is thrown
     */
    public function testGetEnumValuesFromADataBaseTableColumnThatDoesNotExist(){
        // THEN
        $this->expectException(\InvalidArgumentException::class);

        // GIVEN
        // the database column account_types.foobar does not exist as of 2017-02-05
        $column = 'foobar';

        // WHEN
        $enum_values = App\AccountType::get_enum_values($column);
    }

}