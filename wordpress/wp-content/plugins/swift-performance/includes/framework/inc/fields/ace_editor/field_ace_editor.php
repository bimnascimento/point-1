<?php

    /**
     * ReduxSA Framework is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 2 of the License, or
     * any later version.
     * ReduxSA Framework is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     * You should have received a copy of the GNU General Public License
     * along with ReduxSA Framework. If not, see <http://www.gnu.org/licenses/>.
     *
     * @package     ReduxSA_Field
     * @subpackage  ACE_Editor
     * @version     3.0.0
     */

// Exit if accessed directly
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

// Don't duplicate me!
    if ( ! class_exists( 'ReduxSAFramework_ace_editor' ) ) {
        class ReduxSAFramework_ace_editor {

            /**
             * Field Constructor.
             * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
             *
             * @since ReduxSAFramework 1.0.0
             */
            function __construct( $field = array(), $value = '', $parent ) {
                $this->parent = $parent;
                $this->field  = $field;
                $this->value  = $value;

                if ( is_array( $this->value ) ) {
                    $this->value = '';
                } else {
                    $this->value = trim( $this->value );
                }

                if ( ! empty( $this->field['options'] ) ) {
                    $this->field['args'] = $this->field['options'];
                    unset( $this->field['options'] );
                }

            }

            /**
             * Field Render Function.
             * Takes the vars and outputs the HTML for the field in the settings
             *
             * @since ReduxSAFramework 1.0.0
             */
            function render() {

                if ( ! isset( $this->field['mode'] ) ) {
                    $this->field['mode'] = 'javascript';
                }
                if ( ! isset( $this->field['theme'] ) ) {
                    $this->field['theme'] = 'monokai';
                }

                $params = array(
                    'minLines' => 10,
                    'maxLines' => 30,
                );

                if ( isset( $this->field['args'] ) && ! empty( $this->field['args'] ) && is_array( $this->field['args'] ) ) {
                    $params = wp_parse_args( $this->field['args'], $params );
                }

                ?>
                <div class="ace-wrapper">
                    <input type="hidden" 
                        class="localize_data"
                        value="<?php echo htmlspecialchars( json_encode( $params ) ); ?>"
                    />
                    <textarea name="<?php echo esc_attr($this->field['name'] . $this->field['name_suffix']); ?>" id="<?php echo esc_attr($this->field['id']); ?>-textarea" class="ace-editor hide <?php echo esc_attr($this->field['class']); ?>" data-editor="<?php echo esc_attr($this->field['id']); ?>-editor" data-mode="<?php echo esc_attr($this->field['mode']); ?>" data-theme="<?php echo esc_attr($this->field['theme']); ?>"><?php echo esc_textarea($this->value); ?></textarea>
                    <pre id="<?php echo esc_attr($this->field['id']); ?>-editor" class="ace-editor-area"><?php echo htmlspecialchars( $this->value ); ?></pre>
                </div>
            <?php
            }

            /**
             * Enqueue Function.
             * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
             *
             * @since       1.0.0
             * @access      public
             * @return      void
             */
            public function enqueue() {
                if ( $this->parent->args['dev_mode'] ) {
                    if ( ! wp_style_is( 'reduxsa-field-ace-editor-css' ) ) {
                        wp_enqueue_style(
                            'reduxsa-field-ace-editor-css',
                            ReduxSAFramework::$_url . 'inc/fields/ace_editor/field_ace_editor.css',
                            array(),
                            time(),
                            'all'
                        );
                    }
                }

                if ( ! wp_script_is( 'ace-editor-js' ) ) {
                    ReduxSA_CDN::enqueue_script(
                        'ace-editor-js',
                        '//cdn.jsdelivr.net/ace/1.1.9/min/ace.js',
                        array( 'jquery' ),
                        '1.1.9',
                        true
                    );
                }

                if ( ! wp_script_is( 'reduxsa-field-ace-editor-js' ) ) {
                    wp_enqueue_script(
                        'reduxsa-field-ace-editor-js',
                        ReduxSAFramework::$_url . 'inc/fields/ace_editor/field_ace_editor' . ReduxSA_Functions::isMin() . '.js',
                        array( 'jquery', 'ace-editor-js', 'reduxsa-js' ),
                        time(),
                        true
                    );
                }
            }
        }
    }