$('form input[name=group-select]').change(function() {
	$('form input[type=checkbox]').prop('checked', this.checked);
});

$('form input[type=checkbox]:not([name=group-select])').change(function() {
	markUser(this);
});


$('.add-user').click(function() {
	$('#change-user .modal-title').text('Add User');
	$('#add-edit-user').trigger('reset');
	$('#add-edit-user input[name="user"').val('');

	$('#change-user').modal('show');
});

$('.user img').click(function() {
	editUser(this);
});

$('#add-edit-user').submit(function(event) {
	event.preventDefault();
	var data = {};
	$.each($('#add-edit-user').serializeArray(), function() {
		data[this.name] = this.value; 	  
	});
	data.status = $('#add-edit-user #status').prop('checked');

	$.ajax ({
        url: '/addnewuser',
        method: 'POST',
        data: data,
        beforeSend: function (request) {
            return request.setRequestHeader('token', $("meta[name='token']").attr('content'));
        },
        success: function (response) {
        	if(response) response = JSON.parse(response);
	        	if (response.status === true) {
	        		let oldUser = $(`table .user[data-user="${response.user.id}`);
	        		let tr = createUser(response.user);
	        		(oldUser.attr('data-user'))?oldUser.replaceWith(tr):$('.table').append(tr);
	        		$('#change-user').modal('hide');
	        	}
        }
    });
});

$('#update-status').submit(function(event) {
	event.preventDefault();
	let message;
	var users = [];
	let data = {};

	data.status = event.originalEvent.submitter.parentNode.querySelector('select').value;
	$('#update-status input[name]').each(function(key, e) {
		if(!$(e).prop('checked')) return;
		users.push([$(e).parent().parent().parent().attr('data-user')]);
	});
	data.users = users;

	if($.inArray(data.status, ['active', 'notactive', 'delete']) === -1) message = 'You haven\'t selected a status for the selected users!';
	if(!data.users.length) message = 'You haven\'t selected any users!';

	if(message) {
		$('#alert-window .modal-footer .action').hide();
		$('#alert-window .modal-body').html('<p class="text-danger">'+ message + '</p>');
		$('#alert-window').modal('show');
		return
	}
	
	$.ajax ({
        url: '/updatestatususers',
        method: 'POST',
        data: data,
        beforeSend: function (request) {
            return request.setRequestHeader('token', $("meta[name='token']").attr('content'));
        },
        success: function (response) {
        	if(response) response = JSON.parse(response);
	        	if (response.status === true) {
	        		for (user of users) {
		        		if(response.action == 'delete') {
		        			$(`.user[data-user="${user}"`).remove();
		        		} else {
		        			let status = (response.action == 'active')?'status-green':'status-grey';
		        			$(`input[type="checkbox"]`).prop('checked', false);
		        			$(`.user[data-user=${user}] [class^="status-"]`).attr('class', status);
		        		}
	        		}
	        		$('#update-status').trigger('reset');
	        	}
        }
    });

});

function markUser (user) {
	if(!user.checked) {
		$('form input[name=group-select]').prop('checked', false);
	} else if ($('form input[type=checkbox]:not([name=group-select]):checked').length == $('form input[type=checkbox]:not([name=group-select])').length) {
		$('form input[name=group-select]').prop('checked', true);
	}
}

function createUser (user) {
	let tr = document.createElement('tr');
	tr.classList.add('user');
	tr.dataset.user = user.id;

	let checkBox = document.createElement('td');
	let userName = document.createElement('td');
	let status = document.createElement('td');
	let role = document.createElement('td');
	let options = document.createElement('td');

	checkBox.innerHTML = '<div class="custom-control custom-checkbox">'+
							`<input class="custom-control-input" type="checkbox" id="checkbox[${user.id}]">`+
							`<label class="custom-control-label" for="checkbox[${user.id}]"></label>`+
						'</div>';
	userName.innerHTML = `${user.first_name} ${user.last_name}`;
	status.classList.add('text-center');
	status.innerHTML = '<span class="' + (user.status?'status-green':'status-grey') + '"></span>';
	role.innerHTML = user.role;
	options.classList.add('text-center');
	options.innerHTML = '<img data-action="edit" src="/img/edit.png" width="20px">'+
						'<img data-action="delete" src="/img/delete.png" width="20px">';
	for(let child of [checkBox, userName, status, role, options]) {
		tr.appendChild(child);
	}

	checkBox.querySelector('input').onchange = function () {markUser(this)};
	options.childNodes[0].onclick = function () {editUser(this)};
	options.childNodes[1].onclick = function () {editUser(this)};

	return tr;
}

function editUser (e) {
	let action = e.dataset.action;
	let user = e.parentNode.parentNode.dataset.user;

	if (action == 'edit') {
		$.ajax ({
	        url: `/finduser`,
	        method: 'POST',
	        data: {user: user},
	        beforeSend: function (request) {
	            return request.setRequestHeader('token', $("meta[name='token']").attr('content'));
	        },
	        success: function (response) {
	        	if(response) response = JSON.parse(response);
	        	if (response.status === true) {
	        		$('#add-edit-user').trigger('reset');
		        	$('#change-user .modal-title').text('Update User');
		        	$('#change-user .modal-footer .action' ).text('Update');
		        	$('#change-user .modal-body [name=user]').val(response.user.id);
		        	$('#change-user .modal-body #first-name').val(response.user.first_name);
		        	$('#change-user .modal-body #last-name').val(response.user.last_name);
					$('#change-user .modal-body #status').prop('checked', response.user.status);
					$('#change-user .modal-body #role').val(response.user.role);

					$('#change-user').modal('show');
	        	}
	        }
	    });
	} else if (action == 'delete') {
		$('#alert-window .modal-title').text('Delete Confirmation');
		let actionButton = $('#alert-window .modal-footer .action');
		actionButton.text('Delete');
		actionButton.removeClass('btn-primary');
		actionButton.addClass('btn-danger');
		actionButton.click(function() {deleteUser(user)});

		let nameUser = e.parentElement.previousElementSibling.previousElementSibling.previousElementSibling.innerText;

		$('#alert-window .modal-body').html('<p>Are you sure you want to delete <b>'+
			nameUser + '</b>?</p>');
		$('#alert-window .modal-footer .action').show();
		$('#alert-window').modal('show');
	}
}

function deleteUser (user) {
	$.ajax ({
        url: `/deleteuser`,
        method: 'POST',
        data: {user: user},
        beforeSend: function (request) {
            return request.setRequestHeader('token', $("meta[name='token']").attr('content'));
        },
        success: function (response) {
        	if(response) response = JSON.parse(response);
        	if (response.status === true) {
        		$(`.user[data-user="${user}"`).remove();
				$('#alert-window').modal('hide');
        	}
        }
    });
}
