<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaPersonOrgBase;

/**
 * Provides a plugin for the 'schema_program_membership_member' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_member",
 *   label = @Translation("member"),
 *   description = @Translation("A member of an Organization or a ProgramMembership. Organizations can be members of organizations; ProgramMembership is typically for individuals."),
 *   name = "member",
 *   group = "schema_program_membership",
 *   weight = -25,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipMember extends SchemaPersonOrgBase {
  // Nothing here yet. Just a placeholder class for a plugin.
}
