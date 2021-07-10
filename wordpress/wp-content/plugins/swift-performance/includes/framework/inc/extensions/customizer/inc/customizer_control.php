<?php

    if ( ! class_exists( 'ReduxSA_Customizer_Control' ) ) {
        class ReduxSA_Customizer_Control extends WP_Customize_Control {

            public function render() {
                $this->reduxsa_id = str_replace( 'customize-control-', '', 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) ) );
                $class          = 'customize-control reduxsa-group-tab reduxsa-field customize-control-' . $this->type;
                $opt_name       = explode( '[', $this->id );
                $opt_name       = $opt_name[0];
                ?>
                <li id="<?php echo esc_attr( $this->reduxsa_id ); ?>" class="<?php echo esc_attr( $class ); ?>">
                    <?php if ( $this->type != "repeater" ): ?>
                        <input type="hidden"
                            data-id="<?php echo esc_attr( $this->id ); ?>"
                            data-key="<?php echo str_replace( $opt_name . '-', '', $this->reduxsa_id ); ?>"
                            class="reduxsa-customizer-input"
                            id="customizer_control_id_<?php echo esc_attr( $this->reduxsa_id ); ?>" <?php echo esc_url( $this->link() ) ?>
                            value=""/>
                    <?php endif; ?>
                    <?php $this->render_content(); ?>
                </li>
                <?php

            }

            public function render_content() {
                do_action( 'reduxsa/advanced_customizer/control/render/' . $this->reduxsa_id, $this );
            }

            public function label() {
                // The label has already been sanitized in the Fields class, no need to re-sanitize it.
                echo $this->label;
            }

            public function description() {
                if ( ! empty( $this->description ) ) {
                    // The description has already been sanitized in the Fields class, no need to re-sanitize it.
                    echo '<span class="description customize-control-description">' . $this->description . '</span>';
                }
            }

            public function title() {
                echo '<span class="customize-control-title">';
                $this->label();
                $this->description();
                echo '</span>';
            }
        }
    }
