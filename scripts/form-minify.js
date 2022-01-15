(() => {
    const forms = document.getElementsByTagName('form');
    for (let j=0; j<forms.length;j++) {
        const els = forms[j].getElementsByTagName('fieldset');
        for (let i=1; i< els.length; i++) {
            els[i].classList.toggle('invisible');
            els[i].getElementsByTagName('legend')[0].onclick = () => {
                els[i].classList.toggle('invisible');
            };
        }
    }
})();