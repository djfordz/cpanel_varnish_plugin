<?php

// uncomment these to see errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// require files
require_once '/usr/local/cpanel/php/cpanel.php';
require_once 'Varnish.php';
require_once 'varnish.css';

// instantiate CPANEL class and pass class to our Varnish class
$cpanel = new CPANEL();
$varnish = new Varnish($cpanel);

// display header
print $cpanel->header("Varnish Cache", "nemj-varnish");

$dirs = $varnish->listDirs();
$i = 1;
?>
<div id="nemj-wrapper">
    <div id='desc' class="desc">
        <p>Enable/Disable Varnish</p>
    </div>

    <div id="nemj-list">
        <h4>Varnish instances enabled</h4>
        <ul>
        <?php $userConfigs = $varnish->display(); ?>
            <?php foreach($userConfigs as $userConfig) { ?>
                <?php if (isset($userConfig)) { ?>
                <li>
                    <form method='POST' action='disable.live.php' name='disable-<?php echo $i; ?>' id='form-<?php echo $i; ?>'>
                        <input type='text'class='input' value='<?php echo $varnish->getUser(); ?>' id='user-<?php echo $i ?>' readonly />
                        <input type='text' class='input' value='<?php echo $userConfig[1]; ?>' name='port' id='port-<?php echo $i; ?>' readonly />
                        <input type='text' class='input' value='<?php echo $userConfig[2]; ?>' name='dataPath' id='dataPath-<?php echo $i ?>' readonly />
                        <input type='submit' class='button remove' value='Disable' name='disable' id='disable-<?php echo $i; ?>' />
                    </form>
                </li>
                <?php } ?>
            <?php $i++; } ?>
        </ul>
    </div>
    <hr />
    <div id="enable-wrapper" class="enable">
        <h4 class="title"><?php echo $varnish->__($varnish->getUserPath()) . '/'; ?></h4>
        <form method="POST" action="enable.live.php" id="form-enable">
            <select name="path" required>
                <?php foreach($dirs as $dir) {
                $p = explode('/', $dir['path']);
                $path = array_pop($p);
                ?>
                    <option><?php echo $path ?></option>
                <?php } ?>
            </select>
            <input type="submit" class='button update' value="Enable" name='enable' id="enable" />
        </form>
    </div>
</div>

<?php
print $cpanel->footer();
$cpanel->end();
?>


