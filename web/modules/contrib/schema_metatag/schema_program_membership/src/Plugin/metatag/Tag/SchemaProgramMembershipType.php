<?php

namespace Drupal\schema_program_membership\Plugin\metatag\Tag;

use Drupal\schema_metatag\Plugin\metatag\Tag\SchemaTypeBase;

/**
 * Provides a plugin for the 'schema_program_membership_type' meta tag.
 *
 * - 'id' should be a globally unique id.
 * - 'name' should match the Schema.org element name.
 * - 'group' should match the id of the group that defines the Schema.org type.
 *
 * @MetatagTag(
 *   id = "schema_program_membership_type",
 *   label = @Translation("@type"),
 *   description = @Translation("The type of ProgramMembership (fixed by standard)."),
 *   name = "@type",
 *   group = "schema_program_membership",
 *   weight = -10,
 *   type = "string",
 *   secure = FALSE,
 *   multiple = FALSE
 * )
 */
class SchemaProgramMembershipType extends SchemaTypeBase {

  /**
   * {@inheritdoc}
   */
  public static function labels() {
    return [
      'ProgramMembership',
    ];
  }

}
