<?php
/*
Plugin Name: WPTF Post and Page Features
Description: WPTF Post and Page Features allows you to embed a set of features in your Post or Page. This is a lightweight plugin, not too much code and css for flexible use. You need to have an admin privilege "manage_options" most of the time.
Version: 1.0
Author: Jan Michael Cheng
Author URI: http://www.trusted-freelancer.com
License: GPL
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
?>
<?php

class wptf_ppf{
	public function __construct()
	{
		// BackEnd
		add_action('admin_menu', array( &$this, 'wptf_ppf_menu' ));
		register_activation_hook( __FILE__, array( &$this, 'wptf_postpage_features_activate' ));
		register_deactivation_hook( __FILE__, array( &$this, 'wptf_postpage_features_deactivate' ));
		
		register_uninstall_hook( __FILE__, array( &$this, 'wptf_postpage_features_uninstall' ));
		
		wp_enqueue_script( 'my-ajax-request', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ) );
		wp_localize_script( 'my-ajax-request', 'o_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		add_action('wp_ajax_a_spec_ppf_update', array( &$this, 'fnc_spec_ppf_update' ));
		add_action('wp_ajax_a_spec_ppf_delete', array( &$this, 'fnc_spec_ppf_delete' ));
		add_action('admin_menu', array( &$this, 'ppf_meta_box' ));
		add_action('save_post', array( &$this, 'ppf_meta_update' ));
		
		// FrontEnd
		add_shortcode( 'show_ppf', array( &$this, 'show_ppf' ) );
	}
	
	// =======================================================================
	// == WPTF - Post and Page Features - Install DB =================== Start
	function wptf_postpage_features_activate()
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$this->wptf_postpage_features_installtable();
	}

	function wptf_postpage_features_deactivate()
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		// Remove data in 'wp_postmeta' table
		global $wpdb;
		$s_table_name = $wpdb->prefix . 'postmeta';
		$s_sql = "DELETE FROM $s_table_name WHERE meta_key = '_wptf_ppfs'";
		$s_result = $wpdb->query( $wpdb->prepare( $s_sql ) );
		
		// Drop table 'wp_tf_postpage_features'
		$this->wptf_postpage_features_uninstalltable();
	}
	
	function wptf_postpage_features_uninstall()
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
	}
	
	function wptf_postpage_features_installtable()
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		global $wpdb;
		$s_table_name = $wpdb->prefix . 'tf_postpage_features';
		
		$s_sql = "CREATE TABLE " . $s_table_name . " (
			id int(11) NOT NULL AUTO_INCREMENT,
			date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			name tinytext NOT NULL,
			imageurl text DEFAULT '' NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($s_sql);
	}

	function wptf_postpage_features_uninstalltable()
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		global $wpdb;
		$s_table_name = $wpdb->prefix . 'tf_postpage_features';

		$s_sql = "DROP TABLE ". $s_table_name;

		$wpdb->query($s_sql);
	}
	// =======================================================================
	// == WPTF - Post and Page Features - Install DB ===================== End

	
	// =======================================================================
	// == WPTF - Post and Page Features - Panel ======================== Start
	function wptf_ppf_menu() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		add_options_page('WPTF - Post and Page Features - Panel', 'WPTF - Post and Page Features', 'manage_options', 'wptf-ppf-panel', array(&$this, 'ppf_panel'));
	}
	
	function ppf_add($arr_data)
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		if( ($arr_data['txt_ppf_name'] != '') && ($arr_data['txt_ppf_imgurl'] != '')  )
		{
			global $wpdb;
			$s_name = $arr_data['txt_ppf_name'];
			$s_imageurl = $arr_data['txt_ppf_imgurl'];
			$s_table_name = 'tf_postpage_features';
			$s_table_name = $wpdb->prefix . $s_table_name;
			
			/*
			$s_result = $wpdb->insert( 
										$wpdb->prefix . $s_table_name, 
										array( 'date' => current_time('mysql'), 'name' => $s_name, 'imageurl' => $s_imageurl ) 
									);
			*/

			$s_result = $wpdb->query( 
				$wpdb->prepare( "INSERT INTO $s_table_name ( date, name, imageurl ) VALUES ( %s, %s, %s )", current_time('mysql'), $s_name, $s_imageurl ) 
			);

			if($s_result == 1)
			{
				$s_result = '<strong>Notice: </strong> Feature added';
			}
			else
			{
				$s_result = '<strong>Notice: </strong> Feature not added';
			}
		}
		else
		{
			$s_result = '<strong>Notice: </strong> Fill-up <strong>Feature Name</strong> and <strong>Feature Image URL</strong> please.';
		}
		
		return $s_result;
	}
	
	function ppf_panel() 
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		if(isset($_POST['txt_ppf_action']))
		{
			$txt_ppf_add_nonce = $_POST['txt_ppf_add_nonce'];
			if (!wp_verify_nonce($txt_ppf_add_nonce, 'ppf_add_nonce'))
			{
				echo "<div id=\"message\" class=\"updated fade\"><p>Security Check - If you receive this in error, log out and back in to WordPress</p></div>";
				die();
			}
			
			switch ($_POST['txt_ppf_action'])
			{
				case 'add':
					$s_result = $this->ppf_add($_POST);
					break;
				default:
					$s_result = 'Not Recognized';
			}

			if ($s_result)
			{
				echo "<div id=\"message\" class=\"updated fade\"><p>$s_result</p></div>";
			}
		}
		
		?>
			<div class="wrap">
				<h2>WPTF - Post and Page Features</h2>
				<p>
					by <a href="http://www.trusted-freelancer.com/" target="_blank" title="Trusted Freelancer">Jan Michael Cheng</a>. 
				</p>
				<p>
					[show_ppf post_id="1"] <br/>
					[show_ppf post_id="1" show_label="0"]
				</p>
				<hr/>
				<form name="frm_add_ppf" id="frm_add_ppf" action="" method="post">
					<h3>Add a Feature</h3>
					<ul>
						<li>
							<span>Feature Name:</span>
							<input type="text" value="" id="txt_ppf_name" name="txt_ppf_name" maxlength="30" />
						</li>
						<li>
							<span>Feature Image URL:</span>
							<input type="text" value="" id="txt_ppf_imgurl" name="txt_ppf_imgurl" maxlength="300" />
							<br/><span><strong>Note:</strong> Add an image in your Media > Library then paste the "File URL" here.</span>
						</li>
					</ul>
					<input type="hidden" name="txt_ppf_action" value="add" /> 
					<input type="hidden" name="txt_ppf_add_nonce" value="<?php echo wp_create_nonce('ppf_add_nonce'); ?>" />
					<input class="button-primary" type="submit" value="Add &raquo;" title="Add a Feature" />
				</form>
				<br/><hr/>
				<h3>Features</h3>
				<table class="wp-list-table widefat fixed media">
					<thead>
						<tr>
							<th>Action</th>
							<th>Thumbnail</th>
							<th>Feature Name</th>
							<th>Feature Image URL</th>
						</tr>
					</thead>
					<tbody>
						<?php
							global $wpdb;
							$s_table_name = $wpdb->prefix . 'tf_postpage_features';
							//$o_results = $wpdb->get_results( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM  $s_table_name" );
							$o_results = $wpdb->get_results( $wpdb->prepare( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM $s_table_name" ) );

							foreach ( $o_results as $o_feature ) 
							{
						?>
								<tr id="tr_update_ppf_<?php echo $o_feature->ppf_id; ?>" name="tr_update_ppf_<?php echo $o_feature->ppf_id; ?>">
									<td>
										<a href="#" class="ua_ppf" i_ppf_id="<?php echo $o_feature->ppf_id; ?>" title="Update">Update</a> |
										<a href="#" class="da_ppf" i_ppf_id="<?php echo $o_feature->ppf_id; ?>" title="Delete">Delete</a>
										<input type="hidden" value="<?php echo wp_create_nonce('spec_ppf_nonce_' . $o_feature->ppf_id); ?>" name="txt_spec_ppf_nonce_<?php echo $o_feature->ppf_id; ?>" id="txt_spec_ppf_nonce_<?php echo $o_feature->ppf_id; ?>" />
									</td>
									<td>
										<img id="img_ppf_<?php echo $o_feature->ppf_id; ?>" name="img_ppf_<?php echo $o_feature->ppf_id; ?>" src="<?php echo $o_feature->ppf_imageurl; ?>" title="<?php echo $o_feature->ppf_name; ?>" />
									</td>
									<td>
										<input type="text" value="<?php echo $o_feature->ppf_name; ?>" id="txt_ppf_name_<?php echo $o_feature->ppf_id; ?>" name="txt_ppf_name_<?php echo $o_feature->ppf_id; ?>" maxlength="30" />
									</td>
									<td>
										<input type="text" value="<?php echo $o_feature->ppf_imageurl; ?>" id="txt_ppf_imgurl_<?php echo $o_feature->ppf_id; ?>" name="txt_ppf_imgurl_<?php echo $o_feature->ppf_id; ?>" maxlength="300" />
									</td>
								</tr>
						<?php
							}
						?>
					</tbody>
				</table>
			</div>
		<?php
	}

	function ppf_meta_box() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		add_meta_box('ppf_meta_box_panel', 'WPTF - Post and Page Feature', array( &$this, 'ppf_meta_box_panel' ), 'post', 'side');
		add_meta_box('ppf_meta_box_panel', 'WPTF - Post and Page Feature', array( &$this, 'ppf_meta_box_panel' ), 'page', 'side');
	}
	
	function ppf_meta_box_panel() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		global $post;
		global $wpdb;
		$s_table_name = $wpdb->prefix . 'tf_postpage_features';
		//$o_results = $wpdb->get_results( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM  $s_table_name" );
		$o_results = $wpdb->get_results( $wpdb->prepare( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM $s_table_name" ) );
		$a_wptf_ppfs = get_post_meta($post->ID, '_wptf_ppfs');
		$a_wptf_ppfs = $a_wptf_ppfs[0];
		?>
			<div class="wrap">
				<?php
					if($o_results)
					{
				?>
						<ul>
							<?php 
								foreach ( $o_results as $$o_feature )
								{
							?>
									<li>
										<?php 
											$s_ischecked = '';
											if($a_wptf_ppfs != '')
											{
												if (in_array($$o_feature->ppf_id, $a_wptf_ppfs))
												{ $s_ischecked = 'checked'; }
											}
										?>
										<img style="width:20px; height:20px;" id="img_ppf_<?php echo $$o_feature->ppf_id; ?>" name="img_ppf_<?php echo $$o_feature->ppf_id; ?>" src="<?php echo $$o_feature->ppf_imageurl; ?>" title="<?php echo $$o_feature->ppf_name; ?>" />
										<input <?php if($s_ischecked){echo 'checked="checked"';} ?> type="checkbox" name="inp_wptf_ppfs[]" id="" value="<?php echo $$o_feature->ppf_id ; ?>" /> <?php echo $$o_feature->ppf_name ; ?>
									</li>
							<?php
								}
							?>
						</ul>
				<?php
					}
					else
					{
				?>
						<p>Add a Feature in the <a href="options-general.php?page=wptf-ppf-panel">Post and Page Feature Panel</a> first.</p>
				<?php	
					}
				?>		
			</div>
		<?php
	}
	// =======================================================================
	// == WPTF - Post and Page Features - Panel ========================== End
	
	
	// =======================================================================
	// == WPTF - Post and Page Features - Ajax Action ================== Start
	function fnc_spec_ppf_update() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$s_frm_ppf_params = $_POST['s_frm_ppf_params'][0];
		$i_ppf_id = $s_frm_ppf_params['i_ppf_id'];
		$s_ppf_name = urldecode($s_frm_ppf_params['s_ppf_name']);
		$s_ppf_imgurl = urldecode($s_frm_ppf_params['s_ppf_imgurl']);
		$s_spec_ppf_nonce = $s_frm_ppf_params['s_spec_ppf_nonce'];

		if (!wp_verify_nonce( $s_spec_ppf_nonce, 'spec_ppf_nonce_' . $i_ppf_id ))
		{
			die();
			exit;
		}
		
		if( $i_ppf_id != '' )
		{
			global $wpdb;
			$s_table_name = $wpdb->prefix . 'tf_postpage_features';
			
			/*
			$a_data = array(
							"name" => $s_ppf_name,
							"imageurl" => $s_ppf_imgurl
						);
			
			$a_where = array(
							"id" => $i_ppf_id
						);		
			$s_result = $wpdb->update( $s_table_name, $a_data, $a_where );
			*/

			$s_sql = "UPDATE $s_table_name SET name = %s, imageurl = %s WHERE id = %d";
			$s_result = $wpdb->query( $wpdb->prepare( $s_sql, $s_ppf_name, $s_ppf_imgurl, $i_ppf_id ) );

			if($s_result == 1)
			{
				/*
				$o_results = $wpdb->get_results( 
												"SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM " . $s_table_name 
												. " WHERE id = " . $i_ppf_id
											);
				*/							
				//$o_results = $wpdb->get_results( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM  $s_table_name WHERE id = $i_ppf_id" );
				
				$o_results = $wpdb->get_results( $wpdb->prepare( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM $s_table_name WHERE id = $i_ppf_id" ) );
				
				$s_result = '';
				
				foreach ( $o_results as $o_feature ) 
				{
					$s_result = $s_result . '<td>';
					$s_result = $s_result . 	'<a href="#" class="ua_ppf" i_ppf_id="' . $o_feature->ppf_id . '" title="Update">Update</a> | ';
					$s_result = $s_result . 	'<a href="#" class="da_ppf" i_ppf_id="' . $o_feature->ppf_id . '" title="Delete">Delete</a>';
					$s_result = $s_result . 	'<input type="hidden" value="' . wp_create_nonce('spec_ppf_nonce_' . $o_feature->ppf_id) . '" name="txt_spec_ppf_nonce_' . $o_feature->ppf_id . '" id="txt_spec_ppf_nonce_' . $o_feature->ppf_id . '" />';
					$s_result = $s_result . '</td>';
					$s_result = $s_result . '<td>';
					$s_result = $s_result . 	'<img style="width:20px; height:20px;" id="img_ppf_' . $o_feature->ppf_id . '" name="img_ppf_' . $o_feature->ppf_id . '" src="' . $o_feature->ppf_imageurl . '" title="' . $o_feature->ppf_name . '" />';
					$s_result = $s_result . '</td>';
					$s_result = $s_result . '<td>';
					$s_result = $s_result . 	'<input type="text" value="' . $o_feature->ppf_name . '" id="txt_ppf_name_' . $o_feature->ppf_id . '" name="txt_ppf_name_' . $o_feature->ppf_id . '" maxlength="30" />';
					$s_result = $s_result . '</td>';
					$s_result = $s_result . '<td>';
					$s_result = $s_result . 	'<input type="text" value="' . $o_feature->ppf_imageurl . '" id="txt_ppf_imgurl_' . $o_feature->ppf_id . '" name="txt_ppf_imgurl_' . $o_feature->ppf_id . '" maxlength="300" />';
					$s_result = $s_result . '</td>';
				}
			}
		}
		
		echo $s_result;
		exit;
	}
	
	function fnc_spec_ppf_delete() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$s_frm_ppf_params = $_POST['s_frm_ppf_params'][0];
		$i_ppf_id = $s_frm_ppf_params['i_ppf_id'];
		$s_spec_ppf_nonce = $s_frm_ppf_params['s_spec_ppf_nonce'];

		if (!wp_verify_nonce( $s_spec_ppf_nonce, 'spec_ppf_nonce_' . $i_ppf_id ))
		{
			die();
			exit;
		}
		
		if( $i_ppf_id != '' )
		{
			global $wpdb;
			$s_table_name = $wpdb->prefix . 'tf_postpage_features';
		
			//$s_result = $wpdb->query("DELETE FROM $s_table_name WHERE id = $i_ppf_id");
			$s_sql = "DELETE FROM $s_table_name WHERE id = %d";
			$s_result = $wpdb->query( $wpdb->prepare( $s_sql, $i_ppf_id ) );
		}
		
		echo $s_result;
		exit;
	}
	// =======================================================================
	// == WPTF - Post and Page Features - Ajax Action ==================== End
	
	
	// =======================================================================
	// == WPTF - Post and Page Features - Update ======================= Start
	function ppf_meta_update( $post_id ) 
	{
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		if ( !wp_is_post_revision( $post_id ) ) {
			delete_post_meta($post_id, '_wptf_ppfs');
			if($_POST['inp_wptf_ppfs'] != '')
			{
				add_post_meta($post_id, '_wptf_ppfs', $_POST['inp_wptf_ppfs']);
			}
		}
	}
	// =======================================================================
	// == WPTF - Post and Page Features - Update ========================= End
	
	
	function show_ppf( $atts )
	{
		global $post;
		global $wpdb;
		
		extract( shortcode_atts( array(
			'post_id' => '',
			'show_label' => 1,
		), $atts ) );
		
		if($post_id == '')
		{ return ''; }
		
		$a_wptf_ppfs = get_post_meta($post_id, '_wptf_ppfs');
		$a_wptf_ppfs = $a_wptf_ppfs[0];
		
		$s_where = 'WHERE id IN (';
		for($i_x=0; $i_x<sizeof($a_wptf_ppfs) ; $i_x++)
		{
			if($i_x>0)
			{
				$s_where = $s_where . ',';
			}
			$s_where = $s_where . '"' . $a_wptf_ppfs[$i_x]  . '"';
		}
		$s_where = $s_where . ')';
		
		$s_table_name = $wpdb->prefix . 'tf_postpage_features';
		/*
		$o_results = $wpdb->get_results( 
						"SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM " . $s_table_name . " " . $s_where
					);
		*/
		//$o_results = $wpdb->get_results( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM  $s_table_name $s_where" );

		$o_results = $wpdb->get_results( $wpdb->prepare( "SELECT id AS ppf_id, name AS ppf_name, imageurl AS ppf_imageurl FROM $s_table_name $s_where" ) );

		$s_result = '';
		if($o_results)
		{
			$s_result = $s_result . '<ul id="wptf-ppf-list-'. $post->ID .'" class="wptf-ppf-list">';
			foreach ( $o_results as $o_feature ) 
			{
				$s_result = $s_result . '<li class="wptf-ppf-list-li" id="wptf-ppf-list-li-'. $o_feature->ppf_id .'">';
				$s_result = $s_result . 	'<img src="'. $o_feature->ppf_imageurl .'" title="'. $o_feature->ppf_name .'" />';
				if($show_label==0)
				{ $s_result = $s_result . 	'<span style="display:none;">'; }
				else
				{ $s_result = $s_result . 	'<span>'; }
				$s_result = $s_result . 		$o_feature->ppf_name;
				$s_result = $s_result . 	'</span>';
				$s_result = $s_result . '</li>';
			}
			$s_result = $s_result . '</ul>';

		}

		return $s_result;
	}
}

if( class_exists( 'wptf_ppf' ) ) 
{
	$o_wptf_ppf = new wptf_ppf;
}
?>