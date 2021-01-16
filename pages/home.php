<style>
    label {
        width: 100%;
    }

    .btn {
        width: 200px;
    }

</style>
<div class="row" style="margin: 0;">
    <div class="col-6">
        <h3>Sign In</h3>
        <input type="hidden" id="time"/>
        <label>Username:</i>
            <input class="form-control" type="text" id="login_username"/>
        </label><br/>
        <label>Password:
            <input class="form-control" type="password" id="login_password"/>
        </label><br/>
        <button class="btn btn-primary" id="login_account">Sign In</button>
    </div>
    <div class="col-6">
        <h3>Create Account</h3>
        <label>Username: (<i>Note: Do not use an email address as a username!)
            <input class="form-control" type="text" id="create_username"/>
        </label><br/>
        <label>Password:
            <input class="form-control" type="password" id="create_password"/>
        </label><br/>
        <label>Confirm Password:
            <input class="form-control" type="password" id="create_password_confirm"/>
        </label><br/>
        <button class="btn btn-primary" id="create_account">Create Account</button>
    </div>

</div>

<div class="row" style="margin: 0; padding: 2.0em;">
    <div class="col-12">
    <div class="alert alert-info">
        <div>
    <?php echo META_TITLE; ?> is powered by a Distributed Social Network Protocol (DSNP).
        You are encouraged to set up and run your own server and link to your friends on
        any other server without needing an account on their server.
        </div>
        <div>
            Get the source code at: <a href="https://github.com/BenKucenski/PompousRumpus/">https://github.com/BenKucenski/PompousRumpus/</a>
        </div>
    </div>
    </div>
</div>


<script src="js/home.js" crossorigin="anonymous"></script>