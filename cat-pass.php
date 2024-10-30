<?php
/*
Plugin Name: Cat-Pass
Plugin URI: http://www.mores.cc/cat-pass/
Description: A plugin that lets you password protect post categories. <strong>Caution:</strong> deactivating the plugin will render your protected posts visible again.
Author: Daniel Mores 
Version: 1.0
Author URI: http://www.mores.cc/
*/


/* ############# this is where stuff is being done #####################*/
function cat_pass_cookie() {
	global $wpdb;global $post;	
	$cat_pass_array = get_option('cat_pass_array');
    if($_POST['pass'] == $cat_pass_array['password'] && !isset($_COOKIE['cat_pass_cat'])) :
        setcookie("cat_pass_cat", "1", time()+60*60*24*5);
        $_COOKIE['cat_pass_cat'] = 1;
    endif;
    if ($_POST['pass'] && $_POST['pass'] != $cat_pass_array['password']) :
    	$_POST['msg']="err";
    endif;
}
add_action('init', 'cat_pass_cookie');

function cat_pass($content) {
	global $wpdb;global $post;	
	$cat_pass_array = get_option('cat_pass_array');
	$post = get_post($id);
	if ($post->post_type=='post') :
		if ( in_category($cat_pass_array['category'], $post) ) : 
			if ($_COOKIE['cat_pass_cat']) :
				$content = $content;
			else : 
			    $content = '
			    <form name="login" action="'.$PHP_SELF.'" method="Post"> 
			    <input type="password" name="pass"> 
			    <input type="hidden" name="cat_pass_cat" value="'.$cat_pass_array['category'].'"> 
			    <input type="submit" value="Freischalten"> 
			    </form>'; 
			    if ($_POST['msg'] == "err") :
			    	echo 'Oops: Falsches Passwort!';
			    endif;
			endif;
		endif;
	endif;
	return $content;
}
add_filter( 'the_content', 'cat_pass' );

/* ############# this is backend options stuff #####################*/

add_action('admin_menu', 'add_cat_pass_options');
function add_cat_pass_options() {
	add_options_page(__('Cat-Pass Options'), __('Cat-Pass Settings'), 5, basename(__FILE__), 'cat_pass_options');
}

function cat_pass_options() {
	if (isset($_POST['cat_pass_updated'])) {
		$cat_pass_array = array ( 'category' => $_POST['category'], 'password' => $_POST['password'] );
		update_option('cat_pass_array', $cat_pass_array);
		$updated = true;
	}
	
	if(get_option('cat_pass_array')) {
		$cat_pass_array = get_option('cat_pass_array');
	} else {
		add_option('cat_pass_array', "", "Cat-Pass Values", "yes");
	}
	
	if ($updated) {
		echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	}
	?>

	<div class="wrap" id="cat_pass_options">
		<?php screen_icon(); ?>
		<h2>Cat-Pass options</h2>
		<form name="cat_pass_form" method="post" action="<?php echo $_SERVER['../cat-pass/REQUEST_URI']; ?>">

		<input type="hidden" id="cat_pass_updated" name="cat_pass_updated" value="yes" />
		
		<table class="form-table">
		<tbody>
		<tr valign="top">
			<th scope="row">Category to protect:</th>
			<td>
				<select name="category">
				<option value="0">None</option>
				<?php 
				$cats = get_categories();
				foreach( $cats as $cat ) :
					echo '<option value="'.$cat->cat_ID.'"';
					if ($cat_pass_array['category'] == $cat->cat_ID) : echo ' selected'; endif;
					echo '>'.$cat->cat_name.'</option>';
				endforeach;
				?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Password:</th>
			<td>
				<input type="text" name="password" value="<? echo $cat_pass_array['password'] ?>">
			</td>
		</tr>
		</tbody>
		</table>
		
			<p class="submit">
				<input type="submit" name="cat_pass_update" value="Update options &raquo;" class="button-primary" />
			</p>
		</form>
		
		<h3><font color="red">Caution</font></h3>
		<p>When you deactivate Cat-Pass for any reason, all your protected posts will be unprotected again. Just keep it in mind when using it.</p>
		<h3>Instructions</h3>
		<p>Well, this is probably pretty easy. Select a category from the dropdown menu, enter a password, and press the blue button.</p>
		<h3>Help!</h3>
		<p>Need help? Check out the plugin website over at <a href="http://www.mores.cc/cat-pass" target="_blank">www.mores.cc/cat-pass</a> and leave a comment (even if you don't have a problem), or seek help in the forum on wordpress.org</p>
		<h3>"Awesome! This plugin is just what I've been looking for"</h3>
		<p>I'm so glad to hear that. I've been looking around for a feature such as this one and haven't found anything similar, so I had to make it myself.<br/>
		So here it comes: please donate a beer or two if I have been able to make your life easier. It'll make my feel all warm and fuzzy inside, and you will feel it too.</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post"><div class="paypal-donations"><input type="hidden" name="cmd" value="_donations" /><input type="hidden" name="business" value="constance@mores.cc" /><input type="hidden" name="item_name" value="Cat-Pass" /><input type="hidden" name="item_number" value="mores.cc" /><input type="hidden" name="amount" value="10" /><input type="hidden" name="currency_code" value="EUR" /><input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online." /><img alt="" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" /></div></form>
		<p>Thank you,<br/>
		Daniel Mores</p>
	</div>
	<?php
}






?>