
/*
$.fn.isAfter = function(sel){
    sel = "." + sel.attr("class").replace(/ /g, ".");

    return this.prevAll(sel).length !== 0;
}
$.fn.isBefore= function(sel){
    sel = "." + sel.attr("class").replace(/ /g, ".");

    return this.nextAll(sel).length !== 0;
}

jQuery.fn.isAfter = function(sel){
    return jQuery(this).index() > jQuery(sel).index();
};
jQuery.fn.isBefore= function(sel){
    return jQuery(this).index() < jQuery(sel).index();
};
*/

/*
//console.log( jQuery(this).isAfter( '.tc-remaining' ) );
//console.log( jQuery(this).isBefore( jQuery(html) ) );
//window.teste = $( this );
//window.teste2 = $( html );
if( $this.isBefore( '.tc-chars' ) === true ) return;




jQuery(".single_add_to_cart_button.button").on('click', function(e) {
    //jc_item.close();
    //return false;
    //e.preventDefault();
    //e.stopPropagation();
    console.log('entrou');
    //MyObject.editandoPopup(jQuery(this));

    var form = jQuery("form.cart").closest('form');
    window.form = form;
    if(form[0][0].value == "Selecione_0" ){
      console.log("error");
      e.preventDefault();
      e.stopPropagation();
      //return false;
      if( form.find( '.verifica-obrigatorio' ).length == 0 ){
        form.prepend('<div class="verifica-obrigatorio">* Por favor informe o seu Bairro.</div>');
      }
    }else{
      console.log(form[0][0].value);
    }

});


jQuery( "form.cart" ).submit(function( e ) {

  e.preventDefault();
  e.stopPropagation();

  if( jQuery(this).hasClass('product_type_variable') ) return true;

  var form = jQuery("form.cart").closest('form');
  window.form = form;
  console.log("ENTROU");
  if(form[0][0].value == "Selecione_0" ){
    console.log("error");
    return false;
  }
  return false;
});




jQuery(".single_add_to_cart_button.button").on('click', function(e) {
    //jc_item.close();
    //return false;
    //e.preventDefault();
    //e.stopPropagation();
    //console.log('entrou');
    //MyObject.editandoPopup(jQuery(this));

    if( jQuery('body').hasClass('logged-in')==false ){
      jQuery( ".btnLoginPrincipal" ).trigger( "click" );
      return;
    };

    var form = jQuery("form.cart").closest('form');
    window.form = form;
    if( form[0][0] && form[0][0].value == "Selecione_0" ){
      console.log("error");
      e.preventDefault();
      e.stopPropagation();
      //return false;
      //if( form.find( '.verifica-obrigatorio' ).length == 0 ){
        //form.prepend('<div class="verifica-obrigatorio">* Por favor informe o seu Bairro.</div>');
      //}
      //cpf-type-select bairro_select onde-coletar-div
      //form.find('.bairro_select').before('<div class="verifica-bairro verifica-obrigatorio">* Por favor informe o seu Bairro.</div>');
      jQuery.alert('Por favor, informe o seu Bairro.');
    }else{
      jQuery(".verifica-bairro").remove();
      //console.log(form[0][0].value);
    }

});


jQuery(".single_add_to_cart_button.button").on('click', function(e) {
    if( jQuery('body').hasClass('logged-in')==false ){
      jQuery( ".btnLoginPrincipal" ).trigger( "click" );
      return;
    };
    var form = jQuery("form.cart").closest('form');
    //window.form = form;
    if( form[0][0] && form[0][0].value == "Selecione_0" ){
      console.log("error");
      e.preventDefault();
      e.stopPropagation();
      jQuery.alert('Por favor, informe o seu Bairro.');
    }else{
      jQuery(".verifica-bairro").remove();
    }

});



*/

jQuery(".single_add_to_cart_button.button, .click-ver-preco").on('click', function(e) {
    if( jQuery('body').hasClass('logged-in')==false ){
      e.preventDefault();
      e.stopPropagation();
      MyObject.notice_info("Para continuar faça seu login.");
      jQuery( ".btnLoginPrincipal" ).trigger( "click" );
      return false;
    };
    var form = jQuery("form.cart").closest('form');
    //window.form = form;
    if( form[0][0] && form[0][0].value == "Selecione_0" ){
      //console.log("error");
      e.preventDefault();
      e.stopPropagation();
      jQuery.alert('Por favor, informe o seu Bairro.');
      MyObject.notice_error("Por favor, informe o seu Bairro.");
    }else{
      //jQuery(".verifica-bairro").remove();
    }

});


