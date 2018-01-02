<?php

// uncomment these to see errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// require files
require_once '/usr/local/cpanel/php/cpanel.php';
require_once 'Varnish.php';

// instantiate CPANEL class and pass class to our Varnish class
$cpanel = new CPANEL();
$varnish = new Varnish($cpanel);

// display header
print $cpanel->header("Varnish Cache", "nemj-varnish");

if (isset($_POST['dataPath']) && isset($_POST['port']) && isset($_POST['disable'])) {
    if ($_POST['disable'] == 'Disable') {
        $varnish->disable($_POST['dataPath'], $_POST['port']);
    }
}

?>

<div id="wrapper">
    <h4>Varnish Disabled for <?php echo $_POST['dataPath'] ?></h4>
</div>

<script>
var el = document.getElementById('wrapper');
var link = document.createElement('a');
link.innerHTML = 'back';
link.href = 'index.live.php';
el.appendChild(link);
</script>

<?php
print $cpanel->footer();
$cpanel->end();
?>
