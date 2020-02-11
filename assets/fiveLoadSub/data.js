var store = new Array();
var htmlTagRegex = new RegExp(/\<([a-zA-Z0-9]+)([^<\/]+)src\=\"([^\"]+)\"([^<>]*)\>/gi);

function getFile(url) {
    console.log(url);
    var result = url;
    for (row of store) {
        if (row.url == url) {
            result = row.dataUrl;
        }
    }
    return result;
}

function replaceFile() {
    function replacer(match, p1, p2, p3, p4, offset, string) {
        return "<" + p1 + p2 + "src=\"" + getFile(p3) + "\"" + p4 + ">";
    }
    document.documentElement.innerHTML = document.documentElement.innerHTML.replace(htmlTagRegex, replacer); //
}
