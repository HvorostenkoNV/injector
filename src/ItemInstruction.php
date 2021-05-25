<?php
declare(strict_types=1);

namespace HNV\Injector;

use InvalidArgumentException;
use OverflowException;
use UnderflowException;

use function strlen;
use function array_keys;
/**
 * Service building instruction class.
 *
 * Contains information for service building.
 *
 * @package HNV\Injector
 */
class ItemInstruction
{
    private array $arguments = [];
    /**
     * Constructor.
     *
     * @param   string $className               Entity class name.
     *
     * @throws  InvalidArgumentException        Class name is invalid.
     */
    public function __construct(private string $className)
    {
        $this->handleEmptyStringParameter($className, 'class name');
    }
    /**
     * Get entity class name.
     *
     * @return string                           Entity class name.
     */
    public function getClassName(): string
    {
        return $this->className;
    }
    /**
     * Add argument building instruction.
     *
     * @param   string      $name               Argument name.
     * @param   callable    $buildInstruction   Argument building instruction.
     *
     * @return  void
     * @throws  InvalidArgumentException        Argument name is invalid.
     * @throws  OverflowException               Argument is already registered.
     */
    public function addArgument(string $name, callable $buildInstruction): void
    {
        $this->handleEmptyStringParameter($name, 'argument name');

        if (isset($this->arguments[$name])) {
            throw new OverflowException("argument $name is already registered");
        }

        $this->arguments[$name] = $buildInstruction;
    }
    /**
     * Remove argument building instruction.
     *
     * @param   string $name                    Argument name.
     *
     * @return  void
     * @throws  InvalidArgumentException        Argument name is invalid.
     * @throws  UnderflowException              Argument does not registered.
     */
    public function removeArgument(string $name): void
    {
        $this->handleEmptyStringParameter($name, 'argument name');

        if (!isset($this->arguments[$name])) {
            throw new UnderflowException("argument $name does not registered");
        }

        unset($this->arguments[$name]);
    }
    /**
     * Get all registered arguments names set.
     *
     * @return string[]                         Arguments names set.
     */
    public function getArgumentsNames(): array
    {
        return array_keys($this->arguments);
    }
    /**
     * Get argument building instruction.
     *
     * @param   string $name                    Argument name.
     *
     * @return  callable                        Argument building instruction.
     * @throws  InvalidArgumentException        Argument name is invalid.
     * @throws  UnderflowException              Argument does not registered.
     */
    public function getArgument(string $name): callable
    {
        $this->handleEmptyStringParameter($name, 'argument name');

        if (!isset($this->arguments[$name])) {
            throw new UnderflowException("argument $name does not registered");
        }

        return $this->arguments[$name];
    }
    /**
     * Handle empty string parameter and throw exception if needs.
     *
     * @param   string  $value                  Parameter value.
     * @param   string  $name                   Parameter name.
     *
     * @return  void
     * @throws  InvalidArgumentException        String value is empty.
     */
    private function handleEmptyStringParameter(string $value, string $name): void
    {
        if (strlen($value) === 0) {
            throw new InvalidArgumentException("$name parameter is empty");
        }
    }
}