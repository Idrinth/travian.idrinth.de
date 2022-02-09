(() => {
    const els = document.getElementsByClassName('result');
    for (let i=0; i< els.length; i++) {
        els[i].classList.toggle('hidden');
        els[i].getElementsByTagName('h3')[0].onclick = () => {
            if (event && event.originalTarget.parentNode !== els[i]) {
                return;
            }
            els[i].classList.toggle('hidden');
        };
    }
})();