<?php

namespace Prometa\Sleek\Views;

class SleekPageState
{
  private $menuDataProvider = null;
  private $authenticationProvider = null;
  private $documentProvider = null;
  private $languageProvider = null;
  private $alertProvider = null;

  public function raise($message, $type = 'info')
  {
      session()->flash('sleek_alert', ['message' => $message, 'type' => $type]);
  }

  public function language(callable|array $data): static
  {
      $factory =
          is_callable($data)
              ? $data
              : fn () => $data;

      $this->languageProvider = $factory;

      return $this;
  }

  public function resolveLanguage(){
      return $this->resolve($this->languageProvider);
  }

  public function authentication(callable|array|false $data): static
  {
      $factory =
          is_callable($data)
              ? $data
              : fn () => $data;

      $this->authenticationProvider = $factory;

      return $this;
  }

  public function resolveAuthentication(){
      return $this->resolve($this->authenticationProvider, []);
  }

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

  public function alert(callable|array $data): static {
    $factory =
      is_callable($data)
        ? $data
        : fn () => $data;

    $this->alertProvider = $factory;
    return $this;
  }

  public function resolveAlert() {
    return $this->resolve($this->alertProvider, []);
  }

  private function resolve(callable|null $providerFn, mixed $defaultValue = null) {
    return app()->call($providerFn ?? fn () => $defaultValue);
  }
}
