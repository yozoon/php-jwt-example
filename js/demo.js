function goHome() {
    window.location.replace("..");
}

$(document).ready(function () {
    if (!sessionStorage['token']) goHome();

    var decoded = jwt_decode(sessionStorage["token"]);
    var token_epiration = decoded["exp"];
    var now = Math.floor(Date.now() / 1000);
    setTimeout(function () {
        alert("Token expired!");
        goHome();
    }, (token_epiration - now) * 1000);

    $.ajax({
        url:  "../api/resource.php",
        method: "GET",
        beforeSend: function (xhr) {
            xhr.setRequestHeader("Authorization", "Bearer " + sessionStorage["token"]);
        },
        statusCode: {
            401: function (response) {
                alert("Token expired!");
                goHome();
            },
        }, success: function (result) {
            $(".container").append(result["data"]);
            $(".container").append("<br>");
        },
    });

    if (decoded["scopes"].includes("admin")) {
        $.ajax({
            url: "../api/admin.php",
            method: "GET",
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Authorization", "Bearer " + sessionStorage["token"]);
            },
            statusCode: {
                401: function (response) {
                    alert("Token expired!");
                    goHome();
                },
            }, success: function (result) {
                $(".container").append(result["data"]);
            },
        });
    }
});