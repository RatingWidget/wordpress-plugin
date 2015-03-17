<?php
	/*
	 * The view content of the email to send to affiliate@rating-widget.com
	 * called from send_affiliate_application method in rating-widget.php
	 */

	extract($VARS);
?>

<table>
	<tbody>
		<tr>
			<td><strong>Admin Email:</strong></td>
			<td><?php echo $aff_admin_email; ?></td>
		</tr>
		<tr>
			<td><strong>User ID:</strong></td>
			<td><?php echo $aff_user_id; ?></td>
		</tr>
		<tr>
			<td><strong>Site ID:</strong></td>
			<td><?php echo $aff_site_id; ?></td>
		</tr>
		<tr>
			<td><strong>Site Address:</strong></td>
			<td><?php echo $aff_site_address; ?></td>
		</tr>
		<tr>
			<td><strong>Posts #:</strong></td>
			<td><?php echo $aff_total_posts; ?></td>
		</tr>
		<tr>
			<td><strong>Comments #:</strong></td>
			<td><?php echo $aff_total_comments; ?></td>
		</tr>
	</tbody>
</table>