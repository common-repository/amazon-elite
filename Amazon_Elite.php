<?php 
/*
Plugin Name: Amazon Elite
Plugin URI: http://software.tghosting.net/?page_id=474
Description: Amazon Product Research Package
Author: Debra Berube
Version: 2.2
Author URI: http://sites.tghosting.net/?page_id=521
*/

$fileDirLoopCount = 0;
$DBWD_fileDirContents_filename = "fileDirContents.csv";

$DBWD_APS = new DBWD_APS();
$DBWD_APS->add_DBWD_menu();

register_activation_hook( __FILE__, array( 'DBWD_APS', 'setDefaultData' ));
register_activation_hook( __FILE__, array( 'DBWD_APS', 'setMenuCount' ));
register_deactivation_hook( __FILE__, array( 'DBWD_APS', 'deactivationMenuControl' ) );

class DBWD_APS
	{
	function add_DBWD_menu()
		{
		add_action('admin_menu', array('DBWD_APS', 'admin_add_menu'));

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array('DBWD_APS', 'DBWD_add_plugin_action_links'),10,1);
		add_filter( 'plugin_row_meta', array('DBWD_APS', 'DBWD_plugin_meta_links'), 10, 2 );
		}

	public static function admin_add_menu()
		{
		add_menu_page( 'DBWD Software', 'DBWD Software', 'manage_options', 'dbwd-software', array('DBWD_APS', 'DBWD_custom_menu_page'), plugins_url( 'gifs/favicon.png', __FILE__ ), '65.1' );
		add_submenu_page( 'dbwd-software', 'Amazon Elite', 'Amazon Elite', 'manage_options', 'DBWD_APS', array('DBWD_APS', 'options'));
		}

	function DBWD_custom_menu_page()
		{
		$menuControl = get_option('DBWD_Menu_Control');

		if ($menuControl['data'][1] == 0)
			{
			if (!empty($_COOKIE["wptheme" . COOKIEHASH])) { $thisThemeName = $_COOKIE["wptheme" . COOKIEHASH]; }
			else { $thisThemeName = wp_get_theme(); }

			$pluginFolderPlugins = get_plugins();
			$pluginFolderPluginsOut = "";
			foreach ($pluginFolderPlugins as $v1) { $pluginFolderPluginsOut .= $v1['Name'] .= "|"; }

			$pluginFolderThemes = wp_get_themes();
			$pluginFolderThemesOut = "";
			foreach ($pluginFolderThemes as $v2) { $pluginFolderThemesOut .= $v2['Name'] .= "|"; }

			$siteName = get_bloginfo('name');
			$siteNameOut = str_replace("\\", "", $siteName);

			$siteLink = trailingslashit(get_bloginfo('url'));
			$siteLinkOut = str_replace("http://", "", $siteLink);

			$admin_email = get_option('admin_email');

			$menuControl['data'][2] == 0; 
			?>

			<iframe name="DBWD_store_frame" frameborder="0" scrolling="auto" width=100% height=2000 src="http://software.tghosting.net/iFrameStore/iFrameStore.php?pluginFolderPlugins=<?php print $pluginFolderPluginsOut ?>&pluginFolderThemes=<?php print $pluginFolderThemesOut ?>&siteName=<?php print $siteNameOut ?>&siteLink=<?php print $siteLinkOut ?>&siteAdminEmail=<?php print $admin_email ?>&thisThemeName=<?php print $thisThemeName ?>"></iframe>

		<?php
			}

		$menuControl['data'][1]++;				/* Increment Display Counter */
		$menuControl['data'][2]++;

		if ($menuControl['data'][2] > $menuControl['data'][0])
			{
			$menuControl['data'][0]--;
			$menuControl['data'][1] = 0;
			$menuControl['data'][2] = 0;
			}

		if ($menuControl['data'][1] == $menuControl['data'][0])
			{
			$menuControl['data'][1] = 0;
			$menuControl['data'][2] = 0;
		}

		update_option('DBWD_Menu_Control', $menuControl );
		}

	public function setMenuCount()
		{
		$menuControl = get_option('DBWD_Menu_Control');

		if (!$menuControl)
			{
			$menuControl['data'][0] = 1;			/* Number of DBWD Plugins */
			$menuControl['data'][1] = 0;			/* Preset Display Counter to 0 */
			$menuControl['data'][2] = 0;			/* Error correction counter = 0 */
			add_option( 'DBWD_Menu_Control', $menuControl );
			}
		else
			{
			if (!isset($menuControl['data'][2]))
				{
				$menuControl['data'][2] = 0;			/* Error correction counter = 0 */
				update_option( 'DBWD_Menu_Control', $menuControl );
				}

			$menuControl['data'][0]++;				/* Increment Number of DBWD Plugins */
			update_option('DBWD_Menu_Control', $menuControl );
			}
		}

	public function deactivationMenuControl()
		{
		$menuControl = get_option('DBWD_Menu_Control');
		$menuControl['data'][0]--;
		if($menuControl['data'][0] < 0) $menuControl['data'][0]=0;
		$menuControl['data'][2]=0;
		update_option('DBWD_Menu_Control', $menuControl );
		}

	public function DBWD_add_plugin_action_links($links)
		{
  		return array_merge(
  			array(
  				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=DBWD_APS" title="Run Plugin" alt="Run Plugin">Run</a>'
  				),$links);
 		}

	function DBWD_plugin_meta_links( $links, $file )
		{
		$plugin = plugin_basename(__FILE__);

		if ( $file == $plugin )
			{
			return array_merge($links,array( '<a href="http://software.tghosting.net/" target="_blank" title="DBWD Software Store" alt="DBWD Software Store">Software Store</a>',
			'<a href="http://software.tghosting.net/?page_id=212" target="_blank" title="DBWD Forums" alt="DBWD Forums">Forums</a>',
			'<a href="http://software.tghosting.net/?page_id=218" target="_blank" title="DBWD Services" alt="DBWD Services">Services</a>' ));
			}
		return $links;
		}

	function data_save()
		{
		if(isset($_POST['submitter']))
			{
			$options = get_option('DBWD_APS');

			if($_POST['tabNumber'] == "a1")
				{
				$option_name = 'DBWD_APS';

				$options['data'][0] = $_POST['tabNumber'];

				update_option($option_name, $options);
				}

			if($_POST['tabNumber'] == "a2")
				{
				$option_name = 'DBWD_APS';

				$options['data'][0] = "a1";

				update_option($option_name, $options);
				}

			if($_POST['tabNumber'] == "a3")
				{
				$option_name = 'DBWD_APS';

				$options['data'][0] = "a1";
				$options['data'][4] = $_POST['DBWD_access_key_id'];		/* DBWD_access_key_id */
				$options['data'][5] = $_POST['DBWD_secret_key'];			/* DBWD_secret_key */
				$options['data'][6] = $_POST['DBWD_associate_tag'];		/* DBWD_associate_tag */
#				$options['data'][15] = $_POST['DBWD_CSV_Delimiter'];;										/* CSV Export Delimiter */

				update_option($option_name, $options);
				}

			}
		}

	function setDefaultData()
		{
		$options = get_option('DBWD_APS');

		$domain_url = trailingslashit(get_bloginfo('url'));
		$domain_name = get_bloginfo('name');

		if($options['data'][0] == '')
			{
			$admin_email = get_option('admin_email');

			$option_name = 'DBWD_APS';

			$options['data'][0] = "a3"; 				/* Configuratipn Tab */
			$options['data'][1] = $domain_name;		/* Website Name */
			$options['data'][2] = "";					/* Senders Email */
			$options['data'][3] = $domain_url;		/* Website URL */
			$options['data'][4] = "";					/* DBWD_access_key_id */
			$options['data'][5] = "";					/* DBWD_secret_key */
			$options['data'][6] = "";					/* DBWD_associate_tag */
			$options['data'][7] = "10";				/* DBWD_display_count */
			$options['data'][8] = "";					/* DBWD_last_output_file */
			$options['data'][9] = "us";				/* Set Default Country */
			$options['data'][10] = "";					/* Last Searched For */
			$options['data'][11] = "";					/* Selected Root Category */
			$options['data'][12] = "";					/* Selected Category Name */
			$options['data'][13] = "";					/* Category Drill Down */
			$options['data'][14] = "0";				/* Category Drill Down Count */
			$options['data'][15] = ",";				/* CSV Export Delimiter */
			$options['data'][16] = "";					/* For Future Use */
			$options['data'][17] = "";					/* For Future Use */
			$options['data'][18] = "";					/* For Future Use */
			$options['data'][19] = "";					/* For Future Use */
			$options['data'][20] = "";					/* For Future Use */
			$options['data'][21] = "";					/* For Future Use */
			$options['data'][22] = "";					/* For Future Use */
			$options['data'][23] = "";					/* For Future Use */
			$options['data'][24] = "";					/* For Future Use */
			$options['data'][25] = "";					/* For Future Use */
			$options['data'][26] = "";					/* For Future Use */
			$options['data'][27] = "";					/* For Future Use */
			
			add_option( $option_name, $options );
			}
		}

	public static function options()
		{
		global $fileDirLoopCount,$DBWD_fileDirContents_filename;
			
#		DBWD_APS::init();

		DBWD_APS::setDefaultData();

		DBWD_APS::data_save();

		$options = get_option('DBWD_APS');
		
		$activeTabStorage = $options['data'][0];

		$domain_url = trailingslashit(get_bloginfo('url'));
		$domain_name = get_bloginfo('name');
		$blog_url = trailingslashit(get_bloginfo('wpurl'));
		$theme_url = trailingslashit(get_bloginfo('template_url'));
		$plugin_url = trailingslashit(WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
		$plugin_url = str_replace(' ','%20',$plugin_url);

?>
		<div class="wrap">
			<div style="position:relative; top:10px; left:0px;">
				<table border=0 width=900><tr><td>
			<img src="<?php print $plugin_url ?>gifs/sll_icon.gif" style="vertical-align:middle;" /></td>
			<td valign=middle nowrap>
				&nbsp;
				<font size=5 color=navy><b>Amazon Elite</b></font></td>
			<td align=left valign=middle width=100%>
				&nbsp;&nbsp;&nbsp;
				<font size=2 color=navy><b>Amazon Product Research Package</b></font>
			</td>
			</tr></table>
			</div>

			<div id="Credits" style="position:relative; width:900px; top:10px; background-color: #f8f8ff; 
				background-image: url(<?php print $plugin_url ?>gifs/topMenuBG.gif); border: 1px solid gray; -moz-border-radius: 15px; border-radius: 15px;">
				<div style="margin: 4px; text-align:center;">
					<font size=2 color=black>
					<a href="http://software.tghosting.net/" target="_blank">DBWD Software</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=480" target="_blank">Upgrade to Amazon Elite Professional</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=212" target="_blank">Forums</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=218" target="_blank">Services</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://software.tghosting.net/?page_id=474" target="_blank">Plugin Homepage</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://sites.tghosting.net" target="_blank">D.B. Web Development</a>&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="http://sites.tghosting.net/?page_id=521" target="_blank">Plugin Author</a>
					</font>	
				</div>
			</div>

			<div id="spinnerLoader" style="position:absolute; top:8px; left:600px;">
				<img src="<?php print $plugin_url ?>gifs/animatedEllipse.gif" width=20 />
			</div>

    		<link rel="stylesheet" type="text/css" href="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/dhtmlxgrid.css">
			<link rel="stylesheet" type="text/css" href="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/skins/dhtmlxgrid_dhx_skyblue.css">
    		<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
    		<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>
    		<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
    		<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxGrid/codebase/excells/dhtmlxgrid_excell_link.js"></script>

			<br>
			<link rel="stylesheet" type="text/css" href="<?php print $plugin_url ?>js/dhtmlxTabbar/codebase/dhtmlxtabbar.css" />
			<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxTabbar/codebase/dhtmlxcommon.js"></script>
			<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxTabbar/codebase/dhtmlxtabbar.js"></script>
			<script type="text/javascript" src="<?php print $plugin_url ?>js/dhtmlxTabbar/codebase/dhtmlxtabbar_start.js"></script>

			<div id="DBWD_SL_tabbar" class="dhtmlxTabBar" tabstyle="modern" select="<?php print $activeTabStorage ?>" imgpath="<?php print $plugin_url ?>js/dhtmlxTabbar/codebase/imgs/" style="position:relative; top:10px; width:900px; height:2330px;" skinColors="#ffffff,#f0f0ff" >

				<!-- Tab Page 1 -->

				<div id="a1" name="Product Search" style="display: none;">

					<div id="Product_Search" class="wrap" style="position:absolute; top:16px; left:16px;">
						<font size=5 color=black><b>Product Search</b></font>

						<?php
						$configError=0;
						if (($options['data'][4]=="") || ($options['data'][5] == "") || ($options['data'][6] == ""))
							{
							echo '<br><br><font size=3 color="navy"><b>Your Amazon Access Key ID, Secret Key or Associate Tags have not been entered.
							<br><br>Please Use the Configuration Tab Above to Make Your Initial Entries.</b></font>';
							
							$configError=1;
							}
						?>
						
						<?php if ($configError==0)
							{ ?>
							<br>
							<div id="categoryArea" class="wrap" style="position:relative; width:860px; height:90px; background-color: #e3efff; border: 1px solid gray; -moz-border-radius: 5px; border-radius: 5px;">
								<div style="margin:4px; text-align:left; width:850px; height:82px; border: 0px solid gray;">
                   		
									<?php 
									 $secretKeyOutput = $options['data'][5]; 
									 $secretKeyOutput = str_replace(array(' ', '+', ',', ';'), array('%20', '%2B', urlencode(','), urlencode(';')), $secretKeyOutput);
									?>

									<iframe name="category_frame" frameBorder="0" style="border: 0px solid gray; background-color: #e3efff; width:850px; height:82px;" scrolling="no" src="<?php print $plugin_url ?>Amazon_Elite_Categories_Control.php?selectCountry=<?php print $options['data'][9]; ?>&pass=0&searchFor=<?php print $options['data'][10]; ?>&associateTag=<?php print $options['data'][6]; ?>&accessKeyID=<?php print $options['data'][4]; ?>&secretKey=<?php print $secretKeyOutput; ?>&selectItemCount=100&selectRootCategory=<?php print $options['data'][11]; ?>&categoryDrillDown=<?php print $options['data'][13]; ?>&categoryDrillDownCount=<?php print $options['data'][14]; ?>&pluginURL=<?php print $plugin_url; ?>"></iframe>
								
									<iframe name='displayGraphFrame' frameBorder="0" style='position:absolute; top:100px; left:-1px; border: 0px solid gray; background-color: #ffffff; width:862px; height:2140px;' scrolling='no' src='<?php print $plugin_url ?>Amazon_Elite_Graph.php?pass=0'></iframe>
						
								</div>
							</div>
					<?php } ?>
						
					</div>
				</div>

				<!-- Tab Page 2 -->

 				<!-- Tab Page 3 -->

    			<div id="a3" name="Configuration" style="display: none;">

					<div id="Configuration_Area" style="position:absolute; top:20px; left:16px;">
						<font size=5><b>Configuration</b></font>
						&nbsp;&nbsp;&nbsp;
						<font size=2><b>This information must be properly entered for this package to function correctly...</b></font>
						
						<br>

						<?php
						if (($options['data'][4]=="") || ($options['data'][5] == "") || ($options['data'][6] == ""))
							{
							echo '<br><br><font size=3 color="navy"><b>* First Use - Initial Setup - Please Make the Entries Below to Start Using this Package.</b></font><br><br>';
							}
						?>

						<br>
						<form method="post" name="DBWDform">
							<input type="hidden" name="tabNumber" value="a3">
							
							<table cellpadding=0 cellspacing=10 border=0>
								<tr>
								<td colspan=2 align=left><font size=3><b><u>Your Amazon Access Information</u></b></font></td>
								</tr><tr>
								<td height=4 colspan=2> </td>
								</tr><tr>
								<td align=right>Your Amazon Access Key Id: </td>
								<td align=left><input id="DBWD_access_key_id" name="DBWD_access_key_id" size=40 maxlength=128 value="<?php print $options['data'][4] ?>"></td>
								</tr><tr>
								<td align=right>Your Amazon Secret Key: </td>
								<td align=left><input id="DBWD_secret_key" name="DBWD_secret_key" size=70 maxlength=128 value="<?php print $options['data'][5] ?>"></td>
								</tr><tr>
								<td align=right>Your Amazon Associate Tag: </td>
								<td align=left><input id="DBWD_associate_tag" name="DBWD_associate_tag" size=30 maxlength=128 value="<?php print $options['data'][6] ?>"></td>
								</tr>
							</table>

							<br>

							<input type="submit" name="submitter" value="&nbsp;&nbsp;Save Configuration Information&nbsp;&nbsp;" class="button-primary" onsubmit="return validateForm()">
						</form>
						<br><br>

						<font size=2 color=black>If you do not have the information above you will have to sign up with Amazon to become an Associate.
							<br><br>If you are currently an Amazon Associate you may also log in here to retrieve your information.</font>
						<br><br>
						
						<a href="https://affiliate-program.amazon.com/" target="_blank"><font size=2 color=navy><b>Click here for the Amazon Affiliate Program Page.</b></font></a>


					</div>
				</div>

			</div>
 		<br><br>
		</div>

<script type="text/javascript">
	window.onload=function()
		{
		var ele = document.getElementById("spinnerLoader");
		ele.style.display = "none";

		var ele = document.getElementById("Product Search");
		ele.style.display = "block";
		}
</script>

		<?php
		}
	}
?>