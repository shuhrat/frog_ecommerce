<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<script type="text/javascript">
window.onload = function() {
	var sBasePath = '/frog/plugins/fckeditor/fckeditor/' ;
	var oFCKeditor = new FCKeditor('product[description]') ;
	oFCKeditor.BasePath = sBasePath;
	oFCKeditor.Width = '600px';
	oFCKeditor.Height = '300px';
	oFCKeditor.ReplaceTextarea() ;
}
</script>

<h1><?php echo __('Edit Product'); ?></h1>

<form action="<?php echo get_url('plugin/ecommerce/product_update/'.$product->id); ?>" method="post">
	<div class="ec-form-area">
		<span id="product_id" style="display: none;"><?php echo $product->id; ?></span>
		
		<p>
			<label for="title"><?php echo __('Title'); ?></label>
			<input class="textbox" type="text" name="product[title]" id="product_title" size="35" maxlength="50" value="<?php echo htmlentities($product->title, ENT_COMPAT, 'UTF-8'); ?>" />
		</p>
		
		<p>
			<label for="slug"><?php echo __('Slug'); ?></label>
			<input class="textbox" type="text" name="product[slug]" id="product_slug" size="35" value="<?php echo htmlentities($product->slug, ENT_COMPAT, 'UTF-8'); ?>" /> 
			<a  href="javascript:;" onclick="$('whatisslug').toggle();">?</a>
			
			<div id="whatisslug" class="echelp" style="display: none;">
				<h3>What is a slug?</h3>
				<p>The slug is used to access the product in the URL. It is by default the product's title in lowercase. A product with the title "Nice Shirt" would automatically get the slug 'nice-shirt'.</p>
				<p>An example URL using the product would be http://www.example.com/products/nice-shirt</p>
				<p>A URL like this will greatly improve Search Engine Optimization.</p>
				<p><a href="javascript:;" onclick="$('whatisslug').toggle();">Close</a></p>
			</div>
		</p>
		
		<p>
			<label for="description"><?php echo __('Description'); ?></label>
			<textarea class="textarea" cols="40" id="product_description" name="product[description]" rows="10" style="width: 500px; height: 100px;"><?php echo htmlentities($product->description, ENT_COMPAT, 'UTF-8'); ?></textarea>
		</p>
		
		<p>
			<label for="type_id"><?php echo __('Type'); ?></label>
			<select name="product[type_id]" id="product_type_id">
				<option value="">-- Choose Type --</option>
				<?php foreach ($types as $type) : ?>
				<option value="<?php echo $type->id; ?>" <?php if ($type->id == $product->type_id) : ?>selected="1"<?php endif; ?>><?php echo $type->title; ?></option>
				<?php endforeach; ?>
				<option value="">--------</option>
				<option value="create_new">Create new type...</option>
			</select>
			
			<input id="product_type_title" name="product_type[title]" size="20" style="display: none;" type="text" value="" />
			
			<a  href="javascript:void(0);" onclick="$('whatistype').toggle();">?</a>
			
			<div id="whatistype" class="echelp" style="display: none;">
				<h3>What is a product type?</h3>
				<p>A product type is the kind of product it is. Examples types could be T-Shirts, Jeans or Hats.</p>
				<p><a href="javascript:void(0);" onclick="$('whatistype').toggle();">Close</a></p>
			</div>
		</p>
		
		<p class="last">
			<label for="vendor_id"><?php echo __('Vendor'); ?></label>
			<select name="product[vendor_id]" id="product_vendor_id">
				<option value="">-- Choose Vendor --</option>
				<?php foreach ($vendors as $vendor) : ?>
				<option value="<?php echo $vendor->id; ?>" <?php if ($vendor->id == $product->vendor_id) : ?>selected="1"<?php endif; ?>><?php echo $vendor->title; ?></option>
				<?php endforeach; ?>
				<option value="">--------</option>
				<option value="create_new">Create new vendor...</option>
			</select>
			
			<input id="product_vendor_title" name="product_vendor[title]" size="20" style="display: none;" type="text" value="" />
			
			<a  href="javascript:;" onclick="$('whatisvendor').toggle();">?</a>
			
			<div id="whatisvendor" class="echelp" style="display: none;">
				<h3>What is a product vendor?</h3>
				<p>A product vendor is the maker of the product.</p>
				<p><a href="javascript:;" onclick="$('whatisvendor').toggle();">Close</a></p>
			</div>
		</p>
		
		<p>
			<label for="is_published"><?php echo __('Visibility'); ?></label>
			<select name="product[is_published]" id="product_is_published">
				<option value="1" <?if ($product->is_published) : ?>selected="1"<?php endif; ?>>Published</option>
				<option value="0" <?if (!$product->is_published) : ?>selected="1"<?php endif; ?>>Hidden</option>
			</select>
			
			<a  href="javascript:;" onclick="$('published').toggle();">?</a>
			
			<div id="published" class="echelp" style="display: none;">
				<h3>Product Visibility</h3>
				<p>A product is visible on the website by default. Choose 'Hidden' if you do not want the product to be visible on the website.</p>
				<p><a href="javascript:;" onclick="$('published').toggle();">Close</a></p>
			</div>
		</p>
		
		<!-- tags -->
		<fieldset>
			<legend>Tags</legend>
			
			<p style="border: none;">Click in the area below to add a tag. Click on an existing tag to edit it. Press the enter key or , to start a new tag.</p>
			
			<div id="tagger">
				<input type="text" name="product[tags]" id="tags" value="<?php echo htmlentities($product->tags, ENT_COMPAT, 'UTF-8'); ?>" />
			</div>
		</fieldset>
		<!-- /tags -->
	</div>
	
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
		<?php echo __('or'); ?> <a href="<?php echo get_url('plugin/ecommerce/product'); ?>"><?php echo __('Cancel'); ?></a>
	</p>
	<br />
