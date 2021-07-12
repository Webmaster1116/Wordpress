<div class="ce4wp-kvp">
	<p class="ce4wp-typography-root ce4wp-body2" style="color: rgba(0, 0, 0, 0.6);">
        <?= __( 'Your WordPress instance is connected to your Creative Mail account. If you would like to
		unlink your WordPress instance from your account, please click the "Unlink" button below.', 'ce4wp' ); ?>
		<strong><?= __('Unlinking your account is permanent and cannot be undone.', 'ce4wp' ); ?></strong>
	</p>
</div>

<div class="ce4wp-kvp">
	<form name="disconnect" action="" method="post">
		<input type="hidden" name="action" value="disconnect" />
		<input name="disconnect_button" type="submit" class="ce4wp-button-text-primary destructive ce4wp-right" id="disconnect-instance" value="Unlink" onclick="return confirm('Are you sure you want to unlink your CreativeMail account from your WordPress site? This action is permanent and cannot be undone.')" />
	</form>
</div>
