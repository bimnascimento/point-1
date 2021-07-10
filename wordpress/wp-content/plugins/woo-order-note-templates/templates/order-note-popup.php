<?php
/**
* 
*/
class wont_gyrix_display_popup
{	
	function __construct()
	{
		$this->wont_gyrix_loadScript();
	}
	function wont_gyrix_loadScript()
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-dialog');
	}
	function wont_gyrix_display_popup($templates)
	{		
		?>
		<div id="dialog" title="<?php _e('Add Order Note', 'woocommerce'); ?>" style="display:none;">
		
	    <div class="add_note" style="border:none;">  
	    <form method="POST" name="add_notes">  
	        <p>
	            <label>Customer Name:</label>
	            <input type="text" name="customer_name" id="customer_name" style="width:250px;" readonly="readonly" required>
	            <input type="hidden" id = "added_note_type" value="">
	        </p>
	        <p>
	            <label>Order Id:</label>
	            <input type="text" name="order_id" id="order_post_id" style="width:250px;" readonly="readonly" required>
	        </p>
	        <p>
	            <label>Order Note Type:</label>
	            <select name="order_note_type" id="add_order_note_type" style="width:250px;" required>
	            	<option value="">Please select the order note type</option>
	                <?php if($templates) 
	                    {
	                      foreach($templates as $index => $template) : ?>
	                      <option value="<?php if($template['type'] == 'customer') echo 'customer'; else echo'private'; ?>" id="<?php echo $index;?>"><?php echo esc_html($template["title"]); ?></option>
	                      <?php endforeach; 
	                    }
	                    else
	                    { ?>
	                        <option value="customer" id="0">Customer Note</option>
	                        <option value="private" id="1">Private Note</option>
	                    <?php } ?> 
	            </select>	
	            <span class="error_note" style="display: none;">* Required field.</span>           
	        </p>
	        <p>
	            <label>Order Note:</label>		            
	            <textarea type="text" name="order_note" id="add_order_note_text" class="input-text" cols="20" rows="6" required></textarea>
	        	<span class="error_note_txt" style="display: none;">* Required field.</span>
	        </p>
	        <p id="gyrix_default_content">
	            <?php if($templates)
	            {
	                foreach($templates as $index => $template) : ?>
	                <textarea type="text" id="<?php echo 'gyrix_content-'.$index; ?>" required><?php echo esc_textarea($template["content"]); ?></textarea>
	                <?php endforeach;
	            }?> 
	        </p>		        
        </form>
	    </div>	
	    <div class="wont_overlay_note" style="display:none;"></div>
	    <span ><img style="display:none;" class ="wont_img_loader" src="<?php echo WONT_GYRIXTEMPLATEURL; ?>admin/image/loading_icon.gif" ></span>    
	</div>
	<?php
	}
}