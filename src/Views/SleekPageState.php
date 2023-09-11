<?php

namespace Prometa\Sleek\Views;

class SleekPageState
{
  private $menuDataProvider = null;
  private $documentProvider = null;

  public function menu(callable|array $data): static {
    $factory =
      is_callable($data)
      ? $data
      : fn () => $data;

    $this->menuDataProvider = $factory;

    return $this;
  }

  public function resolveMenuStructure() {
    return $this->resolve($this->menuDataProvider, []);
  }

  public function document(callable|string $document): static {
    $factory =
      is_callable($document)
      ? $document
      : fn () => $document;

    $this->documentProvider = $factory;

    return $this;
  }

  public function resolveDocument() {
    return $this->resolve($this->documentProvider);
  }

  private function resolve(callable|null $providerFn, mixed $defaultValue = null) {
    return app()->call($providerFn ?? fn () => $defaultValue);
  }
}
