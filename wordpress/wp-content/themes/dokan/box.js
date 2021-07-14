var endereco = '';
endereco = endereco.trim();
var enderecoCEP = '';
var enderecoBairro = '';
var enderecoCidade = '';
var enderecoEstado = '';
var enderecoDistancia = '';
if(endereco) enderecoDistancia = endereco + ', ' + enderecoCidade;

MyObject2 = {
    dump: function(arr, level) {
        var dumped_text = "";
        if (!level) level = 0;

        var level_padding = "";
        for (var j = 0; j < level + 1; j++) level_padding += "    ";
        if (typeof (arr) == "object") {
            for (var item in arr) {
                var value = arr[item];
                if (typeof (value) == "object") {
                    dumped_text += level_padding + "'" + item + "' \n";
                    dumped_text += MyObject.dump(value, level + 1);
                } else {
                    dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
                }
            }
        } else {
            dumped_text = "===>" + arr + "<===(" + typeof (arr) + ")";
        }
        return dumped_text;
    },
    loadingSite: function(close=false){

      //console.log(close);
      //return;
      if(close){
        jQuery(".loading-site").fadeOut(1000);
        jQuery(".radar-site").fadeOut(1000);
      }else{
        jQuery(".loading-site").hide();
        jQuery(".radar-site").hide();

        jQuery(".loading-site").css("height",jQuery( document ).height());
        jQuery(".radar-site").css("height",jQuery( document ).height());

        jQuery('html,body').animate({scrollTop:0}, 500,'swing');
        jQuery(window).scrollTop(0);
        document.body.scrollTop = document.documentElement.scrollTop = 0;
        jQuery(".loading-site").delay(0).fadeTo(3500,0.90,'linear');
      }

      /*
      if( jQuery(".loading-site").css('display') == 'none' ){
        jQuery(".loading-site").delay(0).fadeTo(2000,0.95);
      }else{
        jQuery(".loading-site").fadeOut(800);
      }

      if( jQuery(".loading-site").css('display') == 'block' ){
        jQuery(".loading-site").fadeOut(800);
      }
      */
      //jQuery('html,body').animate({scrollTop:0}, 500,'swing');
      //jQuery("body").animate({scrollTop:0}, '500', 'swing');
      //jQuery(".loading-site").animate({opacity: 1}, {queue: false, duration: 'slow'});
      //jQuery(".loading-site").animate({opacity: 1}, 1000);
      //jQuery(".loading-site").delay(500).fadeIn(1000).animate({opacity: 0.5}, 1000);
    },
    radarSite: function(close=false){

      if(close){
        jQuery(".loading-site").fadeOut(1000);
        jQuery(".radar-site").fadeOut(1000);
      }else{

          jQuery(".loading-site").hide();
          jQuery(".radar-site").hide();

          jQuery(".loading-site").css("height",jQuery( document ).height());
          jQuery(".radar-site").css("height",jQuery( document ).height());

          jQuery('html,body').animate({scrollTop:0}, 500,'swing');
          jQuery(window).scrollTop(0);
          document.body.scrollTop = document.documentElement.scrollTop = 0;
          jQuery(".radar-site").delay(0).fadeTo(3500,1,'linear');

        }

    },
    verificaUrlCep:function(url) {
          try {
              /*
              var scriptElem = document.createElement('script');
              scriptElem.type = 'text/javascript';
              scriptElem.onerror = function(){ return 'false'; };
              scriptElem.onload = function(){ return 'ok'; };
              scriptElem.src = url;
              document.getElementsByTagName("body")[0].appendChild(scriptElem);
              */
              var getUrl = jQuery.ajax({
                                    dataType: 'jsonp',
                                    crossDomain: true,
                                    contentType: 'application/json',
                                    url:url,
                                  });
              getUrl.done(function( msg ) {
                //console.log('sucesso');
                return true;
              });
              getUrl.fail(function( jqXHR, textStatus ) {
                //alert( "Request failed: " + textStatus );
                //console.log('erro');
                return false;
              });
          } catch(err) {
              //error(err);
              return false;
          }
    },
    verificaCep:function(cep){
          var busca = jQuery.ajax({
                url: '//viacep.com.br/ws/'+cep+'/json/',
                //url: '//correiosapi.apphb.com/cep/' + cep,
                dataType: 'jsonp',
                crossDomain: true,
                contentType: 'application/json',
                success:function(data, status, retorno){

                  //enderecoDistancia = data.tipoDeLogradouro + ' ' + data.logradouro + ', ' + data.cidade;

                  //endereco = data.tipoDeLogradouro + ' ' + data.logradouro;
                  endereco = data.logradouro;
                  endereco = endereco.trim();
                  enderecoBairro = data.bairro;
                  enderecoCidade = data.localidade;
                  enderecoEstado = data.uf;
                  if(endereco) enderecoDistancia = endereco + ', ' + enderecoCidade;

                  if( enderecoCidade !== cidadeAtual || enderecoEstado !== estadoAtual ){
                      MyObject2.salvaEnderecoDistancia();
                  }else{
                      MyObject2.salvaEnderecoDistancia(data);
                  }
                  return;
                }
          });
          enderecoCidade = '';
          MyObject2.salvaEnderecoDistancia();
          return;
    },
    salvaEnderecoDistancia:function(data){
              jQuery.ajax({
                        type: "POST",
                        url: rp_ajax_url+"?action=salva_endereco_distancia",
                        data: data,
                        success: function (retorno) {
                              console.log("SUCESSO SALVA ENDERECO: "+retorno);
                        },
                        error:function(request, status, error){
                              console.log("ERRO STATUS: "+status);
                              console.log("ERRO: "+error);
                        },
              });
              return;
    },
    CalculaDistancia:function(origem, destino) {
        var service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix({
    		    origins: [origem],
    		    destinations: [destino],
    		    travelMode: google.maps.TravelMode.DRIVING
    		}, MyObject.callbackDistancia );
    },
    callbackDistancia:function(response, status) {
        //console.log(status);
        //console.log(response);
        if (status == google.maps.DistanceMatrixStatus.OK) {
            //alert("Distância:" + response.rows[0].elements[0].distance.text);
            //alert("Duração:" + response.rows[0].elements[0].duration.text);
            var distancia = response.rows[0].elements[0].distance.text;
            var duracao = response.rows[0].elements[0].duration.text;
            console.log(distancia);
            console.log(duracao);
        }
    },
};
(function($){
$(document).ready(function(){



  function moeda(z){
    v = z.value;
    v = v.replace(/\D/g,"");  //permite digitar apenas n?meros
    v = v.replace(/[0-9]{12}/,"invalido");   //limita pra m?ximo 999.999.999,99
    v = v.replace(/(\d{1})(\d{8})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 8 digitos
    v = v.replace(/(\d{1})(\d{5})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 5 digitos
    v = v.replace(/(\d{1})(\d{1,2})$/,"$1,$2");        //coloca virgula antes dos ?ltimos 2 digitos
    z.value =  v;
  }

  function limit(element, max) {
      var max_chars = max;
      if(element.value.length > max_chars) {
          element.value = element.value.substr(0, max_chars);
      }
  }
  function dinheiro(valor, casas, separdor_decimal, separador_milhar){

    var valor_total = parseInt(valor * (Math.pow(10,casas)));
    var inteiros =  parseInt(parseInt(valor * (Math.pow(10,casas))) / parseFloat(Math.pow(10,casas)));
    var centavos = parseInt(parseInt(valor * (Math.pow(10,casas))) % parseFloat(Math.pow(10,casas)));


    if(centavos%10 == 0 && centavos+"".length<2 ){
      centavos = centavos+"0";
    }else if(centavos<10){
      centavos = "0"+centavos;
    }

    var milhares = parseInt(inteiros/1000);
    inteiros = inteiros % 1000;

    var retorno = "";

    if(milhares>0){
      retorno = milhares+""+separador_milhar+""+retorno
      if(inteiros == 0){
        inteiros = "000";
      } else if(inteiros < 10){
        inteiros = "00"+inteiros;
      } else if(inteiros < 100){
        inteiros = "0"+inteiros;
      }
    }
    retorno += inteiros+""+separdor_decimal+""+centavos;
    return retorno;

  }
  function getMoney( str ){
    return parseInt( str.replace(/[\D]+/g,'') );
  }
  function formatReal( int ){
    var tmp = int+'';
    tmp = tmp.replace(/([0-9]{2})$/g, ",$1");
    if( tmp.length > 6 ){
      tmp = tmp.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
    }
    return tmp;
  }
  jQuery(document).on('keyup keypress', '.valor', function (e) {
    v = jQuery(this).val();
    v = v.replace(/\D/g,"");  //permite digitar apenas n?meros
    v = v.replace(/[0-9]{12}/,"invalido");   //limita pra m?ximo 999.999.999,99
    //v = v.replace(/(\d{1})(\d{8})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 8 digitos
    //v = v.replace(/(\d{1})(\d{5})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 5 digitos
    v = v.replace(/(\d{1})(\d{1,2})$/,"$1.$2");        //coloca virgula antes dos ?ltimos 2 digitos
    jQuery(this).val(v);
  });
  jQuery(document).on('keyup keypress', '.peso', function (e) {
    v = jQuery(this).val();
    v = v.replace(/\D/g,"");  //permite digitar apenas n?meros
    v = v.replace(/[0-9]{12}/,"invalido");   //limita pra m?ximo 999.999.999,99
    //v = v.replace(/(\d{1})(\d{8})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 8 digitos
    //v = v.replace(/(\d{1})(\d{5})$/,"$1.$2");  //coloca ponto antes dos ?ltimos 5 digitos
    v = v.replace(/(\d{1})(\d{1,3})$/,"$1.$2");        //coloca virgula antes dos ?ltimos 2 digitos
    jQuery(this).val(v);
  });








  jQuery('.pagination a,.navbar-top-area .navbar-nav li:last-child, .nav.navbar-nav.navbar-right .dropdown-menu a, .list-unstyled.list-count a, .dokan-dashboard-content ul.subsubsub li, .click-loading, .dokan-dashboard-menu li > a, .coupons .dokan-btn, .dokan-add-product-link .dokan-btn ').on('click', function(e){

      jQuery(".loading-site").hide();

      jQuery(".loading-site").css("height",jQuery( document ).height());

      jQuery('html,body').animate({scrollTop:0}, 500,'swing');
      jQuery(window).scrollTop(0);
      document.body.scrollTop = document.documentElement.scrollTop = 0;
      jQuery(".loading-site").delay(0).fadeTo(2000,0.80,'linear');


  });

    //jQuery( '#billing_birthdate' ).mask( '99/99/9999' );
    //$( '#billing_postcode, #calc_shipping_postcode, #reg_billing_postcode, #account_cep, #shipping_postcode' ).mask( '99999-999' );
    //$( '#billing_cpf' ).mask( '999.999.999-99' );

    jQuery( '.address-cep' ).mask( '99999-999' );

    jQuery('.buscar-cep').on('click', function(e){
          var cep = jQuery( '.address-cep' ).val().replace( '-', '' );
          if( cep.length == 8 ){
                  jQuery('.buscar-cep').text('Aguarde verificando CEP ... ');
                  MyObject2.verificaCep( cep );
                  setTimeout(function () {
                      if( enderecoCidade.length > 0 ){
                            //console.log( enderecoCidade );
                            if( enderecoCidade !== cidadeAtual ){
                                //jQuery('#account_cep').val('');
                                jQuery('.buscar-cep').text('Por favor, informe um CEP de '+cidadeAtual+' - '+estadoAtual+'.');
                            }else{
                                jQuery('.buscar-cep').text('CEP OK - Buscar novamente');
                                jQuery('.address-city').val(enderecoCidade);
                                jQuery('.address-bairro').val(enderecoBairro);
                                jQuery('.address-endereco').val(endereco);
                            }
                      }else{
                            //jQuery('#account_cep').val('');
                            jQuery('.buscar-cep').text('CEP inválido! Tente novamente.');
                      }
                  },2500);
          }else{

                  jQuery( '.address-cep' ).val('');
                  jQuery('.buscar-cep').text('Informe o CEP completo!');

          }
    });

    // Phone.
    var MaskBehavior = function( val ) {
          return val.replace( /\D/g, '' ).length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        };
    var maskOptions = {
          onKeyPress: function( val, e, field, options ) {
            field.mask( MaskBehavior.apply( {}, arguments ), options );
          }
        };
    jQuery( '#setting_phone' ).mask( MaskBehavior, maskOptions );
    jQuery( '#setting_phone' ).on('blur',function(e){
        var campo = jQuery(this);
        //console.log( campo.val().length );
        if( campo.val().length < 14 ){
            campo.val('');
        }
    });


});
$(window).load(function(){

  jQuery(".loading-site").hide();
  jQuery(".loading-site").css("height",jQuery( document ).height());

  /*
  var Switch = require('ios7-switch')
    , checkbox = document.querySelector('input')
    , mySwitch = new Switch(checkbox);

  // When `mySwitch` is clicked toggle state
  mySwitch.el.addEventListener('click', function(e){
    e.preventDefault();
    mySwitch.toggle();
  }, false)
  */

});
}(jQuery));
