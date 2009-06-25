//include ckfinder file picker
document.write('<script type="text/javascript" src="/public/ckfinder/ckfinder.js"></scr' + 'ipt>'); 

var editedField;
function browse_server(field) {
	editedField = field;
	CKFinder.Popup( '/public/ckfinder', null, null, set_file_field );
}

function set_file_field(fileUrl) {
	document.getElementById( editedField ).value = fileUrl;
}

document.observe('dom:loaded', function() { 
	//generate product slug
	when('product_title', function(title) {
		var slug = $('product_slug'),
		oldTitle = title.value;
		
		new Form.Element.Observer(title, 0.15, function() {
		if (oldTitle.toSlug() == slug.value) slug.value = title.value.toSlug();
			oldTitle = title.value;
		});
	});
	
	//generate type slug
	when('product_type_title', function(title) {
		var slug = $('product_type_slug'),
		oldTitle = title.value;
		
		new Form.Element.Observer(title, 0.15, function() {
		if (oldTitle.toSlug() == slug.value) slug.value = title.value.toSlug();
			oldTitle = title.value;
		});
	});
	
	//generate vendor slug
	when('product_vendor_title', function(title) {
		var slug = $('product_vendor_slug'),
		oldTitle = title.value;
		
		new Form.Element.Observer(title, 0.15, function() {
		if (oldTitle.toSlug() == slug.value) slug.value = title.value.toSlug();
			oldTitle = title.value;
		});
	});
	
	//image upload
	if ($('upload_button')) {
		var action = '/pilot/plugin/ecommerce/image_upload';
		if ($('product_id'))
			action = '/pilot/plugin/ecommerce/image_upload/'+$('product_id').innerHTML;
			
		var button = $('upload_button'), file_li = $$('#upload .files')[0], interval;
		new AjaxUpload(button,{
			action: action,
			name: 'product_image',
			onSubmit : function(file, ext){
				if (! (ext && /^(jpg|jpeg)$/.test(ext))){
				//if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){
					alert('Error: invalid file extension');
					return false;
				}
				else {
					$('ispinner').show();
					this.disable();
				}
			},
			onComplete: function(file, response) {
				$('ispinner').hide();
				this.enable();
				file_li.insert(new Element('li').update(file));
			}
		});
	}
	
	//create new fields for select boxes
	var UpdateableSelectBox = Class.create({
		initialize: function(id, targetField) {
			this.select = $(id);
			this.targetField = $(targetField);			
			this.select.observe('change', this.onSelectedOptionChanged.bindAsEventListener(this));
		},
		
		onSelectedOptionChanged: function(event) {
			var selected = this.select.options[this.select.selectedIndex].value;
			
			if (selected == 'create_new')
				this.targetField.show();
			else
				this.targetField.hide();
		}
	});
	if ($('product_type_id'))
		new UpdateableSelectBox('product_type_id', 'product_type_title');
	if ($('product_vendor_id'))
		new UpdateableSelectBox('product_vendor_id', 'product_vendor_title');
	
	//sortable images
	if ($('product_images')) {
		sortable_images();
	}
	
	//sortable variants
	if ($('product_variants')) {
		sortable_variants();
	}
	
	//sortable files
	if ($('product_files')) {
		sortable_files();
	}
	
	//sortable videos
	if ($('product_videos')) {
		sortable_videos();
	}
	
	//tags
	if ($('tags')) {
		var tagger = new Virgen.Tagger('tags');
	}
	
	//sortable collection products
	if ($('collection_products')) {
		sortable_collection_products();
	}
});

//-------------------------------------------------
// General
//-------------------------------------------------

//confirm action
function confirmIt() {
	var agree = confirm("Are you sure?");
	if (agree)
		return true;
	else
		return false;
}

//highlight element
function highlight(element) {
	new Effect.Highlight(element, { startcolor: '#ffff99', endcolor: '#ffffff', duration: 3 });
}

//blind toggle an element
function blind_toggle(element) {
	new Effect.toggle(element,'Blind', {duration:.5});
}

//-------------------------------------------------
// Collection
//-------------------------------------------------

//sortable collection products
function sortable_collection_products() {
	Sortable.create('collection_products', { tag: 'div', handle: 'drag_handle', constraint:false,			
		onChange: function(item) {
			var list = Sortable.options(item).element;
		},
		onUpdate: function() {
			collection_products_reorder();
		}
	});
}

