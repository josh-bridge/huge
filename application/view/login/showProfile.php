<div class="container">
    <h1>LoginController/showProfile</h1>
            <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>
    <div class="box">
        <h2>Your profile</h2>

        <div>Your username: <?= $this->user_name; ?></div>
        <div>Your email: <?= $this->user_email; ?></div>
        <div>Your avatar image:
            Your avatar pic (saved locally): <img src='<?= $this->user_avatar_file; ?>' /> 
        </div>
        <div>Your account type is: <?= $this->user_account_type; ?></div>
        <p>
            <?php 

            echo '</br>'.$this->user_firstname.' '.$this->user_lastname.'</br>';
            echo '<a href="'.str_replace('public', '', dirname($_SERVER['SCRIPT_NAME'])).'login/register/ref/'.$this->user_refcode.'" target="_blank">'.$this->user_refcode.'</a></br>';
            if ($this->user_business) echo $this->user_business.'</br>';
            if ($this->user_telephone) echo $this->user_telephone.'</br>';
            echo $this->user_mobile.'</br>';
            echo $this->user_dob.'</br></br>'.$this->user_addrline1.'</br>';
            if($this->user_addrline2) echo $this->user_addrline2.'</br>';
            if($this->user_addrline3) echo $this->user_addrline3.'</br>';
            echo $this->user_city.'</br>'.$this->user_country.'</br>'.$this->user_postcode.'<br>';
            
            ?>
        </p>
    </div>
</div>
