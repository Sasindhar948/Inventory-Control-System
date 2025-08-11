<?php
  $page_title = 'Sales Receipt';
  require_once('includes/load.php');
  page_require_level(3);
  
  $sale_id = (int)$_GET['sale_id'];
  $sale = find_sale_by_id($sale_id);
  $sale_items = find_sale_details($sale_id);
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-body">
        <!-- Bill Header -->
        <div class="text-center">
          <h2>COMPANY NAME</h2>
          <p>123 Street Name, City, Country</p>
          <p>Phone: (123) 456-7890</p>
          <p>Email: info@company.com</p>
          <hr>
          <h3>SALES RECEIPT</h3>
        </div>

        <!-- Receipt Details -->
        <div class="row receipt-details">
          <div class="col-xs-6">
            <p><strong>Receipt #:</strong> <?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Date:</strong> <?php echo date('d/m/Y H:i', strtotime($sale['date'])); ?></p>
          </div>
          <div class="col-xs-6 text-right">
            <p><strong>Payment Method:</strong> <?php echo ucfirst($sale['payment_method']); ?></p>
            <p><strong>Payment Status:</strong> <?php echo ucfirst($sale['payment_status']); ?></p>
          </div>
        </div>
        <hr>

        <!-- Items Table -->
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Item Description</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Unit Price</th>
              <th class="text-center">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($sale_items as $item): ?>
            <tr>
              <td>
                <?php echo remove_junk($item['product_name']); ?>
                <br>
                <small class="text-muted"><?php echo remove_junk($item['category_name']); ?></small>
              </td>
              <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
              <td class="text-center">$<?php echo number_format($item['price']/$item['quantity'], 2); ?></td>
              <td class="text-center">$<?php echo number_format($item['price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
              <td class="text-center">$<?php echo number_format($sale['price'], 2); ?></td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Tax (0%)</strong></td>
              <td class="text-center">$0.00</td>
            </tr>
            <tr>
              <td colspan="3" class="text-right"><strong>Total Amount</strong></td>
              <td class="text-center"><strong>$<?php echo number_format($sale['price'], 2); ?></strong></td>
            </tr>
          </tfoot>
        </table>

        <!-- Notes -->
        <?php if(!empty($sale['notes'])): ?>
        <div class="notes">
          <strong>Notes:</strong>
          <p><?php echo nl2br($sale['notes']); ?></p>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center">
          <p>Thank you for your business!</p>
          <p><small>This is a computer generated receipt and requires no signature.</small></p>
        </div>

        <!-- Print/Back Buttons (hidden when printing) -->
        <div class="no-print text-center" style="margin-top: 20px;">
          <button onclick="window.print()" class="btn btn-primary">
            <i class="glyphicon glyphicon-print"></i> Print Receipt
          </button>
          <a href="sales.php" class="btn btn-success">
            <i class="glyphicon glyphicon-arrow-left"></i> Back to Sales
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .panel {
        border: none !important;
    }
    .panel-body {
        padding: 0 !important;
    }
    body {
        margin: 0;
        padding: 15px;
    }
}
</style>

<?php include_once('layouts/footer.php'); ?> 