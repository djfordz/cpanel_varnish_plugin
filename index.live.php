<?php

// uncomment these to see errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// require files
require_once '/usr/local/cpanel/php/cpanel.php';
require_once 'Varnish.php';
require_once 'varnish.css';
require_once 'varnish.js';

// instantiate CPANEL class and pass class to our Varnish class
$cpanel = new CPANEL();
$varnish = new Varnish($cpanel);

// display header
print $cpanel->header("Varnish Cache", "nemj-varnish");

$dirs = $varnish->listDirs();

if (isset($_POST['path']) && isset($_POST['enable'])) {
    $varnish->enable($POST['path']);    
}    

?>

<div class="wrapper">
    <div class="description">
        <p>Enable/Disable Varnish</p>
    </div>

    <form method="POST">
        <div class="list">
            <h4 class="title"><?php echo $varnish->getUserPath() . '/'; ?></h4>
            <select name="path" required>
                <?php foreach($dirs as $dir) {
                $p = explode('/', $dir['path']);
                $path = array_pop($p);
                ?>
                    <option <?php if($_POST['varnish'] == 'enable' && $_POST['path'] == $path) { ?> selected <?php } ?> value="<?php echo $path ?>"><?php echo $path ?></option>
                <?php } ?>
            </select>
            <select name="varnish" required>
            <option <?php if ($_POST['varnish'] == 'disable') { ?> selected <?php } ?> value="disable">disable</option>
                <option <?php if ($_POST['varnish'] == 'enable') { ?> selected <?php } ?> value="enable">enable</option>
            </select>
            <button>Update</button>
        </div>
    </form>
    
</div>

<?php
print $cpanel->footer();
$cpanel->end();
?>


