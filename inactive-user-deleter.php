<?php

/*
Plugin Name: Inactive User Deleter
Plugin URI: http://shra.ru/hobbies/plugins/wordpress-inactive-user-deleter/
Description: When your project lives so long, and site got a lot of fake user's registrations (usually made by spammers). This tool will help you to clean this mess up. You can filter, select and delete users.
Version: 1.1
Author: SHRA
Author URI: http://shra.ru
*/

/*  Copyright 2010 Shra (email : to@shra.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
*/

add_action('admin_menu', 'IUD_menu');
mb_internal_encoding("UTF-8");
mb_language('uni');

//setting menu item
function IUD_menu() {
    add_users_page(__('Inactive users'), __('Inactive users'), 8, __FILE__, '_IUD_tool');
}

/* tool - here*/
function _IUD_tool() {
    global $wpdb, $user_ID;

/*	
	//testing purposes code
	//we create here a necessary amount users records to test then their deleting speed.
	
	if ($_GET['create_users'] == '1') {
		IUD_create_arb_user($_GET['n'] ? $_GET['n'] + 0 : 100);
	}
	//testing purposes code
*/
	$ul = isset($_POST['user_level']) ?  $_POST['user_level'] + 0 : 0;
?>
<div class="wrap">
<h2><?php echo __('Inactive users deleter tool.')?></h2>
<p><?php echo __('When your project lives so long, site accumulates a lot of fake users (usually it is spammer\'s registrations). This tool will help you to clean this mess up')?>.</p>
<p><?php echo __('You can do it manually (one by one) or automatically (be carefull with me, baby :))')?>.</p>
<h3><?php echo __('Let\'s do it automatically')?></h3>
<p><?php echo __('Choose the criterias, then check the user\'s list that is will displayed. If this list will correct - hit the button &laquo;Kill them all&raquo;')?>.</p>

<form method="POST" action="" id="inactive-user-deleter-form">
<input type="hidden" name="op" value="search_users" />
<table >
<tr><th><?php echo __('Criteria')?></th><th align="left"><?php echo __('Description')?></th></tr>
<tr><td align="center"><input id="flag_no_approve" type="checkbox" name="no_approve" value="yes" <?php echo empty($_POST['no_approve']) ? '' : 'checked' ?> /></td><td><label for="flag_no_approve"><?php echo __('User has no approved comments. User registered, but do nothing on your website. He (or she) has no any approved comments.')?></label></td></tr>
<tr><td align="center"><input id="flag_has_spam" type="checkbox" name="has_spam" value="yes" <?php echo empty($_POST['has_spam']) ? '' : 'checked' ?> /></td><td><label for="flag_has_spam"><?php echo __('User has spam comments')?></label></td></tr>
<tr><td align="center"><input id="flag_no_recs" type="checkbox" name="no_recs" value="yes" <?php echo empty($_POST['no_recs']) ? '' : 'checked' ?> /></td><td><label for="flag_no_recs"><?php echo __('User has no records or posts')?></label></td></tr>
<tr><td align="center"><input id="flag_daysleft" type="checkbox" name="f_daysleft" value="yes" <?php echo empty($_POST['f_daysleft']) ? '' : 'checked' ?> /></td><td><label for="flag_daysleft"><?php echo __('User was created more then')?> <input type="text" size="4" name="daysleft" value="<?php echo isset($_POST['daysleft']) ? intval($_POST['daysleft']) : 60 ?>" /><?php echo __('days ago. Set number of days.')?></label></td></tr>
<tr><td colspan="2">
	<label for="user_level"><?php echo __('User level ')?></label>
	<select name="user_level_eq">
<?php
	$columns = array('>=', '<=');
	foreach($columns as $v) {
		print '<option value="' . $v . '" ' . ($_POST['user_level_eq'] == $v ? 'selected' : '') . '>' . $v . '</option>';
	}
?>
	</select>
	<input id="user_level" size="2" maxlength="2" type="text" name="user_level" value="<?php echo $ul?>" />
	<br /><small><?php echo __('User level == 10 is admin, 0 is nobody.')?></small></td></tr>
<tr style="border-top: 1px solid #000000">
	<td align="left" colspan="2">
		<label for="sort_order"><?php echo __('Sort by column')?></label>
		<select id="sort_order" name="sort_order" />
<?php
	$columns = array('login', 'name', 'userlevel', 'regdate', 'posts', 'spam', 'comments');
	foreach($columns as $v) {
		print '<option value="' . $v . '" ' . ($_POST['sort_order'] == $v ? 'selected' : '') . '>' . $v . '</option>';
	}
?>
		</select>
	</td>
	</tr>
<tr><td colspan="2"><input type="submit" size="4" value="<?php echo __('Search')?>" /></td></tr>
</table>

<?php 

    if (!isset($_POST['op'])) $_POST['op'] = 'stand_by';

    switch ($_POST['op']) {
    case 'stand_by':
        //i like it
        break;
    case 'finally_delete':
    case 'delete':
        //delete all selected users
        echo '<hr />';
        if (empty($_POST['f_users'])) {
            echo 'I done all work. Really it was nothing to do. So I did nothing. :) You didn\'t select any user.';
        } else {
            if ( !current_user_can('delete_users') ) __('You can&#8217;t delete users (no rights). Sorry.... :)');
            else 
            if ($_POST['op'] == 'finally_delete') {
            
                echo "Deleting...<br />";
                $cnt_deleted = 0;
                foreach($_POST['f_users'] as $user_id_to_delete) {
                    //real delete
    
                    if ($user_id_to_delete == $user_ID) {
                        echo 'I can\'t to delete your profile ! <br />';
                        continue; //i never will delete current-user
                    }

                    if ($user_id_to_delete == 1) {
                        echo 'I will never delete super-user !<br />';
                        continue; //i never will delete admin-user
                    }
					
					if (get_user_option('wp_user_level', $user_id_to_delete) >= 10) {
                        echo 'I will never delete user with admin privileges !<br />';
                        continue; //i never will delete admin-user
					}
					
                    wp_delete_user($user_id_to_delete);
                    $cnt_deleted ++;
                }
                if ($cnt_deleted == 1) echo $cnt_deleted . ' ' . __('user was deleted.');
                else echo $cnt_deleted . ' ' . __('users were deleted.');
                
            } else {
                if (!is_array($_POST['f_users'])) $_POST['f_users'] = array($_POST['f_users']);
                echo '<span style="background-color: red; padding: 5px; color: white;">This is my last warning !</span><br /><br />
                    This is very serious, I will delete - ' . count($_POST['f_users']) . ' user(s). Data will be erased permanently and cannot be 
                    restored automatically.<br />Do you will proceed ? 
                    <input type="button" value="Yes!" onclick="this.form.op.value=\'finally_delete\'; this.form.submit();"/>&nbsp;
                    <input type="button" value="No, don\'t do it, please !" onclick="this.form.submit();"/>';
            }
        }
    case 'search_users':
        //Ooohh damn, i hate to work!
		$conditions = array();
		
		$days = $_POST['daysleft'] + 0;
		if ($days > 0 && $_POST['f_daysleft'] == 'yes') {
			$tmStr = date('Y-m-d H:i:s', time() - $days * 86400);
			$conditions[] = "WU.user_registered < '$tmStr'";
		}
		
		if ($_POST['user_level_eq'] == '>=') {
			$conditions[] = "(WUM.meta_value >= $ul OR (WUM.meta_value IS NULL AND $ul <= 0))";
		} else {
			$conditions[] = "(WUM.meta_value <= $ul OR (WUM.meta_value IS NULL AND $ul >= 0))";
		}
		
        $query = "
            SELECT COUNT(WC.comment_ID) as approved, COUNT(WC2.comment_ID) as spam, 
                WU.ID, WU.user_login as login, WU.user_url as url, WU.user_registered as dt_reg, 
				WU.display_name as name,
				WUM.meta_value as USL
            FROM $wpdb->users WU 
            LEFT JOIN $wpdb->comments WC ON WC.user_id = WU.ID AND WC.comment_approved = 1
            LEFT JOIN $wpdb->comments WC2 ON WC2.user_id = WU.ID AND WC2.comment_approved = 'spam'
			LEFT JOIN $wpdb->usermeta WUM ON WUM.user_id = WU.ID AND WUM.meta_key = 'wp_user_level'
            WHERE " . implode(' AND ' , $conditions) . "
			GROUP BY WU.ID, WU.user_login, WU.user_url, WU.user_registered, WU.display_name ";
			
		switch ($_POST['sort_order']) {
		case 'name':
			$sort_order = 'WU.display_name';
			break;
		case 'regdate':
			$sort_order = 'WU.user_registered';
			break;
		case 'spam':
			$sort_order = 'COUNT(WC2.comment_ID) DESC, WU.user_login';		
			break;
		case 'userlevel':
			$sort_order = 'WUM.meta_value DESC, WU.user_login';				
			break;
		case 'comments':
			$sort_order = 'COUNT(WC.comment_ID) DESC, WU.user_login';
			break;
		case 'posts':
		default:
			$sort_order = 'WU.user_login';
		}
		
		$query .= " ORDER BY $sort_order";

        $rows = $wpdb->get_results($query, ARRAY_A);

        $user_list = array();
        
        if (!empty($rows)) 
            foreach($rows as $k => $UR) {
                if (!empty($_POST['no_approve']) && $UR['approved']) continue;
                else 
                if (!empty($_POST['has_spam']) && !$UR['spam']) continue;
                $UR['recs'] = 0;
                $user_list[$UR['ID']] = $UR; 
            }
        
		//clean up with registration lifetime ctiteria + check user norecs criteria + count publish posts
        $query = "
            SELECT COUNT(WP.ID) as recs, WU.ID
            FROM $wpdb->users WU 
            LEFT JOIN $wpdb->posts WP ON WP.post_author = WU.ID AND NOT WP.post_type in ('attachment', 'revision') AND post_status = 'publish'
            WHERE 1 " . (empty($_POST['f_daysleft']) ? '' : "AND WU.user_registered < '$tmStr' ") . " GROUP BY WU.ID";

        $rows = $wpdb->get_results($query, ARRAY_A);
        
        if (!empty($rows)) 
            foreach($rows as $k => $UR) {
                $id = $UR['ID'];
                if (isset($user_list[$id])) $user_list[$id]['recs'] = $UR['recs'];
                if (!empty($_POST['no_recs']) && $UR['recs']) unset($user_list[$id]);
            }

		//user's list output
        if (empty($user_list)) {
            echo __('<p><b>No users are found.</b></p>');
        } else {
			echo '<p><b>' . count($user_list) . ' ' . __('record(s) are found.') . '</b></p>';
			
            echo '<hr>' . __('Check this list') . '. <input type="button" value="' . __('Mark all') . '" onclick="
                var f_elm = this.form[\'f_users[]\'];
                if (f_elm.length > 0) {
                    for(i=0; i<f_elm.length; i++)
                        f_elm[i].checked = true;
                } else f_elm.checked = true;
            " /> <input type="button" value="' . __('Unmark all') . '"  onclick="
                f_elm = this.form[\'f_users[]\'];
                if (f_elm.length > 0) {
                    for(i=0; i<f_elm.length; i++)
                        f_elm[i].checked = false;
                } else f_elm.checked = false;
            " /> ' . __('When everything is ready') . ' - <input type="button" value="' . __('Delete all marked users') . '" onclick="
                if (confirm(\'Yes, I really want to delete all marked users.\')) {
                    this.form.op.value=\'delete\';
                    this.form.submit();
                }
            "/>
			<style>
				.clickable {
					cursor: pointer;
				}
			</style>
            <table cellpadding="3"><tr>
				<th>No.</th>
				<th>' . __('Mark') . '</th>
				<th class="clickable" width="150" align="left" onclick="jQuery(\'#sort_order\').val(\'login\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('Login') . '</th>
				<th class="clickable" onclick="jQuery(\'#sort_order\').val(\'name\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('Name') . '</th>
                <th class="clickable" onclick="jQuery(\'#sort_order\').val(\'userlevel\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('User level') . '</th>
                <th class="clickable" width="120" onclick="jQuery(\'#sort_order\').val(\'regdate\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('Reg date') . '</th>
				<th>' . __('Published posts') . '</th>
				<th class="clickable" onclick="jQuery(\'#sort_order\').val(\'spam\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('Spam comments') . '</th>
				<th class="clickable" onclick="jQuery(\'#sort_order\').val(\'comments\'); jQuery(\'#inactive-user-deleter-form\').submit(); ">' . __('Approved comments') . '</th></tr>';
            
            $i = 0;
            foreach($user_list as $UR) {
                $i++;
                $color = $i % 2 ? '#FFFFEE' : '#EEFFFF';
                echo "<tr align=\"center\" style=\"background-color:$color\" ><td>$i.</td><td>";
				if ($UR['USL'] >= 10 || $UR['ID'] == 1) {
					echo "-";
				} else {
					echo "<input type=\"checkbox\" name=\"f_users[]\" value=\"$UR[ID]\"/ " 
                . (isset($_POST['f_users']) && in_array($UR['ID'], $_POST['f_users']) ? 'checked' : '') 
                . ">";
				}
				echo "
					</td>
                    <td align=\"left\">"
                    . (empty($UR['url']) ? $UR['login'] : "<a href=\"$UR[url]\" target=\"_blank\">$UR[login]</a>")
                    . "</td><td>$UR[name]</td>"
					. "</td><td>" . ($UR['USL'] ? $UR['USL'] : '-') . "</td><td>"
					. date('d M Y', strtotime($UR['dt_reg'])) . "</td><td>" 
					. ($UR['recs'] ? $UR['recs'] : '-') 
					. "</td><td>"
					. ($UR['spam'] ? $UR['spam'] : '-') 
					. "</td><td>" 
					. ($UR['approved'] ? $UR['approved'] : '-') 
					. "</td></tr>\n";
            }
            ?></table><?php
           
        }
        
        break;
    }

?>
</form>
</div>
<?php
}

/* fast user generation routine - only for tests */
function IUD_create_arb_user($n = 100) {
	while($n-- > 0) {
		$asr = rand(1000000, 10000000);
		wp_create_user('usr_' . $asr, 'pass_'. $asr, $asr . '@mail.ru');
	}
}
?>