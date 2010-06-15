<?php
/**
 * purl
 * Minimal hurl.it in PHP.
 *
 * Copyright Vishnu Gopal, 2010
 * This file is released under the new BSD license.
 */

include 'lib/purl.php';

?>
<!DOCTYPE html>
<html>
<head>
	<title>purl</title>
	<link rel="stylesheet" href="purl.css"/>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
	<script type="text/javascript">
		document.observe('dom:loaded', function() {
			$('add-param').observe('click', function() {
				$('param-fields').insert({ bottom: '<span><a class="remove-param" href="#">Remove</a> <input type="text" name="param-keys[]" value=""> <input type="text" name="param-vals[]" value=""><br /></span>' });
				$$('.remove-param').each(function(el) {
					el.observe('click', function() {
						el.up().remove();
					})
				});
			});
			$$('.remove-param').each(function(el) {
				el.observe('click', function() {
					el.up().remove();
				})
			});
		});
	</script>
</head>
<body>
<div id="wrap">
	<div id="header">
		<h1>purl</h1>
	</div>
	<div id="content">
		<form id="purl-form" method="post" action="/" name="purl-form">
			<p>
				<label for="url">URL</label> <input type="text" name="url" title="URL" value="<?php echo $_POST['url'] ?>">
			</p>
			<p>
				<select id="select-method" name="method">
					<?php 
						$methods = array('GET', 'POST', 'PUT', 'DELETE');
						foreach($methods as $method) {
							echo '<option value="' . $method . '"' . ($_POST['method'] == $method ? 'selected="selected"' : '') . '>' . $method . '</option>';
						}
					?>
				</select> <span><label><input type="checkbox" id="follow_redirects" name="follow_redirects" <?php echo isset($_POST['follow_redirects']) ? 'checked="checked"' : '' ?>> follow redirects</label></span>
			</p>
			<div id="post-params">
				<a id="add-param" href="#" name="add-param"><span>+</span> add param</a>
				<p id="param-fields">
				<?php if($_POST['param-keys']) { ?>
				<?php foreach($_POST['param-keys'] as $key) { ?>
					<span><a class="remove-param" href="#">Remove</a> <input type="text" name="param-keys[]" value="<?php echo $key ?>"> <input type="text" name="param-vals[]" value="<?php echo current($_POST['param-vals']) ?>"><br /></span>
				<?php 
					next($_POST['param-vals']); 
					}	 
				} 
				if($_POST['param-vals']) {
					reset($_POST['param-vals']);
				}
				?>
				</p>
			</div>
			<p id="send-wrap">
				<button type="submit">Send</button>
			</p>
		</form>
		<?php if($_POST['url']) {
			/* Construct params */
			$request_params = array();
			
			if($_POST['param-keys']) {
				foreach($_POST['param-keys'] as $key) {
					$request_params[$key] = current($_POST['param-vals']);
					next($_POST['param-vals']);
				}
			}
						
			$purl = new Purl();
			$purl->url = $_POST['url'];
			$purl->method = $_POST['method'];
			$purl->follow_redirects = isset($_POST['follow_redirects']);
			$purl->request_params = $request_params;
			$response = $purl->dispatch(); 
		?>
		<p>Response:</p>
<pre>
<?php
		foreach($response['headers'] as $header_name => $header_value) {
			echo '<span class="header_name">' . $header_name . ($header_value ? ':' : '') . '</span>' . '<span class="header_value">' . $header_value . '</span><br />';
		}
		
echo '<br /><br />' . htmlspecialchars($response['body']);
?>
</pre>
		<?php
		}
		?>
	</div>
	<div id="footer">
	</div>
</div>
</body>
</html>

