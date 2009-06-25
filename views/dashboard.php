<?php include(CORE_ROOT.'/plugins/ecommerce/views/_nav.php');?>

<h1><?php echo __('Dashboard'); ?></h1>

<?php if (count($logs)) : ?>

<div id="log">
	<p><strong><?php echo __('Overview'); ?></strong></p>
	<table>
		<?php foreach($logs as $log) : ?>
		<tr>
			<td nowrap="1" class="date"><strong><?php echo date("M d",strtotime($log['created_on'])); ?></strong></dt></td>
			<td><?php echo $log['message']; ?> <span class="at"><?php echo __('at'); ?> <?php echo date("g:i a",strtotime($log['created_on'])); ?> 
				<?php if (!empty($log['name'])) : ?>
				<?php echo __('by'); ?> <?php echo $log['name']; ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
<?php else : ?>
<p><?php echo __('There has been no activity.'); ?></p>
<?php endif; ?>
