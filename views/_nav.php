<?php
$cur_url = Dispatcher::getCurrentUrl();
$order_pos = strpos($cur_url,'/plugin/ecommerce/order');
$product_pos = strpos($cur_url,'/plugin/ecommerce/product');
$collection_pos = strpos($cur_url,'/plugin/ecommerce/collection');
$marketing_pos = strpos($cur_url,'/plugin/ecommerce/marketing');
$settings_pos = strpos($cur_url,'/plugin/ecommerce/settings');
?>

<div id="ecnav">
	<ul>
		<li><a <?php if ($cur_url == '/plugin/ecommerce'): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce'); ?>"><?php echo __('Dashboard'); ?></a></li>
		<li><a <?php if ($order_pos === 0): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce/order'); ?>"><?php echo __('Orders'); ?></a></li>
		<li><a <?php if ($product_pos === 0): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce/product'); ?>"><?php echo __('Products'); ?></a></li>
		<li><a <?php if ($collection_pos === 0): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce/collection'); ?>"><?php echo __('Collections'); ?></a></li>
		<li><a <?php if ($marketing_pos === 0): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce/marketing'); ?>"><?php echo __('Marketing'); ?></a></li>
		<li class="right"><a <?php if ($settings_pos === 0): echo 'class="current"'; endif; ?> href="<?php echo get_url('plugin/ecommerce/settings'); ?>"><?php echo __('Settings'); ?></a></li>
	</ul>
</div>
<br clear="all" /><br />