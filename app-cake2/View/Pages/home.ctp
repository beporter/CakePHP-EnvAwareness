<?php
/**
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Pages
 * @since         CakePHP(tm) v 0.10.0.1076
 */

App::uses('Debugger', 'Utility');
?>
<?php
if (Configure::read('debug') > 0):
	Debugger::checkSecurityKeys();
endif;
?>
<?php
if (file_exists(WWW_ROOT . 'css' . DS . 'cake.generic.css')):
?>
<p id="url-rewriting-warning" style="background-color:#e32; color:#fff;">
	<?php echo __d('cake_dev', 'URL rewriting is not properly configured on your server.'); ?>
	1) <a target="_blank" href="http://book.cakephp.org/2.0/en/installation/url-rewriting.html" style="color:#fff;">Help me configure it</a>
	2) <a target="_blank" href="http://book.cakephp.org/2.0/en/development/configuration.html#cakephp-core-configuration" style="color:#fff;">I don't / can't use URL rewriting</a>
</p>
<?php
endif;
?>
<p>
<?php
	if (version_compare(PHP_VERSION, '5.2.8', '>=')):
		echo '<span class="notice success">';
			echo __d('cake_dev', 'Your version of PHP is 5.2.8 or higher.');
		echo '</span>';
	else:
		echo '<span class="notice">';
			echo __d('cake_dev', 'Your version of PHP is too low. You need PHP 5.2.8 or higher to use CakePHP.');
		echo '</span>';
	endif;
?>
</p>
<p>
	<?php
		if (is_writable(TMP)):
			echo '<span class="notice success">';
				echo __d('cake_dev', 'Your tmp directory is writable.');
			echo '</span>';
		else:
			echo '<span class="notice">';
				echo __d('cake_dev', 'Your tmp directory is NOT writable.');
			echo '</span>';
		endif;
	?>
</p>
<p>
	<?php
		$settings = Cache::settings();
		if (!empty($settings)):
			echo '<span class="notice success">';
				echo __d('cake_dev', 'The %s is being used for core caching. To change the config edit %s', '<em>'. $settings['engine'] . 'Engine</em>', 'APP/Config/core.php');
			echo '</span>';
			echo '<span>';
			echo $this->Html->link('View Cache config dumps', array(
				'controller' => 'pages',
				'action' => 'display',
				'cache_config',
			));
			echo '</span>';
		else:
			echo '<span class="notice">';
				echo __d('cake_dev', 'Your cache is NOT working. Please check the settings in %s', 'APP/Config/core.php');
			echo '</span>';
		endif;
	?>
</p>
<p>
	<?php
		$filePresent = null;
		if (file_exists(APP . 'Config' . DS . 'database.php')):
			echo '<span class="notice success">';
				echo __d('cake_dev', 'Your database configuration file is present.');
				$filePresent = true;
			echo '</span>';
		else:
			echo '<span class="notice">';
				echo __d('cake_dev', 'Your database configuration file is NOT present.');
				echo '<br/>';
				echo __d('cake_dev', 'Rename %s to %s', 'APP/Config/database.php.default', 'APP/Config/database.php');
			echo '</span>';
		endif;
	?>
</p>
<?php
if (isset($filePresent)):
	App::uses('ConnectionManager', 'Model');
	try {
		$connected = ConnectionManager::getDataSource('default');
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
<p>
	<?php
		if ($connected && $connected->isConnected()):
			echo '<span class="notice success">';
				echo __d('cake_dev', 'CakePHP is able to connect to the database.');
			echo '</span>';
		else:
			echo '<span class="notice">';
				echo __d('cake_dev', 'CakePHP is NOT able to connect to the database.');
				echo '<br /><br />';
				echo $errorMsg;
			echo '</span>';
		endif;
	?>
</p>
<?php endif; ?>
<?php
	App::uses('Validation', 'Utility');
	if (!Validation::alphaNumeric('cakephp')):
		echo '<p><span class="notice">';
			echo __d('cake_dev', 'PCRE has not been compiled with Unicode support.');
			echo '<br/>';
			echo __d('cake_dev', 'Recompile PCRE with Unicode support by adding <code>--enable-unicode-properties</code> when configuring');
		echo '</span></p>';
	endif;
?>

<p>
	<?php
		if (CakePlugin::loaded('DebugKit')):
			echo '<span class="notice success">';
				echo __d('cake_dev', 'DebugKit plugin is present');
			echo '</span>';
		else:
			echo '<span class="notice">';
				echo __d('cake_dev', 'DebugKit is not installed. It will help you inspect and debug different aspects of your application.');
				echo '<br/>';
				echo __d('cake_dev', 'You can install it from %s', $this->Html->link('GitHub', 'https://github.com/cakephp/debug_kit'));
			echo '</span>';
		endif;
	?>
</p>

<div class="row">
	<div class="columns large-12 environment checks">
		<h2>Environment Experiments</h2>

		<p>The heading on this page will change depending on the value of the <code>APP_ENV</code> environment variable set on the server.</p>

		<h3>Basic Experiments</h3>

		<ul>
			<li>Open the <code>Config/core.php</code> file.

			<li>Change the hex value of the <code>Defaults.envFlagColor</code> key and save the file.

			<li>Reload the web page.
				<ul>
					<li>What color is the header background now?
				</ul>

			<li>Open the <code>Config/core-vagrant.php</code> file.

			<li>Change the hex value of the <code>Defaults.envFlagColor</code> key.

			<li>Reload the web page.
				<ul>
					<li>What color is the header background now?
					<li>Why is it different now?
				</ul>
		</ul>


		<h3>Advanced Experiments</h3>

		<ul>
			<li>Create a file <code>app/Config/core-demo.php</code> and place this code in it:
				<pre>
<?php echo h(<<<EOD
<?php
\$config = array(
	'debug' => true,
	'Defaults' => array(
		'longName' => 'My Own Demo Env',
		'envFlagColor' => '#2244AA',
	),
);
EOD
); ?>
				</pre>

			<li>Connect to the vagrant VM's command line by running: <code>vagrant ssh</code>

			<li>
				<p>In the VM's <code>/etc/apache2/sites-available/cake-env-awareness.conf</code> file, change the <code>SetEnv APP_ENV vagrant</code> line to <code>SetEnv APP_ENV demo</code>

				<p>This command will perform the substitution for you when run in the VM:<br>
				<code>sudo sed -i'' 's/SetEnv APP_ENV vagrant/SetEnv APP_ENV demo/' /etc/apache2/sites-enabled/cake-env-awareness.conf</code>

			<li>Reload apache's configs using <code>sudo service apache2 reload</code>

			<li>Reload this page in your browser.
				<ul>
					<li>The banner output from <code>Configure::read('Defaults.longName')</code> will have changed to reflect the new override value.
				</ul>

			<li>Did you notice that the &quot;Database Connection&quot; check above started failing?
				<ul>
					<li>This is because no database connection settings are defined in your <code>app-demo.php</code> config file that override the production settings from <code>core.php</code>.
					<li>Try copying the `Datasources` segment from <code>Config/core-vagrant.php</code> into <code>Config/core-demo.php</code> and reloading this page again.
				</ul>

			<li>Continue to experiment with adding and overriding values in the app's config files on your own. What happens if you try to override an entire array of values?
		</ul>
	</div>
</div>
