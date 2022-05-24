$("#passwordInput").on("keyup", calculateEntropy)

function calculateEntropy() {
    let input = $(this).val()
    let length = input.length
    let pool = calculatePool(input)
    let entropy = Math.log2(Math.pow(pool, length))
    changeStyle(entropy, $(this))
}

function calculatePool(input) {
    let pool = 0;
    if (input.match(/\d/)) {
        pool += 10;
    }
    if (input.match(/[a-z]/)) {
        pool += 26;
    }
    if (input.match(/[A-Z]/)) {
        pool += 26;
    }
    if (input.match(/[<>@!#$%^&*()_+\[\]{}?:;|'\\~",./`\-=]/)) {
        pool += 32;
    }

    return pool
}

function changeStyle(entropy, inputField) {
    removeClasses(inputField)
    if (entropy < 40) {
        inputField.addClass("border-bottom-danger")
    } else if (entropy < 60) {
        inputField.addClass("border-bottom-warning")
    } else {
        inputField.addClass("border-bottom-success")
    }
}

function removeClasses(inputField) {
    inputField.removeClass("border-bottom-danger")
    inputField.removeClass("border-bottom-warning")
    inputField.removeClass("border-bottom-success")
}