</form>

<div class="ec-form-area">	
	<!-- variations -->
	<fieldset>
		<legend>Variations</legend> 
		<div id="vspinner"  style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></div>
		
		<div id="product_variants">
			<?php if ($variants) : ?>
			<?php foreach ($variants as $variant) : ?>
			<div id="variant_<?php echo $variant->id; ?>">
				<div class="tools" style="display: none;">
					<a href="#" onclick="variant_delete(<?php echo $variant->id; ?>);return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="variant_form_toggle(<?php echo $variant->id; ?>);return false;">Edit</a>
				</div>
				
				<h3><span id="variant_title_<?php echo $variant->id; ?>"><?php echo $variant->title; ?></span> <img class="drag_handle" src="/pilot/images/drag_to_sort.gif" width="55" height="11" /></h3>
				<div id="variant_description_<?php echo $variant->id; ?>"><?php echo $variant->description; ?></div>
				<table>
					<tr>
						<th>Price</th>
						<th>SKU</th>
						<th>Quantity</th>
						<th>Weight</th>
					</tr>
					<tr>
						<td id="variant_price_<?php echo $variant->id; ?>">$<?php echo $variant->price; ?></td>
						<td id="variant_sku_<?php echo $variant->id; ?>"><?php echo $variant->sku; ?></td>
						<td id="variant_quantity_<?php echo $variant->id; ?>"><?php echo $variant->quantity; ?></td>
						<td id="variant_weight_<?php echo $variant->id; ?>"><?php echo $variant->weight; ?> lbs.</td>
					</tr>
				</table>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox('variant_<?php echo $variant->id; ?>');
			</script>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<div id="variant_forms">
			<?php foreach ($variants as $variant) : ?>
			<div id="variant_form_<?php echo $variant->id; ?>" class="form" style="display: none;">
				<form method="post" action="/pilot/plugin/ecommerce/variant_update/<?php echo $variant->id; ?>" onsubmit="variant_update(<?php echo $variant->id; ?>);return false;">
				<p>
					<label for="title"><?php echo __('Title'); ?></label>
					<input id="variant_form_title_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[title]" id="variant_title" size="30" value="<?php echo htmlentities($variant->title, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p>
					<label for="description"><?php echo __('Description'); ?></label>
					<input id="variant_form_description_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[description]" id="variant_description" size="30" maxlength="255" value="<?php echo htmlentities($variant->description, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p>
					<label for="price"><?php echo __('Price'); ?></label>
					<input id="variant_form_price_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[price]" id="variant_price" size="8" value="<?php echo htmlentities($variant->price, ENT_COMPAT, 'UTF-8'); ?>" /> USD
				</p>
								
				<p>
					<label for="weight"><?php echo __('Weight'); ?></label>
					<input id="variant_form_weight_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[weight]" id="variant_weight" size="8"  value="<?php echo htmlentities($variant->weight, ENT_COMPAT, 'UTF-8'); ?>" /> lbs
				</p>
				
				<p>
					<label for="sku"><abbr title="<?php echo __('Stock Keeping Unit'); ?>"><?php echo __('SKU'); ?></abbr></label>
					<input id="variant_form_sku_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[sku]" id="variant_sku" size="35" value="<?php echo htmlentities($variant->sku, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p>
					<label for="quantity"><?php echo __('Quantity'); ?></label>
					<input id="variant_form_quantity_<?php echo $variant->id; ?>" class="textbox" type="text" name="product_variant[quantity]" id="variant_quantity" size="5" value="<?php echo htmlentities($variant->quantity, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="variant_form_cancel(<?php echo $variant->id; ?>);return false;">Cancel</a>
				</p>
				</form>
			</div>
			<?php endforeach; ?>
		</div>
		
		<!-- add variant form -->
		<div id="add_variant_form" style="display: none;">
		<form name="variant_create_form" id="variant_create_form" method="post" action="/pilot/plugin/ecommerce/variant_create" onsubmit="variant_create();return false;">
			<input type="hidden" name="product_variant[product_id]" value="<?php echo $product->id; ?>" />
			<p>
				<label for="title"><?php echo __('Title'); ?></label>
				<input id="variant_add_form_title" class="textbox" type="text" name="product_variant[title]" id="variant_title" size="30" />
			</p>
			
			<p>
				<label for="description"><?php echo __('Description'); ?></label>
				<input id="variant_add_form_description" class="textbox" type="text" name="product_variant[description]" id="variant_description" size="30" maxlength="255" />
			</p>
			
			<p>
				<label for="price"><?php echo __('Price'); ?></label>
				<input id="variant_add_form_price" class="textbox" type="text" name="product_variant[price]" id="variant_price" size="8" /> USD
			</p>
							
			<p>
				<label for="weight"><?php echo __('Weight'); ?></label>
				<input id="variant_add_form_weight" class="textbox" type="text" name="product_variant[weight]" id="variant_weight" size="8" /> lbs
			</p>
			
			<p>
				<label for="sku"><abbr title="<?php echo __('Stock Keeping Unit'); ?>"><?php echo __('SKU'); ?></abbr></label>
				<input id="variant_add_form_sku" class="textbox" type="text" name="product_variant[sku]" id="variant_sku" size="35" />
			</p>
			
			<p>
				<label for="quantity"><?php echo __('Quantity'); ?></label>
				<input id="variant_add_form_quantity" class="textbox" type="text" name="product_variant[quantity]" id="variant_quantity" size="5" />
			</p>
			<p class="last">
				<input name="commit" type="submit" value="Save"> or <a href="#" onclick="variant_add_form_cancel();return false;">Cancel</a>
			</p>
		</form>
		</div>
		<!-- /add variant form -->
		
		<p class="clear" id="add_variant_button"><a class="button" href="#" onclick="variant_add_form();return false;"><span><?php echo __('Add New Variation'); ?></span></a></p>
	</fieldset>
	<!-- /variations -->
	
	<!-- upload images -->
	<fieldset id="upload">
		<legend>Images</legend>
		
		<?php if ($images) : ?>
		<p>Drag these images to sort them. The first in the list will be the featured image and will be the most prominent one on its product page.</p>
		
		<div id="change_notification"></div>
		
		<div id="product_images">
			<?php foreach ($images as $image) : ?>
			<div class="sorting" id="image_<?php echo $image->id; ?>">
				<img src="/public/ecommerce/images/products/<?php echo str_replace('.','_tn.',$image->filename); ?>" width="100" height="100" /><br />
				<a class="delete" href="#" onclick="image_delete(<?php echo $image->id; ?>);return false;">Delete</a>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		
		<p class="clear"><a class="button" href="<?php echo get_url('plugin/ecommerce/product_create'); ?>" onclick="this.blur();"><span id="upload_button"><?php echo __('Upload Image'); ?></span></a>
			<span id="ispinner" style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></span>
		</p>
		<ol class="files"></ol>
	</fieldset>
	<!-- /upload images -->
	
	<!-- files -->
	<fieldset>
		<legend>Files</legend> 
		<div id="fspinner"  style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></div>
		
		<p><?php echo __('You can add product files for the consumer to download.'); ?></p>
		
		<div id="product_files">
			<?php if ($files) : ?>
			<?php foreach ($files as $file) : ?>
			<div id="file_<?php echo $file->id; ?>">
				<div class="tools" style="display: none;">
					<a href="#" onclick="file_delete(<?php echo $file->id; ?>);return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="file_form_toggle(<?php echo $file->id; ?>);return false;">Edit</a>
				</div>
				
				<h3><span id="file_title_<?php echo $file->id; ?>"><?php echo $file->title; ?></span> <img class="drag_handle" src="/pilot/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox('file_<?php echo $file->id; ?>');
			</script>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		
		<div id="file_forms">
			<?php foreach ($files as $file) : ?>
			<div id="file_form_<?php echo $file->id; ?>" class="form" style="display: none;">
				<form method="post" action="/pilot/plugin/ecommerce/file_update/<?php echo $file->id; ?>" onsubmit="file_update(<?php echo $file->id; ?>);return false;">
				<p>
					<label for="title"><?php echo __('Title'); ?></label>
					<input id="file_form_title_<?php echo $file->id; ?>" class="textbox" type="text" name="product_file[title]" id="file_title" size="30" value="<?php echo htmlentities($file->title, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p>
					<label for="filename"><?php echo __('File'); ?></label>
					<input id="file_form_filename_<?php echo $file->id; ?>" class="textbox" type="text" name="product_file[filename]" size="35" value="<?php echo htmlentities($file->filename, ENT_COMPAT, 'UTF-8'); ?>" />
					<a href="#" onclick="browse_server('file_form_filename_<?php echo $file->id; ?>');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
				</p>
				
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="file_form_cancel(<?php echo $file->id; ?>);return false;">Cancel</a>
				</p>
				</form>
			</div>
			<?php endforeach; ?>
		</div>
		
		<!-- add file form -->
		<div id="add_file_form" style="display: none;">
		<form name="file_create_form" id="file_create_form" method="post" action="/pilot/plugin/ecommerce/file_create" onsubmit="file_create();return false;">
			<input type="hidden" name="product_file[product_id]" value="<?php echo $product->id; ?>" />
			<p>
				<label for="title"><?php echo __('Title'); ?></label>
				<input id="file_add_form_title" class="textbox" type="text" name="product_file[title]" id="file_title" size="30" />
			</p>
			
			<p>
				<label for="filename"><?php echo __('File'); ?></label>
				<input id="file_add_form_filename" class="textbox" type="text" name="product_file[filename]" size="30" maxlength="50" />
				<a href="#" onclick="browse_server('file_add_form_filename');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
			</p>
			
			<p class="last">
				<input name="commit" type="submit" value="Save"> or <a href="#" onclick="file_add_form_cancel();return false;">Cancel</a>
			</p>
		</form>
		</div>
		<!-- /add file form -->
		
		<p class="clear" id="add_file_button"><a class="button" href="#" onclick="file_add_form();return false;"><span><?php echo __('Add New File'); ?></span></a></p>
	</fieldset>
	<!-- /files -->
	
	<!-- videos -->
	<fieldset>
		<legend>Videos</legend> 
		<div id="vispinner" style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></div>
		
		<p><?php echo __('You can add videos (.flv, .swf) for the consumer to watch.'); ?></p>
		
		<div id="product_videos">
			<?php if ($videos) : ?>
			<?php foreach ($videos as $video) : ?>
			<div id="video_<?php echo $video->id; ?>">
				<div class="tools" style="display: none;">
					<a href="#" onclick="video_delete(<?php echo $video->id; ?>);return false;"><img alt="Trash" src="/frog/plugins/ecommerce/images/trash.gif" /></a>&nbsp;
					<a href="#" onclick="video_form_toggle(<?php echo $video->id; ?>);return false;">Edit</a>
				</div>
				
				<h3><span id="video_title_<?php echo $video->id; ?>"><?php echo $video->title; ?></span> <img class="drag_handle" src="/pilot/images/drag_to_sort.gif" width="55" height="11" /></h3>
			</div>
			<script type="text/javascript" language="javascript" charset="utf-8">
			new ToolBox('video_<?php echo $video->id; ?>');
			</script>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<div id="video_forms">
			<?php foreach ($videos as $video) : ?>
			<div id="video_form_<?php echo $video->id; ?>" class="form" style="display: none;">
				<form method="post" action="/pilot/plugin/ecommerce/video_update/<?php echo $video->id; ?>" onsubmit="video_update(<?php echo $video->id; ?>);return false;">
				<p>
					<label for="title"><?php echo __('Title'); ?></label>
					<input id="video_form_title_<?php echo $video->id; ?>" class="textbox" type="text" name="product_video[title]" id="video_title" size="30" value="<?php echo htmlentities($video->title, ENT_COMPAT, 'UTF-8'); ?>" />
				</p>
				
				<p>
					<label for="filename"><?php echo __('Video'); ?></label>
					<input id="video_form_filename_<?php echo $video->id; ?>" class="textbox" type="text" name="product_video[filename]" id="video_filename" size="35" value="<?php echo htmlentities($video->filename, ENT_COMPAT, 'UTF-8'); ?>" />
					<a href="#" onclick="browse_server('video_form_filename_<?php echo $video->id; ?>');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
				</p>
				
				<p class="last">
					<input name="commit" type="submit" value="Save"> or <a href="#" onclick="video_form_cancel(<?php echo $video->id; ?>);return false;">Cancel</a>
				</p>
				</form>
			</div>
			<?php endforeach; ?>
		</div>
		
		<!-- add video form -->
		<div id="add_video_form" style="display: none;">
		<form name="video_create_form" id="video_create_form" method="post" action="/pilot/plugin/ecommerce/video_create" onsubmit="video_create();return false;">
			<input type="hidden" name="product_video[product_id]" value="<?php echo $product->id; ?>" />
			<p>
				<label for="title"><?php echo __('Title'); ?></label>
				<input id="video_add_form_title" class="textbox" type="text" name="product_video[title]" id="video_title" size="30" />
			</p>
			
			<p>
				<label for="filename"><?php echo __('Video'); ?></label>
				<input id="video_add_form_filename" class="textbox" type="text" name="product_video[filename]" size="30" maxlength="50" />
				<a href="#" onclick="browse_server('video_add_form_filename');return false;"><img src="/frog/plugins/ecommerce/images/file_manager_16x16.gif" width="16" height="16" /></a>
			</p>
			
			<p class="last">
				<input name="commit" type="submit" value="Save"> or <a href="#" onclick="video_add_form_cancel();return false;">Cancel</a>
			</p>
		</form>
		</div>
		<!-- /add video form -->
		
		<p class="clear" id="add_video_button"><a class="button" href="#" onclick="video_add_form();return false;"><span><?php echo __('Add New Video'); ?></span></a></p>
	</fieldset>
	<!-- /videos -->
</div>