$(document).ready(function () {
    $("#login").click(function (event) {
        console.log("clicked");
        var _username = $("#username").val();
        var _password = $("#password").val();
        $.ajax({
            url: "api/login.php",
            method: "POST",
            data: { "username": _username, "password": _password, },
            statusCode: {
                401: function (response) {
                    alert("Invalid username or password");
                },
            }, success: function (result) {
                sessionStorage["token"] = result["token"];
                window.location.replace("views/demo.html");
            },
        });
    })
});