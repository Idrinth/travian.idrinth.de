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
    let shouldPlay = false;
    document.getElementById('time').innerHTML = zeropad(date.getUTCHours()) + ':' + zeropad(date.getUTCMinutes()) + ':' + zeropad(date.getUTCSeconds());
    const elements = document.getElementsByClassName('countdown');
    for (let i = 0; i < elements.length; i++) {
        const target = new Date(elements[i].getAttribute('data-countdown') + '.000+00:00');
        if (target < date) {
            elements[i].innerHTML = '00:00:00';
        } else if (target - date > 86400000) {
            const diff = new Date(target - date);
            elements[i].innerHTML = Math.floor(diff/86400000) + 'd ' + zeropad(diff.getUTCHours()) + ':' + zeropad(diff.getUTCMinutes()) + ':' + zeropad(diff.getUTCSeconds());
        } else {
            const diff = new Date(target - date);
            if (diff < 10000 && !elements[i].hasAttribute('data-played')) {
                elements[i].setAttribute('data-played', 'true');
                shouldPlay = true;
            }
            elements[i].innerHTML = zeropad(diff.getUTCHours()) + ':' + zeropad(diff.getUTCMinutes()) + ':' + zeropad(diff.getUTCSeconds());
        }
    }
    if (shouldPlay) {
        document.getElementById('countdown-audio').play();
    }
}, 100);