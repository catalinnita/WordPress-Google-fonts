<?php
/*
Plugin Name: WPFW - Fonts
Plugin URI: http://www.WordpressForward.com
Description: Manage and use all Google fonts.
Version: 1.0.1
Author: Catalin Nita
Author URI: http://www.WordpressForward.com
License: GNU General Public License v2 or later
*/

include('settings.php');
include('functions.php');
include('data.php');

add_action('admin_menu', 'manage_fonts_settings');  
function manage_fonts_settings() {
	add_menu_page('Fonts', __('Fonts Settings', 'wpfw'), 'administrator', 'Fonts', 'manage_fonts', '');
	add_submenu_page('Fonts', 'Google Fonts', __('Google Fonts', 'wpfw'), 'administrator', 'google_fonts', 'google_fonts');	
	//add_submenu_page('Fonts', 'Upload Fonts', __('Upload Fonts', 'wpfw'), 'administrator', 'upload_fonts', 'upload_fonts');	
}

function manage_fonts() {
	global $wpdb;
	
	?>
		<div class="wrap">
		<div id="icon-themes" class="icon32"><br /></div>
		
		<h2><?php _e('Update google fonts list', 'wpfw'); ?></H2><br/>	
				<div id="col-container">
				<div class="col-wrap">
					<?php
						if (isset($_POST['updatefonts']) && $_POST['updatefonts'] == 1) {
							update_fonts();
						}
					?>
					<p>Please click the button below to update the Google fonts list.</p>
					<form name="update_fonts" action="admin.php?page=Fonts" method="post">				
						<input type="hidden" name="updatefonts" value="1">
						<input type="submit" value="Check For New Google Fonts" class="button-primary" />
					</form>
				</div>
				</div>
		
		</div>
	<?php
	
}

function update_fonts() {
	global $wpdb;
	
	$google_fonts = file_get_contents("https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyC3e2SpX8jMuWNS5vCw21yF5nKZsBJfQKM");
	$google_fonts = json_decode($google_fonts, true);
	
	$newfonts = 0;
	$newvariants = 0;
	
	foreach($google_fonts['items'] as $gf) {
		
		// *** font
		$font_check = $wpdb->get_results("SELECT ID FROM es_fonts WHERE FontName = '".$gf['family']."'");
		if (!$font_check[0]->ID) {
			// insert font
			$wpdb->query("INSERT INTO es_fonts (FontName, FontPath) VALUES ('".$gf['family']."', '".str_replace(" ", "+", $gf['family'])."')");
			$font_id = $wpdb->insert_id;
			$newfonts++;
		}
		else {
			// get font id
			$font_id = $font_check[0]->ID;
		}
		
		// *** variants 
		foreach($gf['variants'] as $fv) {
			
			$variant_check = $wpdb->get_results("SELECT ID FROM es_fvariants WHERE VariantName = '".$fv."'");
			if (!$variant_check[0]->ID) {
				// insert variant
				$wpdb->query("INSERT INTO es_fvariants (VariantName) VALUES ('".$fv."')");
				$variant_id = $wpdb->insert_id;
				$newvariants++;
			}
			else {
				// get variant id
				$variant_id = $variant_check[0]->ID;
			}
			
			$variantfonts_check = $wpdb->get_results("SELECT ID FROM es_fonts_variants WHERE VariantID = ".$variant_id." AND FontID = ".$font_id);
			if (!isset($variantfonts_check[0]->ID)) {
				// insert variant and font link
				$wpdb->query("INSERT INTO es_fonts_variants (VariantID, FontID) VALUES (".$variant_id.", ".$font_id.")");
			}
			else {
				// do nothing
			}
			
		}
		
		// *** sets 
		foreach($gf['subsets'] as $fs) {
			
			$set_check = $wpdb->get_results("SELECT ID FROM es_fsets WHERE SetName = '".$fs."'");
			if (!$set_check[0]->ID) {
				// insert variant
				$set_id = $wpdb->insert_id;
				$wpdb->query("INSERT INTO es_fsets (SetName) VALUES ('".$fs."')");
			}
			else {
				// get variant id
				$set_id = $set_check[0]->ID;
			}
			
			$setfonts_check = $wpdb->get_results("SELECT ID FROM es_fonts_sets WHERE SetID = ".$set_id." AND FontID = ".$font_id);
			if (!isset($setfonts_check[0]->ID)) {
				$wpdb->query("INSERT INTO es_fonts_sets (SetID, FontID) VALUES (".$set_id.", ".$font_id.")");
				// insert variant and font link
			}
			else {
				// do nothing
			}
			
		}		
				
	}
	
	echo "<p>";
	
	if ($newfonts == 0 && $newvariants == 0) { 
		echo 'Google fonts list is up to date.<br/>';
	}
	if ($newfonts > 0) { 
		echo $newfonts.' new fonts was installed.<br/>';
	}
	if ($newvariants > 0) { 
		echo $newvariants.' new variants was installed.<br/>';
	}	
		
	$nr = $wpdb->get_results("SELECT ID FROM es_fonts");
	echo '<b>A total of '.count($nr).' are installed.</b>';
	
	echo '</p>';
	
}

