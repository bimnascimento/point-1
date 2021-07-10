<?php
/**
 * Dokan Settings Address form Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>

<?php

$address         = isset( $profile_info['address'] ) ? $profile_info['address'] : '';
$address_street1 = isset( $profile_info['address']['street_1'] ) ? $profile_info['address']['street_1'] : '';
$address_street2 = isset( $profile_info['address']['street_2'] ) ? $profile_info['address']['street_2'] : '';
$address_city    = isset( $profile_info['address']['city'] ) ? $profile_info['address']['city'] : '';
$address_zip     = isset( $profile_info['address']['zip'] ) ? $profile_info['address']['zip'] : '';
$address_country = isset( $profile_info['address']['country'] ) ? $profile_info['address']['country'] : 'BR';
$address_state   = isset( $profile_info['address']['state'] ) ? 'MG' : 'MG';
$address_endereco  = isset( $profile_info['address']['endereco'] ) ? $profile_info['address']['endereco'] : '';
$address_bairro   = isset( $profile_info['address']['bairro'] ) ? $profile_info['address']['bairro'] : '';
$address_numero   = isset( $profile_info['address']['numero'] ) ? $profile_info['address']['numero'] : '';
$address_complemento  = isset( $profile_info['address']['complemento'] ) ? $profile_info['address']['complemento'] : '';

?>


<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="setting_address"><?php _e( 'Address', 'dokan' ); ?><br/>Informe o CEP para preenchimento automático.</label>

    <div class="dokan-w5 dokan-text-left dokan-address-fields">
      <?php
      if ( $seller_address_fields['city'] || $seller_address_fields['zip'] ) {
      ?>
          <div class="dokan-from-group">
              <?php
              if ( $seller_address_fields['zip'] ) { ?>
                  <div class="dokan-form-group dokan-w5 dokan-left">
                      <label class="control-label" for="dokan_address[zip]"><?php _e( 'Post/ZIP Code', 'dokan' ); ?>
                          <?php
                          $required_attr = '';
                          if ( $seller_address_fields['zip']['required'] ) {
                              $required_attr = 'required'; ?>
                              <span class="required"> *</span>
                          <?php } ?>
                      </label>
                      <input <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[zip]" value="<?php echo esc_attr( $address_zip ); ?>" name="dokan_address[zip]" placeholder="<?php _e( 'Postcode / Zip' , 'dokan' ) ?>" class="dokan-form-control input-md address-cep" type="text">
                      <a href="#" class="buscar-cep dokan-btn dokan-btn-danger dokan-btn-theme">buscar cep</a>
                  </div>
                  <?php }
                  if ( $seller_address_fields['city'] ) { ?>
                  <div class="dokan-form-group dokan-w6 dokan-left dokan-right-margin-30">
                      <label class="control-label" for="dokan_address[city]"><?php _e( 'City', 'dokan' ); ?>
                          <?php
                          $required_attr = '';
                          if ( $seller_address_fields['city']['required'] ) {
                              $required_attr = 'required'; ?>
                              <span class="required"> *</span>
                          <?php } ?>
                      </label>
                      <input readonly="false" <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[city]" value="<?php echo esc_attr( $address_city ); ?>" name="dokan_address[city]" placeholder="<?php _e( 'Cidade' , 'dokan' ) ?>" class="dokan-form-control input-md address-city" type="text">
                  </div>
              <?php } ?>
              <div class="dokan-clearfix"></div>
          </div>
          <?php } ?>
        <?php if ( $seller_address_fields['endereco'] ) { ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 control-label" for="dokan_address[endereco]"><?php _e( 'Endereço: ', 'dokan' ); ?>
                    <?php
                    $required_attr = '';
                    if ( $seller_address_fields['endereco']['required'] ) {
                        $required_attr = 'required'; ?>
                        <span class="required"> *</span>
                    <?php } ?>
                </label>
                <input readonly="false" <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[endereco]" value="<?php echo esc_attr( $address_endereco ); ?>" name="dokan_address[endereco]" placeholder="<?php _e( 'Endereço' , 'dokan' ) ?>" class="dokan-form-control input-md address-endereco" type="text">
            </div>
        <?php }
        if ( $seller_address_fields['street_2'] ) { ?>


          <div class="dokan-form-group">
              <label class="dokan-w3 control-label" for="dokan_address[bairro]"><?php _e( 'Bairro:', 'dokan' ); ?>
                  <?php
                  $required_attr = '';
                  if ( $seller_address_fields['bairro']['required'] ) {
                      $required_attr = 'required'; ?>
                      <span class="required"> *</span>
                  <?php } ?>
              </label>
              <input readonly="false" <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[bairro]" value="<?php echo esc_attr( $address_bairro ); ?>" name="dokan_address[bairro]" placeholder="<?php _e( 'Bairro' , 'dokan' ) ?>" class="dokan-form-control input-md address-bairro" type="text">
          </div>


          <div class="dokan-form-group">
                <label class="dokan-w3 control-label" for="dokan_address[numero]"><?php _e( 'Número', 'dokan' ); ?>
                    <?php
                    $required_attr = '';
                    if ( $seller_address_fields['numero']['required'] ) {
                        $required_attr = 'required'; ?>
                        <span class="required"> *</span>
                    <?php } ?>
                </label>
                <input <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[numero]" value="<?php echo esc_attr( $address_numero ); ?>" name="dokan_address[numero]" placeholder="<?php _e( 'Número' , 'dokan' ) ?>" class="dokan-form-control input-md" type="text">
            </div>


            <div class="dokan-form-group hidden">
                <label class="dokan-w3 control-label" for="dokan_address[complemento]"><?php _e( 'Complemento:', 'dokan' ); ?>
                    <?php
                    $required_attr = '';
                    if ( $seller_address_fields['complemento']['required'] ) {
                        $required_attr = 'required'; ?>
                        <span class="required"> *</span>
                    <?php } ?>
                </label>
                <input <?php echo $required_attr; ?> <?php echo $disabled ?> id="dokan_address[complemento]" value="<?php echo esc_attr( $address_complemento ); ?>" name="dokan_address[complemento]" placeholder="<?php _e( 'Complemento' , 'dokan' ) ?>" class="dokan-form-control input-md" type="text">
            </div>
        <?php }
        ///*
        if ( $seller_address_fields['country'] ) {
            $country_obj   = new WC_Countries();
            $countries     = $country_obj->countries;
            $states        = $country_obj->states;
        ?>
            <div class="dokan-form-group hidden">
                <label class="control-label" for="dokan_address[country]"><?php _e( 'Country ', 'dokan' ); ?>
                    <?php
                    $required_attr = '';
                    if ( $seller_address_fields['country']['required'] ) {
                        $required_attr = 'required'; ?>
                        <span class="required"> *</span>
                    <?php } ?>
                </label>
                <select <?php echo $required_attr; ?> <?php echo $disabled ?> name="dokan_address[country]" class="country_to_state dokan-form-control" id="dokan_address_country">
                    <?php dokan_country_dropdown( $countries, $address_country, false ); ?>
                </select>
            </div>
        <?php }
        if ( $seller_address_fields['state'] ) {
            $address_state_class = '';
            $is_input            = false;
            $no_states           = false;
            if ( isset( $states[$address_country] ) ) {
                if ( empty( $states[$address_country] ) ) {
                    $address_state_class = 'dokan-hide';
                    $no_states           = true;
                } else {

                }
            } else {
                $is_input = true;
            }
        ?>
            <div  id="dokan-states-box" class="dokan-form-group hidden">
                <label class="dokan-w3 control-label" for="dokan_address[state]"><?php _e( 'State ', 'dokan' ); ?>
                    <?php
                    $required_attr = '';
                    if ( $seller_address_fields['state']['required'] ) {
                        $required_attr = 'required'; ?>
                        <span class="required"> *</span>
                    <?php } ?>
                </label>
            <?php if ( $is_input ) { ?>
                <input <?php echo $required_attr; ?> <?php echo $disabled ?> name="dokan_address[state]" class="dokan-form-control <?php echo $address_state_class ?>" id="dokan_address_state" value="<?php echo $address_state ?>"/>
            <?php } else { ?>
                <select <?php echo $required_attr; ?> <?php echo $disabled ?> name="dokan_address[state]" class="dokan-form-control" id="dokan_address_state">
                    <?php dokan_state_dropdown( $states[$address_country], $address_state ) ?>
                </select>
            <?php } ?>
            </div>
        <?php } //*/?>

    </div>
</div>
