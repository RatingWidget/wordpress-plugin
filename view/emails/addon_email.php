<?php
/*
 * The content of the email to send to addons@rating-widget.com.
 * Called from send_addon_request method in rating-widget.php
 */

extract($VARS);
?>
<table>
	<tbody>
		<tr>
			<td><strong>Add-on Title:</strong></td>
			<td><?php echo $addon_title; ?></td>
		</tr>
		<tr>
			<td><strong>Add-on Price:</strong></td>
			<td><?php echo $addon_price; ?></td>
		</tr>
		<?php
		if ( isset($addon_user_email) ) {
			?>
			<tr>
				<td><strong>User Email:</strong></td>
				<td><?php echo $addon_user_email; ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td><strong>User Site Address:&nbsp;&nbsp;&nbsp;</strong></td>
			<td><?php echo $addon_site_address; ?></td>
		</tr>
		<tr>
			<td><strong>Action:</strong></td>
			<td><?php echo $addon_action; ?></td>
		</tr>
		<tr>
			<td style="vertical-align: top;"><strong>Order:</strong></td>
			<td style="vertical-align: top;"><?php echo $addon_order; ?></td>
		</tr>
	</tbody>
</table>