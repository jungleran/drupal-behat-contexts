<?php

namespace OrdinaDigitalServices;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class EntityContext.
 *
 * Provides steps to add/edit/delete Drupal entities.
 *
 * @SuppressWarnings("coupling")
 */
class EntityContext extends RawDrupalContext {

  /**
   * List of all created entities keyed by entity type.
   *
   * @var array
   */
  private $entities = [];

  /**
   * Find an entity with a given field value.
   *
   * @param string $entityType
   *   The type of entity.
   * @param string $fieldName
   *   The field name to look for.
   * @param mixed $fieldValue
   *   The value of the field.
   *
   * @return int
   *   The entity id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \RuntimeException
   */
  public function findEntityWithFieldValue(string $entityType, string $fieldName, $fieldValue): int {
    $query = \Drupal::entityTypeManager()->getStorage($entityType)->getQuery();
    $query->condition($fieldName, $fieldValue);
    $ids = $query->execute();

    if (empty($ids)) {
      throw new \RuntimeException("No {$entityType} with field '{$fieldName}' containing '{$fieldValue}' could be found");
    }

    if (\count($ids) > 1) {
      throw new \RuntimeException("Found multiple {$entityType}s with field '{$fieldName}' containing '{$fieldValue}'");
    }

    return \end($ids);
  }

  /**
   * Find an entity with a given combination of field values.
   *
   * @param string $entityType
   *   The type of entity.
   * @param array $fieldValues
   *   The field values to look for, keyed by their field names.
   *
   * @return int
   *   The entity id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function findEntityWithFieldValues(string $entityType, array $fieldValues): int {
    $query = \Drupal::entityTypeManager()->getStorage($entityType)->getQuery();
    $formattedFieldValues = '';
    foreach ($fieldValues as $fieldName => $fieldValue) {
      $query->condition($fieldName, $fieldValue);
      $formattedFieldValues .= "$fieldName: $fieldValue \n";
    }
    $ids = $query->execute();


    if (empty($ids)) {
      throw new \RuntimeException("No {$entityType} exists with the following combination of field values: \n{$formattedFieldValues}");
    }

    if (count($ids) > 1) {
      throw new \RuntimeException("Multiple {$entityType}s exist with the following combination of field values: \n{$formattedFieldValues}");
    }

    return \end($ids);
  }

  /**
   * Create an entity of a given type.
   *
   * @param string $entityType
   *   A string representing the entity type.
   * @param \stdClass $entityData
   *   stdClass containing the required fields for creating the entity.
   *
   * @return int
   *   Identifier of the entity created.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createEntity(string $entityType, \stdClass $entityData): int {
    $definition = \Drupal::entityTypeManager()->getDefinition($entityType, FALSE);
    if ($definition === NULL) {
      throw new \RuntimeException("{$entityType} is not a valid Entity type.");
    }

    if ($entityType === 'node') {
      $saved = $this->nodeCreate($entityData);
      return $saved->nid;
    }

    if ($entityType === 'user') {
      $saved = $this->userCreate($entityData);
      return $saved->uid;
    }

    if ($entityType === 'taxonomy_term') {
      $saved = $this->termCreate($entityData);
      return $saved->tid;
    }

    if ($entityType === 'comment') {
      $saved = $this->commentCreate((array) $entityData);
      return $saved->id();
    }

    return $this->createGenericEntity($entityType, $entityData);
  }

  /**
   * Create a comment entity.
   *
   * @param array $entityData
   *   The comment value array.
   *
   * @return \Drupal\comment\CommentInterface
   *   The comment.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @SuppressWarnings("static")
   */
  private function commentCreate(array $entityData): CommentInterface {
    $comment = Comment::create($entityData);
    $comment->save();
    $this->entities[$comment->getEntityTypeId()][] = $comment;
    return $comment;
  }

