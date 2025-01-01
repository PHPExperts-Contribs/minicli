<?php

declare(strict_types=1);

namespace Minicli\PrebuiltCommands;

use Minicli\App;

class HelpCommand implements IsRegisterableCommand
{
    protected $customCode = null;
    protected array $customCodeArgs = [];

    protected array $knownCommands = [
        ['help', '', 'Lists the available commands.'],
    ];

    public function __construct(private App $app)
    {
        $this->register();
    }

    public function register(): void
    {
        $app = $this->app;
        $app->registerCommand('help', function () use ($app) {
            $app->success($app->me . ' [required] <optional> - Description' , false);

            foreach ($this->knownCommands as [$name, $signature, $description])
            {
                $myDescrip = $description === null ? '' : " -- $description";
                echo $this->formatLine("{$app->me} $name $signature{$myDescrip}") . "\n";
            }

            $this->formatCommands($this->knownCommands);

            if (is_callable($this->customCode)) {
                call_user_func($this->customCode, ...$this->customCodeArgs);
            }
        });
    }

    function formatCommands(array $commands)
    {
    }

    public function registerCallback(callable $customCode, array $customCodeArgs = [])
    {
        $this->customCode = $customCode;
        $this->customCodeArgs = $customCodeArgs;
    }

    public function addCommandListing(string $name, string $signature, ?string $description = null)
    {
        $this->knownCommands[] = [$name, $signature, $description];
    }
}

