<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<tr class="wp-cleanfix-bottom-row">
  <td colspan="6">
    <div class="wpxcf-table-more-info">

      <div class="wpxcf-table-more-display-scroll">
        <table class="wpxcf-table-more-info-header">
          <thead>
          <tr>
            <th class="wpxcf-table-more-info-column-engine"><?php _e( 'Engine', WPCLEANFIX_TEXTDOMAIN ) ?></th>
            <th class="wpxcf-table-more-info-column-name"><?php _e( 'Name', WPCLEANFIX_TEXTDOMAIN ) ?></th>
            <th class="wpxcf-table-more-info-column-auto-increment"><?php _e( 'Auto Increment', WPCLEANFIX_TEXTDOMAIN ) ?></th>
            <th class="wpxcf-table-more-info-column-gain"><?php _e( 'Gain', WPCLEANFIX_TEXTDOMAIN ) ?></th>
          </tr>
          </thead>
        </table>
      </div>

      <div class="wpxcf-table-more-info-content">
        <table class="wpxcf-table-more-info-body">

          <tbody>
          <?php
          foreach ( $module->DatabaseTablesTest->tables() as $table_name => $info ) : ?>
            <tr class="<?php echo $info[ 'optimize' ] ? 'optimize' : '' ?>">
              <td class="wpxcf-table-more-info-column-engine"><?php echo $info[ 'engine' ] ?></td>
              <td class="wpxcf-table-more-info-column-name"><?php echo $table_name ?></td>
              <td class="wpxcf-table-more-info-column-auto-increment"><?php echo $info[ 'auto_increment' ] ?></td>
              <td class="wpxcf-table-more-info-column-gain"><?php echo $info[ 'gain' ] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>

        </table>
      </div>

      <div class="wpxcf-table-more-display-scroll">
        <table class="wpxcf-table-more-info-footer">
          <tfoot>
          <tr>
            <td><?php _e( 'Total Gain', WPCLEANFIX_TEXTDOMAIN ) ?></td>
            <td><?php printf( '%6.2f Kb', $module->DatabaseTablesTest->totalGain() ); ?></td>
          </tr>
          </tfoot>
        </table>
      </div>

    </div>
  </td>
</tr>