<div class="wrap">
    <h2 class="wpcube"><?php echo $this->plugin->displayName; ?> &raquo; <?php _e('Generate'); ?></h2>
           
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
    
    <!-- Form Start -->
	<form id="<?php echo $this->plugin->name; ?>-generate" name="post" method="post" action="admin.php?page=<?php echo $this->plugin->name; ?>-generate">
    	<div id="poststuff">
	    	<div id="post-body" class="metabox-holder columns-2">
	    		<!-- Content -->
	    		<div id="post-body-content">
		            <div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
		               	
		               	<!-- Content -->
		               	<div id="content-panel" class="postbox">
		                    <h3 class="hndle"><?php _e('Content', $this->plugin->name); ?></h3>
		                    
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Title', $this->plugin->name); ?></strong>
		                    		<input type="text" name="<?php echo $this->plugin->name; ?>[title]" value="<?php echo (isset($this->settings['title']) ? stripslashes($this->settings['title']) : ''); ?>" />
		                    	</p>
		                    	<ul class="tags">
	                            	<?php
	                            	if (count($keywords) > 0) {
	                            		// We have keywords - output
	                            		foreach ($keywords as $keyword) {
		                            		?>
		                            		<li><a href="#" title="<?php echo $keyword->keyword; ?>">{<?php echo $keyword->keyword; ?>}</a></li>
		                            		<?php
	                            		}	
	                            	}
	                            	?>
	                            </ul>
		                    </div>
		                    
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Permalink', $this->plugin->name); ?></strong>
		                    		<input type="text" name="<?php echo $this->plugin->name; ?>[permalink]" value="<?php echo (isset($this->settings['permalink']) ? stripslashes($this->settings['permalink']) : ''); ?>" />
		                    	</p>
		                    	<ul class="tags">
	                            	<?php
	                            	if (count($keywords) > 0) {
	                            		// We have keywords - output
	                            		foreach ($keywords as $keyword) {
		                            		?>
		                            		<li><a href="#" title="<?php echo $keyword->keyword; ?>">{<?php echo $keyword->keyword; ?>}</a></li>
		                            		<?php
	                            		}	
	                            	}
	                            	?>
	                            </ul>
		                    	<p class="description">
		                    		<?php _e('Letters, numbers, underscores and dashes only. If left blank, will be automatically generated.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                    
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Content', $this->plugin->name); ?></strong>
		                    	</p>
		                    	
		                    	<?php wp_editor((isset($this->settings['content']) ? stripslashes($this->settings['content']) : ''), 'content'); ?>
		                    	
		                    	<br />
		                    	<p class="description">
		                    		<?php _e('Remember to use your keywords to build dynamic content.  For example, if you have a city keywords, use {city} to have each Page output a unique city name.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                </div>
		                
		                <!-- Author -->
		                <div id="author-panel" class="postbox">    
		                	<h3 class="hndle"><?php _e('Author', $this->plugin->name); ?></h3>
		                	
		                    <div class="option">
		                    	<p>
		                    		<strong><?php _e('Rotate?', $this->plugin->name); ?></strong>
		                    		<input type="checkbox" name="<?php echo $this->plugin->name; ?>[rotateAuthors]" value="1"<?php echo (isset($this->settings['rotateAuthors']) ? ' checked' : ''); ?> data-condition="author" data-checked="hide" />
		                    	</p>
		                    	<br />
		                    	<p class="description">
		                    		<?php _e('If checked, will choose a WordPress User at random for each Page/Post generated.', $this->plugin->name); ?>
		                    	</p>
		                    </div>
		                    
		                    <div class="option author">
		                    	<p>
		                    		<strong><?php _e('Author', $this->plugin->name); ?></strong>
		                    		<select name="<?php echo $this->plugin->name; ?>[author]" size="1">
		                    			<?php
		                    			$authors = get_users(array(
						            		'orderby' => 'nicename',
						            	));
						            	if ($authors AND count($authors) > 0) {
						            		foreach ($authors as $author) {
						            			?>
						            			<option value="<?php echo $author->ID; ?>"<?php echo ((isset($this->settings['author']) AND $author->ID == $this->settings['author']) ? ' selected' : ''); ?>><?php echo $author->user_nicename; ?></option>
						            			<?php
						            		}
						            	}
		                    			?>	
		                    		</select>
		                    	</p>	
		                    </div>
		                </div>
		                
		                <!-- Discussion -->
		                <div id="discussion-panel" class="postbox">    
		                	<h3 class="hndle"><?php _e('Discussion', $this->plugin->name); ?></h3>
		                	
		                    <div class="option">
		                    	<p>
		                    		<label for="comments">
		                    			<input type="checkbox" id="comments" name="<?php echo $this->plugin->name; ?>[comments]" value="1"<?php echo (isset($this->settings['comments']) ? ' checked' : ''); ?> />
		                    			<?php _e('Allow comments.', $this->plugin->name); ?>
		                    		</label>
		                    	</p>
		                    </div>
		                    <div class="option">
		                    	<p>
		                    		<label for="trackbacks">
		                    			<input type="checkbox" id="trackbacks" name="<?php echo $this->plugin->name; ?>[trackbacks]" value="1"<?php echo (isset($this->settings['trackbacks']) ? ' checked' : ''); ?> />
		                    			<?php _e('Allow trackbacks and pingbacks.', $this->plugin->name); ?>
		                    		</label>
		                    	</p>
		                    </div>
		                </div>
					</div>
					<!-- /normal-sortables -->
	    		</div>
	    		<!-- /post-body-content -->
	    		
	    		<!-- Sidebar -->
	    		<div id="postbox-container-1" class="postbox-container">
	    			 <div class="postbox">
	                    <h3 class="hndle"><?php _e('Publish', $this->plugin->name); ?></h3>
	                    
	                    <div class="option">
	                    	<p>
	                    		<strong><?php _e('Status', $this->plugin->name); ?></strong>
	                    		<select name="<?php echo $this->plugin->name; ?>[status]" size="1">
	                    			<option value="draft"<?php echo ((isset($this->settings['status']) AND $this->settings['status'] == 'draft') ? ' selected' : ''); ?>><?php _e('Draft', $this->plugin->name); ?></option>
	                    			<option value="publish"<?php echo ((isset($this->settings['status']) AND $this->settings['status'] == 'publish') ? ' selected' : ''); ?>><?php _e('Publish', $this->plugin->name); ?></option>
	                    		</select>
	                    	</p>
	                    </div>
	                    
	                    <div class="option">
	                    	<p>
	                    		<strong><?php _e('No. Posts', $this->plugin->name); ?></strong>
	                    		<input type="number" name="<?php echo $this->plugin->name; ?>[numberOfPosts]" value="<?php echo (isset($this->settings['numberOfPosts']) ? $this->settings['numberOfPosts'] : ''); ?>" step="1" min="1" max="999" />
							</p>
							<br />
							<p class="description">
								<?php _e('The number of Posts/Pages to generate.', $this->plugin->name); ?>
							</p>
	                    </div>
	                </div>	
	                
	                <!-- Template -->
	                <div class="postbox template page">
	                    <h3 class="hndle"><?php _e('Page Template', $this->plugin->name); ?></h3>
	                    
	                    <div class="option">
	                    	<strong><?php _e('Template', $this->plugin->name); ?></strong>
	                    	<select name="<?php echo $this->plugin->name; ?>[pageTemplate]" size="1">
	                    		<option value="default"<?php echo ((isset($this->settings['pageTemplate']) AND $this->settings['pageTemplate'] == 'default') ? ' selected' : ''); ?>><?php _e('Default Template'); ?></option>
	                			<?php page_template_dropdown($this->settings['pageTemplate']); ?>
	                		</select>
	                	</div>
	                </div>
	                
					<!-- Save Options -->
					<div class="postbox">
	                    <h3 class="hndle"><?php _e('Save', $this->plugin->name); ?></h3>
	                    
	                    <div class="option">
			                <input type="submit" name="submit" value="<?php _e('Save', $this->plugin->name); ?>" class="button button-primary" data-action="save" /> 
			            	<input type="submit" name="submit" value="<?php _e('Test', $this->plugin->name); ?>" class="button button-primary" data-action="test" /> 
			           		<input type="submit" name="submit" value="<?php _e('Generate', $this->plugin->name); ?>" class="button button-primary" data-action="generate" /> 
			            </div>
			        </div>
			    </div>
	    		<!-- /postbox-container -->
	    	</div>
		</div>  
	
	</form>
	<!-- /form end --> 
	
	<!-- Upgrade -->
	<div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-1">
    		<div id="post-body-content">
    			<?php require_once($this->plugin->folder.'/_modules/dashboard/views/footer-upgrade.php'); ?>
    		</div>
    	</div>
    </div>     
</div>