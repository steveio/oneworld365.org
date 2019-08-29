<?

define('IN_PHPBB', true);
$phpbb_root_path = '/www/vhosts/summercampworldwide.com/htdocs/chat/';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

$userdata = session_pagestart($user_ip, PAGE_INDEX);
init_userprefs($userdata);
//
// End session management
//

$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$newest_userdata = get_db_stat('newestuser');
$newest_user = $newest_userdata['username'];
$newest_uid = $newest_userdata['user_id'];


$sql = "SELECT 
   t.topic_title as title, 
   t.topic_id as id, 
   f.forum_id,
   f.forum_name,
   count(p.post_id) as posts,
   max(p.post_time)::abstime::timestamp with time zone at time zone 'GMT'::timestamp as last_post,
   u.username 
FROM 
   phpbb_topics t, 
   phpbb_forums f, 
   phpbb_posts p,
   phpbb_users u
WHERE 
   f.forum_id = t.forum_id AND
   p.topic_id = t.topic_id AND 
   t.topic_poster = u.user_id
GROUP BY t.topic_title, t.topic_id, f.forum_id, f.forum_name, u.username, t.topic_time
ORDER BY topic_time DESC 
LIMIT 6;";


$recent_posts = $db->sql_query($sql);


?>
