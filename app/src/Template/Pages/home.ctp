<?php
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Network\Exception\NotFoundException;
$this->layout = false;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= Configure::read('Defaults.shortName') ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <style>
    	.envSignal {
    		background-color: <?= Configure::read('Defaults.envFlagColor'); ?>;
    	}
    </style>
</head>
<body class="home">
    <header class="envSignal">
        <div class="header-image">
            <?= $this->Html->image('http://cakephp.org/img/cake-logo.png') ?>
            <h1><?= Configure::read('Defaults.longName') ?></h1>
        </div>
    </header>
    <div id="content">
        <?php
        if (Configure::read('debug')):
            Debugger::checkSecurityKeys();
        endif;
        ?>
        <p id="url-rewriting-warning" style="background-color:#e32; color:#fff;display:none">
            URL rewriting is not properly configured on your server.
            1) <a target="_blank" href="http://book.cakephp.org/3.0/en/installation/url-rewriting.html" style="color:#fff;">Help me configure it</a>
            2) <a target="_blank" href="http://book.cakephp.org/3.0/en/development/configuration.html#general-configuration" style="color:#fff;">I don't / can't use URL rewriting</a>
        </p>

        <div class="row">
            <div class="columns large-5 platform checks">
                <?php if (version_compare(PHP_VERSION, '5.4.16', '>=')): ?>
                    <p class="success">Your version of PHP is 5.4.16 or higher.</p>
                <?php else: ?>
                    <p class="problem">Your version of PHP is too low. You need PHP 5.4.16 or higher to use CakePHP.</p>
                <?php endif; ?>

                <?php if (extension_loaded('mbstring')): ?>
                    <p class="success">Your version of PHP has the mbstring extension loaded.</p>
                <?php else: ?>
                    <p class="problem">Your version of PHP does NOT have the mbstring extension loaded.</p>;
                <?php endif; ?>

                <?php if (extension_loaded('openssl')): ?>
                    <p class="success">Your version of PHP has the openssl extension loaded.</p>
                <?php elseif (extension_loaded('mcrypt')): ?>
                    <p class="success">Your version of PHP has the mcrypt extension loaded.</p>
                <?php else: ?>
                    <p class="problem">Your version of PHP does NOT have the openssl or mcrypt extension loaded.</p>
                <?php endif; ?>

                <?php if (extension_loaded('intl')): ?>
                    <p class="success">Your version of PHP has the intl extension loaded.</p>
                <?php else: ?>
                    <p class="problem">Your version of PHP does NOT have the intl extension loaded.</p>
                <?php endif; ?>
            </div>
            <div class="columns large-6 filesystem checks">
                <?php if (is_writable(TMP)): ?>
                    <p class="success">Your tmp directory is writable.</p>
                <?php else: ?>
                    <p class="problem">Your tmp directory is NOT writable.</p>
                <?php endif; ?>

                <?php if (is_writable(LOGS)): ?>
                    <p class="success">Your logs directory is writable.</p>
                <?php else: ?>
                    <p class="problem">Your logs directory is NOT writable.</p>
                <?php endif; ?>

                <?php $settings = Cache::config('_cake_core_'); ?>
                <?php if (!empty($settings)): ?>
                    <p class="success">The <em><?= $settings['className'] ?>Engine</em> is being used for core caching. To change the config edit config/app.php</p>
                <?php else: ?>
                    <p class="problem">Your cache is NOT working. Please check the settings in config/app.php</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="columns large-12  database checks">
                <?php
                    try {
                        $connection = ConnectionManager::get('default');
                        $connected = $connection->connect();
                    } catch (Exception $connectionError) {
                        $connected = false;
                        $errorMsg = $connectionError->getMessage();
                        if (method_exists($connectionError, 'getAttributes')):
                            $attributes = $connectionError->getAttributes();
                            if (isset($errorMsg['message'])):
                                $errorMsg .= '<br />' . $attributes['message'];
                            endif;
                        endif;
                    }
                ?>
                <?php if ($connected): ?>
                    <p class="success">CakePHP is able to connect to the database.</p>
                <?php else: ?>
                    <p class="problem">CakePHP is NOT able to connect to the database.<br /><br /><?= $errorMsg ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="columns large-12">
                <h3>Editing this Page</h3>

				<p>The heading on this page will change depending on the value of the <code>APP_ENV</code> environment variable set on the server.</p>

				<p>To experiment with this:</p>

				<ul>
					<li>Create a file <code>app/config/app-demo.php</code> and place this code in it:
						<pre>
<?= h(<<<EOD
<?php
return [
	'debug' => true,
	'Defaults' => [
		'longName' => 'My Own Demo Env',
		'envFlagColor' => '#2244AA',
	],
];
EOD
) ?>
						</pre>

					<li>
						<p>Change the <code>SetEnv APP_ENV vagrant</code> line in the VM's <code>/etc/apache2/sites-available/cake-env-awareness.conf</code> file to <code>SetEnv APP_ENV demo</code>

						<p>(This command will perform the substitution for you when run in the VM: <code>sudo sed -i'' 's/SetEnv APP_ENV vagrant/SetEnv APP_ENV demo/' /etc/apache2/sites-enabled/cake-env-awareness.conf</code>)

					<li>Restart apache using <code>sudo service apache2 restart</code>.

					<li>Reload the page. The output from <code>Configure::read('Defaults.longName')</code> will have changed to reflect the new override value.

					<li>After you do this, you'll find that the &quot;Database Connection&quot; check above will start failing. This is because no database connection settings are defined in your <code>app-demo.php</code> config file that override the production settings from <code>app.php</code>.

					<li>Continue to experiment with adding and overriding values in the app's config files on your own. What happens if you try to override an array of values?
				</ul>
            </div>
        </div>

    </div>
    <footer>
    </footer>
</body>
</html>
