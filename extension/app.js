//authentify on SosPass, using popop on top right, store id
$(window).on("load", test)

const userId = 3

function test() {
    chrome.storage.sync.get("loginInfo", (storage) => {
        const {username, password} = JSON.parse(storage.loginInfo)
        console.log(storage)
        //TODO: Get domain, autocomplete depending on storage
        if (domContainsLogin()) {
            $("[autocomplete='username']").val(username)
            $("[autocomplete='current-password']").val(password)
        }
    })

    //if current domain contains domain inside password list, then auto complete :
    //window.location.hostname
}

function domContainsLogin() {
    return $("[autocomplete='username']").length && $("[autocomplete='current-password']".length)
}