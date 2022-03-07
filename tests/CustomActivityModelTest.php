<?php

use Spatie\Activitylog\Exceptions\InvalidConfiguration;
use Spatie\Activitylog\Test\Models\Activity;
use Spatie\Activitylog\Test\Models\AnotherInvalidActivity;
use Spatie\Activitylog\Test\Models\InvalidActivity;

uses(TestCase::class);

beforeEach(function () {
    $this->activityDescription = 'My activity';
    collect(range(1, 5))->each(function (int $index) {
        $logName = "log{$index}";
        activity($logName)->log('hello everybody');
    });
});

it('can log activity using a custom model', function () {
    app()['config']->set('activitylog.activity_model', Activity::class);

    $activity = activity()->log($this->activityDescription);

    $this->assertEquals($this->activityDescription, $activity->description);

    $this->assertInstanceOf(Activity::class, $activity);
});

it('does not throw an exception when model config is null', function () {
    app()['config']->set('activitylog.activity_model', null);

    activity()->log($this->activityDescription);

    $this->markTestAsPassed();
});

it('throws an exception when model doesnt implements activity', function () {
    app()['config']->set('activitylog.activity_model', InvalidActivity::class);

    $this->expectException(InvalidConfiguration::class);

    activity()->log($this->activityDescription);
});

it('throws an exception when model doesnt extend model', function () {
    app()['config']->set('activitylog.activity_model', AnotherInvalidActivity::class);

    $this->expectException(InvalidConfiguration::class);

    activity()->log($this->activityDescription);
});

it('doesnt conlict with laravel change tracking', function () {
    app()['config']->set('activitylog.activity_model', Activity::class);

    $properties = [
        'attributes' => [
            'name' => 'my name',
            'text' => null,
        ],
    ];

    $activity = activity()->withProperties($properties)->log($this->activityDescription);

    $this->assertEquals($properties, $activity->changes()->toArray());
    $this->assertEquals($properties, $activity->custom_property->toArray());
});
