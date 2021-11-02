<?php

namespace Drupal\vss_content;

/**
 * An interface defining a default content importer.
 */
interface ImporterInterface {

  /**
   * Imports default content from a given path.
   *
   * @param array $files
   *   The List of files to import.
   * @param bool $update_existing
   *   Whether to update existing entities or ignore them.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of created entities keyed by their UUIDs.
   */
  public function importContent($files, $update_existing = FALSE);

}
