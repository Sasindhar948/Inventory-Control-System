<?php
  $page_title = 'Add Sale';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(3);

  $all_products = find_all('products');
?>
<?php

  if(isset($_POST['add_sale'])){
    $req_fields = array('products', 'quantities');
    validate_fields($req_fields);
    if(empty($errors)){
      $products = $_POST['products'];
      $quantities = $_POST['quantities'];
      
      // Start transaction
      $db->begin_transaction();
      try {
        // Create main sale record
        $sale_date = make_date();
        $sql = "INSERT INTO sales (date) VALUES ('{$sale_date}')";
        $db->query($sql);
        $sale_id = $db->insert_id;
        
        // Add sale details
        $total_amount = 0;
        for($i = 0; $i < count($products); $i++) {
          $product_id = (int)$products[$i];
          $qty = (int)$quantities[$i];
          
          // Get product info
          $product = find_by_id('products', $product_id);
          $price = $product['sale_price'] * $qty;
          
          // Insert sale detail
          $sql = "INSERT INTO sale_details (sale_id, product_id, quantity, price) 
                  VALUES ({$sale_id}, {$product_id}, {$qty}, {$price})";
          $db->query($sql);
          
          // Update product quantity
          update_product_qty($product_id, $qty);
          
          $total_amount += $price;
        }
        
        // Update sale total
        $sql = "UPDATE sales SET price = {$total_amount} WHERE id = {$sale_id}";
        $db->query($sql);
        
        $db->commit();
        $session->msg('s', "Sale added successfully.");
        redirect('sales.php', false);
      } catch (Exception $e) {
        $db->rollback();
        $session->msg('d', $e->getMessage());
        redirect('add_sale.php', false);
      }
    } else {
      $session->msg('d', $errors);
      redirect('add_sale.php', false);
    }
  }

?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
    <form method="post" action="payment.php">
      <div id="product-rows">
        <div class="form-group product-row">
          <div class="row">
            <div class="col-md-6">
              <select class="form-control" name="products[]" onchange="updateTotal()" required>
                <option value="">Select Product</option>
                <?php foreach ($all_products as $prod): ?>
                <option value="<?php echo (int)$prod['id']; ?>" 
                        data-price="<?php echo (float)$prod['sale_price']; ?>">
                  <?php echo $prod['name']; ?> ($<?php echo number_format($prod['sale_price'], 2); ?>)
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" name="quantities[]" 
                     onchange="updateTotal()" onkeyup="updateTotal()" 
                     placeholder="Quantity" min="1" required>
            </div>
          </div>
        </div>
      </div>
      
      <div class="form-group">
        <div class="row">
          <div class="col-md-10">
            <h3>Total Amount: $<span id="total-amount">0.00</span></h3>
          </div>
        </div>
      </div>
      
      <button type="button" class="btn btn-primary" onclick="addProductRow()">Add Another Product</button>
      <button type="submit" name="add_sale" class="btn btn-success">Add Sale</button>
    </form>
  </div>
</div>

<script>
function addProductRow() {
  const container = document.getElementById('product-rows');
  const row = document.querySelector('.product-row').cloneNode(true);
  row.querySelector('select').value = '';
  row.querySelector('input').value = '';
  container.appendChild(row);
  updateTotal();
}

function updateTotal() {
  let total = 0;
  const rows = document.querySelectorAll('.product-row');
  
  rows.forEach(row => {
    const select = row.querySelector('select');
    const quantity = row.querySelector('input').value;
    
    if (select.value && quantity) {
      const selectedOption = select.options[select.selectedIndex];
      const price = parseFloat(selectedOption.dataset.price);
      total += price * parseInt(quantity);
    }
  });
  
  document.getElementById('total-amount').textContent = total.toFixed(2);
}
</script>

<?php include_once('layouts/footer.php'); ?>