//collection product reorder
function collection_products_reorder() {
	new Ajax.Request("/pilot/plugin/ecommerce/collection_products_reorder", {
		method: "post",
		onLoading: function(){$('cpspinner').show()},
		onLoaded: function(){$('cpspinner').hide()},
		parameters: { data: Sortable.serialize("collection_products") }
	});
}

function collection_product_delete(id) {
	if (confirmIt()) {
		new Ajax.Request('/pilot/plugin/ecommerce/collection_product_delete/'+id, {
			asynchronous:true, 
			evalScripts:true, 
			method:'delete',
			onLoading: function(){$('cpspinner').show()},
			onLoaded: function(){
				$('product_'+id).remove();
				$('cpspinner').hide();
			}
		});
	}
}

function collection_product_search() {
	new Ajax.Request('/pilot/plugin/ecommerce/collection_product_search', {
		asynchronous:true, 
		evalScripts:true, 
		method:'get', 
		parameters:Form.serialize($('product_search_form')),
		onLoading: function() {$('spinner').show()},
		onSuccess: function(transport) {
			Element.update("product-select", transport.responseText);
			$('spinner').hide()
		}
	});
}

function collection_product_create(product_id) {
	new Ajax.Request('/pilot/plugin/ecommerce/collection_product_create', {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('possible-product-'+product_id+'-form')), 
		onLoading: function(){$('cpspinner').show()},
		onSuccess: function(request) {
			var new_id = request.responseText;

			//update product html
			var updater = new Ajax.Updater('collection_products', '/pilot/plugin/ecommerce/collection_product_html/'+new_id, {
				parameters: { method: 'get' },
				insertion: 'top',
				onComplete: function() {
					$('possible-product-'+product_id).remove();
					sortable_collection_products();
					new ToolBox('product_'+new_id);
					highlight($('product_'+new_id));
					collection_products_reorder();
				}
			});
			
			$('cpspinner').hide();
		}
	});
}

//-------------------------------------------------
// Order
//-------------------------------------------------
function order_fulfilled(id) {
	new Ajax.Request('/pilot/plugin/ecommerce'+($('fulfilled_'+id).checked ? '/order_fulfilled/':'/order_not_fulfilled/')+id, {
		method: 'get',
		onSuccess: function() {
			$('order_row_'+id).toggleClassName('fulfilled');
		}
	});
}

//-------------------------------------------------
// Images
//-------------------------------------------------

//sortable images
function sortable_images() {
	Sortable.create('product_images', { tag: 'div', overlap:'horizontal',constraint:false,
		onChange: function(item) {
			var list = Sortable.options(item).element;
		},
		
		onUpdate: function() {
			new Ajax.Request("/pilot/plugin/ecommerce/image_reorder", {
				method: "post",
				onLoading: function(){$('ispinner').show()},
				onLoaded: function(){$('ispinner').hide()},
				parameters: { data: Sortable.serialize("product_images") }
			});
		}
	});
}

//image delete
function image_delete(id) {
	if (confirmIt()) {
		new Ajax.Request('/pilot/plugin/ecommerce/image_delete/'+id, {
			asynchronous:true, 
			evalScripts:true, 
			method:'delete',
			onLoading: function(){$('ispinner').show()},
			onLoaded: function(){$('image_'+id).remove();$('ispinner').hide()}
		});
	}
}

//-------------------------------------------------
// Variants
//-------------------------------------------------

//sortable variants
function sortable_variants() {
	Sortable.create('product_variants', { tag: 'div', handle: 'drag_handle', constraint:false,			
		onChange: function(item) {
			var list = Sortable.options(item).element;
		},
		
		onUpdate: function() {
			variants_reorder();
		}
	});
}

//variant reorder
function variants_reorder() {
	new Ajax.Request("/pilot/plugin/ecommerce/variant_reorder", {
		method: "post",
		onLoading: function(){$('vspinner').show()},
		onLoaded: function(){$('vspinner').hide()},
		parameters: { data: Sortable.serialize("product_variants") }
	});
}

