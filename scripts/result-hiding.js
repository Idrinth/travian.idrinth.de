(() => {
    const els = document.getElementsByClassName('result');
    for (let i=0; i< els.length; i++) {
        els[i].classList.toggle('hidden');
        els[i].getElementsByTagName('h3')[0].onclick = () => {
            if (event && event.originalTarget && event.originalTarget.parentNode !== els[i]) {
                return;
            }
            if (event && event.srcElement && event.srcElement.parentNode !== els[i]) {
                return;
            }
            els[i].classList.toggle('hidden');
        };
    }
    if (els.length === 1) {
        els[0].classList.toggle('hidden');
    }
})();