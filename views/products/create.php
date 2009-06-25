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

<h1><?php echo __('Add Product'); ?></h1>

<form action="<?php echo get_url('plugin/ecommerce/product_create'); ?>" method="post" enctype="multipart/form-data">
	<div class="ec-form-area">
		<p>
			<label for="title"><?php echo __('Title'); ?></label>
			<input class="textbox" type="text" name="product[title]" id="product_title" size="35" />
		</p>
		
		<p>
			<label for="slug"><?php echo __('Slug'); ?></label>
			<input class="textbox" type="text" name="product[slug]" id="product_slug" size="35" /> 
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
			<textarea class="textarea" cols="40" id="product_description" name="product[description]" rows="30" style="width: 500px; height: 250px;"></textarea>
		</p>
		
		<p>
			<label for="type_id"><?php echo __('Type'); ?></label>
			<select name="product[type_id]" id="product_type_id">
				<option value="">-- Choose Type --</option>
				<?php foreach ($types as $type) : ?>
				<option value="<?php echo $type->id; ?>"><?php echo $type->title; ?></option>
				<?php endforeach; ?>
				<option value="">--------</option>
				<option value="create_new">Create new type...</option>
			</select>
			
			<input id="product_type_title" name="product_type[title]" size="20" style="display: none;" type="text" value="" />
			<input id="product_type_slug" name="product_type[slug]" type="hidden" />
			
			<a  href="javascript:void(0);" onclick="$('whatistype').toggle();">?</a>
			
			<div id="whatistype" class="echelp" style="display: none;">
				<h3>What is a product type?</h3>
				<p>A product type is the kind of product it is. Examples types could be T-Shirts, Jeans or Hats.</p>
				<p><a href="javascript:void(0);" onclick="$('whatistype').toggle();">Close</a></p>
			</div>
		</p>
		
		<p>
			<label for="vendor_id"><?php echo __('Vendor'); ?></label>
			<select name="product[vendor_id]" id="product_vendor_id">
				<option value="">-- Choose Vendor --</option>
				<?php foreach ($vendors as $vendor) : ?>
				<option value="<?php echo $vendor->id; ?>"><?php echo $vendor->title; ?></option>
				<?php endforeach; ?>
				<option value="">--------</option>
				<option value="create_new">Create new vendor...</option>
			</select>
			
			<input id="product_vendor_title" name="product_vendor[title]" size="20" style="display: none;" type="text" value="" />
			<input id="product_vendor_slug" name="product_vendor[slug]" type="hidden" />
			
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
				<option value="1">Published</option>
				<option value="0">Hidden</option>
			</select>
			
			<a  href="javascript:;" onclick="$('published').toggle();">?</a>
			
			<div id="published" class="echelp" style="display: none;">
				<h3>Product Visibility</h3>
				<p>A product is visible on the website by default. Choose 'Hidden' if you do not want the product to be visible on the website.</p>
				<p><a href="javascript:;" onclick="$('published').toggle();">Close</a></p>
			</div>
		</p>
		
		<!-- variant -->
		<fieldset>
			<legend>Default Variation</legend>
			
			<p>
				<label for="title"><?php echo __('Title'); ?></label>
				<input class="textbox" type="text" name="product_variant[title]" id="variant_title" size="35" maxlength="255" value="Default" /> 
			</p>
			
			<p>
				<label for="description"><?php echo __('Description'); ?></label>
				<input class="textbox" type="text" name="product_variant[description]" id="variant_description" size="35" maxlength="255" /> 
			</p>
			
			<p>
				<label for="price"><?php echo __('Price'); ?></label>
				<input class="textbox" type="text" name="product_variant[price]" id="variant_price" size="8" value="0.00" /> USD
			</p>
			
			<p>
				<label for="weight"><?php echo __('Weight'); ?></label>
				<input class="textbox" type="text" name="product_variant[weight]" id="variant_weight" size="8" value="0.0" /> lbs
			</p>
			
			<p>
				<label for="sku"><abbr title="<?php echo __('Stock Keeping Unit'); ?>"><?php echo __('SKU'); ?></abbr></label>
				<input class="textbox" type="text" name="product_variant[sku]" id="variant_sku" size="35" />
			</p>
			
			<p>
				<label for="quantity"><?php echo __('Quantity'); ?></label>
				<input class="textbox" type="text" name="product_variant[quantity]" id="variant_quantity" size="5" />
				
				<a  href="javascript:;" onclick="$('quantity').toggle();">?</a>
				
				<div id="quantity" class="echelp" style="display: none;">
					<h3>Quantity</h3>
					<p>You can specify how many of this product you have in stock. The product will no longer be available to buy if you set a quantity and sell that amount. Simply leave it blank if you do not want to keep up with quantities.</p>
					<p><a href="javascript:;" onclick="$('quantity').toggle();">Close</a></p>
				</div>
			</p>
		</fieldset>
		
		<!-- tags -->
		<fieldset>
			<legend>Tags</legend>
			
			<p style="border: none;">Click in the area below to add a tag. Click on an existing tag to edit it. Press the enter key or , to start a new tag.</p>
			
			<div id="tagger">
				<input type="text" name="product[tags]" id="tags" value="" />
			</div>
		</fieldset>
		<!-- /tags -->
		
		<!-- upload images -->
		<fieldset id="upload">
			<legend>Images</legend>
			<p class="clear"><a class="button" style="width: 105px;" href="<?php echo get_url('plugin/ecommerce/product_create'); ?>" onclick="this.blur();"><span id="upload_button"><?php echo __('Upload Image'); ?></span></a>
				<span id="ispinner" style="display:none;"><img src="../frog/plugins/ecommerce/images/loading_indicator.gif" /></span>
			</p>
			<ol class="files"></ol>
		</fieldset>
		<!-- /upload images -->
	</div>
	
	<p class="buttons">
		<input class="button" name="commit" type="submit" accesskey="s" value="<?php echo __('Save'); ?>" />
		<?php echo __('or'); ?> <a href="<?php echo get_url('plugin/ecommerce/product'); ?>"><?php echo __('Cancel'); ?></a>
	</p>
	
</form>