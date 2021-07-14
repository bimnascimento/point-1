<?php

//include_once('_produtos_lista_0.php');
//include_once('_produtos_lista_1.php');

//include_once('_produtos_lista_lavanderias.php');
include_once('_produtos_lista_lavar.php');

if( strpos($store_slug, 'lavanderia-sul-america') !== false ){

  //include_once('_produtos_lista_sul_america.php');

}
if( strpos($store_slug, 'lavanderia-5sec-juiz-de-fora') !== false ){

  //include_once('_produtos_lista_5asec.php');

}

if( strpos($store_slug, 'lavanderia-lavar') !== false ){

  //include_once('_produtos_lista_lavar.php');

}




//ksort($lista_itens);


//   ?reset-itens=true
//   ?delete-lavanderia=true

//   ?reset-itens=true&delete-lavanderia=true

if( count($lista_itens) == 0 ) return;
echo '<div class="debug">';
$total = 0;

foreach ($lista_itens as $item => $val) {
      $valor = $val[0];
      $peso = $val[1];
      $prazo = $val[2];
      $categorias = $val[3];

      if( (float)$valor == 0) continue;
      //if( (float)$valor == 1) continue;

      //echo ('<b>Item:</b> '.$item.' | <b>Valor:</b> '.$valor.' | <b>Peso:</b> '.$peso.' | <b>Prazo:</b> '.$prazo.' | <b>Categorias:</b> '.implode(',',$categorias).'<br/>');
      //echo '\''.$item.'\' => array( 0.00, 0.000, 1, array( $opcao_caracteristicas, $opcao_cores, $opcao_defeitos, $opcao_marca ) ), <br/>';
      //echo '\''.$item.'\'<br/>';
      //echo $item.' - '.$valor.'<br/>';
      $total++;

      $valor = 0;
      create_item( $customer_id, $tag, $post_id, $item, $valor, $peso, $prazo, $categorias, $lista_categories );

      //exit;
      //echo '</div>';
      //return;



}
echo 'INSERT: '.$total;
//echo count($lista_itens);
echo '</div>';
//exit;
update_user_meta( $customer_id, 'itens_criados', true );



?>
