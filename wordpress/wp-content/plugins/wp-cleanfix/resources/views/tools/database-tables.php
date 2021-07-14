<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<table class="wp-cleanfix-tools-database" width="100%" border="0" cellpadding="0" cellspacing="0">
  <thead>
  <tr>
    <th><?php _e( 'Table', WPCLEANFIX_TEXTDOMAIN ) ?></th>
    <th><?php _e( 'Auto Increment', WPCLEANFIX_TEXTDOMAIN ) ?></th>
    <th><?php _e( 'Action', WPCLEANFIX_TEXTDOMAIN ) ?></th>
  </tr>
  </thead>

  <tbody>
  <?php foreach ( $database->tables as $table => $label ) : ?>
    <?php $info = $database->getTableInformation( $table ) ?>

    <tr>
      <td>
        <?php echo $label ?>
      </td>

      <td data-table="auto-increment-<?php echo $table ?>">
        <?php echo $info->Auto_increment ?>
      </td>

      <td>
        <button class="button button-primary wp-cleanfix-button-compact"
                data-table="<?php echo $table ?>">
          <?php _e( 'Compact', WPCLEANFIX_TEXTDOMAIN ) ?>
        </button>
      </td>

    </tr>

  <?php endforeach; ?>

  </tbody>

</table>