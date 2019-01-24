<?php

namespace Drupal\Tests\schema_program_membership\Functional;

use Drupal\Tests\schema_metatag\Functional\SchemaMetatagTagsTestBase;

/**
 * Tests that each of the Schema Metatag ProgramMembership tags work correctly.
 *
 * @group schema_metatag
 * @group schema_program_membership
 */
class SchemaProgramMembershipTest extends SchemaMetatagTagsTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['schema_program_membership'];

  /**
   * {@inheritdoc}
   */
  public $moduleName = 'schema_program_membership';

  /**
   * {@inheritdoc}
   */
  public $schemaTagsNamespace = '\\Drupal\\schema_program_membership\\Plugin\\metatag\\Tag\\';

  /**
   * {@inheritdoc}
   */
  public $schemaTags = [
    'schema_program_membership_additional_type' => 'SchemaProgramMembershipAdditionalType',
    'schema_program_membership_alternate_name' => 'SchemaProgramMembershipAlternateName',
    'schema_program_membership_description' => 'SchemaProgramMembershipDescription',
    'schema_program_membership_disambiguating_description' => 'SchemaProgramMembershipDisambiguatingDescription',
    'schema_program_membership_hosting_organization' => 'SchemaProgramMembershipHostingOrganization',
    'schema_program_membership_identifier' => 'SchemaProgramMembershipIdentifier',
    'schema_program_membership_image' => 'SchemaProgramMembershipImage',
    'schema_program_membership_main_entity_of_page' => 'SchemaProgramMembershipMainEntityOfPage',
    'schema_program_membership_member' => 'SchemaProgramMembershipMember',
    'schema_program_membership_membership_number' => 'SchemaProgramMembershipMembershipNumber',
    'schema_program_membership_name' => 'SchemaProgramMembershipName',
    'schema_program_membership_program_name' => 'SchemaProgramMembershipProgramName',
    'schema_program_membership_same_as' => 'SchemaProgramMembershipSameAs',
    'schema_program_membership_type' => 'SchemaProgramMembershipType',
    'schema_program_membership_url' => 'SchemaProgramMembershipUrl',
  ];

}