//variant delete
function variant_delete(id) {
	if (confirmIt()) {
		new Ajax.Request('/pilot/plugin/ecommerce/variant_delete/'+id, {
			asynchronous:true, 
			evalScripts:true, 
			method:'delete',
			onLoading: function(){$('vspinner').show()},
			onLoaded: function(){$('variant_'+id).remove();$('variant_form_'+id).remove();$('vspinner').hide()}
		});
	}
}

//variant form toggle
function variant_form_toggle(id) {
	$('product_variants').toggle();
	$('variant_form_'+id).toggle();
	$('add_variant_button').hide();
}

//variant form cancel
function variant_form_cancel(id) {
	$('product_variants').toggle();
	$('variant_form_'+id).toggle();
	$('add_variant_button').show();
}

//variants show add form
function variant_add_form() {
	$('add_variant_form').show();
	if ($('product_variants'))
		$('product_variants').hide();
	$('add_variant_button').hide();
}

//variant add form cancel
function variant_add_form_cancel() {
	$('product_variants').toggle();
	$('add_variant_form').toggle();
	$('add_variant_button').show();
}

//variant update info after save
function variant_form_update(id) {
	$('variant_title_'+id).innerHTML = $('variant_form_title_'+id).value;
	$('variant_description_'+id).innerHTML = $('variant_form_description_'+id).value;
	$('variant_price_'+id).innerHTML = '$'+$('variant_form_price_'+id).value;
	$('variant_sku_'+id).innerHTML = $('variant_form_sku_'+id).value;
	$('variant_quantity_'+id).innerHTML = $('variant_form_quantity_'+id).value;
	$('variant_weight_'+id).innerHTML = $('variant_form_weight_'+id).value;
}

//variant create variant
function variant_create() {
	new Ajax.Request('/pilot/plugin/ecommerce/variant_create', {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('add_variant_form')), 
		onLoading: function(){$('vspinner').show()}, 
		onLoaded: function(){
			$('product_variants').show();
			$('add_variant_form').hide();
			$('add_variant_button').show();
			$('vspinner').hide();
		},
		onSuccess: function(request) {
			var new_variant_id = request.responseText;
			
			//update variant info html
			var updater = new Ajax.Updater('product_variants', '/pilot/plugin/ecommerce/variant_info_html/'+new_variant_id, {
				parameters: { method: 'get' },
				insertion: 'bottom',
				onComplete: function() {
					sortable_variants();
					new ToolBox('variant_'+new_variant_id);
					highlight($('variant_'+new_variant_id));
					variants_reorder();
				}
			});
			
			//update variant info form
			var updater = new Ajax.Updater('variant_forms', '/pilot/plugin/ecommerce/variant_form_html/'+new_variant_id, {
				parameters: { method: 'get' },
				insertion: 'bottom'
			});
			
			//reset the add form
			$('variant_add_form_title').value = '';
			$('variant_add_form_description').value = '';
			$('variant_add_form_price').value = '';
			$('variant_add_form_sku').value = '';
			$('variant_add_form_quantity').value = '';
			$('variant_add_form_weight').value = '';
		}
	});
}

//variant update
function variant_update(id) {
	new Ajax.Request('/pilot/plugin/ecommerce/variant_update/'+id, {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('variant_form_'+id)), 
		onLoading: function(){
			$('vspinner').show()
		}, 
		onLoaded: function(){
			variant_form_update(id);
			$('variant_form_'+id).hide();
			$('product_variants').show();
			$('vspinner').hide();
			$('add_variant_button').show();
			highlight($('variant_'+id));
		}
	});
}

//-------------------------------------------------
// Files
//-------------------------------------------------

//sortable files
function sortable_files() {
	Sortable.create('product_files', { tag: 'div', handle: 'drag_handle', constraint:false,			
		onChange: function(item) {
			var list = Sortable.options(item).element;
		},
		
		onUpdate: function() {
			files_reorder();
		}
	});
}

//file reorder
function files_reorder() {
	new Ajax.Request("/pilot/plugin/ecommerce/file_reorder", {
		method: "post",
		onLoading: function(){$('fspinner').show()},
		onLoaded: function(){$('fspinner').hide()},
		parameters: { data: Sortable.serialize("product_files") }
	});
}

