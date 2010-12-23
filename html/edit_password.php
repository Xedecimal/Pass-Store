<?php

require 'include/global.php';

lib('Passwords');

if (isset($_POST['save'])) {
	// I LOVE manual form validation
	// TODO: Write it

	if (password_edit($_GET['id'], $_POST['name'], $_POST['description'], $_POST['link'], $_POST['username'], $_POST['password'])) {
		$pass_saved = true;
	}
}

// Get the values for the password


include 'include/header.php';

if (!empty($pass_saved)) {
	echo "<h4>Password Saved</h4>";
}
?>

<form action="edit_password.php" method="post">

<div class="form_container" id="edit_password_form">
	<div class="inner">

	<label for="name">Name:</label>
	<input type="name" name="name" autocomplete="off">
	<br>

	<label for="description">Description:</label>
	<textarea name="description" autocomplete="off" cols="23"></textarea>
	<br>

	<label for="url">URL:</label>
	<input type="url" name="link" value="http://www.">
	<br>

	<br><br>
	<label for="username">Username:</label>
	<input type="text" name="username" autocomplete="off">
	<br>

	<label for="password">Password:</label>
	<input type="password" name="password" autocomplete="off">
	<br>

	<input type="submit" name='create' value="Create">

	</div><!-- inner -->
</div>

<table id="groups">
	<thead>
		<tr class="menu">
			<th></th>
			<th class="inner-first"></th>
			<th>Name</th>
			<th class="inner-last"></th>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td></td>
			<td colspan="5"></td>
		</tr>
		<tr class="spacer">
			<td></td>
		</tr>
	</tfoot>
	<tbody>

	</tbody>
</div>

</form>
<?php

include 'include/footer.php';

?>
