<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="token" content="<?= $_SESSION['token']; ?>">
	<title>First Project</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
</head>
<body>
	<div class="container mt-3">
		<form onsubmit="return false" id="update-status">
			<?php include('inc/group_buttons.php'); ?>
			<div class="form-group">
				<table class="table table-bordered">
					<tr>
						<th>
							<div class="custom-control custom-checkbox">
								<input class="custom-control-input" type="checkbox" name="group-select" id="group-select">
								<label class="custom-control-label" for="group-select"></label>
							</div>
						</th>
						<th>Name</th>
						<th>Status</th>
						<th>Role</th>
						<th>Options</th>
					</tr>


					<?php if($users) foreach($users as $user) { ?>
						<tr class="user" data-user="<?= $user['id']; ?>">
							<td>
								<div class="custom-control custom-checkbox">
									<input class="custom-control-input" name="users<?= $user['id']; ?>" type="checkbox" id="checkbox[<?= $user['id']; ?>]">
									<label class="custom-control-label" for="checkbox[<?= $user['id']; ?>]"></label>
								</div>
							</td>
							<td><?= $user['first_name']; ?> <?= $user['last_name']; ?></td>
							<td class="text-center"><span class="<?= $user['status']; ?>"></span></td>
							<td><?= $user['role']; ?></td>
							<td class="text-center">
								<img data-action="edit" src="/img/edit.png" width="20px">
								<img data-action="delete" src="/img/delete.png" width="20px">
							</td>
						</tr>
					<?php } ?>

				</table>
			</div>
			<?php include('inc/group_buttons.php'); ?>
		</form>

	</div>

	<div id="change-user" class="modal fade" tabindex="-1" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h3 class="modal-title"></h3>
	        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      </div>
	      <div class="modal-body">
	      	<form onsubmit="return false" id="add-edit-user" autocomplete="off">
	      		<input type="hidden"  name="user">
	      		<div class="form-group mb-3">
	      			<label for="first-name">First name</label>
	      			<input class="form-control" id="first-name" type="text" name="first_name" required>
	      		</div>
	      		<div class="form-group mb-3">
	      			<label for="last-name">Last name</label>
	      			<input class="form-control" id="last-name" type="text" name="last_name" required>
	      		</div>
	      		<div class="form-group mb-3">
	      			<label for="status">Status</label><br>
	      			<label class="switch">
	      				<input type="checkbox" id="status" name="status">
	      				<span class="slider round"></span>
	      			</label>
	      		</div>
	      		<div class="form-group mb-3">
	      			<label for="role">Role</label>
	      			<select class="form-control" id="role" type="text" name="role" required>
	      				<option value="admin">Admin</option>
	      				<option value="user">User</option>
	      			</select>
	      		</div>
	      	
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary action">Add</button>
	      </div>
	      </form>
	    </div>
	  </div>
	</div>

	<div id="alert-window" class="modal fade" tabindex="-1" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h3 class="modal-title">Submit Confirmation</h3>
	        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	      </div>
	      <div class="modal-body"></div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="button" class="btn btn-primary action">ОК</button>
	      </div>
	    </div>
	  </div>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.0.min.js" type="text/javascript"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
	<script type="text/javascript" src="/js/js.js"></script>
</body>
</html>