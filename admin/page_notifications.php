<?php

//Checking if WP is running or if this is a direct call..
defined('ABSPATH') or die();

?>
<div>
	<h2>Notifications</h2>

	<form action="options.php" method="post">
		<?php
		wp_nonce_field('ziggeo_nonce_action', 'ziggeo_notifications_nonce');
		settings_errors();
		//settings_fields('ziggeo_notifications');
		//do_settings_sections('ziggeo_notifications');
		ziggeo_a_n_text();
		?>
	</form>
	<div id="ziggeo_messenger"><div id="ziggeo_message"></div><div>X</div></div>
</div>
<?php

// Notifications page allows us to show some messages to admins that would otherwise escape them. For example if there is some potential misuse or issue on the page clients would see it, yet admin might not know. By using this, they can tell it happened (as long as it is reported).
// For devs: Please use only to report errors, notifications and reasons to update the plugin. The notifications should not be used to suggest, nag nor reminder about paying, upgrading to paid or anything similar.

//Function to show the notifications page
function ziggeo_a_n_text() {
	$notifications = ziggeo_get_notifications();

	//Newest first
	//TODO: This will be fine for few notifications here and there, especially since it is only loaded on admin side
	// 		If needed, reach out to us and we will make it paged instead, 50-100 notifications a page only.
	// Note: There really should not be a lot of notifications if everything is running smoothly.
	$notifications['list'] = array_reverse($notifications['list']);

	?>
	<p><?php _e('All of the notifications we captured are shown here.', 'ziggeo'); ?></p>
	<?php
		if(current_user_can('activate_plugins')) {
			?>
			<div class="ziggeo_notifications_admin_tools">
				<span id="ziggeo_notifications_prune" class="ziggeo-ctrl-btn">Prune<i>i<div>Use this to prune (remove) all duplicate entries.</div></i></span>
				<span id="ziggeo_notifications_clear" class="ziggeo-ctrl-btn">Clear<i>i<div>Use this to clear all notifications.</div></i></span>
			</div>
			<?php
		}
	?>
	<div class="ziggeo-frame" id="ziggeo-notifications">
		<?php
			//If you are dev, please use this to notify admins of suggestions, update and errors, no purchase related notifications.
		?>

		<ol id="ziggeo_notifications_list">
			<?php
			for($i = 0, $c = count($notifications['list']); $i < $c; $i++) {
				$notification = $notifications['list'][$i];
				if($notification['status'] === 'HIDE') {
					?>
					<li data-id="<?php echo $notification['id'] ?>" class="hidden message <?php echo $notification['type']; ?>"></li>
					<?php
				}
				elseif($notification['status'] === 'OK') {
					?>
					<li data-id="<?php echo $notification['id'] ?>" class="message ok"><?php echo htmlspecialchars( $notification['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ); ?><div class="hide">HIDE</div></li>
					<?php
				}
				else {
					?>
					<li data-id="<?php echo $notification['id'] ?>" class="message <?php echo $notification['type']; ?>"><?php echo htmlspecialchars( $notification['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ); ?><div class="ok">OK</div><div class="hide">HIDE</div></li>
					<?php
				}
			}
			if($c === 0) {
				?>
				<li><?php _e('Skyes are clear and all is good - no notifications found', 'ziggeo'); ?></li>
				<?php
			}
			?>
		</ol>
	</div>

	<?php
}

	function ziggeo_a_n_change_notification_status() {
		?>
		<div style="display:none;">
			<input id="ziggeo_notification_change" value="" name="ziggeo_notifications[notification_action]">
		</div>
		<?php
	}
?>