//file delete
function file_delete(id) {
	if (confirmIt()) {
		new Ajax.Request('/pilot/plugin/ecommerce/file_delete/'+id, {
			asynchronous:true, 
			evalScripts:true, 
			method:'delete',
			onLoading: function(){$('fspinner').show()},
			onLoaded: function(){
				$('file_'+id).remove();
				$('file_form_'+id).remove();
				$('fspinner').hide()	
			}
		});
	}
}

//file form toggle
function file_form_toggle(id) {
	if ($('product_files'))
		$('product_files').toggle();
	if ($('file_form_'+id))
		$('file_form_'+id).toggle();
	$('add_file_button').hide();
}

//file form cancel
function file_form_cancel(id) {
	if ($('product_files'))
		$('product_files').toggle();
	$('file_form_'+id).toggle();
	$('add_file_button').show();
}

//files show add form
function file_add_form() {
	$('add_file_form').show();
	if ($('product_files'))
		$('product_files').hide();
	$('add_file_button').hide();
}

//file add form cancel
function file_add_form_cancel() {
	if ($('product_files'))	
		$('product_files').toggle();
	$('add_file_form').toggle();
	$('file_add_form_title').value = '';
		$('file_add_form_filename').value = '';
	$('add_file_button').show();
}

//file update info after save
function file_form_update(id) {
	$('file_title_'+id).innerHTML = $('file_form_title_'+id).value;
}

//file create
function file_create() {
	new Ajax.Request('/pilot/plugin/ecommerce/file_create', {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('add_file_form')), 
		onLoading: function(){$('fspinner').show()}, 
		onLoaded: function(){
			if ($('product_files'))
				$('product_files').show();
			$('add_file_form').hide();
			$('add_file_button').show();
			$('fspinner').hide();
		},
		onSuccess: function(request) {
			var new_file_id = request.responseText;
			
			//update file info html
			var updater = new Ajax.Updater('product_files', '/pilot/plugin/ecommerce/file_info_html/'+new_file_id, {
				parameters: { method: 'get' },
				insertion: 'bottom',
				onComplete: function() {
					sortable_files();
					new ToolBox('file_'+new_file_id);
					highlight($('file_'+new_file_id));
					files_reorder();
				}
			});
			
			//update file info form
			var updater = new Ajax.Updater('file_forms', '/pilot/plugin/ecommerce/file_form_html/'+new_file_id, {
				parameters: { method: 'get' },
				insertion: 'bottom'
			});
			
			//reset the add form
			$('file_add_form_title').value = '';
			$('file_add_form_filename').value = '';
		}
	});
}

//file update
function file_update(id) {
	new Ajax.Request('/pilot/plugin/ecommerce/file_update/'+id, {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('file_form_'+id)), 
		onLoading: function(){
			$('fspinner').show()
		}, 
		onLoaded: function(){
			file_form_update(id);
			$('file_form_'+id).hide();
			if ($('product_files'))
				$('product_files').show();
			$('fspinner').hide();
			$('add_file_button').show();
			highlight($('file_'+id));
		}
	});
}

//-------------------------------------------------
// Videos
//-------------------------------------------------

//sortable videos
function sortable_videos() {
	Sortable.create('product_videos', { tag: 'div', handle: 'drag_handle', constraint:false,			
		onChange: function(item) {
			var list = Sortable.options(item).element;
		},
		
		onUpdate: function() {
			videos_reorder();
		}
	});
}

//video reorder
function videos_reorder() {
	new Ajax.Request("/pilot/plugin/ecommerce/video_reorder", {
		method: "post",
		onLoading: function(){$('vispinner').show()},
		onLoaded: function(){$('vispinner').hide()},
		parameters: { data: Sortable.serialize("product_videos") }
	});
}

//video delete
function video_delete(id) {
	if (confirmIt()) {
		new Ajax.Request('/pilot/plugin/ecommerce/video_delete/'+id, {
			asynchronous:true, 
			evalScripts:true, 
			method:'delete',
			onLoading: function(){$('vispinner').show()},
			onLoaded: function(){$('video_'+id).remove();$('video_form_'+id).remove();$('vispinner').hide()}
		});
	}
}

//video form toggle
function video_form_toggle(id) {
	if ($('product_videos'))
		$('product_videos').toggle();
	if ($('video_form_'+id))
		$('video_form_'+id).toggle();
	$('add_video_button').hide();
}

