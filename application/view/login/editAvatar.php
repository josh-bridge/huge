<div class="container">
    <h1>Edit your avatar</h1>

    <!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <div class="box">
        <h3>Upload an Avatar</h3>

        <img src="<?= Session::get('user_avatar_file') ?>" />
        

        <div class="feedback info">
            If you still see the old picture after uploading a new one: Hard-Reload the page with F5!
        </div>

        <form action="<?php echo Config::get('DOC_ROOT'); ?>login/uploadAvatar_action" method="post" enctype="multipart/form-data">
            <label for="avatar_file">Select an avatar image from your hard-disk (will be scaled to 44x44 px, only .jpg currently):</label>
            <input type="file" name="avatar_file" required />
            <!-- max size 5 MB (as many people directly upload high res pictures from their digital cameras) -->
            <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
            <input type="submit" value="Upload image" />
        </form>
    </div>

    <div class="box">
        <h3>Delete your avatar</h3>
        <p>Click this link to delete your (local) avatar: <a href="<?php echo Config::get('HTTPS_URL'); ?>login/deleteAvatar_action">Delete your avatar</a>
    </div>
</div>
