<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphTo};
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Rockbuzz\LaraPricing\Events\{SubscriptionCanceled,
    SubscriptionFinished,
    SubscriptionMakeRecurring,
    SubscriptionStarted};

class Subscription extends Model
{
    use Uuid, SoftDeletes, HasSlug;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'start_at',
        'finish_at',
        'canceled_at',
        'due_day',
        'subscribable_id',
        'subscribable_type',
        'plan_id'
    ];

    protected $casts = [
        'id' => 'string',
        'due_date' => 'date'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
        'start_at',
        'finish_at',
        'canceled_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('pricing.tables.subscriptions'));
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('pricing.models.plan'), 'plan_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(config('pricing.models.subscription_usage'), 'subscription_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function start()
    {
        if ($this->isStarted()) {
            throw new \LogicException();
        }

        $this->update(['start_at' => now()]);

        event(new SubscriptionStarted($this));
    }

    public function isStarted()
    {
        return now()->gte($this->start_at);
    }

    public function finish()
    {
        $this->update(['finish_at' => now()]);

        event(new SubscriptionFinished($this));
    }

    public function isFinished()
    {
        return $this->finish_at and now()->gt($this->finish_at);
    }

    public function cancel()
    {
        $this->update(['canceled_at' => now()]);

        event(new SubscriptionCanceled($this));
    }

    public function isCanceled()
    {
        return !!$this->canceled_at;
    }

    public function isActive()
    {
        return $this->isStarted() and !$this->isFinished() and !$this->isCanceled();
    }

    public function isInactive()
    {
        return !$this->isActive();
    }

    public function makeRecurring()
    {
        $this->update(['finish_at' => null]);

        event(new SubscriptionMakeRecurring($this));
    }

    public function isRecurrent()
    {
        return is_null($this->finish_at);
    }

    public function hasTrial()
    {
        return $this->plan->fresh()->trial_period_days > 0;
    }

    public function trial()
    {
        return $this->plan->fresh()->trial_period_days;
    }
}
