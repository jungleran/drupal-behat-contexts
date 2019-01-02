<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * Class ProfileContext.
 *
 * Provides steps to add profiles to a user.
 */
class ProfileContext implements Context {

  use UsesEntities;
  use UsesDrupal;

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
