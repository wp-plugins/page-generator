<div class="wrap">
	<h2 class="wpcube">
    	<?php echo $this->plugin->displayName; ?> &raquo; <?php _e('Keywords'); ?>
    	<a href="admin.php?page=<?php echo $this->plugin->name; ?>&cmd=add" class="add-new-h2"><?php _e('Add Keyword', $this->plugin->name); ?></a>
    	
    	<?php
	    // Search Subtitle
	    if (isset($_REQUEST['s']) AND !empty($_REQUEST['s'])) {
	    	?>
	    	<span class="subtitle"><?php _e('Search results for', $this->plugin->name); ?> &#8220;<?php echo urldecode($_REQUEST['s']); ?>&#8221;</span>
	    	<?php
	    }
	    ?>
    </h2>
           
    <?php    
    if (isset($this->message)) {
        ?>
        <div class="updated fade"><p><?php echo $this->message; ?></p></div>  
        <?php
    }
    if (isset($this->errorMessage)) {
        ?>
        <div class="error fade"><p><?php echo $this->errorMessage; ?></p></div>  
        <?php
    }
    ?> 
    
    <div id="poststuff">
	    <div id="post-body" class="metabox-holder columns-2">
	    	<!-- Content -->
	    	<div id="post-body-content">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<form action="admin.php?page=<?php echo $this->plugin->name; ?>" method="post">
						<p class="search-box">
					    	<label class="screen-reader-text" for="post-search-input"><?php _e('Search Keywords'); ?>:</label>
					    	<input type="text" id="field-search-input" name="s" value="<?php echo (isset($_REQUEST['s']) ? $_REQUEST['s'] : ''); ?>" />
					    	<input type="submit" name="search" class="button" value="<?php _e('Search Keywords'); ?>" />
					    </p>
					    
						<?php   
						$this->wpListTable->prepare_items();
						$this->wpListTable->display(); 
						?>	
					</form>
				</div>
			</div>
			
			<!-- Sidebar -->
			<div id="postbox-container-1" class="postbox-container">
				<?php require_once($this->plugin->folder.'/_modules/dashboard/views/sidebar-upgrade.php'); ?>		
	    	</div>
	    	<!-- /postbox-container -->
		</div>
	</div>
	
	<!-- Upgrade -->
	<div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-1">
    		<div id="post-body-content">
    			<?php require_once($this->plugin->folder.'/_modules/dashboard/views/footer-upgrade.php'); ?>
    		</div>
    	</div>
    </div> 
</div>