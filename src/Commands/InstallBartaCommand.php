<?php

declare(strict_types=1);

namespace Larament\Barta\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'barta:install', description: 'Install the Barta package and publish the configuration')]
final class InstallBartaCommand extends Command
{
    public function handle(): int
    {
        $this->publishConfigs();

        $this->askToStarRepo('iRaziul/barta');

        return self::SUCCESS;
    }

    private function publishConfigs(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'barta-config',
        ]);
    }

    private function askToStarRepo(string $repoVendorPath): void
    {
        if (confirm('Would you like to star this repo on GitHub?', true)) {
            $repoUrl = "https://github.com/{$repoVendorPath}";

            match (mb_strtolower(PHP_OS_FAMILY)) {
                'darwin' => exec("open {$repoUrl}"),
                'linux' => exec("xdg-open {$repoUrl}"),
                'windows' => exec("start {$repoUrl}"),
                default => null,
            };
        }

        $this->components->info('Thank you ❤️');
    }
}
