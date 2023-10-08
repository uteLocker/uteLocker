<?php

namespace App\Libs\PaymentMethodConfig;


abstract class PaymentMethodConfig implements IPaymentMethodConfig
{
    protected array $config = [];

    public function __construct(array|string $config = [])
    {
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $this->config = $config;
        return $this;
    }

    public function getConfigs(): array
    {
        return $this->config;
    }

    public function addConfig(string $fieldName, string|array $value): static
    {
        $this->config[$fieldName] = $value;
        return $this;
    }

    public function getConfig(string $fieldName): mixed
    {
        return $this->config[$fieldName] ?? null;
    }

    abstract public static function getViewPath(): string;

    abstract public static function getDefaultConfigs(): array;

    public function build(): string
    {
        return json_encode($this->config);
    }
}
