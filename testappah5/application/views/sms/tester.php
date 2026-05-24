<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>SMS Tester</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 20px; }
		.container { max-width: 760px; margin: 0 auto; }
		label { display: block; margin-top: 12px; font-weight: bold; }
		input[type=text], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
		button { margin-top: 16px; padding: 10px 16px; }
		pre { background: #f7f7f7; padding: 12px; overflow: auto; }
		.row { display: flex; gap: 12px; }
		.row > div { flex: 1; }
	</style>
</head>
<body>
<div class="container">
	<h2>SMS Tester</h2>
	<p>Paste your endpoint URL (can include placeholders {to}, {message}, {sender}) and optional Bearer token. Enter recipient and message to test.</p>

	<div>
		<label for="endpoint_url">Endpoint URL</label>
		<input type="text" id="endpoint_url" value="<?php echo html_escape($endpoint_url); ?>" placeholder="https://.../send?to={to}&message={message}">

		<div class="row">
			<div>
				<label for="http_method">HTTP Method</label>
				<select id="http_method">
					<option value="GET" <?php echo ($http_method==='GET'?'selected':''); ?>>GET</option>
					<option value="POST" <?php echo ($http_method==='POST'?'selected':''); ?>>POST</option>
				</select>
			</div>
			<div>
				<label for="success_match">Success Match (text)</label>
				<input type="text" id="success_match" value="<?php echo html_escape($success_match); ?>" placeholder="OK">
			</div>
		</div>

		<label for="token">Bearer Token (optional)</label>
		<input type="text" id="token" value="<?php echo html_escape($token); ?>" placeholder="eyJhbGciOi...">

		<div class="row">
			<div>
				<label for="to">To</label>
				<input type="text" id="to" placeholder="07XXXXXXXX or +94XXXXXXXXX">
			</div>
			<div>
				<label for="sender">Sender (optional)</label>
				<input type="text" id="sender" placeholder="SENDERID">
			</div>
		</div>

		<label for="message">Message</label>
		<textarea id="message" rows="4" placeholder="Hello from SMS tester"></textarea>

		<button id="sendBtn">Send SMS</button>
	</div>

	<h3>Response</h3>
	<pre id="resp">(no request sent yet)</pre>
</div>

<script>
(function(){
	function post(url, data){
		return fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: new URLSearchParams(data)
		});
	}

	document.getElementById('sendBtn').addEventListener('click', function(){
		var data = {
			endpoint_url: document.getElementById('endpoint_url').value.trim(),
			http_method: document.getElementById('http_method').value,
			success_match: document.getElementById('success_match').value.trim(),
			token: document.getElementById('token').value.trim(),
			to: document.getElementById('to').value.trim(),
			message: document.getElementById('message').value,
			sender: document.getElementById('sender').value.trim()
		};
		post('<?php echo site_url('sms/send'); ?>', data)
			.then(function(r){ return r.text(); })
			.then(function(t){ document.getElementById('resp').textContent = t; })
			.catch(function(e){ document.getElementById('resp').textContent = 'Error: ' + e; });
	});
})();
</script>

</body>
</html>



