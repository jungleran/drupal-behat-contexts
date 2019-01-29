<?php

namespace OrdinaDigitalServices;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * Class ParagraphsContext.
 *
 * Adds steps to add paragraphs to entities.
 */
class ParagraphsContext implements Context {

  use UsesEntities;

  /**
   * @Given :entityType with :fieldName :fieldValue has :paragraphsField containing the following paragraphs:
   *
   * @param string $entityType
   *   The entity type id.
   * @param string $fieldName
   *   The machine name of the field to select the entity on.
   * @param string $fieldValue
   *   The value to look for in the given field.
   * @param string $paragraphsField
   *   The machine name of the paragraphs field to add the paragraphs to.
   * @param \Behat\Gherkin\Node\TableNode $paragraphsTable
   *   The hash table containing the paragraph values to add.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Exception
   */
  public function nodeHasTheFollowingParagraphsIn(
    string $entityType,
    string $fieldName,
    string $fieldValue,
    string $paragraphsField,
    TableNode $paragraphsTable
  ): void {
    $entityId = $this->entityContext->findEntityWithFieldValue($entityType, $fieldName, $fieldValue);
    $entity = $this->entityContext->entityLoad($entityType, $entityId);

    foreach ($paragraphsTable->getHash() as $paragraphHash) {
      $paragraph = (object) $paragraphHash;
      $paragraphId = $this->entityContext->createEntity('paragraph', $paragraph);
      $paragraph = $this->entityContext->entityLoad('paragraph', $paragraphId);
      $entity->{$paragraphsField}[] = $paragraph;
    }

    $entity->save();
  }

}
