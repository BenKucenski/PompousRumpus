<?php

/**
 * Class ApiLayer
 *
 * @property DataLayer $dataLayer
 * @property string $api_version;
 */
class ApiLayer
{
    private $dataLayer;
    private $api_version;

    public function __construct(DataLayer $dataLayer)
    {
        $this->dataLayer = $dataLayer;
    }

    public function Process($endpoint, $request, $server)
    {
        $this->api_version = $server->{'x-dsn-version'} ?? 'pompousrumpus';
        $res['error'] = '';
        switch ($endpoint) {
            case 'delete_comment':
                $this->DeleteComment($_SESSION['user_id'] ?? null, $request->response_guid ?? null);
                break;

            case 'get_comments':
                $res['data'] = $this->GetComments($request->post_guid ?? null, $request->post_domain ?? null);
                break;

            case 'comment':
                $this->Comment($_SESSION['user_id'] ?? null, $request->post_guid ?? null, $request->post_domain ?? null, $request->comment ?? null);
                break;

            case 'delete_post':
                $this->DeletePost($_SESSION['user_id'] ?? null, $request->post_guid ?? null);
                break;

            case 'delete_key':
                $this->DeleteKey($_SESSION['user_id'] ?? null, $request->key_guid ?? null);
                break;

            case 'delete_friend':
                $this->DeleteFriend($_SESSION['user_id'] ?? null, $request->remote_guid ?? null, $request->remote_domain ?? null);
                break;


            case 'friend_feed':
                $res['data'] = $this->GetFriendFeed($request->user_guid ?? null, $request->remote_guid ?? null, $request->remote_domain ?? null);
                break;

            case 'friend':
                $res['data'] = $this->GetFriend($request->user_guid ?? null, $request->remote_guid ?? null, $request->remote_domain ?? null);
                break;

            case 'friends':
                $res['data'] = $this->GetFriends($_SESSION['user_id'] ?? null);
                break;

            case 'handshake':
                $new_guid = $this->Handshake($request->guid ?? null, $request->my_guid ?? null, $request->my_host ?? null);
                $res['data'] = $new_guid;
                break;

            case 'add_friend':
                $this->AddFriend($_SESSION['user_id'] ?? null, $request->friend_code ?? null);
                break;

            case 'feed':
                $res['data'] = $this->GetFeed($_SESSION['user_id'] ?? null);
                break;

            case 'create_post':
                $this->CreatePost($_SESSION['user_id'] ?? null, $request->post ?? null, $request->link ?? null, $request->image ?? null);
                break;

            case 'create_key':
                $this->CreateKey($_SESSION['user_id'] ?? null, $request->is_sticky ?? null);
                break;

            case 'keys':
                $res['data'] = $this->GetKeys($_SESSION['user_id'] ?? null);
                break;

            case 'signout':
                unset($_SESSION['user_id']);
                break;

            case 'register':
                $t = $this->CreateUser($request->username ?? null, $request->password ?? null);
                switch ($t) {
                    case -1:
                    case 1:
                    case 2:
                        $res['error'] = 'Invalid Username or Password: ' . $t;
                        break;
                    case '0':
                        $res['error'] = '';
                        break;
                }
                break;

            case 'signin':
                $t = $this->SignIn($request->username ?? null, $request->password ?? null);
                if ($t <= 0) {
                    $res['error'] = 'Invalid Username or Password';
                } else {
                    $_SESSION['user_id'] = $t;
                    $_SESSION['username'] = $request->username;
                    $_SESSION['hour_offset'] = floor((strtotime($request->time ?? 0) - time()) / 3600);
                }
                break;
        }
        return $res;
    }

    public function CreateUser($username, $password)
    {
        if (!$username || !$password || $password !== KeyboardOnly($password)) {
            return -1;
        }
        $username = KeyboardOnly($username);
        $password = KeyboardOnly($password);
        $res = $this->dataLayer->CreateUser($username, $password);
        return $res;
    }

    public function CreatePost($user_id, $post, $link, $image)
    {
        if (!$user_id) {
            return;
        }

        $post = strip_tags($post);
        $link = strip_tags($link);
        $image = strip_tags($image);

        if (!$post) {
            return;
        }

        $this->dataLayer->CreatePost($user_id, $post, $link, $image);
    }

    public function CreateKey($user_id, $is_sticky)
    {
        if (!$user_id) {
            return;
        }
        $this->dataLayer->CreateKey($user_id, $is_sticky);
    }

