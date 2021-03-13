<?php

namespace Puny;

use Puny\Exceptions\NotOkException;
use Puny\Exceptions\SkippedException;
use Puny\Support\Console;

final class Puny
{
    private static $instance = null;

    private string $root;

    private static array $tests = [];

    private int $failed = 0;

    private int $skipped = 0;

    public function setRoot(string $path)
    {
        $this->root = $path;

        return $this;
    }

    public function run(string $path)
    {
        if ($path) {
            $this->setRoot($path);
        }

        Console::write('Puny v'.PUNY_VERSION);

        if (! is_dir($this->root)) {
            Console::error("The tests directory does not exist ({$this->root}).");
            exit(1);
        }

        $start = microtime(true);

        self::requireIfReadable($this->root.'/bootstrap.php');
        self::includeTestFiles($this->root);

        foreach (static::$tests as $name => $callback) {
            try {
                $callback();

                Console::info("\xE2\x9C\x94 {$name}");
            } catch (SkippedException $e) {
                Console::warning("\xE2\x9A\xA0 {$name}");
                
                $this->skipped++;
                continue;
            } catch (NotOkException $e) {
                Console::error("\xF0\x90\x84\x82 {$name} > {$e->getMessage()}");

                $this->failed++;            
            }
        }

        self::requireIfReadable($this->root.'/tidy-up.php');

        Console::write(sprintf(
            "%s tests run. %s passed. %s failed. %s skipped. %02.2f s",
            count(static::$tests),
            count(static::$tests) - $this->failed - $this->skipped,
            $this->failed,
            $this->skipped,
            microtime(true) - $start
        ));
    }

    private static function requireIfReadable(string $path)
    {
        if (! is_readable($path)) {
            return;
        }

        require_once $path;
    }

    private static function includeTestFiles(string $path)
    {
        $files = array_filter(scandir($path), function ($file) {
            return ! in_array($file, ['bootstrap.php', 'tidy-up.php', '.', '..']);
        });

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $target = $path.'/'.$file;

            if (is_dir($target)) {
                self::includeTestFiles($target);
            } else {
                self::requireIfReadable($target);
            }
        }
    }

    public static function register(string $test, \Closure $callback)
    {
        self::$tests[$test] = $callback;
    }

    public static function instance(): self
    {
        return static::$instance ??= new self;
    }
}
