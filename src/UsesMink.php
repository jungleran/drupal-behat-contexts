<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Session;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Trait UsesMink.
 *
 * Provides general functionality to easily access the mink context.
 */
trait UsesMink {

  /**
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  private $minkContext;

  /**
   * {@inheritdoc}
   *
   * @BeforeScenario
   */
  public function getMinkContext(BeforeScenarioScope $scope): void {
    $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
  }

  /**
   * @return \Behat\Mink\Element\DocumentElement
   */
  protected function getPage(): \Behat\Mink\Element\DocumentElement {
    return $this->getSession()->getPage();
  }

  /**
   * @return \Behat\Mink\Session
   */
  protected function getSession(): Session {
    return $this->minkContext->getSession();
  }

  /**
   * @return string
   */
  private function getCurrentUrl(): string {
    return $this->getSession()->getCurrentUrl();
  }

}
