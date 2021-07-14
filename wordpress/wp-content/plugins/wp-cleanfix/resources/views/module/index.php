<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wp-cleanfix-module">
  <?php echo $module->toolBar ?>
  <table class="wp-cleanfix-table-module">
    <tbody>
    <?php foreach( $module->tests as $test ) : ?>

      <?php echo $test->htmlRow() ?>

    <?php endforeach; ?>

    </tbody>
  </table>

</div>