<?php
/*
  WPFront User Role Editor Plugin
  Copyright (C) 2014, WPFront.com
  Website: wpfront.com
  Contact: syam@wpfront.com

  WPFront User Role Editor Plugin is distributed under the GNU General Public License, Version 3,
  June 2007. Copyright (C) 2007 Free Software Foundation, Inc., 51 Franklin
  St, Fifth Floor, Boston, MA 02110, USA

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
  DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
  ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Template for WPFront User Role Editor Role Add/Edit
 *
 * @author Syam Mohan <syam@wpfront.com>
 * @copyright 2014 WPFront.com
 */

namespace WPFront\URE\Roles;

if (!defined('ABSPATH')) {
    exit();
}

use WPFront\URE\Taxonomies\WPFront_User_Role_Editor_Taxonomies;
use WPFront\URE\Post_Type\WPFront_User_Role_Editor_Post_Type;

if (!class_exists('WPFront\URE\Roles\WPFront_User_Role_Editor_Role_Add_Edit_View')) {

    class WPFront_User_Role_Editor_Role_Add_Edit_View extends \WPFront\URE\WPFront_User_Role_Editor_View {

        protected $RoleAddEdit;

        public function __construct() {
            parent::__construct();

            $this->RoleAddEdit = WPFront_User_Role_Editor_Role_Add_Edit::instance();
        }

        public function view() {
            add_thickbox();
            ?>
            <div class="wrap role-add-new">
                <?php $this->title(__('Add New Role', 'wpfront-user-role-editor')); ?>
                <?php $this->display_notices(); ?>
                <?php $this->display_errors(); ?>
                <?php $this->display_description(); ?>
                <form method="post" id="createuser" name="createuser" class="validate">
                    <?php wp_nonce_field('add-new-role'); ?>
                    <?php $this->display_name_fields(); ?>

                    <?php
                    $this->submit_button();
                    ?>
                </form>
            </div>

            <?php
        }

        protected function title($title, $add_new = array(), $search = null) {
            $is_edit_role = $this->RoleAddEdit->edit_role();

            if ($is_edit_role) {
                $title = __('Edit Role', 'wpfront-user-role-editor');

                if (current_user_can('create_roles')) {
                    $add_new[0] = __('Add New', 'wpfront-user-role-editor');
                    $add_new[1] = $this->RoleAddEdit->get_add_new_role_url();
                }
            }

            parent::title($title, $add_new);
        }

        protected function display_description() {
            $is_edit_role = $this->RoleAddEdit->edit_role();
            if (!$is_edit_role) {
                printf('<p>%s</p>', __('Create a brand new role and add it to this site.', 'wpfront-user-role-editor'));
            }
        }

        protected function display_errors() {
            $role_data = $this->RoleAddEdit->get_role_data();
            if (!empty($role_data['error'])) {
                $this->UtilsClass::notice_error($role_data['error']);
            }
        }

        protected function display_notices() {
            if (!empty($_GET['role-added']) && $_GET['role-added'] === 'true') {
                $this->UtilsClass::notice_updated(__('New role added.', 'wpfront-user-role-editor'));
            } elseif (!empty($_GET['role-updated']) && $_GET['role-updated'] === 'true') {
                $this->UtilsClass::notice_updated(__('Role updated.', 'wpfront-user-role-editor'));
            }
        }

        protected function display_name_fields() {
            ?>
            <table class="form-table">
                <tbody>
                    <tr class="form-field form-required <?php echo $this->is_display_name_valid() ? '' : 'form-invalid' ?>">
                        <th scope="row">
                            <label for="display_name">
                                <?php echo __('Display Name', 'wpfront-user-role-editor'); ?> <span class="description">(<?php echo __('required', 'wpfront-user-role-editor'); ?>)</span>
                            </label>
                        </th>
                        <td>
                            <input name="display_name" type="text" id="display_name" value="<?php echo esc_attr($this->get_role_display_name()); ?>" aria-required="true" <?php echo $this->is_role_display_name_disabled() ? 'disabled' : ''; ?> />
                        </td>
                    </tr>
                    <tr class="form-field form-required <?php echo $this->is_role_name_valid() ? '' : 'form-invalid' ?>">
                        <th scope="row">
                            <label for="role_name">
                                <?php echo __('Role Name', 'wpfront-user-role-editor'); ?> <span class="description">(<?php echo __('required', 'wpfront-user-role-editor'); ?>)</span>
                            </label>
                        </th>
                        <td>
                            <input name="role_name" type="text" id="role_name" value="<?php echo esc_attr($this->get_role_name()); ?>" aria-required="true" <?php echo $this->is_role_name_disabled() ? 'disabled' : ''; ?> />
                        </td>
                    </tr>
                    <tr class="form-field">
                        <th scope="row">
                            <label for="role_discount">
                                <?php echo __('Role Discount', 'wpfront-user-role-editor'); ?> <span class="role-discount">(<?php echo __('required', 'wpfront-user-role-editor'); ?>)</span>
                            </label>
                        </th>
                        <td>
                            <input name="role_discount" type="number" min="0" max="99" id="role_discount" value="<?php echo esc_attr($this->get_role_discount()); ?>" aria-required="true" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }



        public function render_caps($value) {
            foreach ($value->caps as $cap) {
                $enabled = apply_filters("wpfront_ure_capability_{$cap}_functionality_enabled", true, $cap);
                $help_url = apply_filters('wpfront_ure_capability_ui_help_link', '', $cap, $value->group_obj);
                $help_url = apply_filters("wpfront_ure_capability_{$cap}_ui_help_link", $help_url, $cap);
                ?>
                <div>
                    <input type="checkbox" class="allow" id="<?php echo 'cap-' . esc_attr($cap) . '-allow'; ?>" name="capabilities[<?php echo esc_attr($cap); ?>][allow]" <?php echo $value->disabled ? 'disabled' : '' ?> <?php echo $this->is_cap_granted($cap) ? 'checked' : '' ?> />
                    <input type="checkbox" class="deny hidden" id="<?php echo 'cap-' . esc_attr($cap) . '-deny'; ?>" name="capabilities[<?php echo esc_attr($cap); ?>][deny]" <?php echo $value->disabled ? 'disabled' : '' ?> <?php echo $this->is_cap_denied($cap) ? 'checked' : '' ?> />
                    <label class="cap-label cap-label-<?php echo esc_attr($cap); ?> <?php echo $enabled ? '' : 'disabled'; ?> <?php echo $this->is_cap_denied($cap) ? 'denied' : '' ?>" data-cap="<?php echo esc_attr($cap); ?>" title="<?php echo esc_attr($cap); ?>"><?php echo esc_html($cap); ?></label>
                    <?php
                    if (!empty($help_url)) {
                        ?>
                        <a target="_blank" href="<?php echo esc_attr($help_url); ?>">
                            <i class="fa fa-question-circle-o"></i>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                <?php
            }
        }



        protected function get_cap_state($cap) {
            $role_data = $this->RoleAddEdit->get_role_data();
            if (empty($role_data)) {
                return null;
            }

            if (isset($role_data['capabilities'][$cap])) {
                return $role_data['capabilities'][$cap];
            }

            return null;
        }

        protected function is_cap_granted($cap) {
            return $this->get_cap_state($cap) === true;
        }

        protected function is_cap_denied($cap) {
            return $this->get_cap_state($cap) === false;
        }

        protected function submit_button() {
            $is_edit_role = $this->RoleAddEdit->edit_role();

            $attr = ['id' => 'createusersub'];
            if ($this->is_sub_controls_disabled()) {
                $attr['disabled'] = true;
            }

            submit_button(
                    $is_edit_role ? __('Update Role', 'wpfront-user-role-editor') : __('Add New Role', 'wpfront-user-role-editor'),
                    'primary',
                    'createrole',
                    true,
                    $attr
            );
        }

        protected function get_role_name() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return '';
            }

            return $role_data['role_name'];
        }
        protected function get_role_discount(){
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return '';
            }

            $option = get_option($role_data['role_name'].'_role_discount');
            return $option;

        }

        protected function is_role_name_disabled() {
            $is_edit_role = $this->RoleAddEdit->edit_role();

            return $is_edit_role;
        }

        protected function is_role_name_valid() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return false;
            }

            return $role_data['is_role_name_valid'];
        }

        protected function get_role_display_name() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return '';
            }

            return $role_data['display_name'];
        }

        protected function is_role_display_name_disabled() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return true;
            }

            return $role_data['is_readonly'];
        }

        protected function is_display_name_valid() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data === null) {
                return false;
            }

            return $role_data['is_display_name_valid'];
        }

        protected function is_sub_controls_disabled() {
            return $this->is_role_display_name_disabled();
        }

        protected function get_copy_from_roles() {
            $role_data = $this->RoleAddEdit->get_role_data();

            if ($role_data !== null && $role_data['is_readonly']) {
                return array();
            }

            return $this->RolesHelperClass::get_names();
        }

        protected function ajax_url() {
            return json_encode(admin_url('admin-ajax.php'));
        }



    }

}

