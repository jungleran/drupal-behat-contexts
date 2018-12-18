<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\DrupalContext;
use Drupal\taxonomy\Entity\Term;

/**
 * Class TaxonomyContext.
 *
 * Provides steps to manipulate taxonomy terms and vocabularies.
 */
class TaxonomyContext implements Context {

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
    /** @var \Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
    $environment = $scope->getEnvironment();

    $this->entityContext = $environment->getContext(EntityContext::class);
    $this->drupalContext = $environment->getContext(DrupalContext::class);
  }

  /**
   * @Given :amount :vocabularyId terms:
   *
   * @param int $amount
   *   The amount of terms to create.
   * @param string $vocabularyId
   *   The id of the vocabulary to create the terms in.
   */
  public function terms(int $amount, string $vocabularyId): void {
    /** @var \Drupal\Component\Utility\Random $random */
    $random = $this->drupalContext->getRandom();

    for ($i = 0; $i < $amount; $i++) {
      $term = (object) [
        'name' => $random->name(8),
        'vocabulary_machine_name' => $vocabularyId,
        'description' => $random->name(255),
        'disabled' => 0,
      ];
      $this->drupalContext->termCreate($term);
    }
  }

  /**
   * Load a taxonomy term by name.
   *
   * @param string $name
   *   The name of the term to search for.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \RuntimeException
   */
  public function loadTermByName(string $name): Term {
    $tid = $this->entityContext->findEntityWithFieldValue('taxonomy_term', 'name', $name);
    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityContext->entityLoad('taxonomy_term', $tid);
    if ($term === NULL) {
      throw new \RuntimeException("$name could not be loaded");
    }
    return $term;
  }

  /**
   * Load Term by name and vocabulary.
   *
   * @param string $name
   *   The name of the term to search for.
   * @param string $vocabularyId
   *   The id of the vocabulary the term should belong to.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \RuntimeException
   */
  public function loadTermByNameAndVocabulary(string $name, string $vocabularyId): Term {
    $entityType = 'taxonomy_term';
    $values = [
      'name' => $name,
      'vid' => $vocabularyId,
    ];
    $tid = $this->entityContext->findEntityWithFieldValues($entityType, $values);

    /** @var \Drupal\taxonomy\Entity\Term $term */
    $term = $this->entityContext->entityLoad($entityType, $tid);
    if ($term === NULL) {
      throw new \RuntimeException("$name could not be loaded");
    }
    return $term;
  }

  /**
   * @Given I delete the term with name :name
   *
   * @param string $name
   *   The term name.
   */
  public function deleteTermsByName(string $name): void {
    $entityType = 'taxonomy_term';
    try {
      /** @var \Drupal\taxonomy\TermInterface[] $terms */
      $terms = \Drupal::entityTypeManager()
        ->getStorage($entityType)
        ->loadByProperties(['name' => $name]);

      foreach ($terms as $term) {
        $term->delete();
      }
    } catch (\Exception $e) {
      throw new \RuntimeException(sprintf('Cannot delete term %s', $name));
    }
  }

}
