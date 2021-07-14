(function($){
$(document).ready(function(){
});
$(window).load(function(){
    //tc-totals-form
    var globalRetorno = '';
    var globalCep = '';
    var globalRua = '';
    var globalErroCep = true;
    var element = '';

    jQuery('.rp_calc_shipping').on('click', function(e) {

        e.preventDefault();
        e.stopPropagation();

        globalRetorno = '';
        globalCep = '';
        globalRua = '';
        globalErroCep = true;

        element = jQuery(this);
        var datastring = jQuery(this).closest(".woocommerce-shipping-calculator").serialize();

        /*
        if( jQuery("input.variation_id").length > 0 ){
            datastring=datastring+"&variation_id="+jQuery("input.variation_id").val();
        }
        if( jQuery("input[name=quantity]").length > 0 ){
            datastring=datastring+"&current_qty="+jQuery("input[name=quantity]").val();
        }
        */
        //console.log(datastring);
        //console.log(rp_ajax_url);

        //jQuery(".woocommerce-shipping-calculator .loaderimage").show();
        jQuery(".rp_calc_shipping").prop('disabled', true);

        globalRetorno = 'Por favor, informe o CEP para verificação!';
        globalCep = jQuery('#calc_shipping_postcode').val().replace( '.', '' ).replace( '-', '' ).replace( '_', '' );
        //console.log(globalCep.length);
        //return;
        if( globalCep == "" || globalCep.length < 8 || globalCep == '00000000' ){
            jQuery('#calc_shipping_postcode').val('');
            globalRetorno = 'Por favor, informe o CEP correto para verificação!';
            //jQuery(".woocommerce-shipping-calculator .loaderimage").hide();
            jQuery(".rp_calc_shipping").prop('disabled', false);
            jQuery(".shippingmethod_container").html(globalRetorno);
            //jQuery(".shippingmethod_container").html('<div class="info">'+globalRetorno+'</div>');
            return;
        /*
        }else if(jQuery('body').hasClass('logged-in')===false){
            jQuery('#calc_shipping_postcode').val('');
            globalRetorno = 'Por favor, faça seu login para continuar!';
            //jQuery(".woocommerce-shipping-calculator .loaderimage").hide();
            jQuery(".rp_calc_shipping").prop('disabled', false);
            jQuery(".shippingmethod_container").html(globalRetorno);
            //jQuery(".shippingmethod_container").html('<div class="info">'+globalRetorno+'</div>');
            return;
          */
        }else if( globalCep.length == 8 ){

                  globalRetorno = 'buscando cep...';
                  jQuery(".shippingmethod_container").html(globalRetorno);

                  //globalErroCep = false;
                  //jQuery(".woocommerce-shipping-calculator .loaderimage").show();

                  $.ajax({
                						//type: 'GET',
                            //https://viacep.com.br/ws/36035000/json/
                            url: '//viacep.com.br/ws/'+globalCep+'/json/',
                						//url: '//correiosapi.apphb.com/cep/' + globalCep,
                						dataType: 'jsonp',
                						crossDomain: true,
                						contentType: 'application/json',
                            //cache: false,
                						/*
                            success: function(address) {
                                  window.retornoCep = address;
                                  console.log(address);
                            },
                            error: function (xhr) {
                                globalRetorno = "CEP não encontrado!";
                                console.log("CEP não encontrado!");
                            }
                            */
                            /*
                            statusCode: {
                              200: function(data) { console.log("CEP encontrado!!"); } // Ok
                              ,400: function(msg) { console.log("CEP não encontrado 400 !!");  } // Bad Request
                              ,404: function(msg) { console.log("CEP não encontrado 404 !!"); } // Not Found
                            },
                            */
                            beforeSend: function( xhr ) {

                                //globalErroCep = false;
                                //jQuery(".woocommerce-shipping-calculator .loaderimage").show();
                                globalRetorno = 'buscando...';
                                jQuery(".shippingmethod_container").html(globalRetorno);
                                return;

                            },
                            success:function(data, status, retorno){

                                        globalErroCep = false;
                                        //console.log("DATA: "+data);
                                        //console.log("SUCESSO STATUS: "+status);
                                        //console.log("SUCESSO RETORNO: "+retorno);

                                        //enderecoCidade = data.cidade;
                                        //enderecoEstado = data.estado;

                                        enderecoCidade = data.localidade;
                                        enderecoEstado = data.uf;

                                        if( enderecoCidade !== cidadeAtual || enderecoEstado !== estadoAtual ){
                                            endereco = '';
                                            enderecoBairro = '';
                                            enderecoDistancia = '';
                                            enderecoLavanderias = [];
                                            globalRetorno = 'Por favor, informe um CEP de '+cidadeAtual+' - '+estadoAtual+'.';
                                            //jQuery(".shippingmethod_container").html(globalRetorno);
                                            jQuery(".shippingmethod_container").html('<div class="info cidade">'+globalRetorno+'</div>');
                                            jQuery(".rp_calc_shipping").prop('disabled', false);
                                            MyObject.salvaEnderecoDistancia();
                                            return;
                                        }

                                        MyObject.salvaEnderecoDistancia(data);
                                        jQuery(".archive-products, .shop-loop-before, .shop-loop-after").fadeOut("slow");

                                        enderecoCEP = globalCep;
                                        //endereco = data.tipoDeLogradouro + ' ' + data.logradouro;
                                        endereco = data.logradouro;
                                        endereco = endereco.trim();
                                        enderecoBairro = data.bairro;
                                        if(endereco) enderecoDistancia = endereco+', '+enderecoBairro+', '+enderecoCidade+' - '+enderecoEstado+', '+enderecoCEP;

                                        globalRetorno = 'CEP encontrado, buscando lavanderias...';
                                        MyObject.notice_success("Aguarde, buscando Lavanderias ...");
                                        jQuery(".shippingmethod_container").html(globalRetorno);
                                        //jQuery(".woocommerce-shipping-calculator .loaderimage").hide();

                                        //window.retorno = retorno;
                                        //window.dados = data;
                                        //enderecoDistancia = data.tipoDeLogradouro + ' ' + data.logradouro + ',' + data.cidade;
                                        globalRetorno = '<b>Endereço:</b> '+endereco+'<br/>';
                                        globalRetorno = globalRetorno + '<b>Bairro:</b> '+enderecoBairro+' / '+enderecoCidade;

                                        //&calc_shipping_city=Juiz de Fora
                                        datastring = datastring+"&calc_shipping_country=BR&calc_shipping_state="+estadoAtual+"&calc_shipping_city="+cidadeAtual;
                                        //console.log(datastring);
                                        /*
                                        [action] => update_shipping_method
                                        [calc_shipping_postcode] => 36035-000
                                        [_wpnonce] => 30af279eb9
                                        [_wp_http_referer] => /lava/juiz-de-fora/
                                        [calc_shipping_country] => BR
                                        [calc_shipping_state] => MG
                                        [calc_shipping_city] => Juiz de Fora
                                        */
                                        MyObject.CalculaDistanciaCep();
                                        //MyObject.CalculaDistancia( enderecoDistancia , 'Avenida dos Andradas, Juiz de Fora');

                                        //jQuery('.post-332').fadeOut('slow');

                                        $.ajax({
                                                  type: "POST",
                                                  url: rp_ajax_url+"?action=update_shipping_method_custom",
                                                  data: datastring,
                                                  success: function (data) {
                                                        //console.log("SUCESSO UPDATE STATUS: "+data);
                                                        /*
                                                        if ( !data ) {
                                                            globalRetorno = globalRetorno+'<br/><span class="error-cep">Ainda não estamos atendendo em sua região.';
                                                            globalRetorno = globalRetorno+'<br/>Em breve novas lavanderias!</span>';
                                                        }else{
                                                            globalRetorno = globalRetorno+'<br/><span class="success-cep">Encontramos lavanderias para sua região!</span>';
                                                        }
                                                        globalRetorno = globalRetorno + data;
                                                        */
                                                        jQuery(".rp_calc_shipping").prop('disabled', false);
                                                        //jQuery(".shippingmethod_container").html(globalRetorno + data);
                                                        jQuery(".shippingmethod_container").html('<div class="info">'+globalRetorno + data+'</div>');
                                                        //MyObject.CalculaDistanciaCep();
                                                        jQuery("html,body").css("height",jQuery('.page-wrapper').height());

                                                  },
                                                  error:function(request, status, error){
                                                        //console.log(request);
                                                        console.log("ERRO STATUS: "+status);
                                                        console.log("ERRO: "+error);

                                                        jQuery(".rp_calc_shipping").prop('disabled', false);
                                                        globalRetorno = 'Algo deu errado! Tente novamente.';
                                                        jQuery(".shippingmethod_container").html('<div class="info error">'+globalRetorno+'</div>');

                                                  },
                                        });


                                        //jQuery(".rp_calc_shipping").prop('disabled', false);
                                        //jQuery(".shippingmethod_container").html(globalRetorno);
                                        return;

                                        //console.log(retorno);
                            },
                            error:function(request, status, error){
                                  console.log(request);
                                  console.log("ERRO STATUS: "+status);
                                  console.log("ERRO: "+error);
                            },
                            fail:function(jqXHR, textStatus, errorThrown){
                                  if( jqXHR.status == 500 || jqXHR.status == 0 ){
                                      // internal server error or internet connection broke
                                      console.log("internal server")
                                  }
                                  if( jqXHR.status == 404 || errorThrown == 'Not Found') {
                                    console.log('ERRO 1 - 404');
                                  }
                                  if( jqXHR.status == 400 ) {
                                    console.log('ERRO 1 - 400');
                                  }
                                  console.log('FAIL');
                            },
          					});
                    /*
                    .done(function (response) {

                            console.log( "DONE CEP" );

                    }).fail(function(jqXHR, textStatus, errorThrown){
                            if(jqXHR.status == 404 || errorThrown == 'Not Found') {
                              console.log('ERRO 2 - 404');
                            }
                            if(jqXHR.status == 400 ) {
                              console.log('ERRO 2 - 400');
                            }
                            console.log( "FAIL CEP" + textStatus );

                            //self.setTitle("Atenção");
                            //self.setContent('Não foi possivel carregar este item.<br/> Por favor, tente mais tarde.');

                    }).always(function() {

                            console.log( "FIM CEP" );

                            //jQuery('html,body').animate({scrollTop:0}, 0,'swing');
                            //jQuery(window).scrollTop(0);
                            //document.body.scrollTop = document.documentElement.scrollTop = 0;

                    });
                    */

          //jQuery(".shippingmethod_container").html(globalRetorno);
          //if(!globalErroCep && globalRua){

          //}else{
            //globalRetorno = 'CEP n&atilde;o encontrado!';
          //}
          //globalRetorno = "CEP não encontrado!!";
          //jQuery(".woocommerce-shipping-calculator .loaderimage").hide();
          //jQuery(".shippingmethod_container").html(globalRetorno);
          //jQuery(".rp_calc_shipping").prop('disabled', false);

          setTimeout(function () {
            //console.log('entrou timeout');
            if( globalErroCep == true ){

                endereco = '';
                enderecoBairro = '';
                enderecoDistancia = '';
                enderecoCidade = '';
                enderecoEstado = '';
                enderecoLavanderias = [];

                globalRetorno = 'CEP inválido ou não existe! Tente novamente.';
                //jQuery(".woocommerce-shipping-calculator .loaderimage").hide();
                jQuery(".rp_calc_shipping").prop('disabled', false);
                //jQuery(".shippingmethod_container").html(globalRetorno);
                //jQuery(".shippingmethod_container").html('<div class="info">'+globalRetorno+'</div>');
                jQuery(".shippingmethod_container").html('<div class="info error">'+globalRetorno+'</div>');
                MyObject.salvaEnderecoDistancia();
                jQuery("html,body").css("height",jQuery('.page-wrapper').height());
            }
          }, 3000);

        }
        return;

    });
});
}(jQuery));
