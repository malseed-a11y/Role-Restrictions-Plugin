<?php

/**
 * Plugin Name: Role Restrictions
 * Description: إضافة حديثة لتقييد الوصول بناءً على أدوار المستخدمين بتصميم احترافي.
 * Version: 2.0
 * Author: mosaab
 * Text Domain: role-restrictions
 * License: GPL v2 or later
 */

// منع الوصول المباشر للملف.
if (!defined('ABSPATH')) {
    exit;
}

// تعريف ثوابت الإضافة.
define('ROLE_RESTRICTIONS_PLUGIN_DIR', plugin_dir_url(__FILE__));
define('ROLE_RESTRICTIONS_VERSION', '2.0.0');

/**
 * دالة لتسجيل إعدادات الإضافة في قاعدة البيانات.
 * نستخدم register_setting لتخزين إعداداتنا في مجموعة (group) خاصة.
 */
add_action('admin_init', 'role_restrictions_register_settings');
function role_restrictions_register_settings()
{
    register_setting('role_restrictions_group', 'role_restrictions_settings', [
        'default' => [
            'restricted_role' => 'editor',
            'menu_items'      => [], // القوائم التي سيتم إخفاؤها
            'restore_items'   => [], // القوائم التي سيتم إظهارها
            'remove_caps'     => [], // الصلاحيات التي سيتم إزالتها
            'add_caps'        => []  // الصلاحيات التي سيتم إضافتها
        ]
    ]);
}

/**
 * دالة لإضافة صفحة إعدادات الإضافة في لوحة التحكم.
 * نضيفها تحت قائمة "إعدادات".
 */
add_action('admin_menu', 'role_restrictions_add_admin_menu');
function role_restrictions_add_admin_menu()
{
    add_options_page(
        __('Role Restrictions Settings', 'role-restrictions'),
        __('Role Restrictions', 'role-restrictions'),
        'manage_options',
        'role-restrictions',
        'role_restrictions_settings_page'
    );
}

/**
 * دالة لعرض صفحة إعدادات الإضافة.
 * هذه الدالة تحتوي على كل أكواد HTML و PHP اللازمة لصفحة الإعدادات.
 */
