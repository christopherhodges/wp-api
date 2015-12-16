<div class="wrap">
	<h2>Convio Integration Settings</h2>
	<form method="post" action="options.php">
		<?php echo settings_fields( 'spark_email_general-settings' ); ?>
		<?php do_settings_sections( 'spark_email_general-settings' ); ?>
		<table class="form-table">
            <tr valign="top">
                <th scope="row">Subject</th>
                <td>
                    <input type="text" name="spark_email_subject" style="width:100%;" value="<?php echo esc_attr( get_option('spark_email_subject') ); ?>"/>
                </td>
            </tr>
	        <tr valign="top">
	        	<th scope="row">Message</th>
	        	<td>
                    <textarea name="spark_email_message" style="width:100%;height:350px;"><?php echo esc_attr( get_option('spark_email_message') ); ?></textarea>
                    <p>You can use: [site_url], [login_url], [reset_url], [blog_name], [first_name], [last_name]</p>
	        	</td>
	        </tr>
	    </table>
		<?php submit_button(); ?>
	</form>
</div>
