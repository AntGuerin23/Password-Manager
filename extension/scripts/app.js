$(window).on("load", test)

async function test() {
    if (domContainsLogin()) {
        const response = await callApi()
        if (response != null) {
            $("[autocomplete='username']").val(response.username)
            $("[autocomplete='current-password']").val(response.password)
        }
    }
}

async function callApi() {
    let storage = await chrome.storage.sync.get(["apiKey", "encryptionKey"])
    const auth = {
        apiKey: storage.apiKey,
        encryptionKey: storage.encryptionKey,
        domain: window.location.hostname
    }
    return await fetch("http://password.local/api/passwords", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(auth)
    })
        .then(response => response.text())
        .then(response => {
            if (response !== "") {
                try {
                    return JSON.parse(response)
                } catch {
                    logout()
                }
            }
            return null
        })
}

function domContainsLogin() {
    return $("[autocomplete='username']").length || $("[autocomplete='current-password']".length)
}

function logout() {
    chrome.storage.sync.remove(["apiKey", "encryptionKey"])
}