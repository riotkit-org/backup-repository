function localeWasChanged() {
    let lang = document.getElementById('locale_picker').value;
    let isHttps = window.location.href.indexOf('https://') !== -1;
    let split = window.location.href.replace('http://', '').replace('https://').split('/');

    if (split[1].length === 2) {
        split[1] = lang;
    } else {
        split.splice(1, 0, lang);
    }

    window.location.href = (isHttps ? 'https://' : 'http://') + split.join('/');
}
