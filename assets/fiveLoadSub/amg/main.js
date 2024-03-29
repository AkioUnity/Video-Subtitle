//functions from internet
function textareaScrollTo(line) {
    var ta = document.getElementById("subtitleTextArea");
    var lineHeight = ta.scrollHeight / (document.getElementById("subtitleTextArea").value.split('\n').length + 1);
    var jump = Math.round(line * lineHeight - (document.getElementById("subtitleFormContainer").clientHeight / 2));
    document.getElementById("subtitleFormContainer").scrollTop = jump;
}

function _string2ArrayBuffer(str) {
    var buf = new ArrayBuffer(str.length * 2); // 2 bytes for each char
    var bufView = new Uint16Array(buf);
    for (var i = 0, strLen = str.length; i < strLen; i++) {
        bufView[i] = str.charCodeAt(i);
    }
    return buf;
}

function _arrayBuffer2String(buf) {
    return String.fromCharCode.apply(null, new Uint16Array(buf));
}

var _appendBuffer = function (buffer1, buffer2) {
    var tmp = new Uint8Array(buffer1.byteLength + buffer2.byteLength);
    tmp.set(new Uint8Array(buffer1), 0);
    tmp.set(new Uint8Array(buffer2), buffer1.byteLength);
    return tmp.buffer;
};

function _zeroPad(num, places) {
    var zero = places - num.toString().length + 1;
    return Array(+(zero > 0 && zero)).join("0") + num;
}

function _makeTextFile(text, type) {

    type = type || 'utf-8';
    if (text) {
        var textEncoder = {};
        var prefixBuffer;

        switch (type) {
            case 'utf-16':
                prefixBuffer = new Uint8Array([255, 254]); //magic of UTF-16:0xFFFE
                textEncoder = new TextEncoder('UTF-16');
                break;
            default:
                prefixBuffer = new Uint8Array([239, 187, 191]); //magic of UTF-18:0xEFBBBF
                textEncoder = new TextEncoder('UTF-8');
        }
        var arrayBuffer = textEncoder.encode(text);
        arrayBuffer = _appendBuffer(prefixBuffer, arrayBuffer);
        var downloadBlob = new Blob([arrayBuffer], {
            type: "text/plain"
        });
        return window.URL.createObjectURL(downloadBlob);
    } else {
        return false;
    }
}

function _arrayBufferToBase64(arrayBuffer, type) {
    var type = type || "utf-8";
    switch (type) {
        case 'utf-8':
            var append = new Uint8Array([239, 187, 191]);
            arrayBuffer = _appendBuffer(append, arrayBuffer);
            break;
        case 'utf-16':
            var append = new Uint8Array([255, 254]);
            arrayBuffer = _appendBuffer(append, arrayBuffer);
            break;
        default:
            var append = new Uint8Array([239, 187, 191]);
            arrayBuffer = _appendBuffer(append, arrayBuffer);

    }
    var base64 = ''
    var encodings = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/'

    var bytes = new Uint8Array(arrayBuffer)
    var byteLength = bytes.byteLength
    var byteRemainder = byteLength % 3
    var mainLength = byteLength - byteRemainder

    var a, b, c, d
    var chunk


    // Main loop deals with bytes in chunks of 3
    for (var i = 0; i < mainLength; i = i + 3) {
        // Combine the three bytes into a single integer
        chunk = (bytes[i] << 16) | (bytes[i + 1] << 8) | bytes[i + 2]

        // Use bitmasks to extract 6-bit segments from the triplet
        a = (chunk & 16515072) >> 18 // 16515072 = (2^6 - 1) << 18
        b = (chunk & 258048) >> 12 // 258048   = (2^6 - 1) << 12
        c = (chunk & 4032) >> 6 // 4032     = (2^6 - 1) << 6
        d = chunk & 63 // 63       = 2^6 - 1

        // Convert the raw binary segments to the appropriate ASCII encoding
        base64 += encodings[a] + encodings[b] + encodings[c] + encodings[d]
    }

    // Deal with the remaining bytes and padding
    if (byteRemainder == 1) {
        chunk = bytes[mainLength]

        a = (chunk & 252) >> 2 // 252 = (2^6 - 1) << 2

        // Set the 4 least significant bits to zero
        b = (chunk & 3) << 4 // 3   = 2^2 - 1

        base64 += encodings[a] + encodings[b] + '=='
    } else if (byteRemainder == 2) {
        chunk = (bytes[mainLength] << 8) | bytes[mainLength + 1]

        a = (chunk & 64512) >> 10 // 64512 = (2^6 - 1) << 10
        b = (chunk & 1008) >> 4 // 1008  = (2^6 - 1) << 4

        // Set the 2 least significant bits to zero
        c = (chunk & 15) << 2 // 15    = 2^4 - 1

        base64 += encodings[a] + encodings[b] + encodings[c] + '='
    }

    return base64
}

function ajax$load(url, callback, method) {
    method = method || 'GET';
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        callback(xhttp);
    };
    xhttp.open(method, url, true);
    xhttp.send();
}