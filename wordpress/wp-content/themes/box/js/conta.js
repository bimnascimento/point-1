(function($){
$(document).ready(function(){

      $('.social-networks a').on('click', function(e) {
          e.preventDefault();
          //console.log('SOCIAL');
          var link = $(this).attr('href');
          window.open(link, "LOGIN", "height=" + screen.height + ",width=" + screen.width + ",toolbar=0,titlebar=0,status=0,menubar=0,location=0,channelmode=0,fullscreen=yes");
          //MyObject.loadingSite();
      });


});
$(window).load(function(){

    $( '#billing_birthdate, #pagseguro-card-holder-birth-date' ).mask( '99/99/9999', { placeholder: 'DD/MM/AAAA' } );
    //$( '#pagseguro-card-holder-birth-date' ).mask( '99 / 99 / 9999', { placeholder: ' ' } );
    $( '#billing_birthdate, #pagseguro-card-holder-birth-date' ).on('blur',function(e){

      var niver = jQuery(this).val().length;
      //console.log(niver);
      if(niver < 10){
        jQuery(this).val('');
      }

    });
    $( '#pagseguro-card-expiry' ).on('blur',function(e){
        var campo = jQuery(this).val().length;
        console.log(campo);
        if(campo < 9){
          jQuery(this).val('');
        }
    });

    $( '#billing_postcode, #calc_shipping_postcode, #reg_billing_postcode, #account_cep, #shipping_postcode' ).mask( '99999-999' );
    $( '#billing_cpf, #pagseguro-card-holder-cpf' ).mask( '999.999.999-99' );
    $( '#billing_cpf, #pagseguro-card-holder-cpf' ).on('blur',function(e){

      var cpf = jQuery(this).val().length;
      //console.log(cpf);
      if(cpf < 14){
        jQuery(this).val('');
      }

    });

    jQuery('#account_cep').after('<label class="error erro-cep"></label>');

    jQuery('#account_cep').on('blur',function(e){
        jQuery('.btn-criar-conta').prop('disabled', true);
        var cep = jQuery(this).val().replace( '-', '' );
        if( cep.length == 8 ){
                jQuery('.erro-cep').html('Aguarde verificando CEP ... ');
                /*
                if( enderecoCidade ){
                      if( enderecoCidade !== cidadeAtual ){
                          jQuery('.erro-cep').html('Informe um CEP de '+cidade+'.');
                      }else{
                          jQuery('.erro-cep').html('CEP OK.');
                          jQuery('.btn-criar-conta').prop('disabled', false);
                      }
                      return;
                }
                */
                MyObject.verificaCep(cep);
                setTimeout(function () {
                    if( enderecoCidade.length > 0 ){
                          //console.log( enderecoCidade );
                          jQuery('#endereco_cep').val(endereco);
                          jQuery('#bairro_cep').val(enderecoBairro);
                          jQuery('#cidade_cep').val(enderecoCidade);
                          jQuery('#estado_cep').val(enderecoEstado);
                          if( enderecoCidade !== cidadeAtual ){
                              jQuery('#account_cep').val('');
                              jQuery('.erro-cep').html('Por favor, informe um CEP de '+cidadeAtual+' - '+estadoAtual+'.');
                              jQuery('.btn-criar-conta').prop('disabled', true);
                          }else{
                              jQuery('.erro-cep').html('CEP OK - '+enderecoCidade+'.');
                              if( jQuery('.btn-criar-conta').hasClass( "disabled" ) === false ){
                                  jQuery('.btn-criar-conta').prop('disabled', false);
                              }
                          }
                    }else{
                          jQuery('#endereco_cep').val('');
                          jQuery('#bairro_cep').val('');
                          jQuery('#cidade_cep').val('');
                          jQuery('#estado_cep').val('');
                          jQuery('#account_cep').val('');
                          jQuery('.btn-criar-conta').prop('disabled', true);
                          jQuery('.erro-cep').html('CEP inválido ou não existe! Tente novamente.');
                    }
                },1500);

        }else{
                jQuery('.erro-cep').html('');
                jQuery('#account_cep').val('');
                jQuery('#endereco_cep').val('');
                jQuery('#bairro_cep').val('');
                jQuery('#cidade_cep').val('');
                jQuery('#estado_cep').val('');
                if( jQuery('.btn-criar-conta').hasClass( "disabled" ) === false ){
                    jQuery('.btn-criar-conta').prop('disabled', false);
                }

        }
    });

    /*
    var SPMaskBehavior = function (val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        },
        spOptions = {
            onKeyPress: function(val, e, field, options) {
                              field.mask(SPMaskBehavior.apply({}, arguments), options);
                            }
        };
    //jQuery( '#billing_phone, #reg_billing_phone, #billing_cellphone, #shop-phone, #account_telefone ' ).mask(SPMaskBehavior, spOptions);
    if( jQuery('#billing_phone').length > 0 ){
        jQuery( '#billing_phone, #billing_cellphone ' ).mask(SPMaskBehavior, spOptions);
    }
    //jQuery( '#billing_phone, #billing_cellphone ' ).mask("(99) 9999-9999?9");
    //jQuery( '#shop-phone, #account_telefone ' ).mask("(99) 9999-9999?9");
    */

    // Phone.
    var MaskBehavior = function( val ) {
          return val.replace( /\D/g, '' ).length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        };
    var maskOptions = {
          onKeyPress: function( val, e, field, options ) {
            field.mask( MaskBehavior.apply( {}, arguments ), options );
          }
        };
    jQuery( '#billing_phone, #billing_cellphone, #shop-phone, #account_telefone, #pagseguro-card-holder-phone' ).mask( MaskBehavior, maskOptions );
    jQuery( '#billing_phone, #billing_cellphone, #shop-phone, #account_telefone, #pagseguro-card-holder-phone' ).on('blur',function(e){
        var campo = jQuery(this);
        //console.log( campo.val().length );
        if( campo.val().length < 14 ){
            campo.val('');
        }
    });




    //$('#billing_phone_field').before('<p class="form-row form-row form-row-wide person-type-field"><h3 class="form-sub-titulo">Informações de contato</h3></p>');
    //$('#billing_country_field').before('<p class="form-row form-row form-row-wide person-type-field mensagem-cep"></p>');
    //jQuery('#billing_city').addClass('disabled').attr('disabled', 'disabled');
    //jQuery('#billing_city').addClass('disabled');

    jQuery('#reg_billing_first_name, #billing_first_name, #account_first_name').on("keyup keydown keypress", function() {
            var $this = jQuery(this);
            var novoText = MyObject.er_replace(/[ 0-9]/g,'', $this.val() );
            var texto = MyObject.titleCase(novoText);
            $this.val( texto );
            //console.log($this);
    });

    jQuery('#reg_billing_last_name, #billing_last_name, #account_last_name').on("keyup keydown keypress", function() {
        var $this = jQuery(this);
        var novoText = MyObject.er_replace(/[0-9]/g,'', $this.val() );
        var texto = MyObject.titleCase(novoText);
        $this.val( texto );
        //console.log($this);
    });

    jQuery('#company-name').on("keyup keydown keypress", function() {
        var $this = jQuery(this);
        //var novoText = MyObject.er_replace(/[0-9]/g,'', $this.val() );
        var texto = MyObject.titleCase($this.val());
        $this.val( texto );
        //console.log($this);
    });

    jQuery('#reg_email').on("keyup keydown keypress", function() {
        var $this = jQuery(this);
        var novoText = MyObject.er_replace(/[ ]/g,'', $this.val() );
        //var texto = titleCase(novoText);
        $this.val( novoText.toLowerCase() );
        //console.log($this);
    });

    jQuery('#seller-url').on("keyup keydown keypress", function() {
        var $this = jQuery(this);
        var text = $this.val();
        ///*
        //console.log(text);
        text = text.replace(new RegExp('[ÁÀÂÃ]','gi'), 'A');
        text = text.replace(new RegExp('[ÉÈÊ]','gi'), 'E');
        text = text.replace(new RegExp('[ÍÌÎ]','gi'), 'I');
        text = text.replace(new RegExp('[ÓÒÔÕ]','gi'), 'O');
        text = text.replace(new RegExp('[ÚÙÛ]','gi'), 'U');
        text = text.replace(new RegExp('[Ç]','gi'), 'C');
        text = text.toLowerCase();
        //*/
        var novoText = MyObject.er_replace('/[ ]/g','-', text );
        novoText = MyObject.er_replace('/[^a-z0-9]/i','-', novoText );
        //var texto = titleCase(novoText);
        $this.val( novoText );
        //console.log($this);
    });

    jQuery('#billing_postcode_field').before('<h3> Meu Endereço <button class="completar-cep button">Completar meu endereço</button> </h3> ');
    jQuery('#billing_phone_field').before('<h3> Informações de contato </h3>');

    jQuery('#billing_postcode').on('blur',function(e){
            var cep = jQuery(this).val().replace( '-', '' );
            if( cep.length == 8 ){
                //completaCep(cep);
            }else{
                //jQuery('#billing_address_1').val('');
                //jQuery('#billing_neighborhood').val('');
                jQuery('#billing_postcode').val('');
                //jQuery('#billing_city').val('');
                //jQuery('#billing_state').val('');
            }
    });

    jQuery('#argmc-submit').on("click", function(e) {
        setTimeout(function () {
            if( jQuery(document).find('.woocommerce-error').length > 0 ){
              MyObject.loadingSite(true);
            }
        },1000);
    });

    jQuery('.completar-cep').on("click", function(e) {
            e.preventDefault();
            e.stopPropagation();
            var cep = jQuery('#billing_postcode').val().replace( '-', '' );
            if( cep.length == 8 ){
                completaCep(cep);
            }else{
                jQuery.alert('Por favor, informe o CEP!');
                jQuery('#billing_address_1').val('');
                jQuery('#billing_neighborhood').val('');
                jQuery('#billing_postcode').val('');
                jQuery('#billing_city').val('');
                jQuery('#billing_state').val('');
            }
    });

    function completaCep(cep){
                        jQuery('#billing_address_1').val('');
                        jQuery('#billing_neighborhood').val('');
                        //jQuery('#billing_postcode').val('');
                        jQuery('#billing_city').val('');
                        jQuery('#billing_state').val('');
                        jQuery('.completar-cep').text('Aguarde verificando CEP ... ');
                        jQuery('.completar-cep').prop('disabled', true);
                        MyObject.verificaCep(cep);
                        setTimeout(function () {
                              jQuery('.completar-cep').text('Completar meu endereço');
                              jQuery('.completar-cep').prop('disabled', false);
                              if( enderecoCidade.length == 0 ){
                                  jQuery.alert('CEP não encontrado, tente novamente!');
                                  return;
                              }
                              if( enderecoCidade !== cidadeAtual ){
                                  jQuery.alert('Por favor, informe um CEP de '+cidadeAtual+' - '+estadoAtual+'.');
                                  return;
                              }
                              jQuery('#billing_address_1').val(endereco);
                              jQuery('#billing_neighborhood').val(enderecoBairro);
                              jQuery('#billing_city').val(enderecoCidade);
                              jQuery('#billing_state').val(enderecoEstado);
                              //jQuery('#billing_country').val(enderecoEstado);
                              return;
                        },2000);
                return;
    };





});
}(jQuery));
