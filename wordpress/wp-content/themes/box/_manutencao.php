<?php

add_action( 'init', 'manutencao' );
function manutencao() {
      if(is_admin()) return;
      dump("em manutencao...");
      exit;

}

 ?>