    public function GetFriends($user_id)
    {
        if (!$user_id) {
            return [];
        }
        return $this->dataLayer->GetFriends($user_id);
    }

    public function DeleteKey($user_id, $key_guid)
    {
        if (!$user_id) {
            return null;
        }

        if (!$key_guid) {
            return null;
        }

        return $this->dataLayer->DeleteKey($user_id, $key_guid);
    }

    public function DeletePost($user_id, $post_guid)
    {
        if (!$user_id) {
            return null;
        }

        if (!$post_guid) {
            return null;
        }

        return $this->dataLayer->DeletePost($user_id, $post_guid);
    }

    public function DeleteComment($user_id, $response_guid)
    {
        if (!$user_id) {
            return null;
        }

        if (!$response_guid) {
            return null;
        }

        return $this->dataLayer->DeleteComment($user_id, $response_guid);
    }

    public function Comment($user_id, $post_guid, $post_domain, $comment)
    {
        if (!$user_id) {
            return null;
        }

        if (!$post_guid) {
            return null;
        }

        if (!$post_domain) {
            return null;
        }
        if (!$comment) {
            return null;
        }

        return $this->dataLayer->Comment($post_guid, $post_domain, $comment);
    }

    public function GetComments($post_guid, $post_domain)
    {
        if (!$post_guid) {
            return null;
        }

        if (!$post_domain) {
            return null;
        }


        return $this->dataLayer->GetComments($_SESSION['user_id'] ?? null, $post_guid, $post_domain);
    }

    public function GetKeys($user_id)
    {
        if (!$user_id) {
            return [];
        }
        return $this->dataLayer->GetKeys($user_id);
    }

    public function Handshake($guid, $my_guid, $my_host)
    {
        if(!$guid) {
            return null;
        }

        if(!$my_guid) {
            return null;
        }

        if(!$my_host) {
            return null;
        }

        $res = $this->dataLayer->Handshake($guid, $my_guid, $my_host);
        return $res;
    }

    public function GetFriend($user_guid, $remote_guid, $remote_domain)
    {
        if(!$user_guid) {
            return null;
        }

        if(!$remote_guid) {
            return null;
        }

        if(!$remote_domain) {
            return null;
        }

        return $this->dataLayer->GetFriend($user_guid, $remote_guid, $remote_domain);
    }

    public function GetFriendFeed($user_guid, $remote_guid, $remote_domain)
    {
        if(!$user_guid) {
            return null;
        }

        if(!$remote_guid) {
            return null;
        }

        if(!$remote_domain) {
            return null;
        }

        return $this->dataLayer->GetFriendFeed($user_guid, $remote_guid, $remote_domain);
    }

    public function DeleteFriend($user_id, $remote_guid, $remote_domain)
    {
        if(!$user_id) {
            return null;
        }

        if(!$remote_guid) {
            return null;
        }

        if(!$remote_domain) {
            return null;
        }

        return $this->dataLayer->DeleteFriend($user_id, $remote_guid, $remote_domain);
    }

    public function AddFriend($user_id, $friend_code)
    {
        if (!$user_id) {
            return null;
        }

        if (!$friend_code) {
            return null;
        }

        $parts = explode(':', $friend_code);
        if (sizeof($parts) != 2) {
            return null;
        }
        $guid = $parts[0];
        $remote_domain = $parts[1];

        $my_guid = GUID();

        $scheme = strcasecmp($remote_domain, $_SERVER['HTTP_HOST']) == 0 ? 'http://' : 'https://';

        $res = Curl::Post($scheme . $remote_domain . '/api/handshake', [
            'guid' => $guid,
            'my_guid' => $my_guid,
            'my_host' => $_SERVER['HTTP_HOST'],
        ]);

        $json = json_decode($res->Body);
        if (isset($json->data)) {
            $remote_guid = $json->data;
            $this->dataLayer->AddFriend($user_id, $my_guid, $remote_guid, $remote_domain, false);
        }
        return null;
    }

    public function GetFeed($user_id)
    {
        if (!$user_id) {
            return [];
        }
        return $this->dataLayer->GetFeed($user_id);
    }

    public function SignIn($username, $password)
    {
        if (!$username || !$password) {
            return -1;
        }

        $username = KeyboardOnly($username);
        $password = KeyboardOnly($password);
        $res = $this->dataLayer->GetUser($username, $password);
        return $res;
    }
}