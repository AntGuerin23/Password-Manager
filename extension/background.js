setInterval(callApi, 60 * 1000)


chrome.runtime.onInstalled.addListener(callApi)

function callApi() {
    console.log("called")
    fetch("http://password.local/passwords-json/" + 3)
        .then(response => response.json())
        .then(data =>chrome.storage.sync.set({"loginInfo" : data}));
}