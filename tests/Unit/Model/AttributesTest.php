<?php
namespace Mongolid\Model;

use Mongolid\TestCase;
use stdClass;

class AttributesTest extends TestCase
{
    public function testShouldHaveDynamicSetters()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;
        };

        $childObj = new stdClass();

        // Assert
        $model->name = 'John';
        $model->age = 25;
        $model->child = $childObj;
        $this->assertSame(
            [
                'name' => 'John',
                'age' => 25,
                'child' => $childObj,
            ],
            $model->attributes()
        );
    }

    public function testShouldHaveDynamicGetters()
    {
        // Arrange
        $child = new stdClass();
        $attributes = [
            'name' => 'John',
            'age' => 25,
            'child' => $child,
        ];

        $model = new class($attributes)
        {
            use Attributes;

            public function __construct(array $attributes)
            {
                $this->_mongolid_attributes = $attributes;
            }
        };

        // Assert
        $this->assertEquals('John', $model->name);
        $this->assertEquals(25, $model->age);
        $this->assertEquals($child, $model->child);
        $this->assertEquals(null, $model->nonexistant);
    }

    public function testShouldCheckIfAttributeIsSet()
    {
        // Arrange
        $model = new class(['name' => 'John', 'ignored' => null])
        {
            use Attributes;

            public function __construct(array $attributes)
            {
                $this->_mongolid_attributes = $attributes;
            }
        };

        // Assert
        $this->assertTrue(isset($model->name));
        $this->assertFalse(isset($model->nonexistant));
        $this->assertFalse(isset($model->ignored));
    }

    public function testShouldCheckIfMutatedAttributeIsSet()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;

            public function __construct()
            {
                $this->mutable = true;
            }

            public function getNameAttribute()
            {
                return 'John';
            }
        };

        // Assert
        $this->assertTrue(isset($model->name));
        $this->assertFalse(isset($model->nonexistant));
    }

    public function testShouldUnsetAttributes()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;

            public function __construct()
            {
                $this->_mongolid_attributes = [
                    'name' => 'John',
                    'age' => 25,
                ];
            }
        };

        // Act
        unset($model->age);
        $result = $model->attributes();

        // Assert
        $this->assertSame(['name' => 'John'], $result);
    }

    public function testShouldGetAttributeFromMutator()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;

            public function __construct()
            {
                $this->mutable = true;
            }

            public function getSomeAttribute()
            {
                return 'something-else';
            }
        };

        $model->some = 'some-value';

        // Assert
        $this->assertEquals('something-else', $model->some);
    }

    public function testShouldIgnoreMutators()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;

            public function getSomeAttribute()
            {
                return 'something-else';
            }

            public function setSomeAttribute($value)
            {
                return strtoupper($value);
            }
        };

        $model->some = 'some-value';

        // Assert
        $this->assertEquals('some-value', $model->some);
    }

    public function testShouldSetAttributeFromMutator()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;

            public function __construct()
            {
                $this->mutable = true;
            }

            public function setSomeAttribute($value)
            {
                return strtoupper($value);
            }
        };

        $model->some = 'some-value';

        // Assert
        $this->assertEquals('SOME-VALUE', $model->some);
    }

    /**
     * @dataProvider getFillableOptions
     */
    public function testShouldFillOnlyPermittedAttributes(
        $fillable,
        $guarded,
        $input,
        $expected
    ) {
        // Arrange
        $model = new class($fillable, $guarded)
        {
            use Attributes;

            public function __construct(array $fillable, array $guarded)
            {
                $this->fillable = $fillable;
                $this->guarded = $guarded;
            }
        };

        // Act
        $model->fill($input);

        // Assert
        $this->assertSame($expected, $model->attributes());
    }

    public function testShouldForceFillAttributes()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;
        };

        $input = [
            'name' => 'Josh',
            'notAllowedAttribute' => true,
        ];

        // Act
        $model->fill($input, true);

        // Assert
        $this->assertTrue($model->notAllowedAttribute);
    }

    public function testShouldBeCastableToArray()
    {
        // Arrange
        $model = new class()
        {
            use Attributes;
        };

        $model->name = 'John';
        $model->age = 25;

        // Assert
        $this->assertEquals(
            ['name' => 'John', 'age' => 25],
            $model->toArray()
        );
    }

    public function testShouldSetOriginalAttributes()
    {
        // Arrange
        $model = new class() implements AttributesAccessInterface
        {
            use Attributes;
        };

        $model->name = 'John';
        $model->age = 25;

        // Act
        $model->syncOriginalAttributes();

        // Assert
        $this->assertSame($model->attributes(), $model->originalAttributes());
    }

    public function getFillableOptions()
    {
        return [
            // -----------------------------
            '$fillable = []; $guarded = []' => [
                'fillable' => [],
                'guarded' => [],
                'input' => [
                    'name' => 'John',
                    'age' => 25,
                    'sex' => 'male',
                ],
                'expected' => [
                    'name' => 'John',
                    'age' => 25,
                    'sex' => 'male',
                ],
            ],

            // -----------------------------
            '$fillable = ["name"]; $guarded = []' => [
                'fillable' => ['name'],
                'guarded' => [],
                'input' => [
                    'name' => 'John',
                    'age' => 25,
                    'sex' => 'male',
                ],
                'expected' => [
                    'name' => 'John',
                ],
            ],

            // -----------------------------
            '$fillable = []; $guarded = []' => [
                'fillable' => [],
                'guarded' => ['sex'],
                'input' => [
                    'name' => 'John',
                    'age' => 25,
                    'sex' => 'male',
                ],
                'expected' => [
                    'name' => 'John',
                    'age' => 25,
                ],
            ],

            // -----------------------------
            '$fillable = ["name", "sex"]; $guarded = ["sex"]' => [
                'fillable' => ['name', 'sex'],
                'guarded' => ['sex'],
                'input' => [
                    'name' => 'John',
                    'age' => 25,
                    'sex' => 'male',
                ],
                'expected' => [
                    'name' => 'John',
                ],
            ],
        ];
    }
}
