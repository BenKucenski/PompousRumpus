function boot() {
    $('#time').val(Timestamp());

    $('#create_account').click(function () {
        let _username = $('#create_username').val();
        let _password = $('#create_password').val();
        let _password_confirm = $('#create_password_confirm').val();

        if (_password !== _password_confirm) {
            alert('Passwords do not match');
            return;
        }
        HTTP.Post('/api/register', {
            username: _username,
            password: _password
        }, function (data) {
            if(data.error) {
                alert(data.error);
            } else {
                location.reload();
            }
        });
    });

    $('#login_account').click(function() {
        let _username = $('#login_username').val();
        let _password = $('#login_password').val();
        let _time = $('#time').val();

        HTTP.Post('/api/signin', {
            username: _username,
            password: _password,
            time: _time
        }, function (data) {
            if (data.error) {
                alert(data.error);
            } else {
                location.reload();
            }
        });
    });
}