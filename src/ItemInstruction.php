<?php
declare(strict_types=1);

namespace HNV\Injector;
/**
 * Injector main class
 *
 * @package HNV\Injector
 */
class ItemInstruction
{
    public function __construct(private Injector $injector)
    {

    }
    public function addArgument(string $name, callable $buildInstruction): void
    {

    }
    public function removeArgument(string $name): void
    {

    }
    public function getArgumentsNames(): array
    {

    }
    public function getArgument(string $name): callable
    {

    }
}