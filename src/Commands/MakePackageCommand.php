<?php

namespace Herlea\Pack\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakePackageCommand extends Command
{
    protected $signature = 'make:package {name}';

    protected $description = 'Create a new package';

    public function handle()
    {
        $packageName = $this->argument('name');
        $pascalCaseName = Str::studly($packageName);
        $packagePath = base_path("packages/{$packageName}");

        // Create directory if not exists
        if (! File::exists($packagePath)) {
            File::makeDirectory($packagePath, $mode = 0777, true, true);
        }

        // Create src directory and service provider
        $srcPath = "{$packagePath}/src";
        if (! File::exists($srcPath)) {
            File::makeDirectory($srcPath, $mode = 0777, true, true);
            $serviceProviderStub = File::get(__DIR__ . '/../stubs/ServiceProvider.stub');
            $serviceProviderContent = str_replace(
                ['DummyNamespace', 'DummyClass'],
                [$pascalCaseName, "{$pascalCaseName}ServiceProvider"],
                $serviceProviderStub
            );
            File::put("{$srcPath}/{$pascalCaseName}ServiceProvider.php", $serviceProviderContent);
        }

        // Create composer.json file
        $composerStub = File::get(__DIR__ . '/../stubs/composer.stub');
        $composerContent = str_replace(
            ['dummy-package', 'DummyNamespace', 'DummyClass'],
            [$packageName, $pascalCaseName, "{$pascalCaseName}ServiceProvider"],
            $composerStub
        );
        File::put("{$packagePath}/composer.json", $composerContent);

        // Create tests directory
        $testsPath = "{$packagePath}/tests";
        if (! File::exists($testsPath)) {
            File::makeDirectory($testsPath, $mode = 0777, true, true);
        }

        // Modify root composer.json
        $composerJsonPath = base_path('composer.json');
        $composerData = json_decode(File::get($composerJsonPath), true);

        // Add to "repositories"
        $composerData['repositories'][] = [
            'type' => 'path',
            'url' => "packages/{$packageName}"
        ];

        // Add to "require"
        $composerData['require']["packages/{$packageName}"] = '@dev';

        File::put($composerJsonPath, json_encode($composerData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Package {$packageName} created successfully");

        // Prompt to update composer
        if ($this->confirm('Do you wish to run composer update now?', true)) {
            $this->info('Running composer update...');

            $process = new Process(['composer', 'update']);
            $process->setWorkingDirectory(base_path());

            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $this->newLine();
            $this->info('Package installed successfully');
        }
    }
}
