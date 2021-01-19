<?php

/**
 * Class DataLayer
 *
 * @property mysqli connection
 */
class DataLayer
{
    private $connection;

    public function __construct($host, $user, $pass, $database)
    {
        $this->connection = mysqli_connect($host, $user, $pass) ?? die('could not connect to database');
        $this->connection->select_db($database);

    }

    private function EscapeQuery($sql, $params)
    {
        $conn = $this->connection;

        $count = 0;
        return preg_replace_callback("/\{\{(.*?)\}\}/i", function ($result)
        use ($params, &$count, $conn, $sql) {
            if (isset($result[1])) {

                if (isset($params[$count])) {
                    $count++;
                    if ($result[1] !== 'nq') {
                        return '"' . mysqli_escape_string($conn, $params[$count - 1]) . '"';
                    } else {
                        return $params[$count - 1]; // don't use mysqli_escape_string here because it will escape quotes which breaks things
                    }
                }

                if (isset($params[$result[1]])) {
                    if (is_array($params[$result[1]])) {
                        die(print_r(['Error: Parameter cannot be array', $params], true));
                    }
                    return '"' . mysqli_escape_string($conn, $params[$result[1]]) . '"';
                }
                die(print_r([[$sql, $params], $result[0] . ' does not having a matching parameter (mysql_escape_query).'], true));
            }
            return null;
        }, $sql);
    }

    /**
     * @param $sql
     * @param null $params
     * @return array
     */
    public function Execute($sql, $params = null)
    {
        $aff = 0;
        $sql = $params && is_array($params) ? $this->EscapeQuery($sql, $params) : $sql;

        mysqli_begin_transaction($this->connection);
        $res = mysqli_multi_query($this->connection, $sql);
        if ($res) {
            do {
                /* store first result set */
                if ($result = mysqli_store_result($this->connection)) {
                    mysqli_free_result($result);
                }
                $aff += mysqli_affected_rows($this->connection);
            } while (mysqli_more_results($this->connection)
            && mysqli_next_result($this->connection));
        }
        $last_id = mysqli_insert_id($this->connection);


        $error = mysqli_error($this->connection);
        if ($error) {
            mysqli_rollback($this->connection);
        } else {
            mysqli_commit($this->connection);
        }

        return [
            'error' => $error,
            'sql' => $sql,
            'last_id' => $last_id,
            'affected_rows' => $aff,
        ];
    }