//video form cancel
function video_form_cancel(id) {
	if ($('product_videos'))
		$('product_videos').toggle();
	$('video_form_'+id).toggle();
	$('add_video_button').show();
}

//videos show add form
function video_add_form() {
	$('add_video_form').show();
	if ($('product_videos'))
		$('product_videos').hide();
	$('add_video_button').hide();
}

//video add form cancel
function video_add_form_cancel() {
	if ($('product_videos'))	
		$('product_videos').toggle();
	$('add_video_form').toggle();
	$('video_add_form_title').value = '';
	$('video_add_form_filename').value = '';
	$('add_video_button').show();
}

//video update info after save
function video_form_update(id) {
	$('video_title_'+id).innerHTML = $('video_form_title_'+id).value;
}

//video create
function video_create() {
	new Ajax.Request('/pilot/plugin/ecommerce/video_create', {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('add_video_form')), 
		onLoading: function(){$('vispinner').show()}, 
		onLoaded: function(){
			if ($('product_videos'))
				$('product_videos').show();
			$('add_video_form').hide();
			$('add_video_button').show();
			$('vispinner').hide();
		},
		onSuccess: function(request) {
			var new_video_id = request.responseText;
			
			//update video info html
			var updater = new Ajax.Updater('product_videos', '/pilot/plugin/ecommerce/video_info_html/'+new_video_id, {
				parameters: { method: 'get' },
				insertion: 'bottom',
				onComplete: function() {
					sortable_videos();
					new ToolBox('video_'+new_video_id);
					highlight($('video_'+new_video_id));
					videos_reorder();
				}
			});
			
			//update video info form
			var updater = new Ajax.Updater('video_forms', '/pilot/plugin/ecommerce/video_form_html/'+new_video_id, {
				parameters: { method: 'get' },
				insertion: 'bottom'
			});
			
			//reset the add form
			$('video_add_form_title').value = '';
			$('video_add_form_filename').value = '';
		}
	});
}

//video update
function video_update(id) {
	new Ajax.Request('/pilot/plugin/ecommerce/video_update/'+id, {
		asynchronous:true, 
		evalScripts:true, 
		method:'put', 
		parameters:Form.serialize($('video_form_'+id)), 
		onLoading: function(){
			$('vispinner').show()
		}, 
		onLoaded: function(){
			video_form_update(id);
			$('video_form_'+id).hide();
			if ($('product_videos'))
				$('product_videos').show();
			$('vispinner').hide();
			$('add_video_button').show();
			highlight($('video_'+id));
		}
	});
}

//-------------------------------------------------
// Toolbox
//-------------------------------------------------

//toolbox
ToolBox = Class.create();
ToolBox.current = null;
ToolBox.prototype = {      
	initialize: function(element) {       
		this.toolbox = $(element);
		if(!this.toolbox) return;
		this.timeout = null;
		this.tools = this.findTools();
		
		Event.observe(this.toolbox, 'mouseover', this.onHover.bindAsEventListener(this), true);
		Event.observe(this.toolbox, 'mouseout', this.onBlur.bindAsEventListener(this), true);
		Event.observe(this.tools, 'mouseover', this.onHover.bindAsEventListener(this));
		Event.observe(this.tools, 'mouseout', this.onBlur.bindAsEventListener(this));
	},
	
	show: function() {
		if(this.timeout) { 
			clearTimeout(this.timeout); 
			this.timeout = null;
		}    
	
		if(ToolBox.current) {
			ToolBox.current.hideTools();      
		}
		
		if(this.tools) {
			Element.show(this.tools);
			ToolBox.current = this;
		}
	},
	
	onHover: function(event) {
		this.show();
	},
	
	onBlur: function(event) {
		this.considerHidingTools();
	},
	
	considerHidingTools: function() {
		if(this.timeout) { clearTimeout(this.timeout); }
		this.timeout = setTimeout(this.hideTools.bind(this), 500);
	},
	
	hideTools: function() {
		clearTimeout(this.timeout);
		this.timeout = null;
		Element.hide(this.tools);          
	},
	
	findTools: function() { 
		var tools = this.toolbox.select('.tools')[0];
		if(!tools) { throw "You called new ToolBox() on an element which has no class=\"tools\" child element"; }
		return tools;
	}
};