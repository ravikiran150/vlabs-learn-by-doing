<?php

/**
 * @author Antonio Duran
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package joomdle
 *
 * Authentication Plugin: Joomdle XMLRPC auth
 *
 * SSO with XMLRPC used to connect with Joomla
 *
 * 2008-11-01  File created.
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
//require_once $CFG->dirroot . '/mnet/xmlrpc/client.php';
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/joomdle/auth.php');

// it gives a warning if no context set, I guess it does nor matter which we use
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
/*
if (!$site = get_site()) {
    print_error('mnet_session_prohibited', 'mnet', '', '');
}

if (!is_enabled_auth('mnet')) {
    error('mnet is disabled');
}
*/
// grab the GET params
$token         = optional_param('token',  '',  PARAM_TEXT);
$username = optional_param('username',   '',   PARAM_TEXT);
$username = strtolower ($username);
$create_user = optional_param('create_user', '',     PARAM_TEXT);
$wantsurl      = optional_param('wantsurl', '',PARAM_TEXT);
$use_wrapper      = optional_param('use_wrapper', '', PARAM_TEXT);
$id      = optional_param('id', '', PARAM_TEXT);
$course_id      = optional_param('course_id', '', PARAM_TEXT); //additional course_id param used for quiz view
$mtype      = optional_param('mtype', '', PARAM_TEXT);
$day      = optional_param('day', '', PARAM_TEXT);
$mon      = optional_param('mon', '', PARAM_TEXT);
$year      = optional_param('year', '', PARAM_TEXT);
$itemid      = optional_param('Itemid', '', PARAM_TEXT);
$lang      = optional_param('lang', '', PARAM_TEXT);
$topic      = optional_param('topic', '', PARAM_TEXT);
$redirect      = optional_param('redirect', '', PARAM_TEXT); //redirect moodle param

$auth = new auth_plugin_joomdle ();

$override_itemid = $auth->call_method ('getDefaultItemid');

if ($override_itemid)
	$itemid = $override_itemid;


if (($username != 'guest') && (!isloggedin()))
//if ($username != 'guest')
{
	/* Logged user trying to access */
	$logged = $auth->call_method ("confirmJoomlaSession", $username, $token);

	if (is_array ($logged) && xmlrpc_is_fault($logged)) {
	    trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
	} else 
		if ($logged) {
			// log in
			$user = get_complete_user_data('username', $username);
			if (!$user)
			{
				if ($create_user)
					$auth->create_joomdle_user ($username); //XXX
				else
				{
					/* If the user does not exists and we don't have to create it, we are done */
					$redirect_url = get_config (NULL, 'joomla_url');
					redirect($redirect_url);
				}

			}
			$user = get_complete_user_data('username', $username);
			complete_user_login($user);

			/*
			if (!empty($localuser->mnet_foreign_host_array)) {
			    $user->mnet_foreign_host_array = $localuser->mnet_foreign_host_array;
			}
			*/
	} //logged
} //username != guest
			// redirect
			if ($use_wrapper)
			{
				$redirect_url = get_config (NULL, 'joomla_url');
				switch ($mtype) 
				{
					case "event":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&day=$day&mon=$mon&year=$year&Itemid=$itemid";
						break;
					case "course":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
						if ($topic)
							$redirect_url .= '&topic='.$topic;
						break;
					case "news":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
						break;
					case "forum":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					case "user":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
						break;
					case "edituser":
                        $redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&Itemid=$itemid";
                        break;
					case "resource":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					case "quiz":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					case "page":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					case "assignment":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					case "folder":
						$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						break;
					default:
						if ($mtype)
							$redirect_url .= "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=$mtype&id=$id&course_id=$course_id&Itemid=$itemid";
						else
						{
							if ($wantsurl)
								$redirect_url =  urldecode ($wantsurl) ;
							else
								$redirect_url = get_config (NULL, 'joomla_url');
						}
				} 
				if ($redirect)
					$redirect_url .= "&redirect=1";
			}
			else
			{
				$redirect_url = $CFG->wwwroot;
				switch ($mtype)
				{
					case "course":
						$redirect_url .= "/course/view.php?id=$id";

						if ($topic)
							$redirect_url .= '&topic='.$topic;
						break;
					case "news":
						$redirect_url .= "/mod/forum/discuss.php?d=$id";
						break;
					case "forum":
						$redirect_url .= "/mod/forum/view.php?id=$id";
						break;
					case "event":
						$redirect_url .= "/calendar/view.php?view=day&cal_d=$day&cal_m=$mon&cal_y=$year";
						break;
					case "user":
						$redirect_url .= "/user/view.php?id=$id";
						break;
					case "resource":
						$redirect_url .= "/mod/resource/view.php?id=$id";
						break;
					case "quiz":
						$redirect_url .= "/mod/quiz/view.php?id=$id";
						break;
					case "page":
						$redirect_url .= "/mod/page/view.php?id=$id";
						break;
					case "assignment":
						$redirect_url .= "/mod/assignment/view.php?id=$id";
						break;
					case "folder":
						$redirect_url .= "/mod/folder/view.php?id=$id";
						break;
					default:
						if ($mtype)
							$redirect_url .= "/mod/$mtype/view.php?id=$id";
						else
						{
							preg_match('@^(?:https?://)?([^/]+)@i',
								get_config (NULL, 'joomla_url'), $matches);
							$host = $matches[0];


							/* If not full URL, see if path/host is needed */
							if (($wantsurl) &&
										(substr ($wantsurl, 0, 7) != 'http://') &&
											(substr ($wantsurl, 0, 8) != 'https://'))
							{
								/* If no initial slash, it is a joomla relative path. We add path */
								if ($wantsurl[0] != '/')
								{
									$path = parse_url (get_config (NULL, 'joomla_url'), PHP_URL_PATH);
									$wantsurl = $path.'/'.$wantsurl;
								}


								if ($wantsurl)
									$redirect_url =  $host.urldecode ($wantsurl) ;
									//$redirect_url =  urldecode ($wantsurl) ;
								else
									$redirect_url = get_config (NULL, 'joomla_url');
									//$redirect_url = get_config (NULL, 'joomla_url');
							}
							else $redirect_url = $wantsurl;
						}

				}
				if ($redirect)
					$redirect_url .= "&redirect=1";
			}
if ($lang)
	$redirect_url .= '&lang='.$lang;
redirect($redirect_url);

?>