    /**
     * @param $sql
     * @param null $params
     * @param null $map
     * @return array|string[]
     */
    public function Query($sql, $params = null, $map = null)
    {
        $sql = $params && is_array($params) ? $this->EscapeQuery($sql, $params) : $sql;

        $return = [
            'error' => '',
            'data' => ''
        ];


        $list = [];
        $res = @mysqli_query($this->connection, $sql, MYSQLI_USE_RESULT);

        if ($res && is_object($res)) {
            while ($r = mysqli_fetch_assoc($res)) {
                $list[] = !is_null($map) ? call_user_func($map, $r) : $r;
            }

            mysqli_free_result($res);

            do {
                /* store first result set */
                if ($result = mysqli_store_result($this->connection)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_more_results($this->connection)
            && mysqli_next_result(
                $this->connection
            ));
        }

        $return['error'] = mysqli_error($this->connection);
        $return['sql'] = $sql;
        $return['data'] = $list;


        if (!$map || $return['error']) {
            return $return;
        }

        return $return['data'];
    }

    public function ChangeUsername($user_id, $username)
    {
        $sql = '
            SELECT
                user_id
            FROM
                user 
            WHERE username = {{username}}
                AND user_id <> {{user_id}}
        ';
        $res = $this->Query($sql, [
            'username' => $username,
            'user_id' => $user_id,
        ]);
        if (sizeof($res['data'])) {
            return 'Invalid Username';
        }

        $sql = '
            UPDATE
                user 
            SET 
                username = {{username}}
            WHERE
                user_id = {{user_id}}
        ';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'username' => $username,
        ]);
        if ($res['error']) {
            return 'Could not update username';
        }
        $_SESSION['username'] = $username;
        return 0;
    }

    public function ChangePassword($user_id, $password)
    {
        $sql = '
            UPDATE
                user 
            SET 
                password = {{password}}
            WHERE
                user_id = {{user_id}}
        ';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'password' => hash('sha256', $password),
        ]);
        if ($res['error']) {
            return 2;
        }
        return 0;
    }

    public function ChangeGUID($user_id)
    {
        $sql = '
            UPDATE
                user 
            SET 
                guid = {{guid}}
            WHERE
                user_id = {{user_id}}
        ';
        $guid = GUID();
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'guid' => $guid,
            ]);
        if ($res['error']) {
            return 'GUID could not be changed';
        }
        $_SESSION['user_guid'] = $guid;
        return 0;
    }


    /**
     * @param $username
     * @param $password
     * @return int
     */
    public function CreateUser($username, $password)
    {
        $sql = '
            SELECT
                user_id
            FROM
                user 
            WHERE username = {{username}}
        ';
        $res = $this->Query($sql, ['username' => $username]);
        if (sizeof($res['data'])) {
            return 1;
        }

        $sql = '
            INSERT INTO
                user 
            SET 
                username = {{username}},
                password = {{password}},
                guid = {{guid}},
                created_at = {{created_at}}
        ';
        $res = $this->Execute($sql, [
            'username' => $username,
            'password' => hash('sha256', $password),
            'guid' => GUID(),
            'created_at' => Timestamp(),
        ]);
        if ($res['error']) {
            return 2;
        }
        return 0;
    }

    /**
     * @param $username
     * @param $password
     * @return int
     */
    public function GetUser($username, $password)
    {
        $sql = '
            SELECT
                user_id,
               guid
            FROM
                user 
            WHERE 
                username = {{username}}
                AND password = {{password}}
        ';
        $res = $this->Query($sql, [
            'username' => $username,
            'password' => hash('sha256', $password),
        ]);
        if (sizeof($res['data'])) {
            $_SESSION['user_guid'] = $res['data'][0]['guid'];
            return (int)$res['data'][0]['user_id'];
        }
        return 0;
    }

    public function GetFriends($user_id)
    {
        $sql = 'SELECT remote_guid, remote_domain, user_guid FROM friend_list WHERE user_id = {{user_id}} AND is_mute = 0';
        $friends = [];
        $res = $this->Query($sql, ['user_id' => $user_id]);
        foreach ($res['data'] as $row) {
            $is_self = strcasecmp($row['remote_domain'], $_SERVER['HTTP_HOST']) == 0;
            if ($is_self) {
                $b = $this->GetFriend($row['remote_guid'], $row['user_guid'], $row['remote_domain']);
                $friends[] = [
                    'version' => 'username_remote_domain_guid',
                    'username' => $b ?? null,
                    'remote_domain' => $row['remote_domain'],
                    'remote_guid' => $row['remote_guid'],
                ];
            } else {
                $c = Curl::Post('https://' . $row['remote_domain'] . '/api/friend', [
                    'user_guid' => $row['remote_guid'],
                    'remote_guid' => $row['user_guid'],
                    'remote_domain' => $_SERVER['HTTP_HOST'],
                ]);
                $b = json_decode($c->Body);
                $friends[] = [
                    'version' => 'username_remote_domain',
                    'username' => $b->data ?? null,
                    'remote_domain' => $row['remote_domain'],
                    'remote_guid' => $row['remote_guid'],
                ];
            }
        }
        usort($friends, function ($a, $b) {
            return strcasecmp($a['username'], $b['username']);
        });
        return $friends;
    }

    /**
     * @param $user_id
     * @return array|string[]
     */
    public function GetKeys($user_id)
    {
        $sql = 'SELECT user_guid, is_sticky FROM friend_request WHERE completed_at IS NULL AND user_id = {{user_id}} ORDER BY created_at ASC';
        $res = $this->Query($sql, ['user_id' => $user_id], function ($row) {
            return [
                'key' => $row['user_guid'] . ':' . $_SERVER['HTTP_HOST'],
                'is_sticky' => $row['is_sticky'],
                'user_guid' => $row['user_guid'],
            ];
        });
        return $res;
    }

    public function CreateKey($user_id, $is_sticky)
    {
        $sql = 'INSERT INTO friend_request SET user_id = {{user_id}}, user_guid = {{guid}}, is_sticky = {{is_sticky}}, created_at = NOW()';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'is_sticky' => (int)$is_sticky ? 1 : 0,
            'guid' => GUID(),
        ]);
        return $res;
    }

    public function CreatePost($user_id, $post, $link, $image)
    {
        $content = json_encode([
            'version' => 'post_link_image',
            'post' => $post,
            'link' => $link,
            'image' => $image,
        ]);
        $sql = 'INSERT INTO `post` SET post_guid = {{GUID}}, user_id = {{user_id}}, content = {{content}}, created_at = {{created_at}}, content_hash = {{content_hash}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'content' => $content,
            'content_hash' => md5($content),
            'created_at' => Timestamp(),
            'GUID' => GUID(),
        ]);
        return $res;
    }

    public function LogIpAccess($user_guid, $remote_guid, $remote_domain)
    {
        $remote_addr = $_SERVER['REMOTE_ADDR'];

        $sql = '
SELECT 
    ip_access_id, 
       user_guid, 
       is_blocked 
FROM ip_access 
WHERE 
    user_guid = {{user_guid}}
    AND remote_guid = {{remote_guid}}
    AND remote_domain = {{remote_domain}}
    AND remote_addr = {{remote_addr}}
';
        $res = $this->Query($sql,[
            'user_guid' => $user_guid,
            'remote_guid' => $remote_guid,
            'remote_domain' => $remote_domain,
            'remote_addr' => $remote_addr,
        ]);
        if(sizeof($res['data'])) {
            if($res['data'][0]['is_blocked'] == 1) {
                return 0;
            }
            $sql = '
            UPDATE
                ip_access
            SET
                last_seen_at = {{last_seen_at}}
            WHERE
                ip_access_id = {{ip_access_id}}
            ';
            $this->Execute($sql,[
                'ip_access_id' => $res['data'][0]['ip_access_id'],
                'last_seen_at' => Timestamp(),
            ]);

        } else {
            $sql = '
INSERT INTO 
    ip_access
SET
    user_guid = {{user_guid}},
    remote_guid = {{remote_guid}},
    remote_domain = {{remote_domain}},
    remote_addr = {{remote_addr}},
    created_at = {{created_at}},
    last_seen_at = {{last_seen_at}}
';
            $this->Execute($sql,[
                'user_guid' => $user_guid,
                'remote_guid' => $remote_guid,
                'remote_domain' => $remote_domain,
                'remote_addr' => $remote_addr,
                'created_at' => Timestamp(),
                'last_seen_at' => Timestamp(),
            ]);
        }
        return 1;
    }

    public function GetFriendFeed($user_guid, $remote_guid, $remote_domain)
    {
        if(!$this->LogIpAccess($user_guid, $remote_guid, $remote_domain)) {
            return [];
        }

        $sql = 'SELECT user_id FROM friend_list WHERE user_guid = {{user_guid}} AND remote_guid = {{remote_guid}} AND remote_domain = {{remote_domain}}';
        $res = $this->Query($sql, [
            'user_guid' => $user_guid,
            'remote_guid' => $remote_guid,
            'remote_domain' => $remote_domain,
        ]);
        if ($res['error']) {
            return null;
        }
        $user_id = $res['data'][0]['user_id'] ?? null;
        if (!$user_id) {
            return null;
        }
        return $this->GetYourFeed($user_id, false, $remote_domain);
    }

    /**
     * @param $user_id
     * @param bool $you
     * @return array|string[]
     */
    public function GetYourFeed($user_id, $you = true, $remote_domain = null)
    {
        // your posts
        $sql = '
SELECT 
       post.post_guid,
       post.content,
       post.created_at,
       user.username AS author
FROM post 
    INNER JOIN user ON user.user_id = post.user_id 
WHERE post.user_id = {{user_id}}
AND post.created_at >= NOW() - INTERVAL ' . POST_DAYS_LIMIT . ' DAY
ORDER BY post.created_at DESC
    LIMIT ' . POST_NUMBER_LIMIT;
        $res = $this->Query($sql, ['user_id' => $user_id], function ($row) use ($you, $remote_domain, $user_id) {
            $content = json_decode($row['content'], true);
            if($_SESSION['user_id'] == $user_id) {
                $content['can_delete'] = 1;
            } else {
                $content['can_delete'] = 0;
            }
            $content['post_guid'] = $row['post_guid'];
            $content['post_domain'] = $_SERVER['HTTP_HOST'];
            $content['post_hash'] = md5($row['post_guid'] . '@' . $_SERVER['HTTP_HOST']);
            $content['created_at'] = Timestamp(strtotime($row['created_at']) + $_SESSION['hour_offset'] * 3600);
            $content['author'] = $you ? '<i>You</i>' : $row['author'] . '@' . $remote_domain;
            return $content;
        });
        return $res;
    }

    public function GetFeed($user_id)
    {
        $friend_feeds = [];
        // friend posts
        $sql = 'SELECT remote_guid, remote_domain, user_guid, user_id FROM friend_list WHERE user_id = {{user_id}}';
        $res = $this->Query($sql, ['user_id' => $user_id]);
        foreach ($res['data'] as $row) {
            $is_self = strcasecmp($row['remote_domain'], $_SERVER['HTTP_HOST']) == 0;
            if ($is_self) {
                $sql = 'SELECT user_id FROM friend_list WHERE remote_guid = {{remote_guid}} AND remote_domain = {{remote_domain}}';
                $res = $this->Query($sql, ['remote_guid' => $row['user_guid'], 'remote_domain' => $row['remote_domain']]);
                $remote_user_id = $res['data'][0]['user_id'] ?? null;
                if ($remote_user_id) {
                    $this->LogIpAccess($row['remote_guid'], $row['user_guid'], $_SERVER['HTTP_HOST']);
                    $b = $this->GetYourFeed($remote_user_id, false, $row['remote_domain']);
                    $friend_feeds[] = json_decode(json_encode($b));
                }
            } else {
                $c = Curl::Get('https://' . $row['remote_domain'] . '/api/friend_feed', [
                    'user_guid' => $row['remote_guid'],
                    'remote_guid' => $row['user_guid'],
                    'remote_domain' => $_SERVER['HTTP_HOST'],
                ]);
                $b = json_decode($c->Body);
                if ($b) {
                    foreach($b->data as $i => $item) {
                        $b->data[$i]->created_at = Timestamp(strtotime($item->created_at) + $_SESSION['hour_offset'] * 3600);
                    }
                    $friend_feeds[] = $b->data;
                }
            }
        }

        $res = $this->GetYourFeed($user_id);

        if (sizeof($friend_feeds)) {
            foreach ($friend_feeds as $items) {
                foreach ($items as $item) {
                    $res[] = json_decode(json_encode($item), true);
                }
            }
        }

        $filter = [];
        foreach ($res as $item) {
            $hash = md5(json_encode($item));
            $filter[$hash] = $item;
        }
        $res = array_values($filter);

        usort($res, function ($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        foreach($res as $i => $item) {
            foreach($item as $k => $v) {
                $res[$i][$k] = strip_tags($v);
            }
        }
        return $res;
    }

    public function Handshake($guid, $my_guid, $my_host)
    {
        $sql = 'SELECT user_id, is_sticky FROM friend_request WHERE user_guid = {{guid}} AND (is_sticky = 1 OR completed_at IS NULL)';
        $res = $this->Query($sql, ['guid' => $guid]);

        if (!sizeof($res['data'])) {
            return null;
        }

        $user_id = $res['data'][0]['user_id'];
        $is_sticky = $res['data'][0]['is_sticky'];
        $user_guid = GUID();

        $user_guid = $this->AddFriend($user_id, $user_guid, $my_guid, $my_host, $is_sticky);


        if (!$is_sticky) {
            $sql = 'UPDATE friend_request SET completed_at = {{completed_at}} WHERE user_guid = {{guid}}';
            $this->Execute($sql, ['guid' => $guid, 'completed_at' => Timestamp()]);
        }

        return $user_guid;
    }

    public function AddFriend($user_id, $user_guid, $remote_guid, $remote_domain, $is_sticky)
    {
        $sql = 'INSERT INTO friend_list SET user_id = {{user_id}}, user_guid = {{user_guid}}, remote_guid = {{remote_guid}}, remote_domain = {{remote_domain}}, created_at = {{created_at}}, is_mute = {{is_mute}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'user_guid' => $user_guid,
            'remote_guid' => $remote_guid,
            'remote_domain' => $remote_domain,
            'created_at' => Timestamp(),
            'is_mute' => $is_sticky ? 1 : 0,
        ]);
        if ($res['error']) {
            return null;
        }
        return $user_guid;
    }

    public function GetFriend($user_guid, $remote_guid, $remote_domain)
    {
        $sql = '
        SELECT
            user.username
        FROM
            friend_list 
        INNER JOIN
            user ON user.user_id = friend_list.user_id

        WHERE
            user_guid = {{user_guid}}
            AND remote_guid = {{remote_guid}}
            AND remote_domain = {{remote_domain}}
        ';
        $res = $this->Query($sql, [
            'user_guid' => $user_guid,
            'remote_guid' => $remote_guid,
            'remote_domain' => $remote_domain,
        ]);
        if ($res['error']) {
            return null;
        }
        return $res['data'][0]['username'] ?? null;
    }

    public function DeleteKey($user_id, $key_guid)
    {
        $sql = 'DELETE FROM friend_request WHERE user_id = {{user_id}} AND user_guid = {{user_guid}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'user_guid' => $key_guid,
        ]);
        return $res;
    }

    public function DeleteFriend($user_id, $remote_guid, $remote_domain)
    {
        $sql = 'DELETE FROM friend_list WHERE user_id = {{user_id}} AND remote_guid = {{remote_guid}} AND remote_domain = {{remote_domain}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'remote_guid' => $remote_guid,
            'remote_domain' => $remote_domain,
        ]);
        return $res;
    }

    public function DeletePost($user_id, $post_guid)
    {
        $sql = 'DELETE FROM post WHERE user_id = {{user_id}} AND post_guid = {{post_guid}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'post_guid' => $post_guid,
        ]);
        return $res;
    }

    public function DeleteComment($user_id, $response_guid)
    {
        $sql = 'DELETE FROM post_response WHERE user_id = {{user_id}} AND response_guid = {{response_guid}}';
        $res = $this->Execute($sql, [
            'user_id' => $user_id,
            'response_guid' => $response_guid,
        ]);
        return $res;
    }

    public function Comment($post_guid, $post_domain, $comment)
    {
        $comment = strip_tags($comment);
        $sql = '
    INSERT INTO
        post_response
    SET
        user_id = {{user_id}},
        response_guid = {{response_guid}},
        post_guid = {{post_guid}},
        post_domain = {{post_domain}},
        content = {{content}},
        user_at_domain = {{user_at_domain}},
        remote_hash = {{remote_hash}},
        created_at = {{created_at}}
        ';
        $this->Execute($sql, [
            'user_id' => $_SESSION['user_id'],
            'response_guid' => GUID(),
            'post_domain' => $post_domain,
            'post_guid' => $post_guid,
            'content' => $comment,
            'user_at_domain' => $_SESSION['username'] . '@' . $_SERVER['HTTP_HOST'],
            'remote_hash' => md5($_SESSION['user_id'] . '@' . $_SERVER['HTTP_HOST']),
            'created_at' => Timestamp(),
        ]);
        return null;
    }

    public function GetComments($user_id, $post_guid, $post_domain)
    {
        $list = [];

        if ($user_id) {
            // remote comments from friend's servers
            $sql = 'SELECT DISTINCT remote_domain FROM friend_list WHERE user_id {{user_id}} AND remote_domain <> {{remote_domain}}';
            $res = $this->Query($sql, ['user_id' => $user_id, 'remote_domain' => $_SERVER['HTTP_HOST']]);
            if(sizeof($res['data'])) {
                foreach($res['data'] as $row) {
                    $comments = Curl::Post('https://' . $row['remote_domain'] . '/post_comments', [
                        'post_guid' => $post_guid,
                        'post_domain' => $post_domain,
                    ]);
                    if($comments->Body) {
                        $cs = json_decode($comments->Body, true);
                        foreach($cs as $item) {
                            $item['created_at'] = Timestamp(strtotime($item['created_at']) + $_SESSION['hour_offset'] * 3600);
                            $item['content'] = strip_tags($item['content']);
                            $list[] = $item;
                        }
                    }
                }
            }
        }

        // local comments
        $sql = 'SELECT user_id, response_guid, user_at_domain, content, remote_hash, created_at FROM post_response WHERE post_guid = {{post_guid}} AND post_domain = {{post_domain}} ORDER BY created_at ASC';
        $res = $this->Query($sql, ['post_guid' => $post_guid, 'post_domain' => $post_domain]);

        foreach($res['data'] as $item) {
            if($item['user_id'] == $_SESSION['user_id']) {
                $item['can_delete'] = 1;
            } else {
                $item['can_delete'] = 0;
            }
            unset($item['user_id']);
            $item['created_at'] = Timestamp(strtotime($item['created_at']) + $_SESSION['hour_offset'] * 3600);
            $item['content'] = strip_tags($item['content']);
            $list[] = $item;
        }

        usort($list, function($a, $b) {
            return strcasecmp($a['created_at'], $b['created_at']);
        });

        return $list;

    }

}