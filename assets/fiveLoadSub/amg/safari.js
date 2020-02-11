var __hack = {
    isOpera: (!!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0 || false),
    isFirefox: (typeof InstallTrigger !== 'undefined'),
    isSafari: (Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0 || false),
    isChrome: (!!window.chrome && !(!!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0 || false)),
    isIE: (false || !!document.documentMode),
    safari: {
        download: {},
        downloader: {}
    },
    chrome: {},
    firefox: {},
};
if (__hack.isSafari) {
    var imported = document.createElement('script');
    imported.src = getFile('../inc/encoding-indexes.min.js');
    document.head.appendChild(imported);
    delete imported;
    var imported = document.createElement('script');
    imported.src = getFile('../inc/encoding.min.js');
    document.head.appendChild(imported);
    delete imported;
}