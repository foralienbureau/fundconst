// scroll bar width
function getScrollBarWidth() {
    var scrollbarWidth = window.innerWidth - document.body.clientWidth;
    document.documentElement.style.setProperty('--scroll-bar-width', scrollbarWidth + 'px'); 
}

document.addEventListener("DOMContentLoaded", function(event) { 
    getScrollBarWidth();
});

document.addEventListener("resize", function(event) { 
    getScrollBarWidth();
});


// util
function isEmailValid(string) {

    var patt = new RegExp(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);

    return patt.test(string);
}

function isTextValid(string) {

    if (string.length > 500) {
        return false;
    }

    if (string.length < 2) {
        return false;
    }

    if(string.match(/^[-а-яА-ЯёЁa-zA-Z0-9\s.,]+$/g)) {
        return true;
    }

    return false;
}

function isAmountValid( amount, min, max ) {
    
    var amount_number = parseInt(amount);
    
    if( amount_number < min ) {
        return false;
    }

    if( amount_number > max ) {
        return false;
    }

    return true;
}

function decodeHtmlentities(encoded_text) {

    var textArea = document.createElement('textarea');
    textArea.innerHTML = encoded_text; 

    return textArea.value;
}
