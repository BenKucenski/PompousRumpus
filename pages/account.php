<div class="row" style="margin: 0">
    <div class="col-6">
        <h3>Change Your Password</h3>
        <div class="alert alert-danger">Copy and paste your password into here so you don't lose it.  Lost passwords cannot be recovered.</div>
        <input type="password" class="form-control" id="password"/>
        <button id="change_password" class="btn btn-primary">Save</button>
    </div>
    <div class="col-6">
        <h3>Change Your Username</h3>
        <input type="text" class="form-control" id="username"/>
        <button id="change_username" class="btn btn-primary">Save</button>

        <h3>Change Your GUID</h3>
        <div class="alert alert-danger">
            The GUID is only used for public access to your data.  Changing it will disable any existing connections
            to your public feed.  It has no impact on your connected friends seeing your posts.
        </div>
        <?php echo $_SESSION['user_guid']; ?><br/>
        <button id="change_guid" class="btn btn-primary">Change</button>

    </div>
</div>


<script src="js/account.js" crossorigin="anonymous"></script>