function google_fonts() {
	global $wpdb;
	
	if(isset($_POST['installfont']) && $_POST['installfont'] == 1) {
		$wpdb->query("UPDATE es_fonts SET Installed = 1 WHERE ID = ".$_POST['font_id']);
	}
	if(isset($_POST['installfont']) && $_POST['installfont'] == -1) {
		$wpdb->query("UPDATE es_fonts SET Installed = 0 WHERE ID = ".$_POST['font_id']);
	}
	
	
	$skey = isset($_GET['s']) ? $_GET['s'] : null;

	if ($skey == null) {
		$fonts = $wpdb->get_results("SELECT ID FROM es_fonts");
	}
	else {
		$fonts = $wpdb->get_results("SELECT * FROM es_fonts WHERE FontName like '%".$skey."%'");
	}	
	
	?>
		<div class="wrap">
		<div id="icon-themes" class="icon32"><br /></div>
		
		<h2><?php echo __('Manage ', 'wpfw').count($fonts).__(' font families', 'wpfw'); ?></H2><br/>
		<?php

	
	$postperpage = 32;
	if (isset($_GET['p'])) {
	$limit = ($_GET['p']-1)*$postperpage;
	$prevpage = $_GET['p']-1;
	$nextpage = $_GET['p']+1;
	$page = $_GET['p'];
	}
	else {
		$limit = 0;
		$nextpage = 2;
		$page = 1;
	}
	if (count($fonts)%$postperpage != 0) {
	$pages = intval(count($fonts)/$postperpage)+1;
	}
	else {
	$pages = intval(count($fonts)/$postperpage);
	}

	$content = '<div class="tablenav top"><form name="paging" action="admin.php">';
	
	$content .= '<div class="tablenav-pages">';
	
	//$content .= '<a class="first-page'; if ($page <=1) { $content .= ' disabled"'; } else {  $content .= '" href="admin.php?page=google_fonts&s='.$skey.'&p=1"'; } $content .= '>&laquo;</a>&nbsp;';
	//$content .= '<a class="prev-page'; if ($page <=1) { $content .= ' disabled"'; } else { $content .= '" href="admin.php?page=google_fonts&s='.$skey.'&p='.$prevpage.'"'; } $content .= '>&lt;</a>&nbsp;';
	$content .= '<a class="btn next-page'; if ($page >=$pages) { $content .= ' disabled"'; } else { $content .= '" href="admin.php?page=google_fonts&s='.$skey.'&p='.$nextpage.'"'; } $content .= '>&#59230</a>&nbsp;';
	$content .= '<a class="btn prev-page'; if ($page <=1) { $content .= ' disabled"'; } else { $content .= '" href="admin.php?page=google_fonts&s='.$skey.'&p='.$prevpage.'"'; } $content .= '>&#59229;</a>&nbsp;';
	
	$content .= '<input type="hidden" name="page" value="google_fonts">
									<input type="hidden" name="s" value="'.$skey.'">
									<span class="paging-input">
										<input onchange="if (this.value < 1 || this.value > '.$pages.') { this.value = '.$page.'; }" class="current-page" type="text" size="1" value="'.$page.'" name="p"> of '.$pages.'
									</span>
								';
	
	//$content .= '<a class="last-page'; if ($page >=$pages) { $content .= ' disabled"'; } else { $content .= '" href="admin.php?page=google_fonts&s='.$skey.'&p='.$pages.'"'; } $content .= '>&raquo;</a>';
	
	$content .= '</form></div>';

	

	$content .= '<form id="SearchForm" name="search" action="admin.php" method="get">
		<input type="hidden" name="page" value="google_fonts">
		<input id="s" type="text" name="s" value="'.$skey.'" placeholder="'.__('Search', 'wpfw').'" />
		<input type=submit value="'.__('Search', 'wpfw').'" class="button-secondary" style="display: none;">
		';
		if ($skey) {
			$content .= '<a class="reset" href="admin.php?page=google_fonts">&#10062;</a>';
		}
		$content .= '</form>';
		
		if(!isset($_SESSION['wpfw-tt'])) { $tt = 1; } else { $tt = $_SESSION['wpfw-tt']; }
		if(!isset($_SESSION['custom-text'])) { $custom_text = 'Enter your text'; } else { $custom_text = $_SESSION['custom-text']; }
		if(!isset($_SESSION['custom-size'])) { $custom_size = 70; } else { $custom_size = $_SESSION['custom-size']; }
		
		$ttypes = array();
		$ttypes[] = 'Enter your text';
		$ttypes[] = 'Ag';
		$ttypes[] = 'Font Names';
		$ttypes[] = 'AaBbCcDdEeFfGg ...';
		$ttypes[] = '0123456789';
		$ttypes[] = 'ff fi fl ffi ffl st ct';
		$ttypes[] = 'Punctuation';
		
		$content .= '<form id="FontType" name="font-type" action="admin.php?page=google_fonts" method="post">';
		
		$content .= '
		<div class="outside select-box-outside">
			<input type="hidden" name="tt" id="TextType" value="'.$tt.'" />
			<span class="button-container select-box-container">
				<span class="select-box"><span class="controler">&#8862;</span>'.$ttypes[$tt].'</span>
			</span>
			<div class="select-box-dd">
				<ul class="select-box-options">';
		
		foreach($ttypes as $key => $ttype) {
		$content .= '<li><a href="#'.$key.'">'.$ttype.'</a></li>';
		}
		$content .= '</ul>
			</div>
		</div>';
		$content .= '<input id="CustomText" type="text" name="custom_text" value="'.$custom_text.'" '; if($tt > 0) { $content .= 'style="display: none;"'; } $content .= ' />
		<div class="outside outside-slider">
			<input class="text slider-text" type="text" name="custom-size" value="'.$custom_size.'">
			<div class="slider-line"></div>
		</div>';
		$content .= '</form>';
		
	$content .= '</div>';
	
	echo $content;	

	if ($skey == null) {
		$fonts = $wpdb->get_results("SELECT * FROM es_fonts LIMIT ".$limit.",".$postperpage);
	}
	else {
		$fonts = $wpdb->get_results("SELECT * FROM es_fonts WHERE FontName like '%".$skey."%' LIMIT ".$limit.",".$postperpage);
	}
	$fpath = '';
	$nr = 1;
	foreach($fonts as $f) {
		if ($nr > 1) { $fpath .= '|'; }
		$fpath .=	$f->FontPath;
		$nr++;
	}
	
	
	?>
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=<?php echo $fpath; ?>" />
	
	
		<form id="install-font" action="admin.php?page=google_fonts" method="post">
			<input type="hidden" id="installaction" name="installfont" value="1">
			<input type="hidden" id="font_id" name="font_id" value="-1" />
		</form>
		<div id="col-container">
				<?php
				
						if(isset($_POST['tt'])) { $_SESSION['wpfw-tt'] = $_POST['tt']; } 
						if(isset($_POST['custom_text'])) { $_SESSION['custom-text'] = $_POST['custom_text']; } 
						if(isset($_POST['custom-size'])) { $_SESSION['custom-size'] = $_POST['custom-size']; } 
						
						if (!isset($_SESSION['wpfw-tt'])) { $_SESSION['wpfw-tt'] = ''; }
						if (!isset($_SESSION['custom-text'])) { $_SESSION['custom-text'] = 'Enter your text'; }
						if (!isset($_SESSION['custom-size'])) { $_SESSION['custom-size'] = 70; }
						
						switch($_SESSION['wpfw-tt']) { 
							case 0: $testdrive = $_SESSION['custom-text']; break;
							case 1: $testdrive = 'Ag'; break;
							case 2: /* in foreach loop */ break;
							case 3: $testdrive = 'A a B b C c D d E e F f G g H h I i J j K k L l M m N n O o P p Q q R r S s T t U u V v W w X x Y y Z z'; break;
							case 4: $testdrive = '1 2 3 4 5 6 7 8 9 0'; break;
							case 5: $testdrive = 'ff fi fl ffi ffl st ct'; break;
							case 6: $testdrive = '.,/\'][=-`<>?"|!@#$%^&*()_+{}'; break;
						}				
				
				?>
			
				<div class="col-wrap">
				<?php
				foreach($fonts as $f) {
					if ($_SESSION['wpfw-tt'] == 2) { $testdrive = $f->FontName; }
					?>
					<div class="font-item <?php if ($f->Installed ==1) { echo 'on'; } ?>">
						<div class="font-item-header"><?php echo $f->FontName; ?></div>
						<div class="font-item-content" style="font-family: '<?php echo $f->FontName; ?>'; font-size: <?php echo $_SESSION['custom-size']; ?>px;">
							<span><?php echo $testdrive; ?></span>
						</div>
						<a id="check-<?php echo $f->ID; ?>" href="#<?php echo $f->ID; ?>" class="check">
							<?php if ($f->Installed ==1) { echo 'Remove From Collection'; } else { echo 'Add To Collection'; } ?>
						</a>
					</div>
					<?php
				}
				?>
				</div>
		</div>
	</div>
	<?php
		wp_enqueue_script( 'jquery-ui-slider');	
		wp_enqueue_script( 'wpfw-fonts-plugin-js', plugins_url( 'js/fonts.js' , __FILE__ ), array( 'jquery' ), 1.0, true );	
}

