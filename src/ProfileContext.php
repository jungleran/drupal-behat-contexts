<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;

/**
 * Class ProfileContext.
 *
 * Provides steps to add profiles to a user.
 */
class ProfileContext implements Context {

  /**
   * @var \OrdinaDigitalServices\EntityContext
   */
  private $entityContext;

  /**
   * @var \Drupal\DrupalExtension\Context\DrupalContext
   */
  private $drupalContext;

  /**
   * @BeforeScenario
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $environment = $scope->getEnvironment();

    $this->entityContext = $environment->getContext(EntityContext::class);
    $this->drupalContext = $environment->getContext(DrupalContext::class);
  }

  /**
   * Creates profiles of a given type provided in the form:
   * | user       |
   * | Joe Editor |
   * | ...        |
   *
   * @Given :type profiles:
   *
   * @param string $type
   * @param \Behat\Gherkin\Node\TableNode $profilesTable
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createProfiles(string $type, TableNode $profilesTable): void {
    foreach ($profilesTable->getHash() as $hash) {
      if (!isset($hash['user'])) {
        throw new \InvalidArgumentException('No user provided');
      }

      try {
        $user = $this->drupalContext->getUserManager()->getUser($hash['user']);
      }
      catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
      }
      unset($hash['user']);
      $hash['uid'] = $user->uid;
      $hash['type'] = $type;

      $profile = (object) $hash;
      $this->entityContext->createEntity('profile', $profile);
    }
  }

}
