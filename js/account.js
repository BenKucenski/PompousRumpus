function boot() {
    $('#my_feed').click(function() {
        window.location = '/';
    });

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

    $('#change_password').click(function() {
        let _password = $('#password').val();
        if(!_password) {
            return null;
        }

        HTTP.Post('/api/change_password', {
            password : _password
        }, function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                alert('Your password has been changed.');
            }
        });
    });

    $('#change_username').click(function() {
        let _username = $('#username').val();
        if(!_username) {
            return null;
        }

        HTTP.Post('/api/change_username', {
            username : _username
        }, function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                alert('Your username has been changed.');
                location.reload();
            }
        });
    });

    $('#change_guid').click(function() {
        HTTP.Post('/api/change_guid', {
        }, function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                alert('Your guid has been changed.');
                location.reload();
            }
        });

    });
}