<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Models\User;
use Rockbuzz\LaraPricing\Traits\Uuid;
use Rockbuzz\LaraPricing\Models\PricingActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PricingActivityTest extends TestCase
{
    protected $signature;

    public function setUp(): void
    {
        parent::setUp();

        $this->signature = new PricingActivity();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(PricingActivity::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->signature->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->signature->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'description',
            'changes',
            'activityable_id',
            'activityable_type',
            'causeable_id',
            'causeable_type'
        ];

        $this->assertEquals($expected, $this->signature->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'changes' => 'array',
            'created_at' => 'datetime'
        ];

        $this->assertEquals($expected, $this->signature->getCasts());
    }

    public function testPricingActivityHasCauser()
    {
        $user = $this->create(User::class);
        $activity = $this->create(PricingActivity::class, [
            'causeable_id' => $user->id,
            'causeable_type' => User::class,
        ]);
        $this->assertInstanceOf(MorphTo::class, $activity->causer());
        $this->assertEquals($user->id, $activity->causer->id);
    }
}
