<?php
  $page_title = 'All sales';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(3);
?>
<?php
$sales = find_all_sales_with_details();
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading clearfix">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>All Sales</span>
          </strong>
          <div class="pull-right">
            <a href="add_sale.php" class="btn btn-primary">Add sale</a>
          </div>
        </div>
        <div class="panel-body">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th class="text-center">Date</th>
                <th class="text-center">Total Amount</th>
                <th class="text-center">Payment Method</th>
                <th class="text-center">Status</th>
                <th class="text-center" style="width: 100px;">Actions</th>
             </tr>
            </thead>
           <tbody>
             <?php foreach ($sales as $sale):?>
             <tr>
               <td class="text-center"><?php echo count_id();?></td>
               <td class="text-center"><?php echo $sale['date']; ?></td>
               <td class="text-center">$<?php echo number_format($sale['price'], 2); ?></td>
               <td class="text-center"><?php echo ucfirst($sale['payment_method']); ?></td>
               <td class="text-center"><?php echo ucfirst($sale['payment_status']); ?></td>
               <td class="text-center">
                  <div class="btn-group">
                     <a href="#" class="btn btn-info btn-xs" data-toggle="modal" 
                        data-target="#saleModal<?php echo (int)$sale['id'];?>" title="View">
                       <span class="glyphicon glyphicon-eye-open"></span>
                     </a>
                     <a href="edit_sale.php?id=<?php echo (int)$sale['id'];?>" class="btn btn-warning btn-xs"  title="Edit" data-toggle="tooltip">
                       <span class="glyphicon glyphicon-edit"></span>
                     </a>
                     <a href="delete_sale.php?id=<?php echo (int)$sale['id'];?>" class="btn btn-danger btn-xs"  title="Delete" data-toggle="tooltip">
                       <span class="glyphicon glyphicon-trash"></span>
                     </a>
                  </div>
               </td>
             </tr>
             <!-- Sale Details Modal -->
             <div class="modal fade" id="saleModal<?php echo (int)$sale['id'];?>" tabindex="-1" role="dialog">
               <div class="modal-dialog" role="document">
                 <div class="modal-content">
                   <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                     </button>
                     <h4 class="modal-title">Sale Details #<?php echo (int)$sale['id'];?></h4>
                   </div>
                   <div class="modal-body">
                     <table class="table table-bordered">
                       <tr>
                         <th>Date:</th>
                         <td><?php echo $sale['date']; ?></td>
                       </tr>
                       <tr>
                         <th>Payment Method:</th>
                         <td><?php echo ucfirst($sale['payment_method']); ?></td>
                       </tr>
                       <tr>
                         <th>Payment Status:</th>
                         <td><?php echo ucfirst($sale['payment_status']); ?></td>
                       </tr>
                     </table>

                     <h4>Products</h4>
                     <table class="table table-bordered table-striped">
                       <thead>
                         <tr>
                           <th>Product</th>
                           <th>Category</th>
                           <th class="text-center">Quantity</th>
                           <th class="text-center">Price</th>
                           <th class="text-center">Total</th>
                         </tr>
                       </thead>
                       <tbody>
                         <?php $sale_items = find_sale_details($sale['id']); 
                         foreach ($sale_items as $item): ?>
                         <tr>
                           <td><?php echo remove_junk($item['product_name']); ?></td>
                           <td><?php echo remove_junk($item['category_name']); ?></td>
                           <td class="text-center"><?php echo (int)$item['quantity']; ?></td>
                           <td class="text-center">$<?php echo number_format($item['price']/$item['quantity'], 2); ?></td>
                           <td class="text-center">$<?php echo number_format($item['price'], 2); ?></td>
                         </tr>
                         <?php endforeach; ?>
                       </tbody>
                       <tfoot>
                         <tr>
                           <td colspan="4" class="text-right"><strong>Total Amount</strong></td>
                           <td class="text-center">$<?php echo number_format($sale['price'], 2); ?></td>
                         </tr>
                       </tfoot>
                     </table>

                     <?php if(!empty($sale['notes'])): ?>
                     <div class="notes">
                       <strong>Notes:</strong>
                       <p><?php echo nl2br($sale['notes']); ?></p>
                     </div>
                     <?php endif; ?>
                   </div>
                   <div class="modal-footer">
                     <a href="generate_bill.php?sale_id=<?php echo (int)$sale['id'];?>" 
                        class="btn btn-info">Print Receipt</a>
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                   </div>
                 </div>
               </div>
             </div>
             <?php endforeach;?>
           </tbody>
         </table>
        </div>
      </div>
    </div>
  </div>
<?php include_once('layouts/footer.php'); ?>
