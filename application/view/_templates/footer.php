        <div class="footer"></div>
    </div><!-- close class="wrapper" -->
</body>
<script type="text/javascript">
<?php 

$url = (Config::get('HTTPS_ENABLED') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$siteURL = Config::get('HTTPS_URL');

$loginURL = ($siteURL.'login' == $url || $siteURL.'login/' == $url || $siteURL.'login/index' == $url || $siteURL.'login/index/' == $url);
$registerURL = ($siteURL.'login/register' == $url || $siteURL.'login/register/' == $url);
$changeUserDetailsURL = ($siteURL.'login/changeUserDetails' == $url || $siteURL.'login/changeUserDetails/' == $url);

if($loginURL || $registerURL || $changeUserDetailsURL) {
?>
    function saveItems() {
        try {
            for (var i = 0; i <= x.length - 1; i++) {
                sessionStorage.setItem(x[i], document.getElementById(x[i]).value);
            }
        } catch (e) {
            if (e.name == "QuotaExceededError") {
                alert('Too many items in session storage.');
            } else {
                alert("Error: " + e.name);
                alert(document.URL);
                console.log(e);
            }
        }
    }

    function loadItems() {
        for (var i = 0; i <= x.length - 1; i++) {
            if (sessionStorage.getItem(x[i]) !== null) {
                document.getElementById(x[i]).value = sessionStorage.getItem(x[i]);
            }
        }
    }
<?php if($loginURL) { ?>

    var x = ['user_name'];
    loadItems.apply(this, x);

    window.onload = function() {
        saveItems.apply(this, x);
    }
<?php } else if ($registerURL) { ?>

    var x = ['user_name', 'user_email', 'user_firstname', 'user_lastname', 'user_dob',
     'user_addrline1', 'user_addrline2', 'user_addrline3', 'user_postcode', 
     'user_city', 'user_country', 'user_telephone', 'user_mobile', 'user_business'];

    if (document.getElementById('user_refcode').value == '') x.push('user_refcode');

    loadItems.apply(this, x);

    window.onload = function() {
        saveItems.apply(this, x);
        if(document.getElementById('user_ref_results').style.display == 'none' && 
           document.getElementById('user_ref_results_error').style.display == 'none' && 
           document.getElementById('user_refcode').value != '') {
            search_user_ref();
        }
    }
<?php } else if ($changeUserDetailsURL) { ?>

    var x = ['user_firstname', 'user_lastname', 'user_dob', 'user_addrline1', 'user_addrline2', 'user_addrline3',
     'user_postcode', 'user_city', 'user_country', 'user_telephone', 'user_mobile', 'user_business'];

    loadItems.apply(this, x);

    window.onload = function() {
        saveItems.apply(this, x);
    }
<?php }} else { ?>
    sessionStorage.clear();
<?php } ?>
</script>
</html>