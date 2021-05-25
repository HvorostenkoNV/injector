<?php
declare(strict_types=1);

namespace HNV\Injector;

use Throwable;
use InvalidArgumentException;
use OverflowException;
use UnderflowException;
use RuntimeException;

use function strlen;
use function interface_exists;
use function class_exists;
/**
 * Injector container class.
 *
 * @package HNV\Injector
 */
class Container
{
    private array $registeredItems  = [];
    private array $builtServices    = [];
    /**
     * Register new service.
     *
     * @param   string  $interface              Service interface.
     * @param   string  $className              Service class name.
     * @param   string  $alias                  Register service alias, can be empty
     *                                          Useful for registering multiple services
     *                                          on one interface.
     *
     * @return  ItemInstruction                 Registered item.
     * @throws  InvalidArgumentException        Interface/class name is invalid.
     * @throws  OverflowException               Service already registered.
     */
    public function register(
        string $interface,
        string $className,
        string $alias = ''
    ): ItemInstruction {
        $this->handleEmptyStringParameter($interface, 'interface');
        $this->handleEmptyStringParameter($className, 'class name');

        if (!interface_exists($interface)) {
            throw new InvalidArgumentException("interface $interface does not exist");
        }
        if (!class_exists($className)) {
            throw new InvalidArgumentException("class $className does not exist");
        }

        $serviceIndex = $this->buildIndex($interface, $alias);

        if (isset($this->registeredItems[$serviceIndex])) {
            $aliasName  = strlen($alias) > 0 ? $alias : 'empty';
            $error      = "service on $interface interface ".
                "with $aliasName alias is already registered";

            throw new OverflowException($error);
        }

        $this->registeredItems[$serviceIndex] = new ItemInstruction($className);

        return $this->registeredItems[$serviceIndex];
    }
    /**
     * Unregister service.
     *
     * @param   string  $interface              Service interface.
     * @param   string  $alias                  Register service alias.
     *
     * @return  void
     * @throws  InvalidArgumentException        Interface name is invalid.
     * @throws  UnderflowException              Service does not registered.
     */
    public function unregister(string $interface, string $alias = ''): void
    {
        $this->handleEmptyStringParameter($interface, 'interface');

        $serviceIndex = $this->buildIndex($interface, $alias);

        if (!isset($this->registeredItems[$serviceIndex])) {
            $aliasName  = strlen($alias) > 0 ? $alias : 'empty';
            $error      = "service on $interface interface ".
                "with $aliasName alias does not registered";

            throw new UnderflowException($error);
        }

        unset(
            $this->registeredItems[$serviceIndex],
            $this->builtServices[$serviceIndex]
        );
    }
    /**
     * Get service.
     *
     * @param   string  $interface              Service interface.
     * @param   string  $alias                  Register service alias.
     *
     * @return  object                          Service.
     * @throws  InvalidArgumentException        Interface name is invalid.
     * @throws  UnderflowException              Service does not registered.
     * @throws  RuntimeException                Service building error.
     */
    public function get(string $interface, string $alias = ''): mixed
    {
        $this->handleEmptyStringParameter($interface, 'interface');

        $serviceIndex = $this->buildIndex($interface, $alias);

        if (!isset($this->registeredItems[$serviceIndex])) {
            $aliasName  = strlen($alias) > 0 ? $alias : 'empty';
            $error      = "service on $interface interface ".
                "with $aliasName alias does not registered";

            throw new UnderflowException($error);
        }

        if (!isset($this->builtServices[$serviceIndex])) {
            $itemInstruction    = $this->registeredItems[$serviceIndex];
            $service            = $this->buildService($itemInstruction);

            $this->builtServices[$serviceIndex] = $service;
        }

        return $this->builtServices[$serviceIndex];
    }
    /**
     * Build service unique index.
     *
     * @param   string  $interface              Service interface.
     * @param   string  $alias                  Register service alias.
     *
     * @return  string                          Service unique index.
     */
    private function buildIndex(string $interface, string $alias = ''): string
    {
        return "$interface#$alias";
    }
    /**
     * Build service.
     *
     * @param   ItemInstruction $instruction    Item building instruction.
     *
     * @return  object                          Service.
     * @throws  RuntimeException                Service building error.
     */
    private function buildService(ItemInstruction $instruction): mixed
    {
        $serviceClassName   = $instruction->getClassName();
        $serviceArguments   = [];

        foreach ($instruction->getArgumentsNames() as $argumentName) {
            try {
                $argumentBuilder    = $instruction->getArgument($argumentName);
                $argument           = $argumentBuilder();

                $serviceArguments[$argumentName] = $argument;
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    "argument $argumentName building for object of ".
                    "class $serviceClassName failed with error {$exception->getMessage()}",
                    0,
                    $exception
                );
            }
        }

        try {
            return new $serviceClassName(...$serviceArguments);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "object of class $serviceClassName building ".
                "failed with error {$exception->getMessage()}",
                0,
                $exception
            );
        }
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