<?php
// thanks to http://www.alfredforum.com/topic/1788-prevent-flash-of-no-result
mb_internal_encoding("UTF-8");
date_default_timezone_set('America/New_York');

use OhAlfred\OhAlfred;
require '../../vendor/autoload.php';

$response = Array();

if(!$_GET["id"] || !$_GET["secret"]) {
	// If we opt out, display the opt out page
	if ($_GET["opt_out"]) {
		?>
			<html>
			<head>
				<title>Spotifious Setup</title>

				<link rel="stylesheet" href="style/normalize.css" />
				<link rel="stylesheet" href="style/style.css">
			</head>

			<body>
				<div id="wrapper" class="wrapper">
					<section>
						<h1>Setup complete!</h1>
						<p>You've successfully opted out of using a Spotify app.</p>
						<p>
							You can always opt back in using the settings menu in
							Spotifious. Just type <kbd>s</kbd> in Spotifious.
						</p>
						<p>You can now reopen Spotifious to continue setup.</p>
					</section>
				</div>
			</body>
			</html>

		<?php
		exit();
	}

	// If we didn't opt out, we're missing information.
	$response["status"] = "error";
	$response["message"] = "You're missing some data!";
	echo json_encode($response);
	exit();
}

$alfred = new OhAlfred();

// Test connection
$session = new SpotifyWebAPI\Session($_GET["id"], $_GET["secret"], 'http://localhost:11114/callback.php');

$scopes = array(
    'playlist-read-private',
    'user-read-private'
);

try {
	$session->requestCredentialsToken($scopes);
} catch(SpotifyWebAPI\SpotifyWebAPIException $e) {
	$response["status"] = "error";

	$response["message"] = "Invalid data - ";
	if ($e->getMessage() == "Invalid client") {
		$response["message"] .= "invalid ID";
	} elseif($e->getMessage() == "Invalid client secret") {
		$response["message"] .= "incorrect secret";
	} else {
		$response["message"] .= $e->getMessage();
	}

	echo json_encode($response);
	exit();
}

// Save data
$alfred->options('spotify_client_id', $_GET["id"]);
$alfred->options('spotify_secret', $_GET["secret"]);

$response["status"] = "success";
$response["message"] = "Saved your information! Make sure to do step 8 :)";
echo json_encode($response);
exit();