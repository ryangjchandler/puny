# Puny

Make unit testing in PHP simpler again. 👌

## Table of Contents

- [Puny](#puny)
  - [Installation](#installation)
  - [Usage](#usage)
    - [Writing your first test](#writing-your-first-test)
    - [Checking things are okay](#checking-things-are-okay)
    - [Checking equality with eq](#checking-equality-with-eq)
    - [Skipping tests](#skipping-tests)
    - [Spying on functions](#spying-on-functions)
  - [Why does Puny exist?](#why-does-puny-exist)

## Installation

To install Puny, run the following command in your project:

```bash
composer require ryangjchandler/puny --dev
```

## Usage

To run Puny, use the following command:

```bash
./vendor/bin/puny
```

This command will invoke Puny and attempt to run all of your tests.

By default, Puny will look for a **`tests` folder in the current working directory** and use that to search for test files. If you are using a different directory name, you can specify it when running Puny on the command-line:

```bash
./vendor/bin/puny ./tests-folder
```

### Writing your first test

If you haven't already, create a `tests` folder in the root of your project. Go ahead and create a new PHP file too.

You can call this file whatever you want, Puny doesn't really care.

To write your first test, import the `Puny\test` function at the top of your file and invoke the function, passing in 2 arguments. The first argument should be the name of your test, the second argument should be the test handler itself (any `callable` type).

> It is a good practice with Puny to use the fully-qualified name (FQN) of the class you are testing as the name of the test.

```php
use function Puny\{test, ok};
use App\Support\StringFormatter;

test(StringFormatter::class, function () {
    $upper = StringFormatter::upper('example');

    ok($upper === 'EXAMPLE', 'correctly transforms to uppercase');
});
```

### Checking things are okay

You can use the `ok` function to check whether or not something correct (okay). The first argument to this function is a `bool`, testing the condition in question. The second argument is the specific scenario you are testing.

> Ensure the scenario is clear as it will be used to indicate failures when running your tests.

```php
ok(1 + 2 === 3, 'math is good');
```

### Checking equality with eq

You can use the `eq` function to check if two values are **strictly** equal. The first argument is your expected value. The second is your actual value. But they are interchangable and just a suggestion. The third argument is the specific scenario you are testing. The return value is a `bool` result of the comparison check.

```php
eq(3, 1 + 2, 'math is good');
```

### Skipping tests

You can skip a test by invoking the `skip` function. Under the hood, this function throws a `SkippedException` that will force the test to be marked as skipped.

```php
test('Example', function () {
    skip();
});
```

### Spying on functions

You can spy on a function / callable via the `spy` function. This helper wraps the provided `callable` and allows you to perform checks on the number of times it has been called, the arguments provided and whether any exceptions were thrown.

```php
test('Example', function () {
    $spied = spy(function ($out) {
        return $out;
    });

    ok(count($spied->called) === 0, 'not yet called');

    $result = $spied('Hello');

    ok($spied->called[0] === ['Hello'], 'args stored correctly');
    ok($result === 'Hello', 'returns result correctly');

    $exception = spy(function () {
        throw new Exception;
    });

    $exception();

    ok(count($exception->thrown) === 1, 'exceptions stored correctly');
    ok($exception->thrown[0] instanceof Exception, 'exceptions stored are objects');
});
```

## Why does Puny exist?

Puny is an effort to **make testing simpler again**. It was originally inspired by [this blog post](https://zserge.com/posts/minimal-testing/) about minimalist testing tools.
 
Puny doesn't have all of the same assertion methods as [PHPUnit](https://phpunit.de/) and it never will. It's designed for testing prototypes, small packages and applications. 

Puny also has a small function-based syntax, moving away from verbose test classes. It borrows this philosophy from the excellent [Pest](https://pestphp.com/) framework.

Puny is also dependency free and has a small call-stack. This means your errors won't be clouded with a list of internal method calls, just a few small steps that Puny takes to make your testing experience a bit smoother.
