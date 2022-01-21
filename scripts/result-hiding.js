(() => {
    const els = document.getElementsByClassName('result');
    for (let i=0; i< els.length; i++) {
        els[i].classList.toggle('hidden');
    }
})();