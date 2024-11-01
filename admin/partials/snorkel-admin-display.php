<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://snorkelapp.com
 * @since      1.0.0
 *
 * @package    Snorkel
 * @subpackage Snorkel/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <h2><?php esc_html_e(get_admin_page_title()); ?></h2>

        </br>
        <form method="post" name="snorkel_options" action="options.php">

		  <?php
			//Grab all options
			$options = get_option($this->plugin_name);

			// Snorkel API KEY 
			$snorkel_api_key = $options['snorkel_api_key'];

			?>

			<?php
				settings_fields($this->plugin_name);
				do_settings_sections($this->plugin_name);
			?>

				  <div class="form-table">
				  <h2><?php esc_html_e('Snorkel API Key', $this->plugin_name); ?></h2>
				  <p>You can get your Snorkel API key from <a href="https://www.snorkelapp.com/install">settings page</a></p>
				  <fieldset>
                      <label for="<?php esc_html_e($this->plugin_name); ?>-snorkel_api_key">
                          <p><input type="text" id="<?php esc_html_e($this->plugin_name); ?>-snorkel_api_key" name="<?php esc_html_e($this->plugin_name); ?>[snorkel_api_key]" value="<?php if(!empty($snorkel_api_key)) esc_html_e($snorkel_api_key); ?>"/></p>
                      </label>
                  </fieldset>

           <?php submit_button(); ?>
		   </form>
</div><!--wrap-->
