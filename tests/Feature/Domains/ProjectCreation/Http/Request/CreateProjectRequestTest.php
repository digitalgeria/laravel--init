<?php

namespace Tests\Feature\Domains\ProjectCreation\Http\Request;

use Domains\CreateProjectForm\Http\Request\CreateProjectRequest;
use Domains\CreateProjectForm\Http\Request\CreateProjectRequest\CreateProjectRequestParameter as P;
use Domains\CreateProjectForm\Sections\Cache\CacheOption;
use Domains\CreateProjectForm\Sections\Cache\RedisCacheDriver;
use Domains\CreateProjectForm\Sections\Cashier\CashierDriverOption;
use Domains\CreateProjectForm\Sections\Cashier\CashierStripeDriver;
use Domains\CreateProjectForm\Sections\Database\DatabaseOption;
use Domains\CreateProjectForm\Sections\Metadata\PhpVersion;
use Domains\CreateProjectForm\Sections\Queue\QueueDriverOption;
use Domains\CreateProjectForm\Sections\Scout\MeiliSearchScoutDriver;
use Domains\CreateProjectForm\Sections\Scout\ScoutDriverOption;
use Domains\Laravel\Sail\MySQLDatabase;
use Domains\Laravel\StarterKit\Breeze;
use Domains\Laravel\StarterKit\BreezeFrontend;
use Domains\Laravel\StarterKit\StarterKit;
use Illuminate\Support\Facades\Validator;
use Tests\Feature\Domains\ProjectCreation\CreateProjectFormFixtures;
use Tests\TestCase;

class CreateProjectRequestTest extends TestCase
{
    /** @test */
    public function it_creates_a_complete_project_form(): void
    {
        $request = CreateProjectRequest::create(
            '/create-project',
            parameters: CreateProjectFormFixtures::allParameters(),
        );

        Validator::validate($request->all(), $request->rules());

        $form = $request->buildForm();

        $metadata = $form->metadata;
        $this->assertEquals('foo', $metadata->vendorName);
        $this->assertEquals('bar', $metadata->projectName);
        $this->assertEquals(PhpVersion::v8_0, $metadata->phpVersion);
        $this->assertEquals('', $metadata->description);
        $this->assertEquals('foo/bar', $metadata->fullName());

        $authentication = $form->authentication;
        $starterKit = $authentication->starterKit;
        $this->assertInstanceOf(Breeze::class, $starterKit);
        $this->assertEquals(
            BreezeFrontend::BLADE,
            $starterKit->frontend->name,
        );
        $this->assertTrue($authentication->usesSocialite);
        $this->assertTrue($authentication->usesPassport);
        $this->assertTrue($authentication->usesFortify);

        $database = $form->database;
        $this->assertInstanceOf(MySQLDatabase::class, $database->database);

        $cache = $form->cache;
        $this->assertInstanceOf(RedisCacheDriver::class, $cache->driver);

        $queue = $form->queue;
        $this->assertNull($queue->driver);
        $this->assertTrue($queue->usesHorizon);

        $search = $form->search;
        $this->assertTrue($search->driver instanceof MeiliSearchScoutDriver);
        $this->assertInstanceOf(
            MeiliSearchScoutDriver::class,
            $search->driver,
        );

        $devTools = $form->developmentTools;
        $this->assertTrue($devTools->usesMailhog);
        $this->assertTrue($devTools->usesTelescope);
        $this->assertTrue($devTools->usesEnvoy);

        $testing = $form->testing;
        $this->assertTrue($testing->usesPest);
        $this->assertTrue($testing->usesDusk);

        $payment = $form->payment;
        $this->assertTrue($payment->driver instanceof CashierStripeDriver);

        $storage = $form->storage;
        $this->assertTrue($storage->usesMinIO);
    }
}
