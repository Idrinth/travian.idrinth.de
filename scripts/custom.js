const zeropad = (string) => {
    string = '' + string;
    if (string.length < 1) {
        return '00';
    }
    if (string.length < 2) {
        return '0' + string;
    }
    return string;
};
window.setInterval(() => {
    const date = new Date();
    document.getElementById('time').innerHTML = zeropad(date.getUTCHours()) + ':' + zeropad(date.getUTCMinutes()) + ':' + zeropad(date.getUTCSeconds());
}, 100);
window.setInterval(() => {
    const date = new Date();
    const elements = document.getElementsByClassName('countdown');
    for (let i = 0; i < elements.length; i++) {
        const target = new Date(elements[i].getAttribute('data-countdown'));
        if (target < date) {
            elements[i].innerHTML = '00:00:00';
        } else if (target - date > 86400000) {
            elements[i].innerHTML = '>24h';
        } else {
            const diff = new Date(target - date);
            elements[i].innerHTML = zeropad(diff.getUTCHours()) + ':' + zeropad(diff.getUTCMinutes()) + ':' + zeropad(diff.getUTCSeconds());
        }
    }
}, 100);
window.setInterval(() => {
    fetch('/ping');
}, 10000);