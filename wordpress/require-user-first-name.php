<?php

/**
 * Requires the native first name field when updating a WordPress user profile.
 *
 * Validates profile updates on the server for both the current user and users
 * edited by an administrator. Adds required to the first name field, using
 * native browser validation without changing other fields.
 *
 * Requirements:
 * - PHP 7.1+
 * - WordPress 4.0+
 *
 * Usage:
 * - Include this file from a plugin or the active theme's functions.php.
 * - Applies to profile updates, not new user registration or creation.
 */
if (!function_exists('lbdc_user_first_name_required_message')) {
    /**
     * Uses existing core translations in the current user's admin locale.
     */
    function lbdc_user_first_name_required_message(): string
    {
        return __('First Name') . ': ' . wp_strip_all_tags(
            __('<strong>Error:</strong> Please fill the required fields.')
        );
    }
}
if (!function_exists('lbdc_require_user_first_name')) {
    /**
     * Validates the first name submitted for an existing user profile.
     *
     * @param WP_Error $errors Validation errors, passed by reference by WordPress.
     * @param bool     $update Whether an existing user is being updated.
     * @param stdClass $user   User data being validated.
     */
    function lbdc_require_user_first_name(WP_Error $errors, bool $update, stdClass $user): void
    {
        if (!$update) {
            return;
        }

        if (!isset($_POST['first_name']) || !is_string($_POST['first_name'])) {
            return;
        }

        $first_name = trim(sanitize_text_field(wp_unslash($_POST['first_name'])));

        if ($first_name === '') {
            $errors->add(
                'lbdc_required_first_name',
                lbdc_user_first_name_required_message(),
                ['form-field' => 'first_name']
            );
        }
    }
    add_action('user_profile_update_errors', 'lbdc_require_user_first_name', 10, 3);
}

if (!function_exists('lbdc_require_user_first_name_script')) {
    /**
     * Adds first name validation to the profile and user edit screens only.
     */
    function lbdc_require_user_first_name_script(): void
    {
?>
        <script>
            (() => {
                const field = document.getElementById('first_name');
                if (!field || !field.form) {
                    return;
                }

                field.required = true;

                const lbdcValidateFirstName = () => {
                    field.setCustomValidity(field.value.trim() === '' ? <?php echo wp_json_encode(lbdc_user_first_name_required_message(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?> : '');
                };

                field.addEventListener('input', lbdcValidateFirstName);
                field.form.addEventListener('submit', (event) => {
                    lbdcValidateFirstName();
                    // WordPress uses novalidate: validate this field alone.
                    if (!field.reportValidity()) {
                        event.preventDefault();
                    }
                });
                lbdcValidateFirstName();
            })();
        </script>
<?php
    }
    add_action('admin_footer-profile.php', 'lbdc_require_user_first_name_script');
    add_action('admin_footer-user-edit.php', 'lbdc_require_user_first_name_script');
}
