function ziggeo_onboard(name, email, success, error) {
	jQuery.ajax({
		type: "POST",
		crossDomain: true,
		url: "https://srvapi.ziggeo.com/v1/accounts",
		data: {
			name: name,
			email: email
		},
		dataType: "json",
		success: function (result) {
			success(result.application.token);
		},
		error: function (err) {
			var errors = "";
			for (var key in err.responseJSON)
				errors += err.responseJSON[key];				
			error(errors);
		}
	});
}
