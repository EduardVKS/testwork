$('#update-status').on('click', function(event) {
	if($(event.target).hasClass('add-user')) {
		let User = {
			id: '',
			first_name: '',
			last_name: '',
			status: false,
			role: 0,
		}
		
		if ($(event.target).attr('data-action') == 'edit') {
			User.id = $(event.target).parent().parent().attr('data-user');
			getUser(User);
		} else {
			showUserForm(User);
		}
	}

	if ($(event.target).attr('data-action') == 'delete') {
		let user = $(event.target).parent().parent().attr('data-user');
		let nameUser = $(event.currentTarget).parent().siblings('.user-fullname').text();
		let message = 'Are you sure you want to delete <b>'+ nameUser + '</b>?';
		deleteConfirmation(message, user, deleteUser);
	}

	if($(event.target).attr('id') == 'group-select') {
		$('#update-status input[type=checkbox]').prop('checked', event.target.checked);
	}

	if($(event.target).attr('name') == 'users') {
		if(!event.target.checked) {
			$('#group-select').prop('checked', false);
		} else if ($('#update-status input[name=users]:checked').length == $('#update-status input[name=users]').length) {
			$('#group-select').prop('checked', true);
		}
	}

});

$('#update-status').submit(function(event) {
	event.preventDefault();
	let message;
	let data = {};
	data.users = [];

	data.status = event.originalEvent.submitter.parentNode.querySelector('select').value;
	$('#update-status input[name="users"]:checked').each(function(key, e) {
		data.users.push([$(e).parent().parent().parent().attr('data-user')]);
	});

	if($.inArray(data.status, ['active', 'notactive', 'delete']) === -1) message = 'You didn\'t select an action for the selected users!';
	if(!data.users.length) message = 'You haven\'t selected any users!';

	if(message) {
		alertConfirmation(message);
		return;
	}

	if(data.status == 'delete') {
		let message = `Are you sure you want delete <b>${data.users.length} ${(data.users.length === 1)?'user':'users'}</b>?`;
		deleteConfirmation(message, data, deleteUsers);
		return;
	}
	
	$.ajax ({
        url: '/updatestatususers',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
        	if (response.status === true) {
        		let validate;
        		$(`input[type="checkbox"]`).prop('checked', false);
        		for (user of response.users) {
	        		let status = (response.action == 'active')?1:0;
        			if(!user.isset) {
        				$(`.user[data-user=${user.id}]`).addClass('bright');
        				validate = true;
        			} else {
        				$(`.user[data-user=${user.id}] [class="status"]`).attr('data-status', status);
        				$(`.user[data-user=${user.id}]`).removeClass('bright');
        			}
        		}
        		$('#update-status').trigger('reset');
        		if(validate) alertConfirmation('Non-existent user have been highlihted. Please refresh the page!');
        	} else if(response.error.code == 104) {
        		if($.inArray(data.status, ['active', 'notactive', 'delete']) === -1) message = 'You didn\'t select an action for the selected users!';
				if(!data.users.length) message = 'You haven\'t selected any users!';
        		alertConfirmation(message);
        	}
        }
    });

});


$('#add-edit-user').submit(function(event) {
	event.preventDefault();

	var data = {};
	$.each($('#add-edit-user').serializeArray(), function() {
		data[this.name] = this.value.trim(); 	  
	});
	
	data.status = $('#add-edit-user #status').prop('checked');
	
	$.ajax ({
        url: '/addnewuser',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
        	if (response.status === true) {
        		createUser(response.user);
        	} else if (response.error.code == 104) {
				$('#add-edit-user p').text('');
				if (!data.first_name) {
					$('#add-edit-user #first-name ~ p').text('This field must not be empty!');
				}
				if (!data.last_name) {
					$('#add-edit-user #last-name ~ p').text('This field must not be empty!');
				}
				if (!$('#role').val()) {
					$('#add-edit-user #role ~ p').text('Please, select role for user!');
				}
        	} else if (response.error.code == 105) {
        		$('#change-user').modal('hide');
        		alertConfirmation('No found this user!');
        	}	
        }
    });
});
$('#first-name, #last-name, #role').focus(function() {
	this.nextElementSibling.innerText = '';
});

