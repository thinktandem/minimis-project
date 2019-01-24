<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaImageBase;

/**
 * Provides a plugin for the 'schema_program_membership_image' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_image",
 *   label = @Translation("image"),
 *   description = @Translation("An image of the item."),
 *   name = "image",
 *   group = "schema_program_membership",
 *   weight = 5,
 *   type = "image",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipImage extends SchemaImageBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
