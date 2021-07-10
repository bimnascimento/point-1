jQuery(document).ready(function($) {
  $('body')
    .on('change', '.woocommerce_order_items input.deposit_paid', function() {
      var row = $(this).closest('tr.item');
      var paid = $(this).val();
      var remaining = $('input.deposit_remaining', row);
      var total = $('input.line_total', row);
      if (paid !== '' && parseFloat(total.val()) - parseFloat(paid) > 0)
        remaining.val(parseFloat(total.val()) - parseFloat(paid));
      else
        remaining.val('');
    })
    .on('change', '.woocommerce_order_items input.deposit_remaining', function() {
      var row = $(this).closest('tr.item');
      var remaining = $(this).val();
      var paid = $('input.deposit_paid', row);
      var total = $('input.line_total', row);
      if (remaining !== '' && parseFloat(total.val()) - parseFloat(remaining) > 0)
        paid.val(parseFloat(total.val()) - parseFloat(remaining));
      else
        paid.val('');
    })
    .on('change', '.woocommerce_order_items input.line_total', function() {
      var row = $(this).closest('tr.item');
      var remaining = $('input.deposit_remaining', row);
      var paid = $('input.deposit_paid', row);
      var total = $(this).val();
      if (paid.val() !== '' && parseFloat(total) - parseFloat(paid.val()) >= 0)
        remaining.val(parseFloat(total) - parseFloat(paid.val()));
      else
        remaining.val('');
    })
    .on('change', '.woocommerce_order_items input.quantity', function() {
      var row = $(this).closest('tr.item');
      var remaining = $('input.deposit_remaining', row);
      var paid = $('input.deposit_paid', row);
      var total = $('input.line_total');
      setTimeout(function() {
        if (paid.val() !== '' && remaining.val() !== '' && parseFloat(total.val()) - parseFloat(paid.val()) >= 0)
          remaining.val(parseFloat(total.val()) - parseFloat(paid.val()));
        else
          remaining.val('');
      }, 100);
    });

  $('#woocommerce-order-items').onFirst('click', 'button.calculate-action', function() {
    $('#woocommerce-order-items').onFirst('click', 'button.save-action', function(event) {
      var remaining = $('#_order_remaining');
      var total = $('#_order_total');
      var order_remaining = 0;
      var order_total = parseFloat(total.val());
      $('.woocommerce_order_items tr.item input.deposit_remaining').each(function() {
        if ($.isNumeric($(this).val()))
          order_remaining += parseFloat($(this).val());
      });
      remaining.val(order_remaining);
      total.val(order_total - order_remaining);
      $(this).off(event);
    });
  });

});
