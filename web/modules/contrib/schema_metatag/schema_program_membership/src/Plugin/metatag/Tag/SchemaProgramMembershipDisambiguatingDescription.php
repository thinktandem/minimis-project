<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaNameBase;

/**
 * Plugin for 'schema_program_membership_disambiguating_description' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_disambiguating_description",
 *   label = @Translation("disambiguatingDescription"),
 *   description = @Translation("A sub property of description. A short description of the item used to disambiguate from other, similar items. Information from other properties (in particular, name) may be necessary for the description to be useful for disambiguation."),
 *   name = "disambiguatingDescription",
 *   group = "schema_program_membership",
 *   weight = 1,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipDisambiguatingDescription extends SchemaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
