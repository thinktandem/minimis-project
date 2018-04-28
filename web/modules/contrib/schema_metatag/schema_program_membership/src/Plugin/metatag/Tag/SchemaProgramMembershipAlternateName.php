<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaNameBase;

/**
 * Plugin for the 'schema_program_membership_alternate_name' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_alternate_name",
 *   label = @Translation("alternateName"),
 *   description = @Translation("An alias for the item."),
 *   name = "alternateName",
 *   group = "schema_program_membership",
 *   weight = -35,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipAlternateName extends SchemaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
