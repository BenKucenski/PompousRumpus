<div class="row" style="margin: 0;">
    <div class="col-7">
        <h3 style="text-align: center;">Feed</h3>
        <label>
            New Post:</label>
            <textarea style="width: 100%; height: 10em;" id="post" class="form-control"></textarea>
            <label>Link:</label>
            <input type="text" class="form-control" id="link"/>
            <label>Image:</label>
            <input type="text" class="form-control" id="image"/>
            <div style="text-align: right;">
                <button id="submit_post" class="btn btn-primary">Post</button>
            </div>

            <div id="feed"></div>
    </div>
    <div class="col-5">
        <div>
        <h3>Keys</h3>
        <button id="create_key" class="btn btn-primary">Create Key</button>
            <input type="checkbox" value="1" id="is_sticky" /> Muted
        <div id="keys"></div>
        </div>

        <div>
            <label><b>Add Friend:</b>
            <input type="text" id="friend_code" class="form-control" />
        </label>
            <button id="add_friend" class="btn btn-primary">Add</button>
        </div>




        <h3>Friends</h3>
        <div id="friends"></div>
    </div>
</div>
<script src="js/feed.js" crossorigin="anonymous"></script>