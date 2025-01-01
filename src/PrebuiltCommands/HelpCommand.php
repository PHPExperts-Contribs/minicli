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

            $this->knownCommands = [
                ['app help', null, 'Lists the available commands.'],
                ['app help2', null, null],
                ['app version', null, "Shows the current app's version"],
                ['app new', null, null],
                ['app deploy', '--force', "Force deploy the application"],
                ['app rollback', '--target <version>', null],
            ];

            $this->formatCommands($this->knownCommands);

            if (is_callable($this->customCode)) {
                call_user_func($this->customCode, ...$this->customCodeArgs);
            }
        });
    }

    function formatCommands(array $commands)
    {
        // Step 1: Build the full command (name + signature if available)
        $fullCommands = [];
        foreach ($commands as $item) {
            [$name, $signature, $description] = $item;
            $fullCommands[] = $signature ? "$name $signature" : $name;
        }

        // Step 2: Find the maximum length among commands
        //         that are <= 40 chars AND have a description.
        $maxLenWithDesc = 0;
        foreach ($commands as $index => [$name, $signature, $description]) {
            $fullCommand = $fullCommands[$index];
            if ($description !== null) {
                $length = strlen($fullCommand);
                if ($length <= 40 && $length > $maxLenWithDesc) {
                    $maxLenWithDesc = $length;
                }
            }
        }

        $dashColumn = min(42, $maxLenWithDesc + 2);

        // Step 3: Output each command according to the rules
        foreach ($commands as $index => [$name, $signature, $description]) {
            $fullCommand = $fullCommands[$index];
            $length = strlen($fullCommand);

            // If no description, just print the command
            if ($description === null) {
                // Fits within 80 chars requirement automatically, unless your command is huge
                echo $fullCommand, PHP_EOL;
                continue;
            }

            // If the command is longer than 40 chars, put description on next line
            if ($length > 40) {
                echo $fullCommand, PHP_EOL;            // Print command alone
                echo str_repeat(' ', 4), '— ', $description, PHP_EOL; // Indented description
            } else {
                // Align dash at $dashColumn
                // Number of spaces = ($dashColumn) - (current position after command)
                // Current position after command = $length + 1 (since we start at col 1)
                $spaces = $dashColumn - ($length + 1);
                if ($spaces < 1) {
                    $spaces = 1;  // Ensure at least one space if something odd happens
                }

                // Construct the line
                $line = $fullCommand
                    . str_repeat(' ', $spaces)
                    . '— '
                    . $description;

                // (Optional) Check if longer than 80 chars – no explicit wrapping below,
                // but you could implement logic here if you wish:
                // if (strlen($line) > 80) { ...wrap logic... }

                echo $line, PHP_EOL;
            }
        }
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


/**
app help                         — Lists the available commands.
app help2
app version                      — Shows the current app's version
app new
app deploy --force               — Force deploy the application
app rollback --target <version>

 */