function get_installed_fonts() {
	global $wpdb;
	
	$fonts = $wpdb->get_results("SELECT * FROM es_fonts WHERE Installed = 1");
	$ret = array();
	foreach($fonts as $f) {
		$ret[$f->FontName] = $f->FontName;
	}
	
	return $ret;
	
}

function load_installed_fonts() {
	global $wpdb;
	
	$fonts = $wpdb->get_results("SELECT * FROM es_fonts WHERE Installed = 1");
	$fpath = '';
	$nr = 1;
	foreach($fonts as $f) {
		if ($nr > 1) { $fpath .= '|'; }
		$fpath .=	$f->FontPath;
		$nr++;
	}
	if ($fpath) {
		return 'http://fonts.googleapis.com/css?family='.$fpath;
	}
	else {
		return false;
	}
}

function wpfw_enqueue_google_fonts() {
	global $wpfw_dir;
	wp_register_style('wpfw-google-fonts', load_installed_fonts());
	wp_enqueue_style('wpfw-google-fonts');
}
	
add_action('wp_enqueue_scripts', 'wpfw_enqueue_google_fonts');		



function upload_fonts() {
	
	?>
	
	<div id="DropZone">
		<div id="DropStatus">Drag your fonts here<span>or</span></div>
		<span class="btn btn-success fileinput-button">
       <span>Select files...</span>
       <!-- The file input field used as target for the file upload widget -->
       <input id="fileupload" type="file" name="files[]" multiple>
    </span>
		<div id="progress" class="progress">
       <div class="progress-bar"></div>
    </div>
	</div>
	<div id="FilesList"></div>

	<script>
		jQuery(function ($) {
    'use strict';
    var url = '<?php echo plugins_url("server/php/index.php" , __FILE__ ); ?>';
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        dropZone: $("#DropZone"),
        start: function (e, data) {
        	$("#progress .progress-bar").removeClass("error").removeClass("success");  
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<p/>').text(file.name).appendTo('#FilesList');
                
								$.ajax({
									url: 'https://ofc.p.mashape.com/directConvert/',
									type: 'POST',
									data: {
										file: 'http://premiumweddingthemes.com/testing/wp-content/plugins/wpfw_fonts/server/php/files/'+file.name,
										format : 'eot',
										callback : 'http://premiumweddingthemes.com/testing/wp-admin/admin.php?page=upload_fonts'
									},
									contentType: "multipart/form-data; charset=utf-8",
									datatype: 'json',
									success: function(data) { alert(JSON.stringify(data)); },
									error: function(err) { alert(err); },
									beforeSend: function(xhr) {
										xhr.setRequestHeader("X-Mashape-Authorization", "oV5LIe0xbxjq2UqTDHdNmIO401OiVMl1");
									}
								});                

            });

            
            $("#progress .progress-bar").addClass("success");
            
        },
        fail: function (e, data) {
        	$("#progress .progress-bar").addClass("error");  
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    });
	
	});
	</script>
	<?php
	
}
?>