function role_restrictions_settings_page()
{
    // الحصول على الإعدادات المحفوظة من قاعدة البيانات.
    $options = get_option('role_restrictions_settings');
    // الحصول على جميع أدوار المستخدمين.
    $roles = wp_roles()->roles;
    // الحصول على قائمة القوائم في لوحة التحكم.
    global $menu;
?>
    <div class="role-restrictions-admin">
        <div class="role-restrictions-header">
            <h1>
                <span class="dashicons dashicons-admin-users"></span>
                <?php _e('Role Restrictions Settings', 'role-restrictions'); ?>
            </h1>
            <p class="role-restrictions-description">
                <?php _e('Configure role-based access restrictions for your WordPress site. Control menu visibility and user capabilities with precision.', 'role-restrictions'); ?>
            </p>
        </div>

        <div class="role-restrictions-form">
            <form method="post" action="options.php">
                <?php
                // حقول الأمان المخفية اللازمة لحفظ الإعدادات.
                settings_fields('role_restrictions_group');
                ?>

                <div class="role-restrictions-section">
                    <div class="section-header">
                        <h3><span class="dashicons dashicons-groups"></span> <?php _e('Target Role Selection', 'role-restrictions'); ?></h3>
                    </div>
                    <div class="section-content">
                        <div class="form-field">
                            <label for="restricted_role" class="field-label"><?php _e('Select Role to Restrict', 'role-restrictions'); ?></label>
                            <select name="role_restrictions_settings[restricted_role]" id="restricted_role" class="role-select">
                                <?php foreach ($roles as $role_key => $role) : ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected($options['restricted_role'], $role_key); ?>>
                                        <?php echo esc_html($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="field-description">
                                <?php _e('Choose the user role that will have restrictions applied to it.', 'role-restrictions'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="role-restrictions-section">
                    <div class="section-header">
                        <h3><span class="dashicons dashicons-admin-settings"></span> <?php _e('Menu Items Management', 'role-restrictions'); ?></h3>
                    </div>
                    <div class="section-content">
                        <div class="two-column-container">
                            <div class="column show-column">
                                <div class="column-header">
                                    <span class="dashicons dashicons-visibility"></span>
                                    <?php _e('Show Menu Items', 'role-restrictions'); ?>
                                </div>
                                <div class="column-content">
                                    <select multiple name="role_restrictions_settings[restore_items][]" id="restore_items">
                                        <?php foreach ($menu as $item) :
                                            $slug = $item[2] ?? '';
                                            $title = strip_tags($item[0] ?? '');
                                            if (!$slug || empty($title)) continue;
                                        ?>
                                            <option value="<?php echo esc_attr($slug); ?>" <?php selected(in_array($slug, $options['restore_items'] ?? []), true); ?>>
                                                <?php echo esc_html($title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="column-description">
                                        <?php _e('Select menu items to make visible for the selected role. These items will be shown in the admin menu.', 'role-restrictions'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="column hide-column">
                                <div class="column-header">
                                    <span class="dashicons dashicons-hidden"></span>
                                    <?php _e('Hide Menu Items', 'role-restrictions'); ?>
                                </div>
                                <div class="column-content">
                                    <select multiple name="role_restrictions_settings[menu_items][]" id="menu_items">
                                        <?php foreach ($menu as $item) :
                                            $slug = $item[2] ?? '';
                                            $title = strip_tags($item[0] ?? '');
                                            if (!$slug || empty($title)) continue;
                                        ?>
                                            <option value="<?php echo esc_attr($slug); ?>" <?php selected(in_array($slug, $options['menu_items'] ?? []), true); ?>>
                                                <?php echo esc_html($title); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="column-description">
                                        <?php _e('Select menu items to hide from the selected role. These items will be removed from the admin menu.', 'role-restrictions'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="role-restrictions-section">
                    <div class="section-header">
                        <h3><span class="dashicons dashicons-admin-users"></span> <?php _e('Capabilities Management', 'role-restrictions'); ?></h3>
                    </div>
                    <div class="section-content">
                        <div class="two-column-container">
                            <div class="column show-column">
                                <div class="column-header">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php _e('Add Capabilities', 'role-restrictions'); ?>
                                </div>
                                <div class="column-content">
                                    <select multiple name="role_restrictions_settings[add_caps][]" id="add_caps">
                                        <?php
                                        // الحصول على جميع صلاحيات المدير لعرضها.
                                        $capabilities = array_keys(wp_roles()->roles['administrator']['capabilities']);
                                        foreach ($capabilities as $cap) :
                                        ?>
                                            <option value="<?php echo esc_attr($cap); ?>" <?php selected(in_array($cap, $options['add_caps'] ?? []), true); ?>>
                                                <?php echo esc_html($cap); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="column-description">
                                        <?php _e('Select capabilities to grant to the selected role. This will give users additional permissions and access rights.', 'role-restrictions'); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="column hide-column">
                                <div class="column-header">
                                    <span class="dashicons dashicons-minus"></span>
                                    <?php _e('Remove Capabilities', 'role-restrictions'); ?>
                                </div>
                                <div class="column-content">
                                    <select multiple name="role_restrictions_settings[remove_caps][]" id="remove_caps">
                                        <?php
                                        // عرض نفس الصلاحيات للإزالة.
                                        foreach ($capabilities as $cap) :
                                        ?>
                                            <option value="<?php echo esc_attr($cap); ?>" <?php selected(in_array($cap, $options['remove_caps'] ?? []), true); ?>>
                                                <?php echo esc_html($cap); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="column-description">
                                        <?php _e('Select capabilities to revoke from the selected role. This will restrict user permissions and limit access rights.', 'role-restrictions'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="role-restrictions-submit">
                    <?php submit_button(__('Save Changes', 'role-restrictions'), 'primary', 'submit', false, ['class' => 'button-primary role-restrictions-save']); ?>
                </div>
            </form>


        </div>
    </div>
<?php
}

/**
 * دالة لتطبيق قيود القوائم والصلاحيات.
 * هذه الدالة تعمل عند تحميل لوحة التحكم للمستخدمين.
 */
add_action('admin_menu', 'role_restrictions_apply_restrictions', 999);
function role_restrictions_apply_restrictions()
{
    // الحصول على الإعدادات.
    $options = get_option('role_restrictions_settings');
    $role_key = $options['restricted_role'] ?? '';
    $menu_items_to_hide = $options['menu_items'] ?? [];
    $menu_items_to_restore = $options['restore_items'] ?? [];
    $remove_caps = $options['remove_caps'] ?? [];
    $add_caps = $options['add_caps'] ?? [];

    // التحقق من أن المستخدم الحالي لديه الدور المستهدف.
    if ($role_key && current_user_can($role_key)) {

        // إخفاء القوائم.
        foreach ($menu_items_to_hide as $slug) {
            // إخفاء القائمة فقط إذا لم يتم اختيارها للإظهار.
            if (!in_array($slug, $menu_items_to_restore)) {
                remove_menu_page($slug);
            }
        }

        // الحصول على كائن الدور لتعديل الصلاحيات.
        $role = get_role($role_key);
        if ($role) {
            // إزالة الصلاحيات.
            foreach ($remove_caps as $cap) {
                $role->remove_cap($cap);
            }
            // إضافة الصلاحيات.
            foreach ($add_caps as $cap) {
                $role->add_cap($cap);
            }
        }
    }
}

/**
 * دالة لتسجيل ملفات CSS و JavaScript.
 * نستخدمها فقط على صفحة إعدادات الإضافة.
 */
add_action('admin_enqueue_scripts', 'role_restrictions_enqueue_admin_assets');
function role_restrictions_enqueue_admin_assets($hook)
{
    // تحميل الملفات فقط على صفحة الإعدادات الخاصة بنا.
    if ($hook !== 'settings_page_role-restrictions') {
        return;
    }

    // تسجيل ملف CSS الخاص بالإضافة.
    wp_enqueue_style(
        'role-restrictions-admin',
        ROLE_RESTRICTIONS_PLUGIN_DIR . 'css/style.css',
        [],
        ROLE_RESTRICTIONS_VERSION
    );

    // تسجيل Dashicons (رموز ووردبريس) لضمان ظهورها.
    wp_enqueue_style('dashicons');
}

/**
 * دالة لإنشاء دور مخصص عند تفعيل الإضافة.
 * نستخدم register_activation_hook لتنفيذ الدالة مرة واحدة.
 */
register_activation_hook(__FILE__, 'role_restrictions_create_website_owner_role');
function role_restrictions_create_website_owner_role()
{
    // إذا كان الدور "website_owner" غير موجود، قم بإنشائه.
    if (get_role('website_owner') === null) {
        $caps = get_role('administrator')->capabilities; // الحصول على صلاحيات المدير
        unset($caps['activate_plugins']); // إزالة صلاحية تفعيل الإضافات
        add_role(
            'website_owner',
            __('Website Owner', 'role-restrictions'),
            $caps
        );
    }
}

/**
 * دالة لتنظيف الإعدادات عند إلغاء تفعيل الإضافة.
 * نستخدم register_deactivation_hook لتنفيذها عند إلغاء التفعيل.
 */
register_deactivation_hook(__FILE__, 'role_restrictions_deactivation_cleanup');
function role_restrictions_deactivation_cleanup()
{
    // إزالة الدور المخصص عند إلغاء تفعيل الإضافة.
    remove_role('website_owner');
    // يمكنك أيضاً حذف الخيارات المخزنة هنا إذا أردت.
    // delete_option('role_restrictions_settings');
}