MyObject = {
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
    loadingFull: function(tipo, tempo = 3500, close=false){
      jQuery(".loading-full").hide();
      if(close){
        jQuery("."+tipo+"-site").fadeOut(1000);
      }else{
        jQuery("."+tipo+"-site").css("height",jQuery( document ).height());
        jQuery('html,body').animate({scrollTop:0}, 500,'swing');
        jQuery(window).scrollTop(0);
        document.body.scrollTop = document.documentElement.scrollTop = 0;
        jQuery("."+tipo+"-site").delay(0).fadeTo(tempo,0.90,'linear');
      }
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

        jQuery('html,body').animate({scrollTop:0}, 2000,'swing');
        //jQuery(window).animate({scrollTop:0}, 2000,'swing');
        //document.body.scrollTop = document.documentElement.scrollTop = 0;
        jQuery(".loading-site").delay(50).fadeTo(3500,0.9,'linear');
      }

      var $html = jQuery('html');
      if ($html.hasClass('sidebar-opened')) {
          setTimeout(function () { jQuery('.sidebar-toggle').click(); },700);
      }
      if ($html.hasClass('panel-opened')) {
          setTimeout(function () { jQuery('.panel-overlay').click(); },700);
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

      //console.log( jQuery(".radar-site").length )
      //jQuery(".radar-site").fadeIn();
      //alert( jQuery(".radar-site").length );
      //if(!e) {
        //var e = e || window.event;
      //}
      //e.preventDefault();
      //var button = jQuery(this);
      //console.log( button );
      //jQuery(".radar-site").show('fast');
      //return;

      if(close){
        jQuery(".loading-site").fadeOut(1000);
        jQuery(".radar-site").fadeOut(1000);
      }else{

          jQuery(".loading-site").hide();
          jQuery(".radar-site").hide();

          jQuery(".loading-site").css("height",jQuery( document ).height());
          jQuery(".radar-site").css("height",jQuery( document ).height());

          var url = window.location.href;
          //if( url.indexOf('cidades') === -1 )
          jQuery('html,body').animate({scrollTop:0},2000,'swing');
          //jQuery(window).scrollTop(0);
          //document.body.scrollTop = document.documentElement.scrollTop = 0;
          //if( MyObject.isMobile() == true ){
            //jQuery(".radar-site").slideToggle('fast');
          //}else{
            jQuery(".radar-site").delay(50).fadeTo(3000,1,'swing');
          //}

        }

        var $html = jQuery('html');
        if ($html.hasClass('sidebar-opened')) {
            setTimeout(function () { jQuery('.sidebar-toggle').click(); },700);
        }
        if ($html.hasClass('panel-opened')) {
            setTimeout(function () { jQuery('.panel-overlay').click(); },700);
        }



    },
    notice:function(titulo,desc,cor,tempo,som) {
      //new jBox('Notice', {
      //  content: 'Wait 5 Seconds',
      //  color: 'black',
      //  autoClose: 5000
      //});
      new jBox('Notice', {
    		animation: {
    			open: 'zoomIn',
    			close: 'zoomOut' //slide:right
    		},
        fade: false,
    		theme: 'NoticeBorder',
    		audio: '/wp-content/themes/box/plugins/jbox/audio/'+som,
    		volume: 80,
    		position: {
    			x: 40,
    			y: 30
    		},
        fixed: true,
    		title: titulo,
        delayOpen:100,
    		content: desc,
    		autoClose: tempo,
    		color: cor,
    		zIndex: 99999999999,
    		onInit: function() {
    			//this.options.color = colorsN2[currentColorN2];
    			//currentColorN2++;
    			//(currentColorN2 >= colorsN2.length) && (currentColorN2 = 0)
    		}
    	});
    },
    notice_error:function(desc){
      //MyObject.notice('ERRO!','mensagem de erro escreve aqui!!!','vermelho',10000,'bling1');
      MyObject.notice('Atenção!',desc,'vermelho',10000,'bling1');
    },
    notice_success:function(desc){
      MyObject.notice('Sucesso!',desc,'verde',9000,'blop');
    },
    notice_info:function(desc){
      MyObject.notice('Informação!',desc,'azul',9000,'boop2');
    },
    notice_delete:function(desc){
      MyObject.notice('Excluído!',desc,'preto',10000,'beep2');
    },
    notice_warn:function(desc){
      MyObject.notice('Alerta!',desc,'laranja',10000,'boop3');
    },
    notice_fatal:function(desc){
      MyObject.notice('Erro!',desc,'vermelho',10000,'blop');
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
                //url: '//correiosapi.apphb.com/cep/' + cep,
                url: '//viacep.com.br/ws/'+cep+'/json/',
                dataType: 'jsonp',
                crossDomain: true,
                contentType: 'application/json',
                success:function(data, status, retorno){
                  //enderecoDistancia = data.tipoDeLogradouro + ' ' + data.logradouro + ', ' + data.cidade;
                  //endereco = data.tipoDeLogradouro + ' ' + data.logradouro;
                  endereco = data.logradouro;
                  endereco = endereco.trim();
                  enderecoCEP = data.cep;
                  enderecoBairro = data.bairro;
                  enderecoCidade = data.localidade;
                  enderecoEstado = data.uf;
                  console.log('VERIFICA CEP: '+endereco+' '+enderecoCEP);
                  if(endereco) enderecoDistancia = endereco+', '+enderecoBairro+', '+enderecoCidade+' - '+enderecoEstado+', '+enderecoCEP;
                  /*
                  if( enderecoCidade !== cidadeAtual || enderecoEstado !== estadoAtual ){
                      MyObject.salvaEnderecoDistancia();
                  }else{
                      MyObject.salvaEnderecoDistancia(data);
                  }
                  */
                  //if(enderecoCidade == cidadeAtual && enderecoEstado == estadoAtual)
                  MyObject.salvaEnderecoDistancia(data);
                  return;
                }
          });
          endereco = '';
          enderecoCEP = '';
          enderecoBairro = '';
          enderecoCidade = '';
          enderecoEstado = '';
          enderecoDistancia = '';
          //MyObject.salvaEnderecoDistancia();
          return;
    },
    getItensCategoria:function(){


          //return;

          if( jQuery('.click-get-itens').length > 0){

                  //console.log('getItensCategoria');
                  //jQuery('.click-get-itens').hide();

                  var item = jQuery('.vc_active.click-get-itens').find('.itens-categoria');
                  MyObject.getItensCategoriaAjax(item);

                  jQuery('.click-get-itens a').on('click', function(e) {
                      var item = jQuery(this).parent().parent().parent().find('.itens-categoria');
                      MyObject.getItensCategoriaAjax(item);
                  });

                  jQuery('.click-get-itens').delay(1000).fadeTo(1500,1,'swing');

                  //jQuery('.click-get-itens').each(function() {
                        //var item_mostra = jQuery(this);
                        //jQuery(this).fadeTo(3000,1,'swing');
                        //setTimeout(function (item_mostra) { jQuery(item_mostra).fadeTo(1000,1,'swing'); },1000); // TIMEOUT
                  //});

          }
          return;

          //jQuery('.itens-categoria').each(function() {

              //var item = jQuery(this);

              //jQuery( document.body ).trigger( 'post-load' );
              //$.ajaxSetup({cache:false});
              //$("#TARGET").load("http://<?php echo $_SERVER[HTTP_HOST]; ?>/ajax/",{id:post_id});
              //var post_id = jQuery(this).data('id');
              ///*


          //});

    },
    getItensCategoriaAjax:function(item){

              //console.log('getItensCategoriaAjax');

              if( jQuery(item).hasClass('loaded') ) return;
              jQuery(item).html('<center>Aguarde, buscando itens...<div class="lavanderias-loading"></div></center>');
              //return;
              //console.log('load');
              var cat_id = jQuery(item).data('cat-id');
              var cat_slug = jQuery(item).data('cat-slug');
              var cat_posts = jQuery(item).data('cat-posts');
              var data = {
                'cat_id': cat_id,
                'cat_slug': cat_slug,
                'cat_posts': cat_posts
              };
              //return;
              jQuery.ajax({
                        type: "POST",
                        url: rp_ajax_url+'?action=get_lista_itens_ajax',
                        data: data,
                        success: function (retorno) {
                              //console.log("SUCESSO CAT-ID: "+data.cat_id);
                              jQuery(item).hide();
                              jQuery(item).html(retorno);
                              jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');
                              jQuery('.produto-table .opcoes, .tm-cart-edit-options').on('click', function(e) {
                                  e.preventDefault();
                                  e.stopPropagation();
                                  MyObject.editandoPopup(jQuery(this));
                              });
                              MyObject.qtywrap();
                              jQuery(item).addClass('loaded');
                              jQuery(item).fadeIn('slow');
                              jQuery(".single_add_to_cart_button.button, .click-ver-preco").on('click', function(e) {
                                  if( jQuery('body').hasClass('logged-in')==false ){
                                    e.preventDefault();
                                    e.stopPropagation();
                                    MyObject.notice_info("Para continuar faça seu login.");
                                    jQuery( ".btnLoginPrincipal" ).trigger( "click" );
                                    return false;
                                  };
                              });
                        },
                        error:function(request, status, error){
                              //console.log("ERRO CAT-ID: "+data.cat_id);
                              //console.log("ERRO STATUS: "+status);
                              //console.log("ERRO: "+error);
                              jQuery(item).html('Não foi possivel carregar os itens, <a class="click-radar2" style="cursor: pointer;" onClick="javascript:MyObject.getItensCategoria();">clique aqui para tentar novamente!</a>');
                        },
              });
              return;

    },
    stopAllAjax:function(){
        while (request = jQuery.xhrPool.pop()) request.abort();
        console.log('ABORT AJAX');
    },
    qtywrap:function(){

      //console.log('qtywrap');

      var ajaxurl             = wcplprovars.ajax_url;
      var carturl             = wcplprovars.cart_url;
      var currency_symbol     = wcplprovars.currency_symbol;
      var thousand_separator  = wcplprovars.thousand_separator;
      var decimal_separator   = wcplprovars.decimal_separator;
      var decimal_decimals    = wcplprovars.decimal_decimals;
      var currency_pos        = wcplprovars.currency_pos;
      var price_display_suffix= wcplprovars.price_display_suffix;
      var gclicked            = 0;
      var glob_clicked        = 0;
      var count               = 0;
      var numofadded          = 0;
      var wcplpro_ajax        = wcplprovars.wcplpro_ajax;
      var $fragment_refresh   = '';
      var get_global_cart_id  = '';
      var formdata            = new Array;
      Number.prototype.formatMoney = function(c, d, t){
      var n = this,
          c = isNaN(c = Math.abs(c)) ? 2 : c,
          d = d == undefined ? "." : d,
          t = t == undefined ? "," : t,
          s = n < 0 ? "-" : "",
          i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
          j = (j = i.length) > 3 ? j % 3 : 0;
         return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
       };
       function get_price_html(price) {
        price = parseFloat(price).formatMoney(decimal_decimals,decimal_separator,thousand_separator);
        if (currency_pos == 'left') {
          price = currency_symbol + price;
        }
        if (currency_pos == 'right') {
          price = price + currency_symbol;
        }
        if (currency_pos == 'left_space') {
          price = currency_symbol +' '+price;
        }
        if (currency_pos == 'right_space') {
          price = price + ' ' + currency_symbol;
        }
        if (price_display_suffix != '') {
          price = price +' '+ price_display_suffix;
        }
        return price;
      }

      jQuery(".wcplprotable div.qtywrap").each(function() {
          //console.log('achou');
          var qtythis = jQuery(this);
          if( jQuery(qtythis).hasClass('ok') ) return;
          jQuery(qtythis).addClass('ok');
          qtythis.find(".minusqty").on("click", function() {
            var valnum = parseInt(qtythis.find("input").val());
            var valmin = qtythis.find("input").attr('min');
            if( typeof valmin === 'undefined' || valmin === null ){
              valmin = 0;
            }
            if (qtythis.find("input").attr("step") && qtythis.find("input").attr("step") > 0) {
              var step = parseInt(qtythis.find("input").attr("step"));
            } else {
              var step = 1;
            }
            if (valnum - step >= valmin) {
              qtythis.find("input").val(valnum - step);
              qtythis.closest('tr').find(".cartcol input.hidden_quantity").val(valnum - step);
              qtythis.closest('tr').find(".totalcol").text(get_price_html((valnum - step) * qtythis.closest('tr').data('price')));
              qtythis.find("input").trigger( "qty:change" );
            }
          });
          qtythis.find(".plusqty").on("click", function() {
            var valnum = parseInt(qtythis.find("input").val());
            var valmax = qtythis.find("input").attr('max');
            if( typeof valmax === 'undefined' || valmax === null ){
              valmax = -1;
            }
            if (qtythis.find("input").attr("step") && qtythis.find("input").attr("step") > 0) {
              var step = parseInt(qtythis.find("input").attr("step"));
            } else {
              var step = 1;
            }
            if ((valnum + step <= valmax) || valmax == -1) {
              qtythis.find("input").val(valnum + step);
              qtythis.closest('tr').find(".cartcol input.hidden_quantity").val(valnum + step);
              qtythis.closest('tr').find(".totalcol").text(get_price_html((valnum + step) * qtythis.closest('tr').data('price')));
              qtythis.find("input").trigger( "qty:change" );
            }
          });
        });
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
    CalculaDistanciaCep:function(){
            if(enderecoDistancia.length > 0 ){
                    if( enderecoCEP.length > 0 ){
                        jQuery('.cep-pesquisado').html('<span class="pesquisado"><b>Último CEP pesquisado:</b> '+enderecoCEP+'</span>');
                        jQuery('.cep-pesquisado .pesquisado').fadeIn();
                    }
                    jQuery('.calcula-cep').each(function() {
                        var campo = jQuery(this);
                        var destino = jQuery(this).text().trim();
                        //console.log(enderecoDistancia);
                        //console.log(enderecoCEP);
                        //console.log(destino);
                        var service = new google.maps.DistanceMatrixService();
                        if(!service) return;
                        service.getDistanceMatrix({
                          origins:[enderecoDistancia],
                          destinations:[destino],
                          //travelMode: google.maps.TravelMode.DRIVING,
                          //travelMode: google.maps.TravelMode.WALKING,
                          travelMode: google.maps.TravelMode.BICYCLING,
                        },
                            function callbackDistanciaCep2(response, status) {
                              //console.log(response);
                              //console.log(status);
                              if (status == google.maps.DistanceMatrixStatus.OK) {
                                  var distancia = response.rows[0].elements[0].distance.text;
                                  var duracao = response.rows[0].elements[0].duration.text;
                                  //console.log(distancia);
                                  //console.log(duracao);
                                  duracao = duracao.replace("minutos", "min");
                                  var retorno = distancia+' ('+duracao+' dist.)';
                                  if( retorno.length > 0 ){
                                      //console.log(retorno);
                                      campo.parent().find('.distancia-cep').fadeOut();
                                      campo.parent().find('.distancia-cep').text(retorno);
                                      campo.parent().find('.distancia-cep').fadeIn();
                                  }
                              }else{
                                  //console.log('erro');
                                  campo.parent().find('.distancia-cep').text('');
                                  campo.parent().find('.distancia-cep').fadeOut();
                              }
                        });
                    });
            }else{
              jQuery('.distancia-cep').text('');
              jQuery('.distancia-cep').fadeOut();
            }
    },
    mostraLavanderiasCep:function(){

        //jQuery(".product").fadeOut("slow");
        //jQuery(".product").fadeTo(1000,0.5,'swing');
        if( jQuery('.lista-lavanderias').length == 0 ) return;
        //setTimeout(function () {
        //jQuery(".product").css('opacity','0.5');

        function mostra(item,index){
            //jQuery(".product").fadeTo(1000,0.5,'swing');
            //console.log(item+' - '+jQuery(item).length);
            //jQuery(".product").fadeOut("slow");
            //jQuery(".product").css('opacity','0.5');
            //jQuery(".product").hide();
            if( jQuery(item).length == 0 ){
                //if( jQuery('.lavanderias-loading').length == 0 && jQuery('#yith-infs-button').length > 0 ) jQuery('#yith-infs-button').click();
                //setTimeout(function () { enderecoLavanderias.forEach(mostra); },3000); // TIMEOUT
                //console.log(item+' nao achou!');
                      if( jQuery('.lavanderias-loading').length == 0 && jQuery('#yith-infs-button').length > 0 ) jQuery('#yith-infs-button').click();
                      setTimeout(function (e) {
                                //if( jQuery('.lavanderias-loading').length == 0 && jQuery('#yith-infs-button').length > 0 ) jQuery('#yith-infs-button').click();
                                MyObject.mostraLavanderiasCep();
                      },3000); // TIMEOUT
                //MyObject.mostraLavanderiasCep();
                //break;
                //return false;
            }else{
                //jQuery(".product").fadeTo(1000,0.5,'swing');
                //jQuery(".post-384").fadeTo(1000,1,'swing');
                //jQuery(item).fadeTo(1000,1,'swing');
                //jQuery(item).css('opacity','1');
                //console.log(item+' achou!');
                jQuery(item).show();
            }
            //console.log('loop');
            //return;
        };
        if( enderecoLavanderias.length > 0 ){
          //jQuery(".product").css('opacity','0.5');
          //jQuery(".product").fadeOut("slow");
          jQuery(".product").hide();
          enderecoLavanderias.forEach(mostra);
        }
        //else if( endereco.length > 0 && enderecoLavanderias.length == 0 ) jQuery(".product").css('opacity','0.5');
        //},2000); // TIMEOUT

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
    isMobile:function () {
      try{ document.createEvent("TouchEvent"); return true; }
      catch(e){ return false; }
    },
    isElementInViewport:function(el) {
			if (typeof jQuery === "function" && el instanceof jQuery) {
				el = el[0];
			}
			var rect = el.getBoundingClientRect();
			return (
				rect.top >= 0 &&
				rect.left >= 0 &&
				rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or jQuery(window).height() */
				rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or jQuery(window).width() */
			);
		},
    getParam: function(param){
      var res = null;
    	try{
    		var qs = decodeURIComponent(window.location.search.substring(1));//get everything after then '?' in URI
    		var ar = qs.split('&');
    		$.each(ar, function(a, b){
    			var kv = b.split('=');
    			if(param === kv[0]){
    				res = kv[1];
    				return false;//break loop
    			}
    		});
    	}catch(e){}
    	return res;
    },
    marker_data: function(marker_data){
        //log location marker data to the console
        //console.log("marker_data");
        //console.log(marker_data);
        //example output
        //{"marker_id": 123, "location_title": "my location marker", "custom_param": "my location"}

        //jQuery('.click-radar').on('click', function(e) {
            //MyObject.radarSite();
        //});

    },
    atualizaPedidoPeca: function(id, enviado_qnt, cart_qnt_atual) {
        console.log('ATUALIZA PEDIDO PECA');
        //break;
        ///*

                       //jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
                                setTimeout(function () {

                                  //if(event.type == 'ajaxComplete' && ( settings.url.indexOf('admin-ajax.php') > -1 || settings.url.indexOf('get_refreshed_fragments') > -1 ) ){

                                    if(!cart_qnt_atual) cart_qnt_atual = 0;

                                    var cart_qnt_new = jQuery(document).find('#cart-'+id).attr('data-qnt');
                                    var total = ( parseInt(enviado_qnt) + parseInt(cart_qnt_atual) );
                                    var total_new = parseInt(cart_qnt_new);

                                    //console.log('qnt new : '+parseInt(cart_qnt_new));
                                    //console.log('qnt atual : '+parseInt(cart_qnt_atual));
                                    //console.log('enviado: '+parseInt(enviado_qnt));
                                    //console.log('total: '+total);
                                    //console.log('total_new: '+total_new);

                                    if( !total_new || total > total_new ){

                                        //console.log('ADD ERROR');
                                        jQuery('.added2cart_'+id+' .added2cart').html('&#10007; <a href="#" onClick="location.reload(true); return false;" target="_self" class="erroAddAjax">ERRO</a>')
                                        jQuery('.added2cart_'+id).css('background-color','#000');


                                    }else{

                                        //console.log('ADD SUCCESS');
                                        //jQuery('.added2cart_'+id+' .added2cart').html('&#10007; <a href="'+home_url+'/minha-lavanderia" target="_blank" class="erroAddAjax">ERRO</a>')
                                        jQuery('.added2cart_'+id+' .added2cart').html('&#10003;')
                                        jQuery('.added2cart_'+id).css('background-color','#4e6d4e');

                                    }
                                    jQuery(".added2cart_"+id).show();

                                  //} // AJAX

                                },50); // TIMEOUT

                        //}); // AJAX COMPLETE
      //*/

    },
    procurar:function(obj, agulha) {
        var chaves = Object.keys(obj);
        for (var i = 0; i < chaves.length; i++) {
            var chave = chaves[i];
            if (!obj[chave]) continue;
            else if (typeof obj[chave] == 'object') return MyObject.procurar(obj[chave], agulha);
            else if (obj[chave].indexOf(agulha) != -1) return [obj, chave];

        }
        return false;
    },
    refineUrl: function (){
        //get full url
        var url = window.location.href;
        //get url after/
        var value = url = url.slice( 0, url.indexOf('?') );
        //get the part after before ?
        //value  = value.replace('@System.Web.Configuration.WebConfigurationManager.AppSettings["BaseURL"]','');
        //return value;
        window.history.pushState("", "", value);
    },
    er_replace:function( pattern, replacement, subject ){
        	return subject.replace( pattern, replacement );
    },
    titleCase:function(str) {
         //pega apenas as palavras e tira todos os espacos em branco.
         return str.replace(/\w\S*/g, function(str) {
            //passa o primeiro caractere para maiusculo, e adiciona o todo resto minusculo
            return str.charAt(0).toUpperCase() + str.substr(1).toLowerCase();
        });
    },
    pedidoPorPeca:function(e){
        console.log('PEDIDO PECA');
        //break;
        /*
                if(!window["cart-684"]){
                  jQuery('.widget_shopping_cart_content .buttons').attr('style', 'display:none !important');
                  jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").text("FINALIZAR PEDIDO POR PE�A");
                }else if(window["cart-684"]){
                  jQuery('.widget_shopping_cart_content .buttons').attr('style', 'display:block !important');
                  jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").text("ADICIONAR PEDIDO POR PE�A");
                }
                jQuery( document ).ajaxComplete(function( event, xhr, settings ) {
                    setTimeout(function () {
                    	if(!window["cart-684"]){
                    		//console.log('nao existe');
                    		jQuery('.widget_shopping_cart_content .buttons').attr('style', 'display:none !important');
                            jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").text("FINALIZAR PEDIDO POR PE�A");
                    	  }else if(window["cart-684"]){
                    		//console.log('existe');
                    		jQuery('.widget_shopping_cart_content .buttons').attr('style', 'display:block !important');
                            jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").text("ADICIONAR PEDIDO POR PE�A");
                    	  }
                    }, 10);
                });
                jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").addClass("btn-lg checkout wc-forward btn-finalizar-pedido");
                //jQuery(".vc_col-sm-4 .wpb_wrapper .cart .single_add_to_cart_button").addClass("wc-forward");
    */
  },
  editandoPopup:function(parametro){



    //console.log("EDITAR");
    if( typeof jc_item == 'string' || ( typeof jc_item == 'object' && jc_item.isClosed() == true ) ){
    //jQuery('.produto-table .opcoes, .tm-cart-edit-options').on('click', function(e) {

         //console.log("POPUP");
         if( jQuery('body').hasClass('logged-in')==false ){
           MyObject.notice_info("Para continuar faça seu login.");
           jQuery( ".btnLoginPrincipal" ).trigger( "click" );
           //e.preventDefault();
           e.stopPropagation();
           return;
         };

         //e.preventDefault();
         //e.stopPropagation();

         var button = jQuery(parametro);
         //parametro = null;
         window.button = jQuery(button);
         //console.log(button);
         //cosnole.log($button);
         //return;
         //var textOld = jQuery(button).text();

         var url_item = '';
         var id_produto = 0;
         var quantidade = 0;

         if( jQuery(button).hasClass( "opcoes" ) === true ){

                 var form = jQuery(button).closest('form');
                 //console.log("FORM: "+form.length);

                 id_produto = parseInt( form.find('input[name="product_id"]').val() );
                 if( id_produto == "null" || !id_produto ) id_produto = parseInt( form.find('input[name="add-to-cart"]').val() );

                 quantidade = parseInt( form.find('input[name="quantity"]').val() );

                 //console.log(" ID PRODUTO: "+id_produto );
                 //console.log(" QNT PRODUTO: "+quantidade );

                 if( id_produto > 0 && quantidade > 0 ){
                    url_item = 'pid='+id_produto;
                 }

        }else if( jQuery(button).hasClass( "tm-cart-edit-options" ) === true ){

                  //console.log('Alterar Servico');

                  //var href = jQuery(button).attr('href');
                  var href = jQuery(button).prop('href');

                  //console.log( href.indexOf('?') !== -1 );
                  //return;

                  if ( href.indexOf('?') !== -1 ) {

                      var str = href.split('?')[1];
                      //console.log(" PARAM: "+str );
                      var id_elemento = '';
                      id_elemento = jQuery(button).parent().parent().attr('id');
                      id_produto = id_elemento.split("-")[1];
                      //id_produto = parseInt(209);
                      url_item = 'pid='+id_produto+'&'+str;

                  }

                  //return;
                  if( parseInt(post_id) == parseInt(id_produto) && jQuery(".product_cat-lavanderias").length == 0 ){
                      MyObject.loadingSite();
                      window.location.href = href;
                      return;
                  }
                  /*
                  if( parseInt(post_id) != parseInt(id_produto) && jQuery(".product_cat-lavanderias").length == 0 ){
                      MyObject.loadingSite();
                      window.location.href = href;
                      return true;
                  }
                  */
                  ///*
                  var extra = jQuery(".cart-pedido-lavanderia");
                  //var str = "Data da Coleta";
                  //var containsFoo = button.parent().indexOf('foo') >= 0; // true
                  var id = jQuery(button).parent().parent().attr('id');
                  var sel = jQuery('#'+id+':contains("Data da Coleta")');
                  var url = window.location.href;
                  //sel.length
                  //console.log(extra.length);
                  //if( extra.length > 0 && id_produto == post_id ){
                  if( sel.length > 0 && extra.length > 0 && parseInt(post_id) != parseInt(id_produto) ){
                    //jQuery(".loading-site").show();
                    //MyObject.loadingSite();
                    //window.location = href;
                    //window.location.href = href;
                    //window.open(href, '_self');
                    //jQuery(button).trigger( "click" );
                    //return;
                    //extra.remove();
                    //extra.hide();
                    //console.log("REMOVE EXTRA 1");
                    if( extra.css('display') == 'block' ) extra.slideToggle( "slow");
                    extra.remove();

                  }else if( sel.length > 0 && extra.length > 0 && parseInt(post_id) == parseInt(id_produto) ){

                      //console.log("REMOVE EXTRA 2");
                      if( extra.css('display') == 'block' ) extra.slideToggle( "slow");
                      extra.remove();
                      /*
                      var buttons = jQuery(".widget_shopping_cart .buttons");
                      var buttonAdd = jQuery(".cart-pedido-lavanderia .single_add_to_cart_button");
                      var edit = jQuery(".cart-pedido-lavanderia input[name='tc_cart_edit_key']");
                      str = str.split("&")[0];
                      str = str.split("=")[1];
                      console.log(str);
                      edit.val(str);
                      buttons.hide();
                      buttonAdd.text("Atualizar Pedido!");
                      extra.fadeIn();
                      return;
                      */
                  }else if( sel.length > 0 && jQuery(".product_cat-lavanderias").length == 0 ){

                    if( url.indexOf('minha-lavanderia') === -1 ){
                      MyObject.loadingSite();
                      window.location.href = href;
                      window.location.href = home_url+'/?p='+id_produto;
                      return;
                    }



                  }
                  //*/

        }

        //return;


            //var login_check = jQuery(".add_to_cart");

            /*
            if( jQuery('body').hasClass('logged-in')==true ){
              console.log("ok");
            }else{
              console.log("error");
              //jQuery(".single_add_to_cart_button").addClass("click-login");
            };


            if( extra.length == 1 && !window["cart-"+post_id] == true ){
                jQuery.alert('Por favor, 1º primeiro informe a data da coleta!');
                return;
            }
            */

            /*
            jQuery( ".cart .single_add_to_cart_button" ).on( "click", function() {
              //console.log("clicou");
              jQuery.alert('clicou!');
              if( jQuery('body').hasClass('logged-in')==false ){
                jQuery( ".btnLoginPrincipal" ).trigger( "click" );
              };
              return;
            });
            */

            //jQuery( "form.cart" ).submit(function( e ) {




            var extra = jQuery(".cart-pedido-lavanderia");
            if( extra.length == 1 && !window["cart-"+post_id] == true ){



                jQuery.alert('Por favor, 1º primeiro informe a data da coleta!');
                return;
            }
            //console.log("URL: "+url_item);
            //console.log("QUANTIDADE: "+quantidade);
            //console.log("ID PRODUTO: "+id_produto);

            //id_produto = parseInt(209);
            //quantidade = parseInt(1);

            //var id = $(this).attr('data-id');
            //var id = $(this).attr('data-product_id');
            //var qnt = $(this).attr('data-product_id');

            if( url_item.length > 0 ){

                  //jQuery('html,body').animate({scrollTop:0}, 500,'swing');

                  //console.log( typeof jc_item );
                  //console.log( jc_item.isOpen() );

                  //if( typeof jc_item == 'string' || ( typeof jc_item == 'object' && jc_item.isClosed() == true ) ){
                      //console.log("ABERTO");
                      //if( typeof jc_item == 'object' ) jc_item.close();
                      MyObject.popupItem( url_item, id_produto, quantidade );
                  //}


            } // END ULR_ITEM

       //});
       //*/
       }// SE ABERTO
       //console.log('ja esta aberto');
       return false;
  },
  popupItem:function( url_item, id_produto = 0, quantidade = 0 ){

    //console.log("HREF: "+href);
    //console.log("URL: "+url_item);
    //console.log("QUANTIDADE: "+quantidade);
    //console.log("ID PRODUTO: "+id_produto);

    /*jQuery.ajax({
        type: "GET",
        url: theme.ajax_url+'?action=porto_product_quickview&'+url_item,
    });*/

    if(id_produto==0) url_item = '';

    var tela;
    window.jc_item = jQuery.confirm({
            title: '',
            //content: 'URL:'+theme.ajax_url+'?action=porto_product_quickview&pid='+id,
            //content: 'URL:'+theme.ajax_url+'?action=porto_product_quickview&pid='+id_produto,
            //content: 'URL:'+href,
            ///*
            content: function () {
                var self = this;

                return jQuery.ajax({
                    //?tm_cart_item_key=fabeaeeb3d525ba074630e61f333b42c&cart_item_key=fabeaeeb3d525ba074630e61f333b42c&_wpnonce=3ae658db12
                    //url: theme.ajax_url+'?action=porto_product_quickview&pid='+id_produto+'&'+str,
                    //url: theme.ajax_url+'?action=porto_product_quickview&pid='+id_produto,

                    url: theme.ajax_url+'?action=porto_product_quickview&'+url_item,
                    context: document.body,
                    //url: href,

                    //cache: false,
                    //dataType: 'json',
                    method: 'get',
                    //method: "POST",
                    //data: { name: "John", location: "Boston" },
                    //dataType: "html",
                    //processData: false,  // tell jQuery not to process the data
                 		//contentType: false,   // tell jQuery not to set contentType
                    beforeSend: function( xhr ) {
                        //console.log( "beforeSend" );
                        //xhr.overrideMimeType( "text/plain; charset=x-user-defined" );

                        //jQuery('html,body').animate({scrollTop:0}, 0,'swing');
                				//jQuery(window).scrollTop(0);
                				//document.body.scrollTop = document.documentElement.scrollTop = 0;

                    }
                }).done(function (response) {

                    //console.log( "done" );

                    var $html = jQuery('html');
                    if ($html.hasClass('sidebar-opened')) {
                        $html.removeClass('sidebar-opened');
                        jQuery('.sidebar-overlay').removeClass('active');
                    }

                    //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js');
                    jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-scripts.js');
                    jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-epo.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');

                    //jQuery(response).getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-scripts.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-epo.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js');
                    //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-timepicker.js');

                    //var script1 = response.createElement("SCRIPT");
                    //script1.src = plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js';
                    //script1.type = 'text/javascript';
                    //response.getElementsByTagName("head")[0].appendChild(script1);

                    self.setContent(response);
                    tela = response;
                    //if(jQuery(tela).find('.pedido-lavanderia').length > 0){
                      //jQuery(tela).find('.tc-epo-totals').hide();
                      //jQuery(".tc-epo-totals.tm-product-id-"+id_produto).remove();
                    //}
                    //self.setContentAppend('<br>Version: ' + response.version);
                    self.setTitle(response.name);

                }).fail(function(response){
                    //console.log( "fail" );
                    self.setTitle("Atenção");
                    self.setContent('Não foi possivel carregar este item.<br/> Por favor, tente mais tarde.');
                }).always(function() {
                    //console.log( "finished" );

                    //jQuery('html,body').animate({scrollTop:0}, 0,'swing');
                    //jQuery(window).scrollTop(0);
                    //document.body.scrollTop = document.documentElement.scrollTop = 0;

                });
            },
            //*/
            contentLoaded: function(data, status, xhr){
                //console.log("contentLoaded");
                //this.setContentAppend('<br>Content loaded!');
                //jQuery('.jconfirm-content .quantity .qty').val(7);
            },
            onContentReady: function(){

               //console.log("onContentReady");

                //this.setContentAppend(jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js'));
                //this.setContentAppend(jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-scripts.js'));
                //this.setContentAppend(jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-epo.js'));
                //this.setContentAppend(jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js'));
                //this.setContentAppend(jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-timepicker.js'));
                //console.log("onContentReady");
                if( quantidade > 0 ) jQuery('.jconfirm-content .quantity .qty').val(quantidade);
                jQuery('.jconfirm-buttons').css('text-align','left');
                //console.log(id_produto);

                /*
                var script1 = document.createElement("SCRIPT");
                script1.src = plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js';
                script1.type = 'text/javascript';
                var script2 = document.createElement("SCRIPT");
                script2.src = plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-epo.js';
                script2.type = 'text/javascript';
                var script3 = document.createElement("SCRIPT");
                script3.src = plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-scripts.js';
                script3.type = 'text/javascript';
                //script.onload = function() {
                    //var $ = window.jQuery;
                    // Use $ here...
                //};
                document.getElementsByTagName("head")[0].appendChild(script1);
                document.getElementsByTagName("head")[0].appendChild(script2);
                document.getElementsByTagName("head")[0].appendChild(script3);
                */
                ///*
                //function reload_js(src) {
                    //jQuery('script[src*="' + src + '"]').remove();
                    //jQuery('<script>').attr('src', src).appendTo('head');
                //}
                //reload_js('source_file.js');
                //*/

                //window.tela = tela;
                //if( jQuery('.campo-data').val() == '00/00/0000' ) jQuery('.campo-data').val('');
                //if( jQuery('.campo-hora').val() == '00:00' ) jQuery('.campo-hora').val('');

                setTimeout(function () {
                  if( jQuery(tela).find('.campo-data').val() == '00/00/0000' ) jQuery('.campo-data').val('');
                  if( jQuery('.campo-hora').val() == '00:00' ) jQuery('.campo-hora').val('');
                }, 200);

                //OK
                //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-scripts.js');
                //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-epo.js');
                jQuery.getScript(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');


                //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-datepicker.js');
                //jQuery.getScript(plugins_url+'/woocommerce-tm-extra-product-options/assets/js/tm-timepicker.js');
                //window.plugin = (plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');
                //reload_js(plugins_url+'/woocommerce-add-ajax/orak-ajax-add-to-cart.js');

                setTimeout(function () {
                  if( jQuery(tela).find('.pedido-lavanderia').length > 0 ){
                    jQuery(".tc-epo-totals.tm-product-id-"+id_produto).remove();
                  }
                }, 100);




            },
            onClose: function () {
                // before the modal is hidden.
                //console.log('onClose');
                //jc_item.setContent('');
                //jQuery('html,body').animate({scrollTop:0}, 500,'swing');
        				//jQuery(window).scrollTop(0);
        				//document.body.scrollTop = document.documentElement.scrollTop = 0;
            },
            onDestroy: function () {
                // when the modal is removed from DOM
                //console.log('onDestroy');
                //jc_item.setContent('');

                //jQuery('html,body').animate({scrollTop:0}, 0,'swing');
                //jQuery(window).scrollTop(0);
                //document.body.scrollTop = document.documentElement.scrollTop = 0;


            },
            containerFluid:false,
            escapeKey:true,
            closeIcon:true,
            bgOpacity: 0.5,
            container: 'body',
            buttons: {},
            alignMiddle: true,
            //theme: 'material', // 'material', 'bootstrap', 'supervan'
            columnClass: 'medium',
            //scrollToPreviousElementAnimate: false,
            //scrollToPreviousElement: false,
            buttons: {
                          Cancelar: function(){
                              //e.preventDefault();
                          },
                          //Continuar: {
                              //text: 'Something else',
                              //btnClass: 'btn-success',
                              //action: function(){
                                  //$.alert('Button name was called');
                                  //return true;
                                  //$(".loading-site").fadeIn();
                                  //location.href = link;
                              //}
                          //},
              },

    });

    //return;
    //console.log(id);
    /*
    jQuery.confirm({
            title: '',
            content: 'URL:'+theme.ajax_url+'?action=porto_product_quickview&pid='+id,
            //content: 'URL:http://192.168.1.25/traz/wp-admin/admin-ajax.php?action=porto_product_quickview&pid='+id,
            containerFluid:true,
            escapeKey:true,
            closeIcon:true,
            bgOpacity: 0.5,
            container: 'body',
            buttons: {},
            alignMiddle: true,
            theme: 'material', // 'material', 'bootstrap', 'supervan'
            buttons: {
                        voltar: function(){
                            //e.preventDefault();
                        },
                        Continuar: {
                            //text: 'Something else',
                            //btnClass: 'btn-success',
                            action: function(){
                                //$.alert('Button name was called');
                                //return true;
                                //$(".loading-site").fadeIn();
                                //location.href = link;
                            }
                        },
            },
            //offsetTop:0,
            onContentReady: function () {
                var self = this;
                //this.setContentPrepend('<div>Prepended text</div>');
                //setTimeout(function () {
                //    self.setContentAppend('<div>Appended text after 2 seconds</div>');
                //}, 2000);
            },
            columnClass: 'xlarge',
            //columnClass: 'col-md-4 col-md-offset-8 col-xs-4 col-xs-offset-8',
    });
    */
  }
  // other functions...
};
