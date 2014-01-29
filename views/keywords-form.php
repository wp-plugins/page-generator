<div class="wrap">
    <h2 class="wpcube"><?php echo $this->plugin->displayName; ?> &raquo; <?php _e('Keywords'); ?></h2>
           
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
    	<div id="post-body" class="metabox-holder columns-1">
    		<!-- Content -->
    		<div id="post-body-content">
    			<!-- Form Start -->
		        <form id="post" name="post" method="post" action="admin.php?page=<?php echo $this->plugin->name; ?>&cmd=<?php echo (isset($_GET['cmd']) ? $_GET['cmd'] : 'add'); ?>&pKey=<?php echo (isset($_GET['pKey']) ? $_GET['pKey'] : ''); ?>" enctype="multipart/form-data">		
	    		    <div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
		                <!-- ID is used by tabbed interface, and must match the href attribute of the tab + -panel -->
		                <div id="keyword-panel" class="postbox">
		                    <h3 class="hndle"><?php _e('Keyword', $this->plugin->name); ?></h3>
		                    <input type="hidden" name="<?php echo $this->plugin->name; ?>[pKey]" id="pKey" value="<?php echo (isset($_GET['pKey']) ? $_GET['pKey'] : ''); ?>" />
	
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Keyword', $this->plugin->name); ?></strong>
		                    		<input type="text" name="<?php echo $this->plugin->name; ?>[keyword]" value="<?php echo (isset($this->keyword['keyword']) ? $this->keyword['keyword'] : ''); ?>" />
		                    	</p>
		                    	<p class="description">
		                    		<?php _e('A unique template tag name, which can then be used when generating content.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                    
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Keyword Data', $this->plugin->name); ?></strong>
		                    		<textarea name="<?php echo $this->plugin->name; ?>[data]" style="height:300px"><?php echo (isset($this->keyword['data']) ? $this->keyword['data'] : ''); ?></textarea>
		                    	</p>
		                    	<p class="description">
		                    		<?php _e('Word(s) or phrase(s) which will be cycled through when generating content using the above keyword template tag.', $this->plugin->name); ?>
		                    		<br />
		                    		<?php _e('One word / phase per line.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                    
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Data Import', $this->plugin->name); ?></strong>
		                    		<input type="file" name="file" />
		                    	</p>
		                    	<p class="description">
		                    		<?php _e('To mass import data, upload either a CSV file (format word1,word2,word3) or TXT file (one word / phrase per line).', $this->plugin->name); ?>
									<br />
									<?php _e('This will append the imported words / phrases to the above Keyword Data.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                    
		                    <div class="option">
		                    	<p>
		                    		<input type="submit" name="submit" value="<?php _e('Save', $this->plugin->name); ?>" class="button button-primary" />
		                    	</p>
		                    </div>
		                </div>
					</div>
					<!-- /normal-sortables -->
			    </form>
			    <!-- /form end -->
    		</div>
    		<!-- /post-body-content -->
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