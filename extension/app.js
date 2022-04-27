$(window).on("load", test)

async function test() {

    if (domContainsLogin()) {
        const response = await callApi()
        console.log(response)
        $("[autocomplete='username']").val(response.username)
        $("[autocomplete='current-password']").val(response.password)
    }
}

async function callApi() {
    //todo : get connection token from storage
    const auth = {
        //put token here
        username: "bob",
        password: "bruh",
        domain: window.location.hostname
    }
    return await fetch("http://password.local/api/passwords", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(auth)
    })
        .then(response => response.text())
        .then(response => JSON.parse(JSON.parse(response)))
}

function domContainsLogin() {
    return $("[autocomplete='username']").length || $("[autocomplete='current-password']".length)
}