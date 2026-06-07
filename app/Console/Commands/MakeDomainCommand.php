<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('make:domain {name : Domain name} {--full : Generate full directory structure with default stubs} {--tests : Generate factory and domain test scaffolding} {--force : Overwrite existing domain files}')]
#[Description('Create a new domain module under app/Domains with standard structure')]
class MakeDomainCommand extends Command
{
    private const DIRECTORIES = [
        'Actions',
        'Controllers',
        'DTOs',
        'Exceptions',
        'Models',
        'Policies',
        'Requests',
        'Resources',
        'Services',
    ];

    public function handle(): int
    {
        $name = $this->normalizeDomainName($this->argument('name'));
        $domainPath = $this->domainPath($name);

        if (is_dir($domainPath) && ! $this->option('force')) {
            $this->components->error("Domain [{$name}] already exists at [{$domainPath}].");

            return self::FAILURE;
        }

        if ($this->option('full')) {
            $this->createDirectories($domainPath, self::DIRECTORIES);
            $this->generatePolicy($name, $domainPath);
            $this->generateException($name, $domainPath);

            if (! $this->option('tests')) {
                $this->generateModel($name, $domainPath);
            }
        } elseif (! is_dir($domainPath)) {
            $this->createDirectories($domainPath, ['Models']);

            if (! $this->option('tests')) {
                $this->generateModel($name, $domainPath);
            }
        }

        if ($this->option('tests')) {
            $this->generateTests($name, $domainPath);
        } elseif ($this->option('force') && is_dir($domainPath) && ! file_exists($domainPath.'/Models/'.$name.'.php')) {
            $this->createDirectories($domainPath, ['Models']);
            $this->generateModel($name, $domainPath);
        }

        $this->components->info("Domain [{$name}] created successfully.");

        return self::SUCCESS;
    }

    private function normalizeDomainName(string $name): string
    {
        $normalized = Str::studly(Str::singular($name));

        if ($normalized === '') {
            throw new RuntimeException('Domain name cannot be empty.');
        }

        return $normalized;
    }

    private function domainPath(string $name): string
    {
        return app_path('Domains/'.$name);
    }

    /**
     * @param  list<string>  $directories
     */
    private function createDirectories(string $domainPath, array $directories): void
    {
        foreach ($directories as $directory) {
            $path = $domainPath.'/'.$directory;

            if (! is_dir($path)) {
                mkdir($path, 0755, true);
                $this->components->twoColumnDetail($directory.'/', '<fg=gray>created</>');
            }
        }
    }

    private function generateTests(string $name, string $domainPath): void
    {
        $this->createDirectories($domainPath, ['Models']);
        $this->generateModel($name, $domainPath);
        $this->generateFactory($name);
        $this->generateModelTest($name);
        $this->generateFeatureApiTest($name);

        if ($this->option('full') || file_exists($domainPath.'/Policies/'.$name.'Policy.php')) {
            $this->generatePolicyTest($name);
        }

        if ($this->option('full') || file_exists($domainPath.'/Exceptions/'.$name.'Exception.php')) {
            $this->generateExceptionTest($name);
        }
    }

    private function generateModel(string $name, string $domainPath): void
    {
        $stub = $this->option('tests') ? 'model-with-factory.stub' : 'model.stub';

        $this->generateFromStub(
            stub: $stub,
            path: $domainPath.'/Models/'.$name.'.php',
            replacements: [
                '{{ namespace }}' => "App\\Domains\\{$name}\\Models",
                '{{ class }}' => $name,
            ],
        );
    }

    private function generatePolicy(string $name, string $domainPath): void
    {
        $this->generateFromStub(
            stub: 'policy.stub',
            path: $domainPath.'/Policies/'.$name.'Policy.php',
            replacements: [
                '{{ namespace }}' => "App\\Domains\\{$name}\\Policies",
                '{{ class }}' => $name,
                '{{ modelNamespace }}' => "App\\Domains\\{$name}\\Models\\{$name}",
                '{{ modelVariable }}' => Str::camel($name),
            ],
        );
    }

    private function generateException(string $name, string $domainPath): void
    {
        $this->generateFromStub(
            stub: 'exception.stub',
            path: $domainPath.'/Exceptions/'.$name.'Exception.php',
            replacements: [
                '{{ namespace }}' => "App\\Domains\\{$name}\\Exceptions",
                '{{ class }}' => $name,
            ],
        );
    }

    private function generateFactory(string $name): void
    {
        $this->generateFromStub(
            stub: 'factory.stub',
            path: base_path('database/factories/'.$name.'Factory.php'),
            replacements: [
                '{{ class }}' => $name,
                '{{ modelNamespace }}' => "App\\Domains\\{$name}\\Models\\{$name}",
            ],
        );
    }

    private function generateModelTest(string $name): void
    {
        $this->generateFromStub(
            stub: 'tests/model-test.stub',
            path: base_path('tests/Unit/Domains/'.$name.'/Models/'.$name.'Test.php'),
            replacements: [
                '{{ namespace }}' => "Tests\\Unit\\Domains\\{$name}\\Models",
                '{{ class }}' => $name,
                '{{ modelNamespace }}' => "App\\Domains\\{$name}\\Models\\{$name}",
            ],
        );
    }

    private function generatePolicyTest(string $name): void
    {
        $this->generateFromStub(
            stub: 'tests/policy-test.stub',
            path: base_path('tests/Unit/Domains/'.$name.'/Policies/'.$name.'PolicyTest.php'),
            replacements: [
                '{{ namespace }}' => "Tests\\Unit\\Domains\\{$name}\\Policies",
                '{{ class }}' => $name,
                '{{ modelNamespace }}' => "App\\Domains\\{$name}\\Models\\{$name}",
                '{{ policyNamespace }}' => "App\\Domains\\{$name}\\Policies\\{$name}Policy",
            ],
        );
    }

    private function generateExceptionTest(string $name): void
    {
        $this->generateFromStub(
            stub: 'tests/exception-test.stub',
            path: base_path('tests/Unit/Domains/'.$name.'/Exceptions/'.$name.'ExceptionTest.php'),
            replacements: [
                '{{ namespace }}' => "Tests\\Unit\\Domains\\{$name}\\Exceptions",
                '{{ class }}' => $name,
                '{{ exceptionNamespace }}' => "App\\Domains\\{$name}\\Exceptions\\{$name}Exception",
            ],
        );
    }

    private function generateFeatureApiTest(string $name): void
    {
        $this->generateFromStub(
            stub: 'tests/feature-api-test.stub',
            path: base_path('tests/Feature/Domains/'.$name.'/'.$name.'ApiTest.php'),
            replacements: [
                '{{ namespace }}' => "Tests\\Feature\\Domains\\{$name}",
                '{{ class }}' => $name,
            ],
        );
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function generateFromStub(string $stub, string $path, array $replacements): void
    {
        if (file_exists($path) && ! $this->option('force')) {
            $this->components->warn("File already exists: {$path}");

            return;
        }

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $stubPath = base_path('stubs/domain/'.$stub);

        if (! file_exists($stubPath)) {
            throw new RuntimeException("Stub not found: {$stubPath}");
        }

        $contents = str_replace(
            array_keys($replacements),
            array_values($replacements),
            file_get_contents($stubPath),
        );

        file_put_contents($path, $contents);

        $this->components->twoColumnDetail(str_replace(base_path().'/', '', $path), '<fg=gray>created</>');
    }
}
