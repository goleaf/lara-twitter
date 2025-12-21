<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateMissingTests extends Command
{
    protected $signature = 'test:generate 
                            {--type=all : Type of tests to generate (all, models, services, controllers, livewire)}
                            {--force : Overwrite existing test files}';

    protected $description = 'Generate missing test files for models, services, controllers, and Livewire components';

    public function handle(): int
    {
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info('Generating missing tests...');
        $this->newLine();

        $generated = 0;

        if ($type === 'all' || $type === 'models') {
            $generated += $this->generateModelTests($force);
        }

        if ($type === 'all' || $type === 'services') {
            $generated += $this->generateServiceTests($force);
        }

        if ($type === 'all' || $type === 'controllers') {
            $generated += $this->generateControllerTests($force);
        }

        if ($type === 'all' || $type === 'livewire') {
            $generated += $this->generateLivewireTests($force);
        }

        $this->newLine();
        $this->info("Generated {$generated} test file(s)!");

        return Command::SUCCESS;
    }

    protected function generateModelTests(bool $force): int
    {
        $this->info('Checking models...');
        $models = $this->getModels();
        $generated = 0;

        foreach ($models as $model) {
            $testPath = base_path("tests/Unit/Models/{$model}Test.php");

            if (File::exists($testPath) && !$force) {
                continue;
            }

            $stub = $this->getModelTestStub($model);
            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $stub);
            $this->line("  ✓ Created: tests/Unit/Models/{$model}Test.php");
            $generated++;
        }

        return $generated;
    }

    protected function generateServiceTests(bool $force): int
    {
        $this->info('Checking services...');
        $services = $this->getServices();
        $generated = 0;

        foreach ($services as $service) {
            $testPath = base_path("tests/Unit/Services/{$service}Test.php");

            if (File::exists($testPath) && !$force) {
                continue;
            }

            $stub = $this->getServiceTestStub($service);
            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $stub);
            $this->line("  ✓ Created: tests/Unit/Services/{$service}Test.php");
            $generated++;
        }

        return $generated;
    }

    protected function generateControllerTests(bool $force): int
    {
        $this->info('Checking controllers...');
        $controllers = $this->getControllers();
        $generated = 0;

        foreach ($controllers as $controller) {
            $testPath = base_path("tests/Feature/{$controller}Test.php");

            if (File::exists($testPath) && !$force) {
                continue;
            }

            $stub = $this->getControllerTestStub($controller);
            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $stub);
            $this->line("  ✓ Created: tests/Feature/{$controller}Test.php");
            $generated++;
        }

        return $generated;
    }

    protected function generateLivewireTests(bool $force): int
    {
        $this->info('Checking Livewire components...');
        $components = $this->getLivewireComponents();
        $generated = 0;

        foreach ($components as $component) {
            $testPath = base_path("tests/Feature/Livewire/{$component}Test.php");

            if (File::exists($testPath) && !$force) {
                continue;
            }

            $stub = $this->getLivewireTestStub($component);
            File::ensureDirectoryExists(dirname($testPath));
            File::put($testPath, $stub);
            $this->line("  ✓ Created: tests/Feature/Livewire/{$component}Test.php");
            $generated++;
        }

        return $generated;
    }

    protected function getModels(): array
    {
        return collect(File::allFiles(app_path('Models')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->filter(fn ($name) => $name !== 'Model')
            ->sort()
            ->values()
            ->toArray();
    }

    protected function getServices(): array
    {
        return collect(File::allFiles(app_path('Services')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->sort()
            ->values()
            ->toArray();
    }

    protected function getControllers(): array
    {
        $controllers = collect(File::allFiles(app_path('Http/Controllers')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->filter(fn ($name) => $name !== 'Controller')
            ->sort()
            ->values()
            ->toArray();

        // Handle nested controllers (e.g., Auth/LoginController)
        $nested = collect(File::allFiles(app_path('Http/Controllers')))
            ->filter(fn ($file) => $file->getRelativePath() !== '')
            ->map(fn ($file) => str_replace('/', '\\', $file->getRelativePath()) . '\\' . $file->getFilenameWithoutExtension())
            ->filter(fn ($name) => !str_ends_with($name, '\\Controller'))
            ->sort()
            ->values()
            ->toArray();

        return array_merge($controllers, $nested);
    }

    protected function getLivewireComponents(): array
    {
        return collect(File::allFiles(app_path('Livewire')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->filter(fn ($name) => !in_array($name, ['Component', 'Concerns']))
            ->sort()
            ->values()
            ->toArray();
    }

    protected function getModelTestStub(string $model): string
    {
        $modelLower = Str::camel($model);
        $tableName = Str::snake(Str::plural($model));

        return <<<PHP
<?php

namespace Tests\Unit\Models;

use App\Models\\{$model};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$model}Test extends TestCase
{
    use RefreshDatabase;

    public function test_{$modelLower}_can_be_created(): void
    {
        \${$modelLower} = {$model}::factory()->create();

        \$this->assertInstanceOf({$model}::class, \${$modelLower});
        \$this->assertDatabaseHas('{$tableName}', [
            'id' => \${$modelLower}->id,
        ]);
    }

    public function test_{$modelLower}_has_factory(): void
    {
        \${$modelLower} = {$model}::factory()->make();

        \$this->assertInstanceOf({$model}::class, \${$modelLower});
    }
}
PHP;
    }

    protected function getServiceTestStub(string $service): string
    {
        $serviceLower = Str::camel($service);

        return <<<PHP
<?php

namespace Tests\Unit\Services;

use App\Services\\{$service};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$service}Test extends TestCase
{
    use RefreshDatabase;

    protected {$service} \$service;

    protected function setUp(): void
    {
        parent::setUp();

        \$this->service = app({$service}::class);
    }

    public function test_service_can_be_instantiated(): void
    {
        \$this->assertInstanceOf({$service}::class, \$this->service);
    }
}
PHP;
    }

    protected function getControllerTestStub(string $controller): string
    {
        $controllerName = str_replace('\\', '', $controller);
        $resourceName = Str::kebab(str_replace('Controller', '', $controllerName));

        return <<<PHP
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {$controllerName}Test extends TestCase
{
    use RefreshDatabase;

    public function test_controller_returns_successful_response(): void
    {
        // TODO: Implement test for {$controllerName}
        \$this->markTestIncomplete('This test has not been implemented yet.');
    }
}
PHP;
    }

    protected function getLivewireTestStub(string $component): string
    {
        $componentLower = Str::camel($component);

        return <<<PHP
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\\{$component};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class {$component}Test extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_be_rendered(): void
    {
        \$component = Livewire::test({$component}::class);

        \$component->assertSuccessful();
    }

    public function test_component_exists(): void
    {
        \$this->assertTrue(class_exists({$component}::class));
    }
}
PHP;
    }
}

