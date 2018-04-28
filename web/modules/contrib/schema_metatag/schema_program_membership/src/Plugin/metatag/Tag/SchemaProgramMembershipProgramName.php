<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaNameBase;

/**
 * Provides a plugin for the 'schema_program_membership_program_name' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_program_name",
 *   label = @Translation("programName"),
 *   description = @Translation("The program providing the membership."),
 *   name = "programName",
 *   group = "schema_program_membership",
 *   weight = -40,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipProgramName extends SchemaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