  /**
   * Creates a generic entity.
   *
   * @param string $entityType
   *   The entity type id.
   * @param \stdClass $entityData
   *
   * @return int
   *   The id of the created entity.
   *
   * @throws \RuntimeException
   */
  private function createGenericEntity(string $entityType, \stdClass $entityData): int {
    try {
      $this->parseEntityFields($entityType, $entityData);
      /** @var \StdClass $entity */
      $entity = $this->getDriver()->createEntity($entityType, $entityData);
      $this->entities[$entityType][] = $entity;
      return $entity->id;
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Remove any created entities.
   *
   * @AfterScenario
   */
  public function cleanEntities(): void {
    foreach ($this->entities as $entityType => $entities) {
      foreach ($entities as $entity) {
        $this->getDriver()->entityDelete($entityType, $entity);
      }
    }
    $this->entities = [];
  }

  /**
   * Load an entity.
   *
   * @param string $entityType
   *   The entity type.
   * @param int|string $entityId
   *   The entity id.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The loaded entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function entityLoad(string $entityType, $entityId): EntityInterface {
    return \Drupal::entityTypeManager()->getStorage($entityType)->load($entityId);
  }

  /**
   * @Given :entityType :uuid has label :label
   *
   * @param string $entityType
   *   The entity type.
   * @param string $uuid
   *   The entity's uuid.
   * @param string $label
   *   The label to set.
   *
   * @throws \RuntimeException
   */
  public function entityTypeWithUuidHasLabel(string $entityType, string $uuid, string $label): void {
    $entity = $this->loadEntityByUuid($entityType, $uuid);
    if (!$entity instanceof ContentEntityInterface) {
      throw new \RuntimeException("{$entityType} with uuid {$uuid} can not have a label");
    }

    $labelKey = $entity->getEntityType()->getKey('label');
    $entity->set($labelKey, $label);
    $entity->save();
  }

  /**
   * @param string $entityType
   *   The entity type.
   * @param string $uuid
   *   The entity's uuid.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function loadEntityByUuid(string $entityType, string $uuid) {
    $entityStorage = \Drupal::entityTypeManager()->getStorage($entityType);
    $entities = $entityStorage->loadByProperties(['uuid' => $uuid]);
    if (empty($entities)) {
      throw new \RuntimeException("No {$entityType} with uuid {$uuid} could be found");
    }

    $entity = reset($entities);
    return $entity;
  }

  /**
   * @Given :entityType :uuid has empty :field
   *
   * @param string $entityType
   *   The entity type.
   * @param string $uuid
   *   The entity's uuid.
   * @param string $fieldName
   *   The field name.
   *
   * @throws \RuntimeException
   */
  public function entityTypeWithUuidHasEmptyField(string $entityType, string $uuid, string $fieldName): void {
    $entity = $this->loadEntityByUuid($entityType, $uuid);
    if (!$entity instanceof ContentEntityInterface) {
      throw new \RuntimeException("{$entityType} with uuid {$uuid} can not have fields");
    }

    $entity->set($fieldName, null);
    $entity->save();
  }

  /**
   * @Given :entityType :uuid has :field with value :value
   *
   * @param string $entityType
   *   The entity type.
   * @param string $uuid
   *   The entity's uuid.
   * @param string $fieldName
   *   The field name.
   * @param string $value
   *   The field value.
   *
   * @throws \RuntimeException
   */
  public function entityTypeWithUuidHasFieldValue(string $entityType, string $uuid, string $fieldName, string $value): void {
    $entity = $this->loadEntityByUuid($entityType, $uuid);
    if (!$entity instanceof ContentEntityInterface) {
      throw new \RuntimeException("{$entityType} with uuid {$uuid} can not have fields");
    }

    // We need to pretend that we're processing an entity to be able to parse
    // the entity fields correctly.
    $mockEntity = (object) [
      $entity->getEntityType()->getKey('bundle') => $entity->bundle(),
      $fieldName => $value,
    ];
    $this->parseEntityFields($entityType, $mockEntity);

    // AbstractCore::expandEntityFields is protected, but we need it to be able
    // to correctly attach files to file fields. We'll therefore make it
    // accessible using reflection and let it do it's thing.
    $core = $this->getDriver()->getCore();
    $reflectedCore = new \ReflectionClass($core);
    $method = $reflectedCore->getMethod('expandEntityFields');
    $method->setAccessible(true);
    $method->invokeArgs($core, [$entityType, $mockEntity]);

    // Now that we've parsed and expanded the field and it's value, we can set
    // the result on the entity and save it.
    $entity->set($fieldName, $mockEntity->{$fieldName});
    $entity->save();
  }

}
