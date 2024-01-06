Feature: Generate a POT file of a WordPress project

  Background:
    Given a WP install

  Scenario: Skips Twig files
    Given an empty foo-theme directory
    And a foo-theme/views/index.twig file:
      """
        {{ __( 'Hello World from Twig', 'foo-theme' ) }}
      """
    When I run `wp i18n make-pot foo-theme foo-theme.pot --skip-twig`
    Then STDOUT should be:
      """
      Success: POT file successfully generated!
      """
    And the foo-theme.pot file should not contain:
      """
      msgid "Hello World from Twig"
      """

  Scenario: Extract all supported functions
    Given an empty foo-theme directory
    And a foo-theme/views/index.twig file:
      """
        {{ __( '__', 'foo-theme' )|e }}
        {{ esc_attr__( 'esc_attr__', 'foo-theme' ) }}
        {{ esc_html__( 'esc_html__', 'foo-theme' ) }}
        {{ esc_xml__( 'esc_xml__', 'foo-theme' ) }}
        {{ _e( '_e', 'foo-theme' ) }}
        {{ esc_attr_e( 'esc_attr_e', 'foo-theme' ) }}
        {{ esc_html_e( 'esc_html_e', 'foo-theme' ) }}
        {{ esc_xml_e( 'esc_xml_e', 'foo-theme' ) }}
        {{ _x( '_x', '_x_context', 'foo-theme' ) }}
        {{ _ex( '_ex', '_ex_context', 'foo-theme' ) }}
        {{ esc_attr_x( 'esc_attr_x', 'esc_attr_x_context', 'foo-theme' ) }}
        {{ esc_html_x( 'esc_html_x', 'esc_html_x_context', 'foo-theme' ) }}
        {{ esc_xml_x( 'esc_xml_x', 'esc_xml_x_context', 'foo-theme' ) }}
        {{ _n( '_n_single', '_n_plural', number, 'foo-theme' ) }}
        {{ _nx( '_nx_single', '_nx_plural', number, '_nx_context', 'foo-theme' ) }}
        {{ _n_noop( '_n_noop_single', '_n_noop_plural', 'foo-theme' ) }}
        {{ _nx_noop( '_nx_noop_single', '_nx_noop_plural', '_nx_noop_context', 'foo-theme' ) }}
        {{ _n( '_n_single', '_n_plural', number, 'foo-theme' ) }}

        // Compat.
        {{ _( '_', 'foo-theme' ) }}

        // Deprecated.
        {{ _c( '_c', 'foo-theme' ) }}
        {{ _nc( '_nc_single', '_nc_plural', number, 'foo-theme' ) }}
        {{ __ngettext( '__ngettext_single', '__ngettext_plural', number, 'foo-theme' ) }}
        {{ __ngettext_noop( '__ngettext_noop_single', '__ngettext_noop_plural', 'foo-theme' ) }}

        {{ __unsupported_func( '__unsupported_func', 'foo-theme' ) }}
        {{ __( 'wrong-domain', 'wrong-domain' ) }}
      """

    When I run `wp i18n make-pot foo-theme`
    Then STDOUT should be:
      """
      Success: POT file successfully generated!
      """
    And the foo-theme/foo-theme.pot file should exist
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "__"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_attr__"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_html__"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_xml__"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_e"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_attr_e"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_html_e"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_xml_e"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_x"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "_x_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_ex"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "_ex_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_attr_x"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "esc_attr_x_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_html_x"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "esc_html_x_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "esc_xml_x"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "esc_xml_x_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_n_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "_n_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_nx_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "_nx_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "_nx_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_n_noop_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "_n_noop_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_nx_noop_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "_nx_noop_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "_nx_noop_context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_c"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "_nc_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "_nc_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "__ngettext_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "__ngettext_plural"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "__ngettext_noop_single"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "__ngettext_noop_plural"
      """
    And the foo-theme/foo-theme.pot file should not contain:
      """
      msgid "__unsupported_func"
      """
    And the foo-theme/foo-theme.pot file should not contain:
      """
      msgid "wrong-domain"
      """

  Scenario: Extract functions that could contain function calls
    Given an empty foo-theme directory
    And a foo-theme/views/index.twig file:
      """
        {{ _n( 'Single with attribute', 'Plural with attribute', attribute(obj, 'prop'), 'foo-theme' ) }}
        {{ _n( 'Single with filter', 'Plural with filter', array|count, 'foo-theme' ) }}
        {{ _nx( 'Single with unknown function and context', 'Plural with unknown function and context', func('param'), 'unknown function context', 'foo-theme' ) }}
      """
    When I run `wp i18n make-pot foo-theme`
    Then the foo-theme/foo-theme.pot file should contain:
      """
      msgid "Single with attribute"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "Plural with attribute"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "Single with filter"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "Plural with filter"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid "Single with unknown function and context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgid_plural "Plural with unknown function and context"
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      msgctxt "unknown function context"
      """

  Scenario: Extract translator comments
    Given I run `echo "\t"`
    And save STDOUT as {TAB}
    Given an empty foo-theme directory
    And a foo-theme/views/index.twig file:
      """
      {# translators: Translators 1! #}
      {{ __( 'hello world', 'foo-theme' ) }}

      {# Translators: Translators 2! #}
      {{ __( 'foo', 'foo-theme' ) }}

      {# translators: this should get extracted. #} {{ __( 'baba', 'foo-theme' ) }}

      {# translators: boo #} {# translators: this should get extracted too. #} {# some other comment #} {{__( 'bubu', 'foo-theme' ) }}
      """

    When I run `wp i18n make-pot foo-theme`
    Then STDOUT should be:
      """
      Success: POT file successfully generated!
      """
    And the foo-theme/foo-theme.pot file should exist
    And the foo-theme/foo-theme.pot file should contain:
      """
      #. translators: Translators 1!
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      #. Translators: Translators 2!
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      #. translators: this should get extracted.
      """
    And the foo-theme/foo-theme.pot file should contain:
      """
      #. translators: this should get extracted too.
      """

  Scenario: Handle unknown tests
    Given an empty foo-theme directory
    And a foo-theme/views/index.twig file:
      """
        {% if form is not rootform %}
          {{ __( 'Foo', 'foo-theme' ) }}
        {% endif %}
      """
    When I run `wp i18n make-pot foo-theme`
    Then the foo-theme/foo-theme.pot file should contain:
      """
      msgid "Foo"
      """
