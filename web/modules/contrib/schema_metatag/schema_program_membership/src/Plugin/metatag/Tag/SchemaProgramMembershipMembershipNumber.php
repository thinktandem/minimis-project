<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaNameBase;

/**
 * Provides a plugin for the 'schema_program_membership_number' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_membership_number",
 *   label = @Translation("membershipNumber"),
 *   description = @Translation("A unique identifier for the membership."),
 *   name = "membershipNumber",
 *   group = "schema_program_membership",
 *   weight = -20,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipMembershipNumber extends SchemaNameBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
