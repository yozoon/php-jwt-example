$(document).ready(function () {
    $("form").submit(function (event) {
        var _username = $("#username").val();
        var _password = $("#password").val();
        $.ajax({
            url: "http://localhost:8000/api/login.php",
            method: "POST",
            data: { "username": _username, "password": _password, },
            statusCode: {
                401: function (response) {
                    alert("Invalid username or password");
                },
            }, success: function (result) {
                sessionStorage["token"] = result["token"];
                window.location.replace("http://localhost:8000/views/demo.html");
            },
        });
    })
});