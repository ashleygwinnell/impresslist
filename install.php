<?php
// ---
// install.php
// ---

$require_login = false;
$require_config = false;
include_once("init.php");

if ($impresslist_installed) {
	header("Location: /");
	die();
}

?>

<!DOCTYPE html>
<html lang="en-gb">
	<head>
		<?php include_once("includes/head.html"); ?>

	</head>
	<body>
		<?php
			$showNavigation = false;
			include_once("includes/nav.html");
		?>

		<script type='text/javascript'>

			var page = 0;
			var pages = [
				'install-database',
				'install-administrator',
				'install-cronjobs',
				'install-system-email',
				'install-twitter-settings',
				'install-youtube-settings',
				'install-complete'
			];
			var prevPage = function() {
				$('#'+pages[page]).fadeOut(400);
				if (page >= 1) {
					page--;
					$('#'+pages[page]).delay(400).fadeIn(400);
				}
			}
			var nextPage = function() {
				$('#'+pages[page]).fadeOut(400);
				if (page < pages.length-1) {
					page++;
					$('#'+pages[page]).delay(400).fadeIn(400);
				}
			}

			var setFormReadonly = function(formName, boo) {
				if (boo) {
					$('#'+formName+'-submit').prop('disabled', boo);
					$('#'+formName+' input').prop("disabled", boo);
				} else {
					$('#'+formName+'-submit').removeAttr('disabled');
					$('#'+formName+' input').removeAttr("disabled");
				}
			}
			$(document).ready(function() {

				for(var i = 0; i < pages.length; i++) {
					$('#'+pages[i]).hide();
				}
				$('#'+pages[0]).fadeIn();

				// page 1
				$('#install-database-submit').click(function() {

					setFormReadonly('install-database', true);

					var requestData = {
						mysql_host: $('#mysql-host').val(),
						mysql_username: $('#mysql-username').val(),
						mysql_password: $('#mysql-password').val(),
						mysql_database: $('#mysql-database').val(),
					};
					API.request('/install/database/', requestData,
						function(data) {
							API.successMessage('Database configured!');
							nextPage();
						},
						function(){
							setFormReadonly('install-database', false);
						});
				});

				// page 2
				$('#install-administrator-submit').click(function() {

					setFormReadonly('install-administrator', true);

					var requestData = {
						forename: $('#administrator-forename').val(),
						surname: $('#administrator-surname').val(),
						email: $('#administrator-email').val(),
						password: $('#administrator-password').val(),
					};
					API.request('/install/administrator/', requestData,
						function(data) {
							API.successMessage('Administrator configured!');
							nextPage();
						},
						function(){
							setFormReadonly('install-administrator', false);
						});
				});

				// page 3
				$('#install-cronjobs-submit').click(function(){
					API.request('/install/cronjobs/', {}, function(data) {
						API.successMessage('File/folder permissions configured!');
						nextPage();
					}, impresslist.noop);
				});

				// page 4
				$('#install-system-email-submit').click(function(){
					setFormReadonly('install-system-email', true);
					var requestData = {
						email_host: $('#impress-email-host').val(),
						email_address: $('#impress-email-address').val(),
						email_password: $('#impress-email-password').val(),
					};
					API.request('/install/system-email/', requestData, function(data) {
						API.successMessage('impress[] system email configured!');
						nextPage();
					}, function(){
						setFormReadonly('install-system-email', false);
					});
				});
				$('#install-system-email-skip').click(nextPage);

				// page 5
				$('#install-twitter-settings-submit').click(function() {
					setFormReadonly('install-twitter-settings', true);
					var requestData = {
						twitter_consumer_key: $('#twitter-consumer-key').val(),
						twitter_consumer_secret: $('#twitter-consumer-secret').val(),
						twitter_oauth_token: $('#twitter-oauth-token').val(),
						twitter_oauth_secret: $('#twitter-oauth-secret').val(),
					};
					API.request('/install/twitter-settings/', requestData, function(data) {
						API.successMessage('Twitter settings configured!');
						nextPage();
					}, function(){
						setFormReadonly('install-twitter-settings', false);
					});
				});
				$('#install-twitter-settings-skip').click(nextPage);

				// page 6
				$('#install-youtube-settings-submit').click(function() {
					setFormReadonly('install-youtube-settings', true);
					var requestData = {
						youtube_api_key: $('#youtube-api-key').val()
					};
					API.request('/install/youtube-settings/', requestData, function(data) {
						API.successMessage('YouTube settings configured!');
						nextPage();
					}, function(){
						setFormReadonly('install-youtube-settings', false);
					});
				});
				$('#install-youtube-settings-skip').click(nextPage);

				// final page
				$('#install-complete-submit').click(function(){
					API.request('/install/complete/', {}, function(data) {
						API.successMessage('Install Complete! You will now be redirected.');

						setTimeout(function(){
							window.location = '/';
						}, 2000)

					}, function(){

					});
				});

			});
			//API.successMessage('yay');
		</script>

		<!-- Spacer -->
		<div class='container mycontainer' style='margin-top:50px;'></div>

		<div class='container mycontainer'>
			<h1>Install</h1>

			<div id='install-database' class='oa'>

				<h4>Step 1 - create database</h4>
				<div class='alert alert-info'>You should create a database and user on your web hosting provider before starting.</div>

				<div class='form-group'>
					<label>MySQL Host:</label>
					<input id='mysql-host' type='text' class='form-control' placeholder='yourwebdomain.com' />
				</div>
				<div class='form-group'>
					<label>MySQL Username:</label>
					<input id='mysql-username' type='text' class='form-control' placeholder='yourwebd_impress' />
				</div>
				<div class='form-group'>
					<label>MySQL User Password:</label>
					<input id='mysql-password' type='password' class='form-control' />
				</div>

				<div class='form-group'>
					<label>MySQL Database Name:</label>
					<input id='mysql-database' type='text' class='form-control' placeholder='yourwebd_impresslist'/>
				</div>

				<button id='install-database-submit' class='btn btn-large btn-primary fr'>Continue</button>

			</div>

			<div id='install-administrator' class='oa'>
				<h4>Step 2 - create administrator user</h4>
				<div class='alert alert-info'>This will be your account to login. You may add additional accounts later.</div>
				<div class='form-group'>
					<label>Administrator First Name:</label>
					<input id='administrator-forename' type='text' class='form-control' placeholder='Ashley' />
				</div>
				<div class='form-group'>
					<label>Administrator Surname:</label>
					<input id='administrator-surname' type='text' class='form-control' placeholder='Gwinnell' />
				</div>
				<div class='form-group'>
					<label>Administrator Email Address:</label>
					<input id='administrator-email' type='text' class='form-control' placeholder='contact@forceofhab.it' />
				</div>
				<div class='form-group'>
					<label>Administrator Password:</label>
					<input id='administrator-password' type='password' class='form-control' />
				</div>

				<button id='install-administrator-submit' class='btn btn-large btn-primary fr'>Continue</button>
			</div>

			<div id='install-cronjobs' class='oa'>
				<h4>Step 3 - configure server</h4>

				<h5>Set up Cron tasks.</h5>
				<p>Cron tasks are scripts that run at specified periods throughout the day. They are used to keep data in impress[] up to date and also to perform any tasks you have scheduled. You can modify the frequency if you like.</p><p>If this is making no sense, try <a href='https://www.siteground.co.uk/tutorials/cpanel/cron_jobs.htm' target='new'>this tutorial</a>.</p>
				<table class='table table-striped'>
					<thead>
						<tr>
							<th>Script</th>
							<th>Frequency</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$crons = [

								['file' => 'includes/tasks/backup-email.php', 'frequency' => 'once every day'],
								['file' => 'includes/tasks/refresh-coverage.php', 'frequency' => 'once every 15 minutes'],
								['file' => 'includes/tasks/refresh-coverage-youtube.php', 'frequency' => 'once every 15 minutes'],
								['file' => 'includes/tasks/refresh-email-latests.php', 'frequency' => 'once every minute'],
								['file' => 'includes/tasks/refresh-email.php', 'frequency' => 'once every minute'],
								['file' => 'includes/tasks/refresh-emailcampaignsimple.php', 'frequency' => 'once every minute'],
								['file' => 'includes/tasks/refresh-emailqueue.php', 'frequency' => 'once every minute'],
								['file' => 'includes/tasks/refresh-favicons.php', 'frequency' => 'once every 30 minutes'],
								['file' => 'includes/tasks/refresh-rss.php', 'frequency' => 'once  every 30 minutes'],
								['file' => 'includes/tasks/refresh-socialqueue.php', 'frequency' => 'once every minute'],
								['file' => 'includes/tasks/refresh-twitter-configuration.php', 'frequency' => 'once every day'],
								['file' => 'includes/tasks/refresh-twitter.php', 'frequency' => 'once every 30 minutes'],
								['file' => 'includes/tasks/refresh-youtubers.php', 'frequency' => 'once every 30 minutes'],
							];
						?>
						<?php foreach ($crons as $cron): ?>
							<tr><td><?= $cron['file']; ?></td><td><?= $cron['frequency']; ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class='alert alert-info'>Each script will have its own command, like this:<br/>
				<code>/usr/bin/wget -q -O /dev/null http://yourwebdomain.com/includes/tasks/refresh-email.php</code></div>
				<div class='alert alert-warning'><p>impress[] has no way of checking you have added these tasks. </p>
				</div>


				<h5>Set file/folder permissions.</h5>
				<p>This can be done using 'chmod' from any good FTP client.
				<ul>
					<li>chmod 755 data/uploads/</li>
					<li>chmod 777 includes/config/</li>
					<!-- <li>chmod 644 all other files</li> -->
				</ul>

				<button id='install-cronjobs-submit' class='btn btn-large btn-primary fr'>Continue</button>

				<br/><br/><br/><br/>
			</div>
			<div id='install-system-email' class='oa'>
				<h4>Optional Step 1 - create impress[] email</h4>
				<div class='alert alert-warning'>If you would like impress[] to record your emails from an outside client (e.g. Gmail) you will need to create an email account exclusively for the system. You should BCC this address in all your messages. You may do this later.</div>

				<div class='form-group'>
					<label>Email IMAP Host:</label>
					<input id='impress-email-host' type='text' class='form-control'/>
				</div>
				<div class='form-group'>
					<label>Email Address:</label>
					<input id='impress-email-address' type='text' class='form-control' placeholder='inbox@impress.yourwebdomain.com'/>
				</div>

				<div class='form-group'>
					<label>Email Password:</label>
					<input id='impress-email-password' type='password' class='form-control' />
				</div>

				<button id='install-system-email-submit' class='btn btn-large btn-primary fr'>Continue</button>
				<button id='install-system-email-skip' class='btn btn-large btn-default fr' style='margin-right:5px;'>Skip</button>

			</div>

			<div id='install-twitter-settings' class='oa'>
				<h4>Optional Step 2 - create Twitter app</h4>
				<div class='alert alert-warning'>If you would like impress[] to update Twitter data for your contacts you will need to set up a Twitter app. You can do this from <a href='https://apps.twitter.com/' target='new'>https://apps.twitter.com/</a> You may do this later.</div>

				<div class='form-group'>
					<label>Twitter Consumer Key:</label>
					<input id='twitter-consumer-key' type='text' class='form-control'/>
				</div>
				<div class='form-group'>
					<label>Twitter Consumer Secret:</label>
					<input id='twitter-consumer-secret' type='text' class='form-control'/>
				</div>
				<div class='form-group'>
					<label>Twitter OAuth Token:</label>
					<input id='twitter-oauth-token' type='text' class='form-control'/>
				</div>
				<div class='form-group'>
					<label>Twitter OAuth Secret:</label>
					<input id='twitter-oauth-secret' type='text' class='form-control'/>
				</div>

				<button id='install-twitter-settings-submit' class='btn btn-large btn-primary fr'>Continue</button>
				<button id='install-twitter-settings-skip' class='btn btn-large btn-default fr' style='margin-right:5px;'>Skip</button>
			</div>

			<div id='install-youtube-settings' class='oa'>
				<h4>Optional Step 3 - create YouTube app</h4>
				<div class='alert alert-warning'>If you would like impress[] to update YouTube data for your contacts you will need to set up a YouTube app. You can do this from <a href='https://developers.google.com/youtube/v3/' target='new'>https://developers.google.com/youtube/v3/</a> You may do this later.</div>

				<div class='form-group'>
					<label>YouTube API Key:</label>
					<input id='youtube-api-key' type='text' class='form-control'/>
				</div>

				<button id='install-youtube-settings-submit' class='btn btn-large btn-primary fr'>Continue</button>
				<button id='install-youtube-settings-skip' class='btn btn-large btn-default fr' style='margin-right:5px;'>Skip</button>
			</div>

			<div id='install-complete' class='oa'>
				<h4>Final Step - generate config</h4>
				<p>OK! Now you just need to hit 'Finish' below and you're good to go.</p>
				<button id='install-complete-submit' class='btn btn-large btn-success fr' style='width:100%;'>Finish</button>
			</div>

		</div>

		<?php include_once("includes/footer.html"); ?>
	</body>
</html>

