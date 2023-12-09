<?php

namespace Alnaggar\Spaify\Console;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ScaffoldCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'spaify:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold Laravel project with Vue, Tailwindcss, InertiaJS, Ziggy, and Fontawesome';

    /**
     * Execute the console command.
     * 
     * @return int
     */
    public function handle() : int
    {
        // Start showing that npm dependencies are being installed.
        $this->comment('Installing npm dependencies. Please wait as this may take a few seconds.');

        // Step 1: Install npm dependencies.
        if (! $this->installDependencies()) {
            // If npm dependencies installation fails, return FAILURE status.
            return self::FAILURE;
        }

        // Notify that Inertia middleware setup is in progress.
        $this->comment('Setting up Inertia middleware.');

        // Step 2: Publish and install Inertia middleware.
        $this->setupInertiaMiddleware();

        // Notify that setting up the default files is in progress.
        $this->comment('Setting up default files.');

        // Step 3: Copy stubs to their default location.
        $this->configureDefaultFiles();

        // Finalizing...
        $this->info("The project has been successfully scaffolded for Single Page Application (SPA) development. Remember to execute 'npm install & npm run dev' to complete the setup.");

        // Return SUCCESS status indicating successful execution.
        return self::SUCCESS;
    }

    /**
     * Install npm dependencies and devDependencies and return the installation status.
     *
     * @return bool
     */
    protected function installDependencies() : bool
    {
        // Install regular npm dependencies.
        $isInstalled = $this->runProcess(
            [
                'npm',
                'install',
                'vue',
                '@inertiajs/vue3',
                '@fortawesome/fontawesome-svg-core',
                '@fortawesome/free-solid-svg-icons',
                '@fortawesome/free-regular-svg-icons',
                '@fortawesome/free-brands-svg-icons',
                '@fortawesome/vue-fontawesome@latest-3',
            ]
        );

        // If regular dependencies are installed successfully, install devDependencies.
        if ($isInstalled) {
            $isInstalled = $this->runProcess(
                [
                    'npm',
                    'install',
                    '-D',
                    '@vitejs/plugin-vue',
                    'tailwindcss',
                    'postcss',
                    'autoprefixer',
                ]
            );
        }

        // Return the overall installation status.
        return $isInstalled;
    }

    /**
     * Publish and install Inertia middleware to the web group in the application Http Kernel.
     *
     * @return void
     */
    protected function setupInertiaMiddleware() : void
    {
        // Run the artisan command to publish and install the Inertia middleware.
        $this->runProcess('php artisan inertia:middleware');

        // Read the content of the Http Kernel file.
        $httpKernel = File::get(app_path('Http/Kernel.php'));

        // Define the Inertia middleware to be added to the 'web' middleware group.
        $middleware = '\App\Http\Middleware\HandleInertiaRequests::class';

        // Extract the content between '$middlewareGroups = [' and '];' to locate the middleware groups.
        $middlewareGroups = Str::before(Str::after($httpKernel, '$middlewareGroups = ['), '];');

        // Extract the content between "'web' => [" and "]," to locate the 'web' middleware group.
        $middlewareGroup = Str::before(Str::after($middlewareGroups, "'web' => ["), '],');

        // Extract the indentation before the first middleware in the 'web' middleware group to add it before the inertia middleware for consistency.
        $indentation = Str::before($middlewareGroup, '\\');

        // Check if the Inertia middleware is already present in the 'web' middleware group.
        if (str_contains($middlewareGroup, $middleware)) {
            return; // Inertia middleware already added, no further action needed.
        }

        // Add the Inertia middleware to the 'web' middleware group.
        $modifiedMiddlewareGroup = Str::replaceLast(
            ',',
            ',' . $indentation . $middleware . ',',
            $middlewareGroup
        );

        // Replace the original 'web' middleware group with the modified version.
        $modifiedMiddlewareGroups = Str::replaceLast(
            $middlewareGroup,
            $modifiedMiddlewareGroup,
            $middlewareGroups
        );

        // Replace the original middlewareGroups content with the modified version.
        $modifiedHttpKernel = Str::replaceLast(
            $middlewareGroups,
            $modifiedMiddlewareGroups,
            $httpKernel
        );

        // Write the modified content back to the Http Kernel file.
        File::put(app_path('Http/Kernel.php'), $modifiedHttpKernel);
    }

    /**
     * Configure default files for the application.
     *
     * @return void
     */
    protected function configureDefaultFiles() : void
    {
        // Blade...
        File::copy(__DIR__ . '/../../stubs/app.blade.php', resource_path('views/app.blade.php'));

        // Css...
        File::copy(__DIR__ . '/../../stubs/app.css', resource_path('css/app.css'));

        // Js...
        File::copy(__DIR__ . '/../../stubs/app.js', resource_path('js/app.js'));

        // Vue Directories...
        File::ensureDirectoryExists(resource_path('js/Components'));
        File::ensureDirectoryExists(resource_path('js/Layouts'));
        File::ensureDirectoryExists(resource_path('js/Pages'));

        // Tailwindcss...
        File::copy(__DIR__ . '/../../stubs/postcss.config.js', base_path('postcss.config.js'));
        File::copy(__DIR__ . '/../../stubs/tailwind.config.js', base_path('tailwind.config.js'));

        // Vite...
        File::copy(__DIR__ . '/../../stubs/vite.config.js', base_path('vite.config.js'));
    }

    /**
     * Run a process with the provided command and return the success status.
     *
     * @param array|string $command
     * @return bool
     */
    protected function runProcess(array|string $command) : bool
    {
        // Set the working directory for the process to the base path of the application.
        // This ensures that the process is executed in the context of the application.
        $result = Process::path(base_path())
            ->forever() // Keep the process running until manually stopped.
            ->run($command); // Run the specified command.

        // Check if the process failed, and if so, log the error output.
        if ($result->failed()) {
            $this->error($result->errorOutput());
        }

        // Return true if the process was successful, false otherwise.
        return $result->successful();
    }
}
