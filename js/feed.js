function boot() {
    LoadKeys();
    LoadFriends();
    LoadFeed();

    $('#signout').click(function() {
        HTTP.Post('/api/signout', {
        }, function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                location.reload();
            }
        });
    });

    $('#create_key').click(function() {
        let _is_sticky = $('#is_sticky').is(":checked") ? 1 : 0;
        HTTP.Post('/api/create_key', {is_sticky: _is_sticky}, function (data) {
            LoadKeys();
        });
    });

    $('#add_friend').click(function() {
        let _friend_code = $('#friend_code').val();
        if(!_friend_code) {
            return;
        }

        HTTP.Post('/api/add_friend',{
            friend_code:_friend_code
        },function(data) {
            LoadFriends();
            $('#friend_code').val('');
        });
    });

    $('#submit_post').click(function(){
        let _post = $('#post').val();
        let _link = $('#link').val();
        let _image = $('#image').val();

        if(!_post) {
            return;
        }

        HTTP.Post('/api/create_post', {
            post: _post,
            image: _image,
            link: _link
        }, function (data) {

            $('#post').val('');
            $('#link').val('');
            $('#image').val('');

            if(data.error) {
                alert(data.error);
            } else {
                LoadFeed();
            }
        });

    });
}

function LoadKeys() {
    // https://material.io/resources/icons/?style=baseline
    HTTP.Get('/api/keys', {}, function (data) {
        let html = '';
        for (var i in data.data) {
            let key = data.data[i];
            let trash = '<button class="user_key_delete btn btn-danger" data-key_guid="' + key.user_guid + '"><span class="material-icons">delete</span></button>';
            if (parseInt(key.is_sticky) === 1) {
                html += '<div class="user_key" data-key="' + key.key + '">' + trash + '<i>' + key.key + '</i></div>';
            } else {
                html += '<div class="user_key" data-key="' + key.key + '">' + trash + '' + key.key + '</div>';
            }
        }
        $('#keys').html(html);

        $('.user_key').click(function (event) {
            let _key = $(this).data('key');
            window.prompt("Copy to clipboard: Ctrl+C, Enter", _key);
            event.stopPropagation();
        });

        $('.user_key_delete').click(function (event) {
            if(!confirm('Are you sure?')) {
                event.stopPropagation();
                return;
            }
            let _key_guid = $(this).data('key_guid');
            HTTP.Post('/api/delete_key', {key_guid: _key_guid}, function (data) {
                LoadKeys();
            });
            event.stopPropagation();
        });
    });
}

function LoadFeed()
{
    HTTP.Get('/api/feed',{},function(data) {
        let html ='';
        for(var i in data.data) {
            let post = data.data[i];

            let post_content = '';
            if(post.link) {
                post_content += '<div class="post-link"><a href="' + post.link + '">' + post.link + '</a></div>';
            }

            if(post.image) {
                post_content += '<div class="post-image"><a href="' + post.image + '"><img src="' + post.image + '"/></a></div>';
            }

            let str = post.post.replace(/(?:\r\n|\r|\n)/g, '<br>');
            post_content += str;

            let post_trash = '';
            if(parseInt(post.can_delete) === 1) {
                post_trash = '<button class="float-right user_post_delete btn btn-danger" data-post_guid="' + post.post_guid + '"><span class="material-icons">delete</span></button>';
            }

            let post_comment = `
                <hr/>
                <div style="text-align: right;"><input id="` + post.post_hash + `" type="text" class="form-control" /><button class="post_comment btn btn-primary" data-post_hash="` + post.post_hash + `" data-post_guid="` + post.post_guid + `" data-post_domain="` + post.post_domain + `">Comment</button></div>
                <div id="` + post.post_hash + `_comments"></div>
                `;

            html += `
<div class="feed_post">
<div class="author_time">
   <div class="created_at">`+ post.created_at +`</div> <div class="author">`+ post.author +`</div>
</div>
<div class="content">
` + post_trash + `
` + post_content + `
` + post_comment + `
</div>
</div>
`;
        }

        $('#feed').html(html);

        for(var i in data.data) {
            let post = data.data[i];
            LoadComments(post.post_hash, post.post_guid, post.post_domain);
        }

        $('.user_post_delete').click(function (event) {
            if(!confirm('Are you sure?')) {
                event.stopPropagation();
                return;
            }
            let _post_guid = $(this).data('post_guid');
            HTTP.Post('/api/delete_post', {
                post_guid: _post_guid
            }, function (data) {
                LoadFeed();
            });
            event.stopPropagation();
        });

        $('.post_comment').click(function(event) {
            let _post_domain = $(this).data('post_domain');
            let _post_guid = $(this).data('post_guid');
            let _post_hash = $(this).data('post_hash');
            let _comment = $('#' + _post_hash);
            if(!_comment.val()) {
                return;
            }
            HTTP.Post('/api/comment', {
                post_guid: _post_guid,
                post_domain: _post_domain,
                comment: _comment.val()
            }, function (data) {
                LoadComments(_post_hash, _post_guid, _post_domain);
            });
            _comment.val('');
            event.stopPropagation();
        });
    });
}

function LoadComments(post_hash, post_guid, post_domain) {
    HTTP.Post('/api/get_comments', {
        post_guid: post_guid,
        post_domain: post_domain
    }, function (data) {
        if(data.data.length === 0) {
            return;
        }
        let html = `<hr/>`
        for(var i in data.data) {
            let comment = data.data[i];

            let post_trash = '';
            if(parseInt(comment.can_delete) === 1) {
                post_trash = '<button class="float-right user_comment_delete btn btn-danger" data-response_guid="' + comment.response_guid + '"><span class="material-icons">delete</span></button>';
            }


            html += `
                <div class="comment">
                    <div class="author_time">
                       <div class="created_at">`+ comment.created_at +`</div> <div class="author">`+ comment.user_at_domain +`</div>
                    </div>
                    <div class="content">
                        ` + post_trash + `
                        ` + comment.content + `
                    </div>
                </div>
                `;
        }

        $('#' + post_hash + '_comments').html(html);

        $('.user_comment_delete').click(function(event){
            if(!confirm('Are you sure?')) {
                event.stopPropagation();
                return;
            }
            let _response_guid = $(this).data('response_guid');
            HTTP.Post('/api/delete_comment', {
                response_guid: _response_guid
            }, function (data) {
                LoadFeed();
            });
            event.stopPropagation();

        });
    });

}

function LoadFriends() {
    HTTP.Get('/api/friends', {}, function (data) {
        let html = '';
        for (var i in data.data) {
            let friend = data.data[i];
            let trash = '<button class="user_friend_delete btn btn-danger" data-remote_guid="' + friend.remote_guid + '" data-remote_domain="' + friend.remote_domain + '"><span class="material-icons">delete</span></button>';
            html += '<div class="friend">' + trash + friend.username + '@' + friend.remote_domain + '</div>';
        }
        html += '</ul>';
        $('#friends').html(html);

        $('.user_friend_delete').click(function (event) {
            if (!confirm('Are you sure?')) {
                event.stopPropagation();
                return;
            }
            let _remote_guid = $(this).data('remote_guid');
            let _remote_domain = $(this).data('remote_domain');
            HTTP.Post('/api/delete_friend', {
                remote_guid: _remote_guid,
                remote_domain: _remote_domain,
            }, function (data) {
                LoadFriends();
            });
            event.stopPropagation();
        });

    });
}