<?php
  $page_title = 'Payment';
  require_once('includes/load.php');
  page_require_level(3);
  
  if(!isset($_POST['products'])) {
    $session->msg('d', "No products selected!");
    redirect('add_sale.php', false);
  }

  $products = $_POST['products'];
  $quantities = $_POST['quantities'];
  $sale_items = array();
  $total_amount = 0;

  // Gather all product details
  for($i = 0; $i < count($products); $i++) {
    $product_id = (int)$products[$i];
    $qty = (int)$quantities[$i];
    
    $product = find_by_id('products', $product_id);
    $category = find_by_id('categories', $product['categorie_id']);
    
    $item = array(
      'product' => $product,
      'category' => $category['name'],
      'quantity' => $qty,
      'subtotal' => $qty * $product['sale_price']
    );
    
    $sale_items[] = $item;
    $total_amount += $item['subtotal'];
  }

  if(isset($_POST['process_payment'])) {
    $req_fields = array('payment_method', 'payment_status');
    validate_fields($req_fields);
    
    if(empty($errors)) {
        // Start transaction using older MySQL syntax
        $db->query('START TRANSACTION');
        try {
            // Create main sale record
            $sale_date = make_date();
            $payment_method = $db->escape($_POST['payment_method']);
            $payment_status = $db->escape($_POST['payment_status']);
            $notes = $db->escape($_POST['notes']);
            
            $sql = "INSERT INTO sales (date, payment_method, payment_status, notes) 
                    VALUES ('{$sale_date}', '{$payment_method}', '{$payment_status}', '{$notes}')";
            if(!$db->query($sql)){
                throw new Exception("Error creating sale record");
            }
            $sale_id = $db->insert_id();
            
            // Add sale details
            $total_amount = 0;
            for($i = 0; $i < count($products); $i++) {
                $product_id = (int)$products[$i];
                $qty = (int)$quantities[$i];
                
                // Get product info
                $product = find_by_id('products', $product_id);
                if(!$product) {
                    throw new Exception("Product not found");
                }
                $price = $product['sale_price'] * $qty;
                
                // Insert sale detail
                $sql = "INSERT INTO sale_details (sale_id, product_id, quantity, price) 
                        VALUES ({$sale_id}, {$product_id}, {$qty}, {$price})";
                if(!$db->query($sql)){
                    throw new Exception("Error creating sale detail");
                }
                
                // Update product quantity
                $sql = "UPDATE products SET quantity = quantity - {$qty} WHERE id = {$product_id}";
                if(!$db->query($sql)){
                    throw new Exception("Error updating product quantity");
                }
                
                $total_amount += $price;
            }
            
            // Update sale total
            $sql = "UPDATE sales SET price = {$total_amount} WHERE id = {$sale_id}";
            if(!$db->query($sql)){
                throw new Exception("Error updating sale total");
            }
            
            // Commit transaction
            if(!$db->query('COMMIT')){
                throw new Exception("Error completing transaction");
            }
            
            $session->msg('s', "Sale processed successfully.");
            
            // Redirect to generate bill
            redirect('generate_bill.php?sale_id='.$sale_id, false);
            
        } catch (Exception $e) {
            // Rollback transaction
            $db->query('ROLLBACK');
            $session->msg('d', $e->getMessage());
            redirect('add_sale.php', false);
        }
    } else {
        $session->msg('d', $errors);
    }
  }
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-8">
    <?php echo display_msg($msg); ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-shopping-cart"></span>
          <span>Order Summary</span>
        </strong>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Price</th>
              <th class="text-center">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($sale_items as $item): ?>
            <tr>
              <td><?php echo remove_junk($item['product']['name']); ?></td>
              <td><?php echo remove_junk($item['category']); ?></td>
              <td class="text-center"><?php echo $item['quantity']; ?></td>
              <td class="text-center">$<?php echo number_format($item['product']['sale_price'], 2); ?></td>
              <td class="text-center">$<?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-right"><strong>Total Amount</strong></td>
              <td class="text-center">$<?php echo number_format($total_amount, 2); ?></td>
            </tr>
          </tfoot>
        </table>

        <form method="post" action="payment.php">
          <!-- Hidden fields to maintain product data -->
          <?php foreach($products as $i => $product): ?>
            <input type="hidden" name="products[]" value="<?php echo $product; ?>">
            <input type="hidden" name="quantities[]" value="<?php echo $quantities[$i]; ?>">
          <?php endforeach; ?>

          <div class="form-group">
            <label>Payment Method</label>
            <select class="form-control" name="payment_method" required>
              <option value="">Select Payment Method</option>
              <option value="cash">Cash</option>
              <option value="credit_card">Credit Card</option>
              <option value="debit_card">Debit Card</option>
              <option value="bank_transfer">Bank Transfer</option>
            </select>
          </div>

          <div class="form-group">
            <label>Payment Status</label>
            <select class="form-control" name="payment_status" required>
              <option value="paid">Paid</option>
              <option value="pending">Pending</option>
            </select>
          </div>

          <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
          </div>

          <button type="submit" name="process_payment" class="btn btn-success">Process Payment</button>
          <a href="add_sale.php" class="btn btn-danger">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?> 