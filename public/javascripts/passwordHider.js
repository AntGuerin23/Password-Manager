const staticStart = "Password  : "
$("[data-target]").on("click", toggle)
$("[data-id]").each(hide)

function toggle() {
    let passwordElement = $(`[data-id = '${$(this).data("target")}']`)
    let hidden = (passwordElement.html().indexOf("●") !== -1)
    if (hidden) {
        passwordElement.html(staticStart + passwordElement.data("password"))
        $(this).html(`<i aria-hidden="true" class="fa-solid fa-eye-slash"></i>`)
    } else {
        passwordElement.html(staticStart + "●".repeat(passwordElement.html().length - staticStart.length))
        $(this).html(`<i aria-hidden="true" class="fa-solid fa-eye"></i>`)

    }
}

function hide() {
    $(this).html(staticStart + "●".repeat($(this).html().length - staticStart.length));
    $(this).css({"min-width" : `${$(this).width()}px`})
}