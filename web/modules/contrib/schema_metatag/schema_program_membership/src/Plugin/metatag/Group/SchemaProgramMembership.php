<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Group;

use Drupal\schema_metatag\Plugin\metatag\Group\SchemaGroupBase;

/**
 * Provides a plugin for the 'ProgramMembership' meta tag group.
 *
 * @MetatagGroup(
 *   id = "schema_program_membership",
 *   label = @Translation("Schema.org: ProgramMembership"),
 *   description = @Translation("See Schema.org definitions for this Schema type at <a href="":url"">:url</a>.", arguments = { ":url" = "https://schema.org/ProgramMembership"}),
 *   weight = 10,
 * )
 */
class SchemaProgramMembership extends SchemaGroupBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
