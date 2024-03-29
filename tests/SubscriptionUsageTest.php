<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraPricing\Traits\Activityable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraPricing\Models\{SubscriptionUsage, PricingActivity};

class SubscriptionUsageTest extends TestCase
{
    protected $signature;

    public function setUp(): void
    {
        parent::setUp();

        $this->signature = new SubscriptionUsage();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            SoftDeletes::class,
            Activityable::class,
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(SubscriptionUsage::class))
        );
    }

    public function testFillable()
    {
        $expected = [
            'used',
            'subscription_id',
            'feature_id',
            'metadata'
        ];

        $this->assertEquals($expected, $this->signature->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'int',
            'metadata' => 'array'
        ];

        $this->assertEquals($expected, $this->signature->getCasts());
    }

    public function testDates()
    {
        $expected = array_values([
            'deleted_at',
            'created_at',
            'updated_at'
        ]);

        $this->assertEquals(
            $expected,
            array_values($this->signature->getDates())
        );
    }

    public function testSubscriptionUsageCanHaveActivities()
    {
        $subscriptionUsage = $this->create(SubscriptionUsage::class);

        $activity = $this->create(PricingActivity::class, [
            'activityable_id' => $subscriptionUsage->id,
            'activityable_type' => SubscriptionUsage::class,
        ]);

        $this->assertInstanceOf(MorphMany::class, $subscriptionUsage->activities());
        $this->assertContains($activity->id, $subscriptionUsage->activities->pluck('id'));
    }
}
