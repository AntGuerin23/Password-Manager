await checkStatus()

async function tryLogin() {
    let username = document.getElementById("usernameInput").value
    let password = document.getElementById("passwordInput").value
    let json = await authenticate(username, password);
    if (json.valid) {
        chrome.storage.sync.set({apiKey : json.apiKey, encryptionKey : json.encryptionKey})
        await checkStatus()
    } else {
        addFlash();
    }
}

async function logout() {
    chrome.storage.sync.remove(["apiKey", "encryptionKey"])
    await checkStatus()
}

async function authenticate(username, password) {
    const auth = {
        username: username,
        password: password,
    }
    return await fetch("http://password.local/api/authenticate", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(auth)
    })
        .then(response => response.text())
        .then(response => JSON.parse(response))
}

async function checkStatus() {
    const logged = await isLogged();
    const body = document.getElementById("bs-override")
    if (logged) {
        body.innerHTML = getLoggedHtml()
    } else {
        body.innerHTML = getDefaultHtml()
    }
    initializeEventListeners();
    // document.querySelector("body").style.height = "max-content";
    // document.querySelector("body").style.width = "max-content";
}
async function isLogged() {
    let storage = await chrome.storage.sync.get(["apiKey", "encryptionKey"])
    return (storage.apiKey !== undefined && storage.encryptionKey !== undefined)
}

function getLoggedHtml() {
    return `
        <h1 class="h4 text-white text-center">You are logged in.</h1>
        <div class="text-center">
            <a id="logoutButton" class="d-sm-inline-block ml-3 btn btn-sm btn-danger shadow-sm">Logout</a>
        </div>
    `
}

function getDefaultHtml() {
    return `    
    <div class="card">
        <div class="card-body" id="card">
            <h1 class="h4 text-gray-900 mb-4 text-center">Login</h1>
            <form class="user">
                <div class="form-group">
                    <input type="text"
                           class="form-control form-control-user"
                           id="usernameInput" placeholder="Enter Username">
                </div>
                <div class="form-group">
                    <input name="password" type="password"
                           class="form-control form-control-user"
                           id="passwordInput" placeholder="Enter Password">
                </div>
                <button type="button" id="loginButton" class="btn btn-primary btn-user btn-block">
                    Login
                </button>
            </form>
        </div>
    </div>
`
}

function addFlash() {
    if (document.getElementById("flash") !== null) return
    const card = document.getElementById("card")
    const flash = document.createElement("div")
    card.prepend(flash);
    flash.outerHTML = `<div id="flash" class="alert alert-danger" role="alert">Invalid login info</div>`
}

function initializeEventListeners() {
    let loginButton = document.getElementById("loginButton")
    if (loginButton != null) {
        loginButton.addEventListener("click", tryLogin)
    }
    let logoutButton = document.getElementById("logoutButton")
    if (logoutButton != null) {
        logoutButton.addEventListener("click", logout)
    }
}