function createUser (user) {
	let tr = `<tr class="user" data-user="${user.id}"></tr>`;
	let checkBox = '<td><div class="custom-control custom-checkbox">'+
						`<input class="custom-control-input" type="checkbox" name="users" id="checkbox[${user.id}]">`+
						`<label class="custom-control-label" for="checkbox[${user.id}]"></label>`+
					'</div></td>';
	let userName = $(`<td class="user-fullname"></td>`).text(`${user.first_name} ${user.last_name}`);
	let status = `<td class="text-center"><span class="status" data-status="${user.status}"></span></td>`;
	let role = `<td>${user.role}</td>`;
	let options = '<td class="text-center">' +
						'<span class="add-user" data-action="edit"></span>'+
						'<span data-action="delete"></span>'
					'</td>';
	let result = $(tr).append(checkBox, userName, status, role, options);

	oldUser = $(`.table .user[data-user="${user.id}"`).attr('data-user');
	if(oldUser) {
		$(`.table .user[data-user="${user.id}"`).replaceWith(result);
	} else {
		$('.table').append(result);	
	}

	$('#change-user').modal('hide');
}

function getUser (User) {
	$.ajax ({
        url: `/finduser`,
        method: 'POST',
        data: {user: User.id},
        dataType: 'json',
        success: function (response) {
        	if (response.status === true) {
        		Object.assign(User, response.user);
        		showUserForm(User);
        	} else if (response.error.code == 104) {
				alertConfirmation('Wrong data');
        	} else if (response.error.code == 105) {
        		alertConfirmation('No found this user!')
        	}
        }
    });
}

function showUserForm (User) {
	$('#add-edit-user p').text('');
	if(User.id) {
		$('#change-user .modal-title').text('Update User');
		$('#change-user .modal-footer .action' ).text('Update');
	} else {
		$('#change-user .modal-title').text('Add User');
		$('#change-user .modal-footer .action' ).text('Add');
	}
	
	$('#change-user .modal-body [name=user]').val(User.id);
	$('#change-user .modal-body #first-name').val(User.first_name);
	$('#change-user .modal-body #last-name').val(User.last_name);
	$('#change-user .modal-body #status').prop('checked', User.status);
	$('#change-user .modal-body #role').val(User.role);

	$('#change-user').modal('show');
}

function deleteUser (user) {
	$.ajax ({
        url: `/deleteuser`,
        method: 'POST',
        data: {user: user},
        dataType: 'json',
        success: function (response) {
        	if (response.status === true) {
        		$(`.user[data-user="${response.id}"`).remove();
				if ($('form input[name^=users]:checked').length == $('form input[name^=users]').length) {
					$('form input#group-select').prop('checked', true);
				}
				if(response.error?.code == 105) {
					alertConfirmation('Non-existent user have been removed. Please refresh the page!');
				} else {
					$('#alert-window').modal('hide');
				}
        	} else {
        		alertConfirmation('Wrong data!');
        	}
        }
    });
}

function deleteUsers (data) {
	$.ajax ({
        url: '/updatestatususers',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function (response) {
        	if (response.status === true) {
        		let validate;
        		for (user of response.users) {
	        		$(`.user[data-user="${user.id}"`).remove();
	        		if(!user.isset) validate = true;
        		}
        		$('#update-status').trigger('reset');
        		if(validate) alertConfirmation('Non-existent users have been removed. Please refresh the page!');
        		else $('#alert-window').modal('hide');
        	} else if(response.error.code == 104) {
				if(!data.users.length) message = 'You haven\'t selected any users!';
        		alertConfirmation(message);
        	}
        }
    });
}

function deleteConfirmation (message, users, func) {
	$('#alert-window .modal-title').text('Delete Confirmation');
	let actionButton = $('#alert-window .modal-footer .action');
	actionButton.text('Delete');
	actionButton.removeClass('btn-primary');
	actionButton.addClass('btn-danger');
	actionButton.one('click', () => func(users));

	$('#alert-window .modal-body').html('<p>' + message + '</p>');
	$('#alert-window .modal-footer .action').show();
	$('#alert-window').modal('show');
}

function alertConfirmation (message) {
	$('#alert-window .modal-title').text('Alert Confirmation');
	$('#alert-window .modal-footer .action').hide();

	$('#alert-window .modal-body').html('<p class="text-danger">' + message + '</p>');
	$('#alert-window').modal('show');
}
