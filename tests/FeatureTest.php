<?php

namespace Tests\Models;

use Tests\TestCase;
use Spatie\Sluggable\HasSlug;
use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraPricing\Models\Feature;

class FeatureTest extends TestCase
{
    protected $feature;

    public function setUp(): void
    {
        parent::setUp();

        $this->feature = new Feature();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class,
            HasSlug::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Feature::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->feature->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->feature->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'name',
            'slug',
            'sort_order'
        ];

        $this->assertEquals($expected, $this->feature->getFillable());
    }

    public function testCasts()
    {
        $expected = ['id' => 'string'];

        $this->assertEquals($expected, $this->feature->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->feature->getDates())
        );
    }

    public function testFeatureMustHaveSlug()
    {
        $feature = $this->create(Feature::class, [
            'name' => 'Max Users',
            'slug' => null
        ]);

        $this->assertEquals('max-users', $feature->slug);
    }
}
