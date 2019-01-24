<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaMainEntityOfPageBase;

/**
 * Plugin for the 'schema_program_membership_main_entity_of_page' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_main_entity_of_page",
 *   label = @Translation("mainEntityOfPage"),
 *   description = @Translation("Indicates a page (or other CreativeWork) for which this thing is the main entity being described."),
 *   name = "mainEntityOfPage",
 *   group = "schema_program_membership",
 *   weight = 10,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipMainEntityOfPage extends SchemaMainEntityOfPageBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
