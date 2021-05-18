<?php
declare(strict_types=1);

namespace HNV\Injector;
/**
 * Injector container class
 *
 * @package HNV\Injector
 */
class Container
{
    public function register(string $interface, string $alias = ''): ItemInstruction
    {

    }
    public function unregister(string $interface, string $alias = ''): void
    {

    }
    public function get(string $interface, string $alias = ''): mixed
    {

    }
}