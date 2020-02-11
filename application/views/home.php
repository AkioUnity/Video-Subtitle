<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <meta name="keywords" content="fiveLoadSub,subtitle,Subtitling,SRT,SBV,FLS"/>
    <meta name="description" content="Open source web-based subtitling software"/>
    <meta name="author" content="Hans "/>
    <title>Hans Video subtitle</title>

    <link rel="stylesheet" type="text/css" href="<?php echo subtitle_url('subtitle.css') ?>">

    <!-- Including -->
    <script src="<?php echo subtitle_url('inc/wavesurfer.min.js') ?>"></script>
    <script src="<?php echo subtitle_url('inc/wavesurfer.timeline.min.js') ?>"></script>
    <script src="<?php echo subtitle_url('inc/wavesurfer.regions.min.js') ?>"></script>

<!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.js') ?><!--"></script>-->
<!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.timeline.js') ?><!--"></script>-->
<!--    <script src="--><?php //echo subtitle_url('inc/src/wavesurfer.regions.js') ?><!--"></script>-->

    <script src="<?php echo subtitle_url('inc/jszip.min.js') ?>"></script>
<!--    <script src="--><?php //echo subtitle_url('inc/keypress-2.1.4.min.js') ?><!--"></script>-->
    <script src="<?php echo subtitle_url('inc/videoControl/richVideoControl.standalone.js') ?>"></script>

    <script>
        //Hacks, non-standard things
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
            imported.src = getFile('./inc/encoding-indexes.min.js');
            document.head.appendChild(imported);
            delete imported;
            var imported = document.createElement('script');
            imported.src = getFile('./inc/encoding.min.js');
            document.head.appendChild(imported);
            delete imported;
        }
    </script>

    <script>
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
    </script>

    <script>
        function _GET(val) {
            var result = undefined,
                tmp = [];
            location.search
            //.replace ( "?", "" )
            // this is better, there might be a question mark inside
                .substr(1)
                .split("&")
                .forEach(function (item) {
                    tmp = item.split("=");
                    if (tmp[0] === val) result = decodeURIComponent(tmp[1]);
                });
            return result;
        }

        function parseLocalizeTable(csv) {
            var lines = csv.split("\n");
            var result = {};
            var headers = lines[0].split(",");

            for (var i = 1; i < lines.length; i++) {
                var currentline = lines[i].split(",");
                for (var j = 1; j < currentline.length; j++) {
                    result[headers[j]] = result[headers[j]] || {};
                    result[headers[j]][currentline[0]] = currentline[j];

                }
            }
            return result;
        }

        function parseLocalizeAlias(csv) {
            var lines = csv.split("\n");
            var result = {};
            for (var i = 1; i < lines.length; i++) {
                var currentline = lines[i].split(",");
                currentline[1] = currentline[1] || "";
                result[currentline[0]] = currentline[1];

            }
            return result;
        }

        var localize = {
            prefix: 'fiveLoadSub',
            tableCSV: 'nth,en-us,zh-hk,zh-cn,fr\r\nmenu.importVideo,Import Video,\u532F\u5165\u5F71\u7247,\u5BFC\u5165\u5F71\u7247,Importer la Vid\u00E9o\r\nmenu.importSubtitle,Import Sub,\u532F\u5165\u5B57\u5E55&#47;\u6587\u5B57,\u5BFC\u5165\u5B57\u5E55&#47;\u6587\u672C,Importer des Sous-titres\r\nmenu.conversion,Conversion Tools,\u8F49\u63DB\u5DE5\u5177,\u8F6C\u6362\u5DE5\u5177,Outils de conversion\r\nmenu.saveSubtitle,Save Subtitle,\u5132\u5B58\u5B57\u5E55,\u5BFC\u51FA\u5B57\u5E55,Enregistrer des Sous-titres\r\nmenu.settings,Settings,\u8A2D\u5B9A,\u8BBE\u7F6E,Param\u00E8tres\r\nmenu.help,Help,\u8AAA\u660E,\u8BF4\u660E,Aide\r\nmenu.donation,Donation,\u8D0A\u52A9,\u8D5E\u52A9,Donation\r\nvideoPlayer.dropFileHere,Drop video here,\u62D6\u62FD\u5F71\u7247\u6216\u97F3\u983B\u5230\u6B64,\u62D6\u62FD\u5F71\u7247\u6216\u97F3\u9891\u5230\u6B64,D\u00E9poser la vid\u00E9o ici\r\ntextEditor.fontSize,Font Size,\u5B57\u9AD4\u5927\u5C0F,\u5B57\u4F53\u5927\u5C0F,Taille de police\r\ntextEditor.placeHolder,Paste text here,\u5728\u6B64\u8CBC\u4E0A\u6587\u5B57,\u5728\u6B64\u8D34\u4E0A\u6587\u5B57,Coller le texte ici\r\nsaveDialog.title,Select format to save,\u9078\u64C7\u5B58\u5132\u683C\u5F0F,\u9009\u62E9\u5BFC\u51FA\u683C\u5F0F,s\u00E9lectionner le format d\'enregistrement\r\nsaveDialog.xmlInputLabel.fcpx,Choose Final Cut Pro X Title Template(.fcpxml),\u532F\u5165Final Cut Pro X\u6A19\u984C\u6A23\u5F0F(.fcpxml),\u5BFC\u5165Final Cut Pro X\u6807\u9898\u6837\u5F0F(.fcpxml),Choisir Final Cut Pro X Titre Template (.fcpxml)\r\nsaveDialog.xmlInputLabel.fcp7,Choose Final Cut Pro 7 Title Template(.xml),\u532F\u5165Final Cut Pro 7\u6A19\u984C\u6A23\u5F0F(.xml),\u5BFC\u5165Final Cut Pro 7\u6807\u9898\u6837\u5F0F(.xml),Choisir Final Cut Pro 7 Titre Template(.xml)\r\nsaveDialog.xmlInputLabel.premiere,Choose Adobe Premiere Title Template(.prtl),\u532F\u5165Adobe Premiere\u6A19\u984C\u6A23\u5F0F(.prtl),\u5BFC\u5165Adobe Premiere\u6807\u9898\u6837\u5F0F(.prtl),Choisir Adobe Premiere Titre Template(.prtl)\r\nsaveDialog.xmlInfo.fillLabel,Fill some info of your video,\u8ACB\u8F38\u5165\u5F71\u7247\u7684\u8CC7\u8A0A,\u8BF7\u8F93\u5165\u5F71\u7247\u5C5E\u6027,Remplissez quelques informations de votre vid\u00E9o\r\nsaveDialog.xmlInfo.width,Width,\u5BEC\u5EA6,\u5BBD\u5EA6,Largeur\r\nsaveDialog.xmlInfo.height,Height,\u9AD8\u5EA6,\u9AD8\u5EA6,Hauteur\r\nsaveDialog.xmlInfo.frameRate,Frame Rate,\u5E40\u901F,\u5E27\u901F\u7387,Fr\u00E9quence Image\r\nsaveDialog.copyFromHereLabel,Copy from here,\u7531\u6B64\u5FA9\u5236,\u7531\u6B64\u590D\u5236,Copier d\'ici\r\nsaveDialog.cacnel,Cacnel,\u53D6\u6D88,\u53D6\u6D88,Annuler\r\nsaveDialog.saveAs,Save As...,\u5B58\u5132...,\u5BFC\u51FA...,Enregistrer\r\nsaveDialog.safariPrompt.fcp7,Press \u2318+S and save as 1.xml in the next page!,\u5728\u4E0B\u4E00\u9801\u6309\u2318+S\u5132\u5B58\u70BA1.xml !,\u5728\u4E0B\u4E00\u9875\u6309\u2318+S\u4FDD\u5B58\u4E3A1.xml !,Appuyez sur \u2318 + S et enregistrer en tant que 1.xml dans la page suivante!\r\nsaveDialog.safariPrompt.fcpx,Press \u2318+S and save as 1.fcpxml in the next page!,\u5728\u4E0B\u4E00\u9801\u6309\u2318+S\u5132\u5B58\u70BA1.fcpxml !,\u5728\u4E0B\u4E00\u9875\u6309\u2318+S\u4FDD\u5B58\u4E3A1.fcpxml !,Appuyez sur \u2318 + S et enregistrer en tant que 1.fcpxml dans la page suivante!\r\nsaveDialog.safariPrompt.pr,Press \u2318+S and save as 1.zip in the next page!,\u5728\u4E0B\u4E00\u9801\u6309\u2318+S\u5132\u5B58\u70BA1.zip !,\u5728\u4E0B\u4E00\u9875\u6309\u2318+S\u4FDD\u5B58\u4E3A1.zip! ,Appuyez sur \u2318 + S et enregistrer en tant que 1.zip dans la page suivante!\r\nconvertDialog.title,Convert Tools,\u8F49\u63DB\u5DE5\u5177,\u8F6C\u6362\u5DE5\u5177,Outil de conversion\r\nconvertDialog.OK,OK,\u78BA\u5B9A,\u786E\u5B9A,OK\r\nconvertDialog.cancel,Cancel,\u53D6\u6D88,\u53D6\u6D88,Annuler\r\nconvertDialog.selection.removeEmptyLines,Remove empty lines,\u79FB\u9664\u7A7A\u884C,\u79FB\u9664\u7A7A\u884C,Supprimer les lignes vides\r\nconvertDialog.selection.splitSpaces,Split lines by spaces,\u5C07\u7A7A\u683C\u8F49\u63DB\u70BA\u5206\u884C,\u5C06\u7A7A\u683C\u8F6C\u6362\u4E3A\u5206\u884C,S\u00E9parer les lignes par des espaces\r\nconvertDialog.selection.removePunctuations,Remove punctuations&#40;and turn comma&#44;periods etc. to space&#41;,\u79FB\u9664\u6A19\u9EDE\u7B26\u865F&#40;\u4E26\u628A\u9017\u865F\u53E5\u865F\u7B49\u8B8A\u70BA\u7A7A\u683C&#41;,\u79FB\u9664\u6807\u70B9\u7B26\u53F7&#40;\u5E76\u628A\u9017\u53F7\u53E5\u53F7\u7B49\u53D8\u4E3A\u7A7A\u683C&#41;,\"Retirer les ponctuations (et changer des virgules, des points etc. \u00E0 des espaces)\"\r\nconvertDialog.selection.splitPunctuations,Remove punctuations&#40;and turn comma&#44;periods etc. to new line&#41;,\u79FB\u9664\u6A19\u9EDE\u7B26\u865F&#40;\u4E26\u628A\u9017\u865F\u53E5\u865F\u7B49\u8B8A\u70BA\u63DB\u884C&#41;,\u79FB\u9664\u6807\u70B9\u7B26\u53F7&#40;\u5E76\u628A\u9017\u53F7\u53E5\u53F7\u7B49\u53D8\u4E3A\u63DB\u884C&#41;,\"Retirer les ponctuations (et changer des virgules, des points etc. \u00E0 une nouvelle ligne)\"\r\nconvertDialog.selection.removeQuotes,Remove Quotes,\u79FB\u9664\u5F15\u865F,\u79FB\u9664\u5F15\u53F7,Supprimer les guillemets\r\nconvertDialog.selection.charsetImportFile,Import subtitle\/text file to convert,\u532F\u5165\u6587\u5B57\u6216\u5B57\u5E55\u6A94\u6848\u8F49\u63DB,\u5BFC\u5165\u6587\u672C\u6216\u5B57\u5E55\u6863\u6848\u8F6C\u6362,Importer des sous-titres \/ fichier texte \u00E0 convertir\r\nconvertDialog.selection.charsetTo,Convert To,\u8F49\u63DB\u70BA\u7DE8\u78BC,\u8F6C\u6362\u4E3A\u7F16\u7801,Convertir \u00E0\r\nconvertDialog.original,Original,\u539F\u683C\u5F0F,\u539F\u683C\u5F0F,Format d\'origine\r\nconvertDialog.conversionPreview,Conversion Preview,\u8F49\u63DB\u9810\u89BD,\u8F6C\u6362\u9884\u89C8,Pr\u00E9visualiser la conversion\r\nconvertDialog.tabLabel.text,Text Tool,\u6587\u5B57\u5DE5\u5177,\u6587\u672C\u5DE5\u5177,Outil de Texte\r\nconvertDialog.tabLabel.subtitle,Subtitle Conversion,\u5B57\u5E55\u683C\u5F0F\u8F49\u63DB,\u5B57\u5E55\u683C\u5F0F\u8F6C\u6362,Conversion des Sous-titres\r\nconvertDialog.tabLabel.charset,Charset Conversion,\u6587\u5B57\u7DE8\u78BC\u8F49\u63DB,\u6587\u672C\u7F16\u7801\u8F6C\u6362,Conversion de jeux de caract\u00E8res\r\napp.exitConfirmMsg,Save before leave or changes will lost!,\u96E2\u958B\u524D\u4EFB\u4F55\u672A\u5132\u5B58\u4E4B\u8B8A\u66F4\u5C07\u6D41\u5931,\u79BB\u5F00\u524D\u4EFB\u4F55\u672A\u4FDD\u5B58\u4E4B\u53D8\u66F4\u5C06\u6D41\u5931,Enregistrer avant de quitter sinon les modifications non enregistr\u00E9es seront perdues !',
            aliasCSV: 'code,alias,name\r\naf,,afrikaans\r\naf-za,,afrikaans (south africa)\r\nar,ar,arabic\r\nar-ae,ar,arabic (u.a.e.)\r\nar-bh,ar,arabic (bahrain)\r\nar-dz,ar,arabic (algeria)\r\nar-eg,ar,arabic (egypt)\r\nar-iq,ar,arabic (iraq)\r\nar-jo,ar,arabic (jordan)\r\nar-kw,ar,arabic (kuwait)\r\nar-lb,ar,arabic (lebanon)\r\nar-ly,ar,arabic (libya)\r\nar-ma,ar,arabic (morocco)\r\nar-om,ar,arabic (oman)\r\nar-qa,ar,arabic (qatar)\r\nar-sa,ar,arabic (saudi arabia)\r\nar-sy,ar,arabic (syria)\r\nar-tn,ar,arabic (tunisia)\r\nar-ye,ar,arabic (yemen)\r\naz,,azeri (latin)\r\naz-az,,azeri (latin) (azerbaijan)\r\naz-az,,azeri (cyrillic) (azerbaijan)\r\nbe,be,belarusian\r\nbe-by,be,belarusian (belarus)\r\nbg,,bulgarian\r\nbg-bg,,bulgarian (bulgaria)\r\nbs-ba,,bosnian (bosnia and herzegovina)\r\nca,,catalan\r\nca-es,,catalan (spain)\r\ncs,,czech\r\ncs-cz,,czech (czech republic)\r\ncy,,welsh\r\ncy-gb,,welsh (united kingdom)\r\nda,,danish\r\nda-dk,,danish (denmark)\r\nde,de,german\r\nde-at,de,german (austria)\r\nde-ch,de,german (switzerland)\r\nde-de,de,german (germany)\r\nde-li,de,german (liechtenstein)\r\nde-lu,de,german (luxembourg)\r\ndv,,divehi\r\ndv-mv,,divehi (maldives)\r\nel,,greek\r\nel-gr,,greek (greece)\r\nen,en-us,english\r\nen-au,en-us,english (australia)\r\nen-bz,en-us,english (belize)\r\nen-ca,en-us,english (canada)\r\nen-cb,en-us,english (caribbean)\r\nen-gb,en-us,english (united kingdom)\r\nen-ie,en-us,english (ireland)\r\nen-jm,en-us,english (jamaica)\r\nen-nz,en-us,english (new zealand)\r\nen-ph,en-us,english (republic of the philippines)\r\nen-tt,en-us,english (trinidad and tobago)\r\nen-us,en-us,english (united states)\r\nen-za,en-us,english (south africa)\r\nen-zw,en-us,english (zimbabwe)\r\neo,,esperanto\r\nes,es,spanish\r\nes-ar,es,spanish (argentina)\r\nes-bo,es,spanish (bolivia)\r\nes-cl,es,spanish (chile)\r\nes-co,es,spanish (colombia)\r\nes-cr,es,spanish (costa rica)\r\nes-do,es,spanish (dominican republic)\r\nes-ec,es,spanish (ecuador)\r\nes-es,es,spanish (castilian)\r\nes-es,es,spanish (spain)\r\nes-gt,es,spanish (guatemala)\r\nes-hn,es,spanish (honduras)\r\nes-mx,es,spanish (mexico)\r\nes-ni,es,spanish (nicaragua)\r\nes-pa,es,spanish (panama)\r\nes-pe,es,spanish (peru)\r\nes-pr,es,spanish (puerto rico)\r\nes-py,es,spanish (paraguay)\r\nes-sv,es,spanish (el salvador)\r\nes-uy,es,spanish (uruguay)\r\nes-ve,es,spanish (venezuela)\r\net,,estonian\r\net-ee,,estonian (estonia)\r\neu,,basque\r\neu-es,,basque (spain)\r\nfa,,farsi\r\nfa-ir,,farsi (iran)\r\nfi,,finnish\r\nfi-fi,,finnish (finland)\r\nfo,,faroese\r\nfo-fo,,faroese (faroe islands)\r\nfr,fr,french\r\nfr-be,fr,french (belgium)\r\nfr-ca,fr,french (canada)\r\nfr-ch,fr,french (switzerland)\r\nfr-fr,fr,french (france)\r\nfr-lu,fr,french (luxembourg)\r\nfr-mc,fr,french (principality of monaco)\r\ngl,,galician\r\ngl-es,,galician (spain)\r\ngu,,gujarati\r\ngu-in,,gujarati (india)\r\nhe,,hebrew\r\nhe-il,,hebrew (israel)\r\nhi,,hindi\r\nhi-in,,hindi (india)\r\nhr,,croatian\r\nhr-ba,,croatian (bosnia and herzegovina)\r\nhr-hr,,croatian (croatia)\r\nhu,,hungarian\r\nhu-hu,,hungarian (hungary)\r\nhy,,armenian\r\nhy-am,,armenian (armenia)\r\nid,,indonesian\r\nid-id,,indonesian (indonesia)\r\nis,,icelandic\r\nis-is,,icelandic (iceland)\r\nit,it,italian\r\nit-ch,it,italian (switzerland)\r\nit-it,it,italian (italy)\r\nja,,japanese\r\nja-jp,,japanese (japan)\r\nka,,georgian\r\nka-ge,,georgian (georgia)\r\nkk,,kazakh\r\nkk-kz,,kazakh (kazakhstan)\r\nkn,,kannada\r\nkn-in,,kannada (india)\r\nko,ko,korean\r\nko-kr,ko,korean (korea)\r\nkok,,konkani\r\nkok-in,,konkani (india)\r\nky,,kyrgyz\r\nky-kg,,kyrgyz (kyrgyzstan)\r\nlt,,lithuanian\r\nlt-lt,,lithuanian (lithuania)\r\nlv,,latvian\r\nlv-lv,,latvian (latvia)\r\nmi,,maori\r\nmi-nz,,maori (new zealand)\r\nmk,,fyro macedonian\r\nmk-mk,,fyro macedonian (former yugoslav republic of macedonia)\r\nmn,,mongolian\r\nmn-mn,,mongolian (mongolia)\r\nmr,,marathi\r\nmr-in,,marathi (india)\r\nms,ms,malay\r\nms-bn,ms,malay (brunei darussalam)\r\nms-my,ms,malay (malaysia)\r\nmt,,maltese\r\nmt-mt,,maltese (malta)\r\nnb,,norwegian (bokm?l)\r\nnb-no,,norwegian (bokm?l) (norway)\r\nnl,,dutch\r\nnl-be,,dutch (belgium)\r\nnl-nl,,dutch (netherlands)\r\nnn-no,,norwegian (nynorsk) (norway)\r\nns,,northern sotho\r\nns-za,,northern sotho (south africa)\r\npa,,punjabi\r\npa-in,,punjabi (india)\r\npl,,polish\r\npl-pl,,polish (poland)\r\nps,,pashto\r\nps-ar,,pashto (afghanistan)\r\npt,pt,portuguese\r\npt-br,pt,portuguese (brazil)\r\npt-pt,pt,portuguese (portugal)\r\nqu,,quechua\r\nqu-bo,,quechua (bolivia)\r\nqu-ec,,quechua (ecuador)\r\nqu-pe,,quechua (peru)\r\nro,,romanian\r\nro-ro,,romanian (romania)\r\nru,,russian\r\nru-ru,,russian (russia)\r\nsa,,sanskrit\r\nsa-in,,sanskrit (india)\r\nse,,sami (northern)\r\nse-fi,,sami (northern) (finland)\r\nse-fi,,sami (skolt) (finland)\r\nse-fi,,sami (inari) (finland)\r\nse-no,,sami (northern) (norway)\r\nse-no,,sami (lule) (norway)\r\nse-no,,sami (southern) (norway)\r\nse-se,,sami (northern) (sweden)\r\nse-se,,sami (lule) (sweden)\r\nse-se,,sami (southern) (sweden)\r\nsk,,slovak\r\nsk-sk,,slovak (slovakia)\r\nsl,,slovenian\r\nsl-si,,slovenian (slovenia)\r\nsq,,albanian\r\nsq-al,,albanian (albania)\r\nsr-ba,,serbian (latin) (bosnia and herzegovina)\r\nsr-ba,,serbian (cyrillic) (bosnia and herzegovina)\r\nsr-sp,,serbian (latin) (serbia and montenegro)\r\nsr-sp,,serbian (cyrillic) (serbia and montenegro)\r\nsv,,swedish\r\nsv-fi,,swedish (finland)\r\nsv-se,,swedish (sweden)\r\nsw,,swahili\r\nsw-ke,,swahili (kenya)\r\nsyr,,syriac\r\nsyr-sy,,syriac (syria)\r\nta,,tamil\r\nta-in,,tamil (india)\r\nte,,telugu\r\nte-in,,telugu (india)\r\nth,,thai\r\nth-th,,thai (thailand)\r\ntl,,tagalog\r\ntl-ph,,tagalog (philippines)\r\ntn,,tswana\r\ntn-za,,tswana (south africa)\r\ntr,,turkish\r\ntr-tr,,turkish (turkey)\r\ntt,,tatar\r\ntt-ru,,tatar (russia)\r\nts,,tsonga\r\nuk,,ukrainian\r\nuk-ua,,ukrainian (ukraine)\r\nur,,urdu\r\nur-pk,,urdu (islamic republic of pakistan)\r\nuz,uz,uzbek (latin)\r\nuz-uz,uz,uzbek (latin) (uzbekistan)\r\nuz-uz,uz,uzbek (cyrillic) (uzbekistan)\r\nvi,,vietnamese\r\nvi-vn,,vietnamese (viet nam)\r\nxh,,xhosa\r\nxh-za,,xhosa (south africa)\r\nzh,zh-hk,chinese\r\nzh-cn,zh-hk,chinese (s)\r\nzh-hk,zh-tw,chinese (hong kong)\r\nzh-mo,zh-hk,chinese (macau)\r\nzh-sg,zh-cn,chinese (singapore)\r\nzh-tw,zh-hk,chinese (t)\r\nzu,,zulu\r\nzu-za,,zulu (south africa)',
            table: {},
            alias: {},
            currentLanguage: (_GET('lang') || navigator.language || navigator.userLanguage || "en-US").toLocaleLowerCase(),
            failBackLanguage: ('en-US').toLocaleLowerCase(),
        }

        localize.table = parseLocalizeTable(localize.tableCSV.replace(/\r/, ''));
        localize.alias = parseLocalizeAlias(localize.aliasCSV.replace(/\r/, ''));
        var stopMonitorMainScrolling = false;
        var lastMainScrollTo = 0;
        var slide3Stop = false;


        function localizedText(str) {
            str = str || "";
            result = "";
            if (str != "") {
                if (localize.table != {}) {
                    if ((localize.currentLanguage in localize.table) && (typeof localize.table[localize.currentLanguage][str] != "undefined")) {
                        result = localize.table[localize.currentLanguage][str];
                    } else if ((localize.alias[localize.currentLanguage] in localize.table) && (typeof localize.table[localize.alias[localize.currentLanguage]][str] != "undefined")) {
                        result = localize.table[localize.alias[localize.currentLanguage]][str];
                    } else if ((localize.alias[localize.alias[localize.currentLanguage]] in localize.table) && (typeof localize.table[localize.alias[localize.alias[localize.currentLanguage]]][str] != "undefined")) {
                        result = localize.table[localize.alias[localize.alias[localize.currentLanguage]]][str];
                    } else {
                        result = localize.table[localize.failBackLanguage][str];

                    }
                }
            }

            return result;
        }

        function replaceLocalizedText() {
            var doc = document.body;
            for (key in localize.table["en-us"]) {
                doc.innerHTML = doc.innerHTML.replace(new RegExp('\{' + localize.prefix + '_' + key + '\}', 'g'), localizedText(key));
            }

        }
    </script>
    <script>
        //Object definition
        function currentStatusObject() {
            this.videoFile = "";
            this.videoIsPlaying = false,
                this.lineNo = 0;
            this.buttonMapping = [];
            this.savedChanges = false;
            this.disableWaveformSeekEvent = false;
            this.disableVideoSeekEvent = false;
            this.timerID = 0;
            this.saveFormat = "";
            this.subtitleTextAreaLock = false;
            this.editHistory = []; //Store 100 step of history
            this.editHistoryIndex = -1; //Index number starting from 0, sames as the array index
            this.UI = {
                horizontalDragRatio: 1.0,
                verticalDragRatio: 1.0,
                videoWaveHeightRatio: 0.0,
                dragging: false
            }
            this.convertDialog = {
                lastSelection: 'format',
            }
        }

        function settingsObject() {
            //Cmd+Z and Shift+Cmd+Z will be hard-coded

            function keyboardSettingsObject() {
                this.sortSubtitle = "Command+/";
                this.play = "Command+Enter";
                this.lockTextarea = "Command+Shift+L";
                this.gotoFirstLine = "Command+[";
                this.goBackFiveSeconds = "Command+,";
                this.goForwardFiveSeconds = "Command+.";
                this.zoomInWaveform = "=";
                this.zoomOutWaveform = "-";
                this.zoomFullWaveform = "Command+0";
                this.settings = "Command+,";
                this.importText = "Command+Shift+T";
                this.importVideo = "Command+Shift+I";
                this.exportSubtitle = "Command+E";
                this.timecodeNext = "Space";
                this.timecodeIn = "Command+Left";
                this.timecodeOut = "Command+Right";
                this.timecodePeriod = "~";
                this.gotoNextLine = "Command+Down";
                this.gotoPreviousLine = "Command+Up";
            }

            this.key = new keyboardSettingsObject(); //key must be an object without methods/sub/functions because it's going to be store into localstorage using JSON.stringify()
            this.init = function () {
                var store = localStorage.getItem("info.luniz.fiveLoadSub-settings.key");
                if (store != null) {
                    if (JSON.stringify(store) != JSON.stringify(this.key)) {
                        this.key = store;
                    }
                }
            };

            this.save = function () {
                var store = JSON.stringify(this.key);
                return localStorage.setItem("info.luniz.fiveLoadSub-settings.key", store);
            };
        }

        function bufferObject() {
            this.subtitleOverlayContent = "";
        }

        function timeCodeObject() {
            this.h = 0;
            this.m = 0;
            this.s = 0;
            this.f = 0;
            this.empty = true;
            this.strVal = function () {
                var result = "[" + _zeroPad(this.h, 2) + ":" + _zeroPad(this.m, 2) + ":" + _zeroPad(this.s, 2) + "." + _zeroPad(this.f, 2) + "]";
                if (this.empty) {
                    result = "";
                }
                return result;
            };
            this.totalFrames = function (frameRate) {
                var result = 0;
                frameRate = frameRate || 30;
                result = Math.round((this.f / 30) * frameRate) + this.s * frameRate + this.m * 60 * frameRate + this.h * 3600 * frameRate;
                return result;
            }
            this.parseFromString = function (str) {
                if ((str.charAt(0) == "[") && (str.charAt(3) == ":") && (str.charAt(6) == ":") && (str.charAt(9) == ".") && (str.charAt(12) == "]") &&
                    (/^\d+$/.test(str.charAt(1))) && (/^\d+$/.test(str.charAt(2))) && (/^\d+$/.test(str.charAt(4))) && (/^\d+$/.test(str.charAt(5))) && (/^\d+$/.test(str.charAt(7))) && (/^\d+$/.test(str.charAt(8))) && (/^\d+$/.test(str.charAt(10))) && (/^\d+$/.test(str.charAt(11)))) {
                    this.h = parseInt(str.charAt(1) + str.charAt(2));
                    this.m = parseInt(str.charAt(4) + str.charAt(5));
                    this.s = parseInt(str.charAt(7) + str.charAt(8));
                    this.f = parseInt(str.charAt(10) + str.charAt(11));
                    this.empty = false;
                    return true;
                } else {
                    this.empty = true;
                    return false;
                }
            }
        }

        function subtitleLineObject() {
            this.begin = new timeCodeObject();
            this.text = "";
            this.end = new timeCodeObject();
            this.empty = true;
            this.parseFromString = function (str) {
                var result = false;
                if ((str.length > 13) && (str.length <= 26)) {
                    this.text = str.substring(13, str.length);
                    result = this.begin.parseFromString(str.substr(0, 13));
                } else if (str.length > 26) {

                    this.text = str.substring(13, str.length - 13);
                    if (!this.end.parseFromString(str.substr(str.length - 13, 13))) {
                        this.text = str.substring(13, str.length);
                    }
                    result = this.begin.parseFromString(str.substr(0, 13));
                } else {
                    this.text = str;
                }
                this.empty = !result;
                return result;
            }
            this.strVal = function () {
                var result = "";
                result += this.begin.strVal();
                result += this.text;
                result += this.end.strVal();
                return result;
            }
        }

        function subtitleDocObject() {
            //This is the object to handle the whole subtitle document inside textarea.
            //It also responsable to import/export/output/convert other format and provide a indexed sequeuence for displaying overlay subtitle.
            //Basicly it will skip any line that is invalid(maybe still editing).
            function prXmlObject() {
                var video = {
                    frameRate: 25,
                    isNtsc: false,
                    width: 0,
                    height: 0,
                    totalFrames: 0,
                    timeCodeString: "00:00:00:00"

                };
                this.xml = "";
                this.prtl = "";
                this.prtlFiles = [];
                this.download = "";
                var ready = false;
                var xmlHead = '<?xml version = "1.0" encoding = "UTF-8"?><!DOCTYPE xmeml><xmeml version="4"><project><name>fiveLoadSubImport</name><children><sequence id="fiveLoadSubImport" TL.SQAudioVisibleBase="0" TL.SQVideoVisibleBase="0" TL.SQVisibleBaseTime="0" TL.SQHideShyTracks="0" Monitor.ProgramZoomIn="0" MZ.WorkInPoint="0"><uuid>{uuid}</uuid><duration>{video.totalFrames}</duration><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><name>fiveLoadSubImport</name><media><video><format><samplecharacteristics><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>square</pixelaspectratio><fielddominance>lower</fielddominance><colordepth>24</colordepth></samplecharacteristics></format>';
                var xmlFooter = '</video></media><timecode><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><string>{video.timeCodeString}</string><frame>0</frame><displayformat>NDF</displayformat></timecode></sequence></children></project></xmeml>';
                this.get = function (str) {
                    return video[str];
                }

                this.updatePrtl = function () {
                    var templateInput = document.getElementById('templateInput');
                    var reader = new FileReader();
                    reader.onload = function () {
                        this.prtl = reader.result;
                        subtitleDocument.prXml.prtl = this.prtl;

                        var parser = new DOMParser();
                        var xmlDoc = parser.parseFromString(this.prtl, 'text/xml');

                        video.width = xmlDoc.getElementsByTagName('pXPIXELS')[0].innerHTML || '0';
                        video.width = parseInt(video.width);

                        video.height = xmlDoc.getElementsByTagName('pYLINES')[0].innerHTML || '0';
                        video.height = parseInt(video.height);

                        video.aspectRatio = xmlDoc.getElementsByTagName('pSCREENAR')[0].innerHTML || '1';
                        video.aspectRatio = parseInt(video.aspectRatio);

                        video.width = video.width * video.aspectRatio;

                        document.getElementById('saveDialogXml_width').value = video.width;
                        document.getElementById('saveDialogXml_height').value = video.height;

                    }
                    reader.readAsText(templateInput.files[0]);
                    video.totalFrames = subtitleDocument.endding.totalFrames();
                    if (video.totalFrames > 0) {
                        video.totalFrames = video.totalFrames + 900 * video.frameRate;
                    }
                }

                this.updateConfig = function () {
                    var width = parseInt(document.getElementById('saveDialogXml_width').value);
                    var height = parseInt(document.getElementById('saveDialogXml_height').value);
                    var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
                    if (width > 0) {
                        video.width = width;
                    }
                    if (height > 0) {
                        video.height = height;
                    }
                    if (frameRate > 0) {
                        video.frameRate = frameRate;
                    }
                    if ((video.frameRate == 15) || (video.frameRate == 24) || (video.frameRate == 30) || (video.frameRate == 60)) {
                        video.timeCodeString = "00;00;00;00";
                        video.isNtsc = true;
                    } else {
                        video.timeCodeString = "00:00:00:00";
                        video.isNtsc = false;
                    }
                    video.totalFrames = subtitleDocument.endding.totalFrames();
                    if (video.totalFrames > 0) {
                        video.totalFrames = video.totalFrames + 900 * video.frameRate;
                    }
                }
                this.genXML = function () {
                    function guid() {
                        function s4() {
                            return Math.floor((1 + Math.random()) * 0x10000)
                                .toString(16)
                                .substring(1);
                        }

                        return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                            s4() + '-' + s4() + s4() + s4();
                    }

                    xmlHead = xmlHead.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
                    xmlHead = xmlHead.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
                    xmlHead = xmlHead.replace(/\{video\.width\}/gi, video.width.toString());
                    xmlHead = xmlHead.replace(/\{video\.height\}/gi, video.height.toString());
                    xmlHead = xmlHead.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
                    xmlHead = xmlHead.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString);
                    xmlHead = xmlHead.replace(/\{uuid\}/gi, guid());
                    xmlFooter = xmlFooter.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
                    xmlFooter = xmlFooter.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
                    xmlFooter = xmlFooter.replace(/\{video\.width\}/gi, video.width.toString());
                    xmlFooter = xmlFooter.replace(/\{video\.height\}/gi, video.height.toString());
                    xmlFooter = xmlFooter.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
                    xmlFooter = xmlFooter.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString.toString());
                    this.xml = xmlHead;
                    this.xml += '{subtitleTracksReplacement}';
                    this.xml += xmlFooter;
                    return this.xml;

                }
            }

            function fcp7XmlObject() {
                this.input = "";
                this.inputDoc = {};
                this.inputItem = {};
                this.output = "";
                this.outputDoc = {};
                this.download = '';
                var outputTemplate = '<?xml version = "1.0" encoding = "UTF-8" standalone = "yes"?><!DOCTYPE xmeml><xmeml version="5"><sequence id="fiveLoadSub Import"><uuid>{uuid}</uuid><updatebehavior>add</updatebehavior><name>fiveLoadSub Import</name><duration>{video.totalFrames}</duration><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><timecode><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><string>{video.timeCodeString}</string><frame>0</frame><source>source</source><displayformat>NDF</displayformat></timecode><in>-1</in><out>-1</out><media><video><format><samplecharacteristics><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>Square</pixelaspectratio><fielddominance>upper</fielddominance><rate><ntsc>{video.isNtsc}</ntsc><timebase>{video.frameRate}</timebase></rate><colordepth>24</colordepth><codec><name>Apple ProRes 422</name><appspecificdata><appname>Final Cut Pro</appname><appmanufacturer>Apple Inc.</appmanufacturer><appversion>7.0</appversion><data><qtcodec><codecname>Apple ProRes 422</codecname><codectypename>Apple ProRes 422</codectypename><codectypecode>apcn</codectypecode><codecvendorcode>appl</codecvendorcode><spatialquality>1024</spatialquality><temporalquality>0</temporalquality><keyframerate>0</keyframerate><datarate>0</datarate></qtcodec></data></appspecificdata></codec></samplecharacteristics><appspecificdata><appname>Final Cut Pro</appname><appmanufacturer>Apple Inc.</appmanufacturer><appversion>7.0</appversion><data><fcpimageprocessing><useyuv>TRUE</useyuv><usesuperwhite>FALSE</usesuperwhite><rendermode>Float10BPP</rendermode></fcpimageprocessing></data></appspecificdata></format><track><enabled>TRUE</enabled><locked>FALSE</locked></track></video></media></sequence></xmeml>';
                var parser = new DOMParser();

                var video = {
                    width: 0,
                    height: 0,
                    frameRate: 0,
                    isNtsc: false,
                    timeCodeString: "00:00:00:00",
                    totalFrames: 0
                };

                this.loadInput = function () {
                    var templateInput = document.getElementById('templateInput');
                    var reader = new FileReader();
                    reader.onload = function () {
                        subtitleDocument.fcp7Xml.input = reader.result;

                        subtitleDocument.fcp7Xml.inputDoc = parser.parseFromString(subtitleDocument.fcp7Xml.input, 'text/xml');

                        if (subtitleDocument.fcp7Xml.inputDoc) {
                            video.width = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('width')[0].innerHTML);
                            video.height = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('height')[0].innerHTML);
                            video.frameRate = parseInt(subtitleDocument.fcp7Xml.inputDoc.getElementsByTagName('samplecharacteristics')[0].getElementsByTagName('timebase')[0].innerHTML);
                            document.getElementById('saveDialogXml_width').value = video.width;
                            document.getElementById('saveDialogXml_height').value = video.height;
                            document.getElementById('saveDialogXml_width').value = video.width;
                            document.getElementById('saveDialogXml_frameRate').value = video.frameRate;
                        }
                    }
                    reader.readAsText(templateInput.files[0]);
                };

                this.updateConfig = function () {
                    var width = parseInt(document.getElementById('saveDialogXml_width').value);
                    var height = parseInt(document.getElementById('saveDialogXml_height').value);
                    var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
                    if (width > 0) {
                        video.width = width;
                    }
                    if (height > 0) {
                        video.height = height;
                    }
                    if (frameRate > 0) {
                        video.frameRate = frameRate;
                    }
                    video.totalFrames = subtitleDocument.endding.totalFrames(video.frameRate);
                    if (video.totalFrames > 0) {
                        video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
                    }
                }

                function guid() {
                    function s4() {
                        return Math.floor((1 + Math.random()) * 0x10000)
                            .toString(16)
                            .substring(1);
                    }

                    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                        s4() + '-' + s4() + s4() + s4();
                }

                this.genXML = function (lines, endding) {
                    if ((video.width > 0) && (video.height > 0) && (video.frameRate > 0) && (endding.totalFrames() > 0)) {
                        var foundElement = false;
                        var template = {};

                        this.download = '';

                        if ((video.frameRate == 15) || (video.frameRate == 24) || (video.frameRate == 30) || (video.frameRate == 60)) {
                            video.timeCodeString = "00;00;00;00";
                            video.isNtsc = true;
                        } else {
                            video.timeCodeString = "00:00:00:00";
                            video.isNtsc = false;
                        }


                        try {
                            this.inputDoc = parser.parseFromString(this.input, 'text/xml');
                        } finally {
                        }
                        ;

                        var tmp = [];
                        try {
                            tmp = this.inputDoc.getElementsByTagName('generatoritem');
                        } finally {
                        }
                        ;

                        //->effect->effectcatrgory = "Spherico Text"

                        for (i = 0; i <= tmp.length - 1; i++) {
                            var tmp2 = tmp[i].getElementsByTagName('effect');
                            for (j = 0; j <= tmp2.length - 1; j++) {
                                if (tmp2[j].getElementsByTagName('effectcategory')[0].innerHTML == "Spherico Text") {
                                    foundElement = true;
                                    template = tmp[i];
                                }
                            }
                        }

                        if (foundElement) {

                            this.output = outputTemplate;
                            this.output = this.output.replace(/\{video\.frameRate\}/gi, video.frameRate.toString());
                            this.output = this.output.replace(/\{video\.isNtsc\}/gi, video.isNtsc.toString());
                            this.output = this.output.replace(/\{video\.width\}/gi, video.width.toString());
                            this.output = this.output.replace(/\{video\.height\}/gi, video.height.toString());
                            this.output = this.output.replace(/\{video\.totalFrames\}/gi, video.totalFrames.toString());
                            this.output = this.output.replace(/\{video\.timeCodeString\}/gi, video.timeCodeString);
                            this.output = this.output.replace(/\{uuid\}/gi, guid());
                            this.outputDoc = parser.parseFromString(this.output, 'text/xml');
                            for (i = 0; i < lines.length; i++) {
                                var line = lines[i];
                                if ((!line.begin.empty) && (!line.end.empty)) {
                                    var subtitleTag = template.cloneNode(true);

                                    subtitleTag.setAttribute('id', template.getAttribute('id') + '-' + i);
                                    subtitleTag.getElementsByTagName('duration')[0].innerHTML = (line.end.totalFrames(video.frameRate) + 101).toString();
                                    subtitleTag.getElementsByTagName('in')[0].innerHTML = line.begin.totalFrames(video.frameRate).toString();
                                    subtitleTag.getElementsByTagName('out')[0].innerHTML = line.end.totalFrames(video.frameRate).toString();
                                    subtitleTag.getElementsByTagName('start')[0].innerHTML = (line.begin.totalFrames(video.frameRate) + 100).toString();
                                    subtitleTag.getElementsByTagName('end')[0].innerHTML = (line.end.totalFrames(video.frameRate) + 100).toString();
                                    subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\<rate\>(.*)\<\/rate\>/gi, '<rate><ntsc>' + video.isNtsc.toString() + '</ntsc><timebase>' + video.frameRate.toString() + '</timebase></rate>');
                                    subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\{fiveLoadSub\.replacement\[1\]\}/g, line.text);
                                    subtitleTag.innerHTML = subtitleTag.innerHTML.replace(/\<uuid\>(.*)\<\/uuid\>/g, '<uuid>' + guid() + '</uuid>');
                                    this.outputDoc.getElementsByTagName('track')[0].appendChild(subtitleTag);
                                }
                            }


                            this.download = _makeTextFile('<?xml version = "1.0" encoding = "UTF-8" standalone = "yes"?><!DOCTYPE xmeml><xmeml version="5">' + this.outputDoc.documentElement.innerHTML + '</xmeml>');
                            //this.download = "data:text/xml;base64," + _arrayBufferToBase64(arrayBuffer);

                        }
                    }
                }

            }

            function fcpxXmlObject() {
                var video = {
                    width: 0,
                    height: 0,
                    frameRate: 0,
                    totalFrames: 0
                };
                this.get = function (str) {
                    return video[str];
                }
                this.updateConfig = function () {
                    var width = parseInt(document.getElementById('saveDialogXml_width').value);
                    var height = parseInt(document.getElementById('saveDialogXml_height').value);
                    var frameRate = parseInt(document.getElementById('saveDialogXml_frameRate').value);
                    if (width > 0) {
                        video.width = width;
                    }
                    if (height > 0) {
                        video.height = height;
                    }
                    if (frameRate > 0) {
                        video.frameRate = frameRate;
                    }
                    video.totalFrames = subtitleDocument.endding.totalFrames(video.frameRate);
                    if (video.totalFrames > 0) {
                        video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
                    }
                }

                function fcpxframe(f) {
                    return (f * 100).toString() + '/' + (video.frameRate * 100).toString() + 's';
                }

                var parser = new DOMParser();
                this.output = '';
                this.outputDoc = {};
                this.input = '';
                this.inputDoc = {};
                this.download = '';

                this.loadInput = function () {
                    video.totalFrames = Math.round(document.getElementById('videoPlayer').duration) || 0;
                    if (video.totalFrames > 0) {
                        video.totalFrames = video.totalFrames + 900 * video.frameRate; //15mins
                    }
                    var templateInput = document.getElementById('templateInput');
                    var reader = new FileReader();
                    reader.onload = function () {
                        input = reader.result;
                        if (input != '') {
                            subtitleDocument.fcpxXml.inputDoc = parser.parseFromString(input, 'text/xml');

                            try {
                                video.width = parseInt(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('width'));
                            } finally {
                                document.getElementById('saveDialogXml_width').value = video.width;
                            }
                            try {
                                video.height = parseInt(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('height'));
                            } finally {
                                document.getElementById('saveDialogXml_height').value = video.height;
                            }
                            try {
                                video.frameRate = 1 / eval(subtitleDocument.fcpxXml.inputDoc.getElementsByTagName('resources')[0].getElementsByTagName('format')[0].getAttribute('frameDuration').replace(/[a-z]+/gi, ''));
                                /*100/2500s  -> 100/2500 -> 0.04*/
                            } finally {
                                document.getElementById('saveDialogXml_frameRate').value = video.frameRate;

                            }

                        }
                    }
                    reader.readAsText(templateInput.files[0]);
                }


                this.genXML = function (lines, endding) {

                    function guid() {
                        function s4() {
                            return Math.floor((1 + Math.random()) * 0x10000)
                                .toString(16)
                                .substring(1);
                        }

                        return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                            s4() + '-' + s4() + s4() + s4();
                    }

                    this.output = '<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?><!DOCTYPE fcpxml><fcpxml version="1.4"><resources></resources><library location="file:///Users/User/Movies/fiveLoadSub.fcpbundle/"><event name="fiveLoadSubImport" uid="' + guid() + '"><project name="fiveLoadSubImportSequence" uid="' + guid() + '"></project></event></library></fcpxml>';
                    this.outputDoc = parser.parseFromString(this.output, 'text/xml');
                    if ((video.width > 0) && (video.height > 0) && (video.frameRate > 0) && (endding.totalFrames() > 0)) {
                        var template;
                        template = this.inputDoc.getElementsByTagName('title')[0];

                        this.outputDoc.getElementsByTagName('resources')[0].innerHTML = this.inputDoc.getElementsByTagName('resources')[0].innerHTML;
                        this.outputDoc.getElementsByTagName('project')[0].appendChild(this.inputDoc.getElementsByTagName('sequence')[0].cloneNode(false));
                        this.outputDoc.getElementsByTagName('sequence')[0].innerHTML = '<spine><gap name="Gap" offset="0s" duration="' + fcpxframe(this.get('totalFrames')) + '" start="36000s"><spine lane="2" offset="36000s"></spine></gap></spine>';

                        this.outputDoc.getElementsByTagName('sequence')[0].setAttribute('duration', fcpxframe(video.totalFrames));
                        var lastEnd = -1;
                        for (var i = 0; i < lines.length; i++) {
                            var tmp = template.cloneNode(true);
                            tmp.innerHTML = tmp.innerHTML.replace(/\{fiveLoadSub\.replacement\[1\]\}/g, lines[i].text);
                            var begin = lines[i].begin.totalFrames(this.get('frameRate'));
                            var end = lines[i].end.totalFrames(this.get('frameRate'));
                            ;
                            tmp.setAttribute('offset', fcpxframe(begin));
                            tmp.setAttribute('duration', fcpxframe(end - begin));
                            var j = 1;
                            var tmpArray = tmp.getElementsByTagName('text-style');

                            for (k = 0; k < tmpArray.length; k++) {
                                if (tmpArray[k].getAttribute('ref')) {
                                    tmpArray[k].setAttribute('ref', 'ts' + i + '-' + j);

                                    j++;
                                }
                            }
                            j = 1;
                            tmpArray = tmp.getElementsByTagName('text-style-def');
                            for (k = 0; k < tmpArray.length; k++) {
                                tmpArray[k].setAttribute('id', 'ts' + i + '-' + j);
                                j++;
                            }
                            if ((lastEnd > -1) && (lastEnd < begin)) {
                                var gap = begin - lastEnd;
                                this.outputDoc.getElementsByTagName('spine')[1].innerHTML = this.outputDoc.getElementsByTagName('spine')[1].innerHTML + '<gap offset="' + fcpxframe(lastEnd) + '" name="Gap" duration="' + fcpxframe(gap) + '" start="0s"/>';
                            } else if ((lastEnd == -1) && (begin > 0)) {
                                this.outputDoc.getElementsByTagName('spine')[1].innerHTML = this.outputDoc.getElementsByTagName('spine')[1].innerHTML + '<gap offset="0s" name="Gap" duration="' + fcpxframe(begin) + '" start="0s"/>';

                            }
                            lastEnd = end;
                            tmp.removeAttribute('lane');
                            this.outputDoc.getElementsByTagName('spine')[1].appendChild(tmp);
                        }

                        this.download = _makeTextFile('<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?><!DOCTYPE fcpxml><fcpxml version="1.4">' + this.outputDoc.documentElement.innerHTML + '</fcpxml>');
                    }
                }
            }

            this.prXml = new prXmlObject();
            this.fcpxXml = new fcpxXmlObject();
            this.fcp7Xml = new fcp7XmlObject();

            this.lines = []; //
            this.linesSortedByBegin = [];
            this.linesSortedByEnd = [];
            this.endding = {};
            this.cachedStr = "";
            this.cachedQueue = [];
            this.parseFromString = function (str, format) {
                format = format || "fls";
                //Clear all of this...
                if (str != this.cachedStr) {
                    this.lines = [];
                    this.linesSortedByBegin = [];
                    this.linesSortedByEnd = [];
                    this.cachedQueue = [];
                    this.cachedStr = str;

                    switch (format) {
                        case "fls":
                            var array = str.split(/\n/);
                            for (var i = 0; i < array.length; i++) {
                                this.lines.push(new subtitleLineObject());
                                this.lines[this.lines.length - 1].parseFromString(array[i]);
                            }
                            break;
                        case "srt":
                            var srtText = str;
                            var srtSearch = -1;
                            var srtLines = [];
                            var srtRegex = new RegExp(/[\d]+\n([0-9][0-9]\:[0-9][0-9]\:[0-9][0-9]\,[0-9]{3}\s\-\-\>\s[0-9][0-9]\:[0-9][0-9]\:[0-9][0-9]\,[0-9]{3})\n(.*)\n/);
                            var result = [];
                            var output = "";
                            srtSearch = srtText.search(srtRegex);

                            if (srtSearch > -1) {
                                srtLines = srtText.split(/\n/);
                                srtText = srtText.substring(srtSearch);
                            }
                            //console.log(srtLines);
                            _srtLines_length = srtLines.length
                            while ((_srtLines_length >= 3) && (srtSearch > -1)) {
                                var tmp = {
                                    start: {
                                        h: 0,
                                        m: 0,
                                        s: 0,
                                        f: 0
                                    },
                                    end: {
                                        h: 0,
                                        m: 0,
                                        s: 0,
                                        f: 0
                                    },
                                    text: ""
                                };

                                tmp.start.h = parseInt(srtLines[1].substr(0, 2));
                                tmp.start.m = parseInt(srtLines[1].substr(3, 2));
                                tmp.start.s = parseInt(srtLines[1].substr(6, 2));
                                tmp.start.f = parseInt(srtLines[1].substr(9, 3));

                                tmp.end.h = parseInt(srtLines[1].substr(17, 2));
                                tmp.end.m = parseInt(srtLines[1].substr(20, 2));
                                tmp.end.s = parseInt(srtLines[1].substr(23, 2));
                                tmp.end.f = parseInt(srtLines[1].substr(26, 3));
                                tmp.text = srtLines[2];

                                result.push(tmp);
                                delete tmp;

                                srtText = srtText.replace(srtRegex, "");
                                //srtText = srtText.replace(/\n\n/, "\n");
                                srtSearch = srtText.search(srtRegex);
                                srtText = srtText.substring(srtSearch);
                                srtLines = srtText.split(/\n/);
                                _srtLines_length = srtLines.length


                            }

                            for (line of result) {
                                output += '[' + _zeroPad(line.start.h, 2) + ':' + _zeroPad(line.start.m, 2) + ':' + _zeroPad(line.start.s, 2) + '.' + _zeroPad(Math.round(line.start.f / 1000 * 30), 2) + ']';
                                output += line.text;
                                output += '[' + _zeroPad(line.end.h, 2) + ':' + _zeroPad(line.end.m, 2) + ':' + _zeroPad(line.end.s, 2) + '.' + _zeroPad(Math.round(line.end.f / 1000 * 30), 2) + ']';
                                output += '\n';
                            }
                            this.parseFromString(output);
                            delete output;
                            break;
                        case 'sbv':

                        function replacer(match, p1, p2, p3, p4, p5, p6, p7, p8, p9, offset, string) {
                            return '[' + _zeroPad(parseInt(p1), 2) + ':' + _zeroPad(parseInt(p2), 2) + ':' + _zeroPad(parseInt(p3), 2) + '.' + _zeroPad(Math.round(parseInt(p4) / 1000 * 30), 2) + ']' + p9 + '[' + _zeroPad(parseInt(p5), 2) + ':' + _zeroPad(parseInt(p6), 2) + ':' + _zeroPad(parseInt(p7), 2) + '.' + _zeroPad(Math.round(parseInt(p8) / 1000 * 30), 2) + ']';

                        }

                            var srtRegex = /([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.([0-9]{3})\,([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.([0-9]{3})\n([^\n]+)\n/gi;
                            var output = str;
                            output = output.replace('\n\r', '\n');
                            output = output.replace(srtRegex, replacer);
                            output = output.replace('\n\n\n', '\n\n');
                            output = output.replace('\n\n', '\n');
                            this.parseFromString(output);
                            break;
                        default:
                            var array = str.split(/\n/);
                            for (var i = 0; i < array.length; i++) {
                                this.lines.push(new subtitleLineObject());
                                this.lines[this.lines.length - 1].parseFromString(array[i]);
                            }
                    }

                    this.linesSortedByBegin = this.sort();
                    this.linesSortedByEnd = this.sort("end");
                    var enddingSort = this.sort('end', true);
                    var enddingFound = false;
                    var i = 0;

                    if (enddingSort.length > 0) {
                        while (!enddingFound) {
                            if (!enddingSort[i].end.empty) {
                                enddingFound = true;
                                this.endding = enddingSort[i].end;
                            }
                            if ((!enddingFound) && (i == enddingSort.length - 1)) {
                                enddingFound = true;

                                this.endding = enddingSort[i].end;
                            }
                            i++;

                        }
                    }
                }
            }
            this.strVal = function (format) {
                format = format || "fls";
                var result = "";
                switch (format) {
                    case "fls":
                        for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                            result += this.linesSortedByBegin[i].strVal() + "\n";
                        }
                        break;
                    case "srt":
                        var j = 1;
                        for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                            if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                                var begin = _zeroPad(this.linesSortedByBegin[i].begin.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.s.toString(), 2) + "," + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].begin.f) / 30 * 1000)).toString(), 3);
                                var end = _zeroPad(this.linesSortedByBegin[i].end.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.s.toString(), 2) + "," + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].end.f) / 30 * 1000)).toString(), 3);
                                result += j.toString() + '\n' + begin + ' --> ' + end + '\n' + this.linesSortedByBegin[i].text + '\n' + '\n';
                                j++;
                            }
                        }
                        break;
                    case "sbv":
                        for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                            if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                                var begin = _zeroPad(this.linesSortedByBegin[i].begin.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].begin.s.toString(), 2) + "." + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].begin.f) / 30 * 1000)).toString(), 3);
                                var end = _zeroPad(this.linesSortedByBegin[i].end.h.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.m.toString(), 2) + ":" + _zeroPad(this.linesSortedByBegin[i].end.s.toString(), 2) + "." + _zeroPad((Math.round(parseInt(this.linesSortedByBegin[i].end.f) / 30 * 1000)).toString(), 3);
                                result += begin + ',' + end + '\n' + this.linesSortedByBegin[i].text + '\n' + '\n';
                            }
                        }
                        break;
                    case "prxml":

                        if ((this.prXml.get('frameRate') > 0) && (this.prXml.get('width') > 0) && (this.prXml.get('height') > 0) && (this.prXml.get('totalFrames') > 0) && (this.prXml.prtl != '')) {
                            var clipTmp = '<clipitem id="{sub[n].clipID}" frameBlend="FALSE"><name>{sub[n].fileName}</name><enabled>TRUE</enabled><duration>108000000</duration><start>{sub[n].in}</start><end>{sub[n].out}</end><in>89999</in><out>{sub[n].totalFrames}</out><alphatype>none</alphatype><pixelaspectratio>square</pixelaspectratio><anamorphic>FALSE</anamorphic><file id="{sub[n].fileID}"><name>{sub[n].fileName}</name><pathurl>{sub[n].fullPath}</pathurl><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><timecode><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><string>{video.timeCodeString}</string><frame>0</frame><displayformat>DF</displayformat><reel><name></name></reel></timecode><media><video><duration>18000</duration><samplecharacteristics><rate><timebase>{video.frameRate}</timebase><ntsc>{video.isNtsc}</ntsc></rate><width>{video.width}</width><height>{video.height}</height><anamorphic>FALSE</anamorphic><pixelaspectratio>square</pixelaspectratio><fielddominance>none</fielddominance></samplecharacteristics></video></media></file></clipitem>';
                            var trackTmp = '<track TL.SQTrackShy="0" TL.SQTrackExpandedHeight="25" TL.SQTrackExpanded="0" MZ.TrackTargeted="0"><enabled>TRUE</enabled><locked>FALSE</locked>{subtitleClipsReplacement}</track>';
                            var trackNo = 1;
                            var tracks = [];
                            var xml = '';
                            var prtlFiles = {};
                            var trackReplacement = '';
                            var next = {};
                            this.files = {};

                            for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                                if ((!this.linesSortedByBegin[i].begin.empty) && (!this.linesSortedByBegin[i].end.empty)) {
                                    var sub = {
                                        in: 0,
                                        out: 0,
                                        totalFrames: 0,
                                        fullPath: '/sub' + i.toString() + '.prtl',
                                        fileName: 'sub' + i.toString() + '.prtl',
                                        fileID: 'file-' + i.toString(),
                                        clipID: 'clip-' + i.toString()
                                    };
                                    clipXml = clipTmp;
                                    sub.in = this.linesSortedByBegin[i].begin.totalFrames(this.prXml.get('frameRate'));
                                    sub.out = this.linesSortedByBegin[i].end.totalFrames(this.prXml.get('frameRate'));
                                    sub.totalFrames = sub.out - sub.in;

                                    clipXml = clipXml.replace(/\{video\.frameRate\}/gi, this.prXml.get("frameRate").toString());
                                    clipXml = clipXml.replace(/\{video\.isNtsc\}/gi, this.prXml.get("isNtsc").toString());
                                    clipXml = clipXml.replace(/\{video\.width\}/gi, this.prXml.get("width").toString());
                                    clipXml = clipXml.replace(/\{video\.height\}/gi, this.prXml.get("height").toString());
                                    clipXml = clipXml.replace(/\{video\.totalFrames\}/gi, this.prXml.get("totalFrames").toString());
                                    clipXml = clipXml.replace(/\{video\.timeCodeString\}/gi, this.prXml.get("totalFrames").toString());


                                    clipXml = clipXml.replace(/\{sub\[n\]\.fileName\}/gi, sub.fileName);
                                    clipXml = clipXml.replace(/\{sub\[n\]\.fileID\}/gi, sub.fileID);
                                    clipXml = clipXml.replace(/\{sub\[n\]\.fullPath\}/gi, sub.fullPath);
                                    clipXml = clipXml.replace(/\{sub\[n\]\.in\}/gi, sub.in.toString());
                                    clipXml = clipXml.replace(/\{sub\[n\]\.out\}/gi, sub.out.toString());
                                    clipXml = clipXml.replace(/\{sub\[n\]\.clipID\}/gi, sub.clipID);
                                    clipXml = clipXml.replace(/\{sub\[n\]\.totalFrames\}/gi, (sub.totalFrames + 89999).toString());

                                    if (this.linesSortedByBegin[i + 1] != undefined) {
                                        var overlap = false;
                                        if ((!this.linesSortedByBegin[i + 1].begin.empty) && (!this.linesSortedByBegin[i + 1].end.empty)) {
                                            next.in = this.linesSortedByBegin[i + 1].begin.totalFrames(this.prXml.get('frameRate'));
                                            next.out = this.linesSortedByBegin[i + 1].begin.totalFrames(this.prXml.get('frameRate'));
                                            if (((next.in >= sub.in) && (next.in <= sub.out)) || ((next.in <= sub.in) && (next.out >= sub.in))) {
                                                overlap = true;

                                            }
                                        }
                                        if (overlap) {
                                            trackNo++;
                                        }
                                    }
                                    var parser = new DOMParser();
                                    var currentPrtl = parser.parseFromString(this.prXml.prtl, 'text/xml');
                                    var tmp = currentPrtl.getElementsByTagName('TRString');
                                    for (k = 0; k < tmp.length; k++) {
                                        var child = tmp[k];
                                        if (child.innerHTML.search('{fiveLoadSub.replacement\\\[' + trackNo.toString() + '\\\]}') > -1) { //Use search to avoid space problem
                                            child.innerHTML = this.linesSortedByBegin[i].text;
                                            child.parentNode.getElementsByTagName('RunLengthEncodedCharacterAttributes')[0].getElementsByTagName('CharacterAttributes')[0].setAttribute("RunCount", (this.linesSortedByBegin[i].text.length + 1).toString());
                                            prtlFiles[sub.fileName] = '<?xml version = "1.0" encoding = "UTF-16" ?><Adobe_Root>' + currentPrtl.documentElement.innerHTML + '</Adobe_Root>';
                                            tracks[trackNo] = tracks[trackNo] || "";
                                            tracks[trackNo] += clipXml;
                                        }
                                    }

                                    /*if (tmp.search('{fiveLoadSub.replacement\\\[' + trackNo.toString() + '\\\]}') > -1) {
                                        tmp = tmp.replace('{fiveLoadSub.replacement[' + trackNo.toString() + ']}', this.linesSortedByBegin[i].text);
                                        tmp = tmp.replace('{fiveLoadSub.replacementCount[' + trackNo.toString() + ']}', (this.linesSortedByBegin[i].text.length+1).toString());
                                        prtlFiles[sub.fileName] = tmp;
                                        tracks[trackNo] = tracks[trackNo] || "";
                                        tracks[trackNo] += clipXml;

                                    }*/
                                    result = 'Click Save as...';

                                }
                                trackNo = 1;
                            }

                            for (track of tracks) {
                                var tmp = trackTmp;
                                tmp = tmp.replace('{subtitleClipsReplacement}', track);
                                trackReplacement += tmp;
                            }

                            xml = this.prXml.genXML();
                            xml = xml.replace('{subtitleTracksReplacement}', trackReplacement);
                            var zip = new JSZip();
                            zip.file("import.xml", xml);
                            var sub = zip.folder("sub");


                            textEncoder = new TextEncoder('UTF-16');

                            for (var fileName in prtlFiles) {
                                if (prtlFiles.hasOwnProperty(fileName)) {
                                    var arrayBuffer = textEncoder.encode(prtlFiles[fileName]);
                                    sub.file(fileName, _appendBuffer(new Uint8Array([255, 254]), arrayBuffer), {
                                        base64: false,
                                        binary: true
                                    })
                                }
                            }
                            if (__hack.isSafari) {
                                this.prXml.download = window.URL.createObjectURL(new Blob([zip.generate({
                                    type: 'arraybuffer'
                                })], {
                                    type: "text/plain"
                                }));
                            } else {
                                this.prXml.download = window.URL.createObjectURL(new Blob([zip.generate({
                                    type: 'arraybuffer'
                                })], {
                                    type: "application/zip"
                                }));
                            }
                        }

                        break;
                    case 'fcpxxml':
                        this.fcpxXml.updateConfig();
                        this.fcpxXml.genXML(this.linesSortedByBegin, this.endding);
                        if (this.fcpxXml.download != '') {
                            result = 'Click Save as..';
                        }
                        break;
                    case 'fcp7xml':
                        this.fcp7Xml.updateConfig();
                        this.fcp7Xml.genXML(this.linesSortedByBegin, this.endding);
                        if (this.fcp7Xml.download != '') {
                            result = 'Click Save as..';
                        }
                        break;
                    default:
                        for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                            result += this.linesSortedByBegin[i].strVal() + "\n";
                        }
                }
                return result;
            }
            this.getLineByLineNum = function (num) {
                num = num || 0;
                var result;
                if (num > this.lines.length) {
                    result = false;
                } else {
                    result = this.lines[num - 1];
                }
                return result;
            }
            this.sort = function (by, isReverse) {
                by = by || "begin";
                isReverse = isReverse || false;
                arrayToSort = [];
                //pick out timecodes(in total number of frames)
                switch (by) {
                    case "begin":
                        for (var i = 0; i < this.lines.length; i++) {
                            var tmp = [];
                            tmp['index'] = i;
                            tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                            if (this.lines[i].empty) tmp['totalFrames'] = -1;
                            arrayToSort.push(tmp);

                        }
                        break;
                    case "end":
                        for (var i = 0; i < this.lines.length; i++) {
                            var tmp = [];
                            tmp['index'] = i;
                            tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                            if (this.lines[i].empty) {
                                tmp['totalFrames'] = -1;
                            }
                            arrayToSort.push(tmp);
                        }
                        break;
                    default:

                        for (var i = 0; i < this.lines.length; i++) {
                            var tmp = [];
                            tmp['index'] = i;
                            tmp['totalFrames'] = this.lines[i].begin.totalFrames();
                            if (this.lines[i].empty) {
                                tmp['totalFrames'] = -1;
                            }
                            arrayToSort.push(tmp);
                        }
                }
                //sorting now...
                var i = 0;
                var offset = 1;
                while (i + offset < arrayToSort.length) {
                    var a = arrayToSort[i];
                    var b = arrayToSort[i + offset];
                    if (a.totalFrames == -1) {
                        i++;
                    } else if (b.totalFrames == -1) {
                        offset++;
                    } else {
                        if ((a.totalFrames > b.totalFrames) && (b.totalFrames != -1) && (a.totalFrames != -1)) {
                            c = a;
                            a = b;
                            b = c;
                            arrayToSort[i] = a;
                            arrayToSort[i + offset] = b;
                        }
                        i++; //remember you SHOULD increment even no need to swap
                        offset = 1;
                    }
                }
                //Okay, push sorted result into array
                var result = [];
                for (var i = 0; i < this.lines.length; i++) {
                    result.push(this.lines[arrayToSort[i].index]);
                }
                if (isReverse) {
                    var tmpResult = [];
                    for (j = result.length - 1; j >= 0; j--) {
                        tmpResult.push(result[j]);
                    }
                    result = tmpResult;
                }
                return result;
            }
            this.sortQueue = function () {
                //Generate a sequeue for displaying subtitle overlay on video
                //It is something like indexing. Search from this array will result a faster speed coz you don't need to search the this.lines[] and dig into every timecode object inside
                //Use cache to boost performance. Only update if cache not available.
                var result = [];
                result = this.cachedQueue;
                if (result.length == 0) {
                    var arraySortedByBegin = [];
                    var arraySortedByEnd = [];
                    for (var i = 0; i < this.linesSortedByBegin.length; i++) {
                        var tmp = [];
                        tmp['index'] = i;
                        tmp['frames'] = this.linesSortedByBegin[i].begin.totalFrames();
                        if (this.lines[i].begin.empty) tmp['frames'] = -1;
                        arraySortedByBegin.push(tmp);
                    }
                    for (var i = 0; i < this.linesSortedByEnd.length; i++) {
                        var tmp = [];
                        tmp['index'] = i;
                        tmp['frames'] = this.linesSortedByBegin[i].end.totalFrames();
                        if (this.lines[i].end.empty) tmp['frames'] = -2;
                        arraySortedByEnd.push(tmp);
                    }
                    result['begin'] = arraySortedByBegin;
                    result['end'] = arraySortedByEnd;
                }
                return result;
            }
        }

        var current = new currentStatusObject;
        var wavesurfer = Object.create(WaveSurfer);
        var subtitleDocument = new subtitleDocObject();
        var buffer = new bufferObject();
    </script>
    <script>
        function onLoad() {
            if (typeof replaceFile == 'function') {
                replaceFile();
            }
            replaceLocalizedText();
            setupKeystroke();
            setupWaveform();
            convertDialogInit();
            UI$init();
            richVideoControl$applyControl(document.getElementById('videoPlayer'));
            // Setup the dnd listeners.
            var dropZone = document.getElementById('videoFrame');
            dropZone.addEventListener('dragover', handleDragOver, false);
            dropZone.addEventListener('drop', handleFileSelect, false);

            document.getElementById('videoPlayer').onplay = function (e) {
                current.videoIsPlaying = true;
            };
            document.getElementById('videoPlayer').onpause = function (e) {
                current.videoIsPlaying = false;
            };
            document.getElementById('videoPlayer').onended = function (e) {
                current.videoIsPlaying = false;
            };

            window.addEventListener("beforeunload", function (e) {
                var confirmationMessage = localizedText('app.exitConfirmMsg');

                if (!current.savedChanges) {
                    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
                    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
                }
            });
        }

        function UI$init() {
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                windowWidth = w.innerWidth || e.clientWidth || g.clientWidth,
                windowHeight = w.innerHeight || e.clientHeight || g.clientHeight;
            var videoFrame = document.getElementById('videoFrame');
            var subtitleFrame = document.getElementById('subtitleFrame');
            var waveformFrame = document.getElementById('waveformFrame');
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            var subtitleFormContainer = document.getElementById('subtitleFormContainer');
            var ribbon = document.getElementById('headerFrame');
            var horizontalDragLine = document.getElementById('horizontalDragLine');
            var horizontalDragDiv = document.getElementById('horizontalDragDiv');
            var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

            videoFrame.style.height = Math.round(videoFrame.offsetWidth / 16 * 9) + "px";
            subtitleFrame.style.width = (windowWidth - videoFrame.offsetWidth - 5) + "px";
            subtitleFrame.style.height = videoFrame.style.height;
            waveformFrame.style.height = (windowHeight - videoFrame.offsetHeight - 1 - ribbon.clientHeight - 2) + "px";
            if (isMacLike) {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 56) + "px";
            } else {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 70) + "px";
            }
            subtitleTextArea.style.minHeight = (subtitleFrame.offsetHeight - 45) + "px";
            subtitleFormContainer.style.height = (subtitleFrame.offsetHeight - 30) + "px";
            horizontalDragLine.style.height = videoFrame.style.height;
            horizontalDragDiv.style.height = videoFrame.style.height;
            current.UI.videoWaveHeightRatio = videoFrame.clientHeight / waveformFrame.clientHeight;
            updateSubtitleFrame();
            textareaScrollTo(current.lineNo);

            if (wavesurfer.getDuration() > 0) {
                wavesurfer.params.height = (waveformFrame.offsetHeight - 30);
                wavesurfer.drawer.setHeight((waveformFrame.offsetHeight - 30));
                wavesurfer.drawBuffer();
            }
        }

        function UI$mouse$setCursor(cursorStyle) {
            var elem = document.documentElement;
            if (elem.style) elem.style.cursor = cursorStyle;

        }

        function updateUI() {
            var w = window,
                d = document,
                e = d.documentElement,
                g = d.getElementsByTagName('body')[0],
                windowWidth = w.innerWidth || e.clientWidth || g.clientWidth,
                windowHeight = w.innerHeight || e.clientHeight || g.clientHeight;
            var videoFrame = document.getElementById('videoFrame');
            var subtitleFrame = document.getElementById('subtitleFrame');
            var waveformFrame = document.getElementById('waveformFrame');
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            var subtitleFormContainer = document.getElementById('subtitleFormContainer');
            var ribbon = document.getElementById('headerFrame');
            var horizontalDragLine = document.getElementById('horizontalDragLine');
            var isMacLike = navigator.platform.match(/(Mac|iPhone|iPod|iPad)/i) ? true : false;

            videoFrame.style.width = Math.round(windowWidth * 0.5 * current.UI.horizontalDragRatio) + 'px';
            videoFrame.style.height = Math.round((windowHeight - ribbon.clientHeight) * (current.UI.videoWaveHeightRatio / (current.UI.videoWaveHeightRatio + 1))) + "px";

            subtitleFrame.style.width = (windowWidth - videoFrame.offsetWidth - 5) + "px";
            subtitleFrame.style.height = videoFrame.style.height;
            waveformFrame.style.height = (windowHeight - videoFrame.offsetHeight - 1 - ribbon.clientHeight - 2) + "px";
            if (isMacLike) {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 56) + "px";
            } else {
                subtitleTextArea.style.width = (subtitleFrame.offsetWidth - 70) + "px";
            }
            subtitleTextArea.style.minHeight = (subtitleFrame.offsetHeight - 45) + "px";
            subtitleFormContainer.style.height = (subtitleFrame.offsetHeight - 30) + "px";
            horizontalDragLine.style.height = videoFrame.style.height;
            horizontalDragDiv.style.height = videoFrame.style.height;

            updateSubtitleFrame();

            if ((wavesurfer.getDuration() > 0) && (!current.UI.dragging)) {
                wavesurfer.params.height = (waveformFrame.offsetHeight - 30);
                wavesurfer.drawer.setHeight((waveformFrame.offsetHeight - 30));
                wavesurfer.drawBuffer();
            }

        }

        function updateSubtitleFrame() {
            current.savedChanges = false;
            var subtitleTextArea = document.getElementById("subtitleTextArea");
            subtitleTextArea.style.height = "auto";
            document.getElementById("subtitleLineCount").style.height = subtitleTextArea.scrollHeight.toString() + "px";
            subtitleTextArea.style.height = subtitleTextArea.scrollHeight.toString() + "px";
            total = subtitleTextArea.value.split('\n').length;
            var resultHTML = "";
            for (var i = 0; i < total; i++) {
                if (i == current.lineNo) {
                    resultHTML = resultHTML + '<li class="subtitle_li selected_li">' + (i + 1).toString() + '</li>';
                } else {
                    resultHTML = resultHTML + '<li class="subtitle_li">' + (i + 1).toString() + '</li>';
                }
            }
            document.getElementById("subtitleLineCount_ui").innerHTML = resultHTML;
        }

        function changeFontSizeSlider() {
            var fontSizeSlider = document.getElementById("fontSizeSlider");
            document.getElementById("fontSizeSpan").innerHTML = Math.round(fontSizeSlider.value);
            var subtitleElements = document.getElementsByClassName('subtitleElements');
            for (i = 0; i < subtitleElements.length; i++) {
                subtitleElements[i].style.fontSize = Math.round(fontSizeSlider.value) + "px";
            }
            updateUI();
        }

        function openMedia() {
            current.videoIsPlaying = false;
            document.getElementById("videoPlayer").src = current.videoFile;
            drawWaveform();
        }

        function setupWaveform() {
            wavesurfer.init({
                container: document.getElementById('waveform'),
                waveColor: "#000",
                progressColor: '#A4EBDF', //131
                loaderColor: 'purple',
                cursorColor: 'yellow',
                height: 200
            });
            wavesurfer.setVolume(0);
            wavesurfer.on('ready', function () {
                var timeline = Object.create(WaveSurfer.Timeline);

                timeline.init({
                    wavesurfer: wavesurfer,
                    container: document.getElementById("waveformFrame")
                });
                wavesurfer.on('seek', function () {
                    current.disableVideoSeekEvent = true;
                    seekMedia("waveform");
                    current.disableVideoSeekEvent = false;
                });
                UI$init();
            });
        }

        function setupVideoPlayer() {
            //Setup Video Player
            document.getElementById("videoPlayer").addEventListener("play", function (e) {
                playMedia();
            });
            document.getElementById("videoPlayer").addEventListener("pause", function (e) {
                pauseMedia();
            });
            document.getElementById("videoPlayer").addEventListener("seeking", function (e) {
                current.disableWaveformSeekEvent = true;
                seekMedia("video");
                current.disableWaveformSeekEvent = false;
            });
            //document.getElementById("videoPlayer").onpause = ;
            //document.getElementById("videoPlayer").onplay;
        }

        function playMedia() {
            current.disableWaveformSeekEvent = true;
            wavesurfer.seekTo(document.getElementById("videoPlayer").currentTime / wavesurfer.backend.getDuration());
            current.disableWaveformSeekEvent = false;
            // wavesurfer.play();
            clearInterval(current.timerID);
            current.timerID = setInterval(function () {
                updateSubtitleOverlay();
            }, 30);
        }

        function pauseMedia() {
            wavesurfer.pause();
            clearInterval(current.timerID);
            var interval_id = window.setInterval("", 9999); // Get a reference to the last
            // interval +1
            for (var i = 1; i < interval_id; i++)
                window.clearInterval(i);
            //for clearing all intervals
        }

        function seekMedia(from) {
            switch (from) {
                case "video":
                    if (!current.disableVideoSeekEvent) {
                        wavesurfer.seekTo(document.getElementById("videoPlayer").currentTime / wavesurfer.backend.getDuration());
                    }
                    break;
                case "waveform":
                    if (!current.disableWaveformSeekEvent) {
                        document.getElementById("videoPlayer").currentTime = wavesurfer.getCurrentTime();
                    }
                    break;
            }
            updateSubtitleOverlay();
        }

        function updateSubtitleDocumentObject() {
            subtitleDocument.parseFromString(document.getElementById("subtitleTextArea").value);
        }

        function updateSubtitleOverlay() {
            var currentFrames = calcTimeCode().totalFrames();
            var arrayNeedToDisplay = [];
            var begin = subtitleDocument.sortQueue()['begin'];
            var end = subtitleDocument.sortQueue()['end'];
            var displayText = "&nbsp;";
            for (var i = 0; i < begin.length; i++) {
                if ((currentFrames >= begin[i].frames) && ((currentFrames <= end[i].frames) || (end[i].frames == -2)) && (begin[i].frames != -1) && (end[i].frames != -1)) {
                    arrayNeedToDisplay.push(i + 1);
                }
            }
            if (arrayNeedToDisplay.length > 0) {
                document.getElementById("subtitleOverlay").style.display = 'block';
                for (var i = 0; i < arrayNeedToDisplay.length; i++) {
                    if (i > 0) {
                        displayText += "<br/>";
                    }
                    displayText += subtitleDocument.getLineByLineNum(arrayNeedToDisplay[i]).text;
                }
                if (displayText != buffer.subtitleOverlayContent) {
                    buffer.subtitleOverlayContent = displayText;
                    document.getElementById("subtitleOverlay").innerHTML = displayText;
                }
            } else {
                document.getElementById("subtitleOverlay").style.display = 'none';
            }
        }

        function pushEditHistory(txt) {
            if (txt != current.editHistory[current.editHistory.length - 1]) {
                for (var i = current.editHistoryIndex + 1; i < current.editHistory.length; i++) {
                    current.editHistory.pop();
                }
                if (current.editHistory.length > 100) {
                    current.editHistory.shift();
                }

                current.editHistory.push(txt);
                current.editHistoryIndex++;
            }
            return true;
        }

        function undoEdit() {
            if (current.editHistoryIndex >= 0) {
                current.editHistoryIndex--;
            }
            document.querySelector("#subtitleTextArea").value = current.editHistory[current.editHistoryIndex];

        }

        function redoEdit() {
            if ((current.editHistoryIndex < 100) && (current.editHistoryIndex < current.editHistory.length - 1)) {
                current.editHistoryIndex++;
            }
            document.querySelector("#subtitleTextArea").value = current.editHistory[current.editHistoryIndex];
        }

        function updateEditHistory() {
            pushEditHistory(document.querySelector("#subtitleTextArea").value);
        }

        function UI$toggleRibbon() {
            var ribbon = document.getElementById('headerFrame');
            var imgs = document.getElementById('ribbonMenu').getElementsByTagName('img');
            var brs = ribbon.getElementsByTagName('br');
            var toggle = document.getElementById('ribbonToggleDiv');
            if ((ribbon.style.height == '90px') || (ribbon.style.height == '')) {
                ribbon.style.height = '48px';
                toggle.style.lineHeight = '48px';
                for (var i = 0; i < imgs.length; i++) {
                    imgs[i].style.height = '16px';
                    imgs[i].style.width = '16px';
                }
                for (var i = 0; i < brs.length; i++) {
                    brs[i].style.display = 'none';
                }
            } else if (ribbon.style.height == '48px') {
                ribbon.style.height = '90px';
                toggle.style.lineHeight = '120px';
                for (var i = 0; i < imgs.length; i++) {
                    imgs[i].style.height = '40px';
                    imgs[i].style.width = '40px';
                }
                for (var i = 0; i < brs.length; i++) {
                    brs[i].style.display = 'block';
                }
            }
            updateUI();
        }

        function UI$horizontalDrag(e) {
            if (e.clientX > 0) {
                var w = window,
                    d = document,
                    de = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    windowWidth = w.innerWidth || de.clientWidth || g.clientWidth,
                    windowHeight = w.innerHeight || de.clientHeight || g.clientHeight;
                current.UI.horizontalDragRatio = e.clientX * 2 / windowWidth;
                updateUI();
            }
            e.stopPropagation();
            e.preventDefault();

        }

        function UI$verticalDrag(e) {
            if (e.clientY > 0) {
                var w = window,
                    d = document,
                    de = d.documentElement,
                    g = d.getElementsByTagName('body')[0],
                    windowWidth = w.innerWidth || de.clientWidth || g.clientWidth,
                    windowHeight = w.innerHeight || de.clientHeight || g.clientHeight;
                current.UI.videoWaveHeightRatio = (e.clientY - document.getElementById('headerFrame').clientHeight) / (windowHeight - e.clientY);
                updateUI();
            }
            e.stopPropagation();
            e.preventDefault();

        }

        function UI$confirmSafari(str) {
            var isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
            if (isSafari) {
                return confirm(str);
            } else {
                return true;
            }

        }
    </script>
    <script>
        function handleFileSelect(evt) {
            evt.stopPropagation();
            evt.preventDefault();

            var files = evt.dataTransfer.files;
            loadFile("video", files);
        }

        function handleDragOver(evt) {
            evt.stopPropagation();
            evt.preventDefault();
            evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
        }


        function setupKeystroke() {

            pressed = new Array();

            window.onkeydown = function (e) {
                var key = e.keyCode;

                if (key == 192 && current.subtitleTextAreaLock && !pressed[key]) {
                    applyTimeCode();
                    updateSubtitleDocumentObject();
                }

                pressed[key] = true;
                if ((key == 17 || key == 18) && (pressed[17] == pressed[18] == true)) { //Ctrl+Alt
                    current.subtitleTextAreaLock = !current.subtitleTextAreaLock;
                    document.getElementById("subtitleTextArea").disabled = current.subtitleTextAreaLock;
                    if (current.subtitleTextAreaLock) {
                        var tmp = document.getElementById("subtitleFormContainer").cloneNode(true);
                        var tmpParent = document.getElementById("subtitleFormContainer").parentNode
                        var value = document.getElementById("subtitleTextArea").value;
                        var selectionStart = document.getElementById("subtitleTextArea").selectionStart;
                        tmpParent.removeChild(document.getElementById("subtitleFormContainer"));
                        tmpParent.appendChild(tmp);
                        document.getElementById("subtitleTextArea").value = value;
                        document.getElementById("subtitleTextArea").selectionStart = selectionStart;
                        document.getElementById("videoPlayer").focus();
                    }
                }
                if ((key == 18 || key == 90) && (pressed[18] == pressed[90] == true)) { //Alt+Z
                    if (document.getElementById('videoPlayer').paused) {
                        document.getElementById('videoPlayer').play();
                    } else {
                        document.getElementById('videoPlayer').pause();
                    }
                }
                if ((key == 91 || key == 90) && (pressed[91] == pressed[90] == true)) { //Cmd+Z
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 17 || key == 90) && (pressed[17] == pressed[90] == true)) { //Ctrl+Z
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 17 || key == 89) && (pressed[17] == pressed[89] == true)) { //Ctrl+Y
                    undoEdit();
                    e.preventDefault();
                }
                if ((key == 91 || key == 16 || key == 90) && (pressed[91] == pressed[16] == pressed[90] == true)) { //Cmd+Shift+Z
                    redoEdit();
                    e.preventDefault();
                }
            }
            window.onkeyup = function (e) {
                var key = e.keyCode;
                pressed[key] = false;
                if (current.subtitleTextAreaLock) {
                    if (key == 32) {
                        applyTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 13) {
                        applyDoubleTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 192) {
                        applyTimeCode();
                        updateSubtitleDocumentObject();
                    }
                    if (key == 187) {
                        zoomWaveform(10);
                    }
                    if (key == 189) {
                        zoomWaveform(-10);
                    }
                }
            }
            /*
            var listener = new window.keypress.Listener();

            listener.simple_combo("ctrl alt",function () {

            });

            listener.simple_combo("=",function () {
                if (current.subtitleTextAreaLock) {
                    zoomWaveform(10);
                }
            });

            listener.simple_combo("-",function () {
                if (current.subtitleTextAreaLock) {
                    zoomWaveform(-10);
                }
            });

            listener.register_combo({
                "keys":             "cmd z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "cmd shift z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "ctrl z",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });

            listener.register_combo({
                "keys":             "ctrl y",
                "on_keyup":         function () {console.log(2);},
                "prevent_default":  true,
                "prevent_repeat":   true,
                "is_sequence":      true
            });


            /*

                    /*
			8		BKSP
			9		Tab
			13		Enter
			16		Shift
			17		left-Ctrl
			18		option/alt
			32		Space
			37		Left
			38		Up
			39		Right
			40		Down
			91		Cmd/Win(Left)
			192		`
			187		=
			189		-
                    if (key == 187) {
                        zoomWaveform(10);
                    }
                    if (key == 189) {
                        zoomWaveform(-10);
                    }
                }
            }
            */
        }

        function calcTimeCode() {
            var input = document.getElementById("videoPlayer").currentTime;
            var frameBase = (1 / 30);
            var totalFrames = Math.round(input / frameBase);
            var timeCode = new timeCodeObject;
            timeCode.h = Math.floor(totalFrames / 108000);
            timeCode.m = Math.floor(totalFrames % 108000 / 1800);
            timeCode.s = Math.floor(totalFrames % 1800 / 30);
            timeCode.f = Math.floor(totalFrames % 30);
            timeCode.empty = false;
            var result = timeCode;

            return result;
        }

        function readTimeCode(inputLine) {
            this.endTime = new timeCodeObject;
            this.startTime = new timeCodeObject;
            this.content = "";
            this.gotStart = false;
            this.gotEnd = false;

            if (inputLine.length >= 13) {
                this.gotStart = this.startTime.parseFromString(inputLine);
                if (inputLine.length >= 26) {
                    this.content = inputLine.substring(13, inputLine.length - 13);
                    inputLine = inputLine.substr(inputLine.length - 13, 13);
                    this.gotEnd = this.endTime.parseFromString(inputLine);
                }
            }

            var result = "";
            if (this.gotStart && this.gotEnd) {
                result = "both";
            } else if (this.gotStart && !this.gotEnd) {
                result = "start";
            } else {
                result = "failed";
            }
            return result;
        }

        function applyTimeCode() {

            if (current.videoIsPlaying && current.lineNo > -1) {
                var subtitleTextAreaLines = document.getElementById("subtitleTextArea").value.split('\n');
                var resultTxt = "";
                updateSubtitleFrame();
                if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "failed") {
                    subtitleTextAreaLines[current.lineNo] = calcTimeCode().strVal() + subtitleTextAreaLines[current.lineNo];
                } else if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "start") {
                    subtitleTextAreaLines[current.lineNo] = subtitleTextAreaLines[current.lineNo] + calcTimeCode().strVal();
                    current.lineNo++;
                }
                for (var i = 0; i < subtitleTextAreaLines.length; i++) {
                    if (i < subtitleTextAreaLines.length - 1) {
                        resultTxt += subtitleTextAreaLines[i] + "\n";
                    } else {
                        resultTxt += subtitleTextAreaLines[i];
                    }
                }
                textareaScrollTo(current.lineNo);
                document.getElementById("subtitleTextArea").value = resultTxt;
                pushEditHistory(resultTxt);

            }
        }

        function applyDoubleTimeCode() {
            var subtitleTextAreaLines = document.getElementById("subtitleTextArea").value.split('\n');
            if (readTimeCode(subtitleTextAreaLines[current.lineNo]) == "failed") {
                applyTimeCode();
            } else {
                applyTimeCode();
                applyTimeCode();
            }
        }

        function updateCurrentLineNo() {
            current.lineNo = document.getElementById("subtitleTextArea").value.substr(0, document.getElementById("subtitleTextArea").selectionStart).split("\n").length - 1;
        }

        function selectFile(type) {
            return;
            document.getElementById("fileLoader").click();
            document.getElementById("fileLoader").onchange = function (e) {
                files = e.target.files;
                loadFile(type, files);
            }

        }

        function loadFile(type, files) {
            switch (type) {
                case "video":

                    var URL = window.URL || window.webkitURL;
                    if ((files[0].type.substr(0, 5) == 'video') || (files[0].type.substr(0, 5) == 'audio')) {

                        current.videoFile = URL.createObjectURL(files[0]);
                        openMedia();
                        document.getElementById('dropLabel').style.display = 'none';
                    }
                    break;
                case "text":
                    var reader = new FileReader();
                    reader.onloadend = function (evt) {
                        document.getElementById("subtitleTextArea").value = evt.target.result;
                        updateCurrentLineNo();
                        updateSubtitleFrame();
                        updateSubtitleDocumentObject();
                        updateEditHistory();
                    }
                    reader.readAsText(files[0]);
                    break;
                default:
            }

        }

        function drawWaveform() {
            wavesurfer.load(current.videoFile);
        }

        function zoomWaveform(offset) {
            if (wavesurfer.getDuration() > 0) {
                var zoomTo = wavesurfer.params.minPxPerSec + offset;
                if (zoomTo > 200) {
                    zoomTo = 200;
                }
                if (zoomTo < 1) {
                    zoomTo = 1;
                }
                wavesurfer.zoom(zoomTo);
                return true;
            } else {
                return false;
            }
        }

        //Other
        function _zeroPad(num, n) {
            return (Array(n).join(0) + num).slice(-n);
        }
    </script>

</head>

<body onLoad="javascript:onLoad();" onResize="javascript:updateUI();">
<input type="file" hidden="true" id="fileLoader"/>
<div id="mainContainer">
    <div id="headerFrame">
        <div style="font-size:16px;text-align:right;">Video Subtitle v1.0.1 by Hans&nbsp;&nbsp;&nbsp;</div>

        <div id="ribbonMenu" style="text-align:left">
            <div class="imgButton" onClick="selectFile('video');"><img src="<?php echo subtitle_url('img/importVideo.png') ?>" width="40"
                                                                       height="40" alt=""/>
                <br/>{fiveLoadSub_menu.importVideo}
            </div>
            <div class="imgButton" onClick="selectFile('text');"><img src="<?php echo subtitle_url('img/importText.png') ?>" width="40" height="40"
                                                                      alt=""/>
                <br/>{fiveLoadSub_menu.importSubtitle}
            </div>
            <div class="imgButton" onClick="showConvertDialog();"><img name="imageField" type="image"
                                                                       src="<?php echo subtitle_url('img/conversion.png') ?>" width="40" height="40"
                                                                       alt=""/>
                <br/>{fiveLoadSub_menu.conversion}
            </div>
            <div class="imgButton" onClick="showSaveDialog();"><img name="imageField" type="image"
                                                                    src="<?php echo subtitle_url('img/output.png') ?>" width="40" height="40"
                                                                    alt=""/>
                <br/>{fiveLoadSub_menu.saveSubtitle}
            </div>

        </div>
        <div id="ribbonToggleDiv" class="imgButton" onClick="UI$toggleRibbon();">
            <img src="<?php echo subtitle_url('img/ribbonToggle.png') ?>" width="30px" height="30px"/>
        </div>

    </div>
    <div id="videoFrame">
        <video id="videoPlayer" src="<?php echo $video_link ?>" width="100%" height="100%" onCanPlay="setupVideoPlayer();" controls></video>
<!--        <div id="dropLabel"-->
<!--             style="width:100%; font-size:24px; height:inherit; float:none; text-align:center; left:0px; top:50%; position:absolute; color:white;">-->
<!--            {fiveLoadSub_videoPlayer.dropFileHere}-->
<!--        </div>-->
        <div id="subtitleOverlay"></div>
    </div>
    <div id="horizontalDragDiv" onmouseover="UI$mouse$setCursor('col-resize')" onmouseout="UI$mouse$setCursor('auto')">
        <hr id="horizontalDragLine" ondrag="UI$horizontalDrag(event)" draggable="true"
            ondragstart="current.UI.dragging = true;" ondragend="current.UI.dragging = false;updateUI();"/>
    </div>
    <div id="subtitleFrame">
        <div id="subtitleToolbar">
            <input id="fontSizeSlider" type="range" min="12" max="120" value="12"
                   onChange="javascript:changeFontSizeSlider();" onMouseMove="javascript:changeFontSizeSlider();"
                   onClick=""/> {fiveLoadSub_textEditor.fontSize}: <span id="fontSizeSpan">12</span>px
        </div>
        <div id="subtitleFormContainer">
            <div id="subtitleLineCount" class="subtitleElements" onLoad="updateSubtitleFrame()">
                <ul class="subtitleElements" id="subtitleLineCount_ui"></ul>
                &nbsp;
            </div>
            <textarea placeholder="{fiveLoadSub_textEditor.placeHolder}" wrap="off" class="subtitleElements"
                      id="subtitleTextArea"
                      onChange="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();updateEditHistory();"
                      onkeyUp="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();"
                      onMouseUp="updateCurrentLineNo();updateSubtitleFrame();updateSubtitleDocumentObject();updateEditHistory();"
                      onSelect="updateCurrentLineNo();updateSubtitleFrame();"><?php echo $text ?></textarea>
        </div>
    </div>
    <div id="verticalDragDiv" onmouseover="UI$mouse$setCursor('row-resize')" onmouseout="UI$mouse$setCursor('auto')">
        <hr id="verticalDragLine" ondrag="UI$verticalDrag(event)" draggable="true"
            ondragstart="current.UI.dragging = true;" ondragend="current.UI.dragging = false;updateUI();"/>
    </div>
    <div id="waveformFrame">
        <div id="waveform">
            <div class="progress progress-striped active" id="progress-bar">
                <div class="progress-bar progress-bar-info"></div>
            </div>
            <!-- Here be waveform -->
        </div>
    </div>
</div>
<div class="dialogContainer" id="saveDialogContainer">
    <script>
        function clickSaveFormatChoice(e, format) {
            var allChoicesObject = document.getElementsByClassName("saveDialogChoiceBox");
            for (i = 0; i < allChoicesObject.length; i++) {
                allChoicesObject.item(i).className = "saveDialogChoiceBox";
            }
            e.target.className += " selectedSaveDialogChoiceBox";
            current.saveFormat = format;
            document.querySelector("#saveDialogTextarea").value = subtitleDocument.strVal(current.saveFormat);
            document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="">' + localizedText('saveDialog.saveAs') + '</a>';
            document.querySelector("#saveDialogSaveButton").style.color = "#000";
            switch (format) {
                case 'prxml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.prXml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.prXml.download + '" download="fiveLoadSub.zip" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.pr\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.prXml.updatePrtl();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'prxml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.premiere') + ':';
                    break;
                case 'fcpxxml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.fcpxXml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.fcpxXml.download + '" download="fiveLoadSub.fcpxml" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.fcpx\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.fcpxXml.loadInput();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'fcpxxml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.fcpx') + ':';
                    break;
                case 'fcp7xml':
                    document.getElementById('saveDialogXmlDiv').style.display = 'block';
                    if (subtitleDocument.fcp7Xml.download != '') {
                        document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + subtitleDocument.fcp7Xml.download + '" download="fiveLoadSub.xml" target="_blank" onclick="return UI$confirmSafari(localizedText(\'saveDialog.safariPrompt.fcp7\'))" >' + localizedText('saveDialog.saveAs') + '</a>';
                        document.querySelector("#saveDialogSaveButton").style.color = "#36F";
                    }
                    document.getElementById('templateInput').onchange = function onchange(event) {
                        subtitleDocument.fcp7Xml.loadInput();
                    };
                    document.getElementById('xmlOkButton').onclick = function onclick(event) {
                        clickSaveFormatChoice(event, 'fcp7xml');
                    };
                    document.getElementById('xmlTemplateInputLabelDiv').innerHTML = localizedText('saveDialog.xmlInputLabel.fcp7') + ':';
                    break;
                default:
                    document.getElementById('saveDialogXmlDiv').style.display = 'none';
                    var download = "data:text/plain;charset=utf-8," + encodeURIComponent(document.querySelector("#saveDialogTextarea").value);
                    var ext = format;
                    document.querySelector("#saveDialogSaveButton").innerHTML = '<a style="color:#36F;" href="' + download + '" download="fiveLoadSub.' + ext + '" target="_blank">' + localizedText('saveDialog.saveAs') + '</a>';
                    document.querySelector("#saveDialogSaveButton").style.color = "#36F";
            }

        }

        function showSaveDialog() {
            // document.querySelector("#saveDialogContainer").style.display = "block";
        }

        function hideSaveDialog() {
            document.querySelector("#saveDialogContainer").style.display = "none";
        }
    </script>

    <div id="saveDialog" class="promptDialog">
        <div style="background:#777;color:#FFF; vertical-align:central; line-height:30px; font-size:16px;">
            {fiveLoadSub_saveDialog.title}
        </div>
        <div style="text-align:center">
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fls');">.FLS</div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'srt');">.SRT</div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'sbv');">.SBV</div>
            <br/>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'prxml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Adobe
                        Premiere
                    </div>
                </div>
            </div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fcpxxml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Final Cut Pro
                        X
                    </div>
                </div>
            </div>
            <div class="saveDialogChoiceBox" onclick="clickSaveFormatChoice(event,'fcp7xml');">
                <div onclick="this.parentNode.click();event.stopPropagation();"
                     style="display:inline-block;vertical-align: middle;">
                    <div onclick="this.parentNode.click();event.stopPropagation();" style="line-height:40px;">.XML</div>
                    <div onclick="this.parentNode.click(); event.stopPropagation();"
                         style="font-size:10px;line-height:10px;display:block; vertical-align:middle;">for Final Cut Pro
                        7
                    </div>
                </div>
            </div>
        </div>
        <div id="saveDialogXmlDiv" style="display: none;">
            <br>
            <div id="xmlTemplateInputLabelDiv">{fiveLoadSub_saveDialog.xmlInputLabel.fcpx}:</div>
            <br>
            <input type="file" id="templateInput"
                   onchange="subtitleDocument.prXml.updatePrtl();subtitleDocument.fcpxXml.loadInput();subtitleDocument.fcp7Xml.loadInput();">
            <br>
            <br> {fiveLoadSub_saveDialog.xmlInfo.fillLabel}:
            <br> {fiveLoadSub_saveDialog.xmlInfo.width}:
            <input type="text" id="saveDialogXml_width" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">px
            &nbsp;{fiveLoadSub_saveDialog.xmlInfo.height}:
            <input type="text" id="saveDialogXml_height" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">px
            &nbsp;{fiveLoadSub_saveDialog.xmlInfo.frameRate}:
            <input type="text" id="saveDialogXml_frameRate" size="7"
                   onchange="subtitleDocument.prXml.updateConfig();subtitleDocument.fcpxXml.updateConfig();subtitleDocument.fcp7Xml.updateConfig();">fps
            <br>
            <br>
            <input id="xmlOkButton" type="button" value="OK" onclick="clickSaveFormatChoice(event,'prxml');"
                   class=" selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox selectedSaveDialogChoiceBox">
        </div>
        <div style="text-align:left;padding:0 10px;">{fiveLoadSub_saveDialog.copyFromHereLabel}
            <br>
        </div>
        <textarea cols="50" rows="10" style="margin:0 5px;" id="saveDialogTextarea"></textarea>
        <div style="text-align:right;padding:0 10px;"><span id="cancelSubtitleDialogCacnelButton" style="color:#36F;"
                                                            onclick="hideSaveDialog();">{fiveLoadSub_saveDialog.cacnel}</span>&nbsp;&nbsp;&nbsp;<span
                    id="saveDialogSaveButton" style="color: rgb(51, 102, 255);"><a style="color:#36F;" target="_blank"
                                                                                   onclick="return UI$confirmSafari(localizedText('saveDialog.safariPrompt.fcpx'))">{fiveLoadSub_saveDialog.saveAs}</a></span>
        </div>
    </div>

</div>
<div class="dialogContainer" id="convertDialogContainer">
    <script>
        function showConvertDialog() {
            return;
            var convertDialogInput = document.getElementById('convertDialogInput');
            var subtitleTextArea = document.getElementById('subtitleTextArea');
            document.querySelector("#convertDialogContainer").style.display = "block";
            convertDialogInput.value = subtitleTextArea.value;
            updateConvertDialog();
        }

        function hideConvertDialog() {
            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            document.querySelector("#convertDialogContainer").style.display = "none";
            convertDialogInput.value = '';
            convertDialogOutput.value = '';
        }

        function updateConvertDialog() {

            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            if (current.convertDialog.lastSelection == 'format') {

                var selection = '';

                if (document.getElementById('convertDialogInputFormat_srt').checked) {
                    selection = 'srt';
                } else if (document.getElementById('convertDialogInputFormat_sbv').checked) {
                    selection = 'sbv';
                }
                switch (selection) {
                    case 'srt':
                        subtitleDocument.parseFromString(convertDialogInput.value, 'srt');
                        convertDialogOutput.value = subtitleDocument.strVal();
                        break;
                    case 'sbv':
                        subtitleDocument.parseFromString(convertDialogInput.value, 'sbv');
                        convertDialogOutput.value = subtitleDocument.strVal();
                        break;
                    default:
                        convertDialogOutput.value = convertDialogInput.value;
                }
            } else if (current.convertDialog.lastSelection == 'text') {

                //Text Tool
                var pineline = convertDialogInput.value;

                if (document.getElementById('convertDialogRemovePunctuations').checked) {
                    document.getElementById('convertDialogSplitPunctuations').checked = false;
                    pineline = pineline.replace(/\,|\.|\;|\?|\:|\!/g, ' '); //Half width symbol, possible end of sentences
                    pineline = pineline.replace(/\uFF0C|\u3002|\uFF1F|\uFF01|\uFF1A|\uFF1B|\u3001|\uA143|\uA148|\uA149|\uA147|\uA146|\uA142/g, ' '); //Full width symbol, possible end of sentences。？！：；、
                    pineline = pineline.replace(/\-|\~/g, '');
                    pineline = pineline.replace(/\uFF0D|\uFF5E|\u22EF/g, ''); //－ ～ ⋯
                }

                if (document.getElementById('convertDialogSplitPunctuations').checked) {
                    document.getElementById('convertDialogRemovePunctuations').checked = false;
                    pineline = pineline.replace(/\,|\.|\;|\?|\:|\!/g, '\n'); //Half width symbol, possible end of sentences
                    pineline = pineline.replace(/\uFF0C|\u3002|\uFF1F|\uFF01|\uFF1A|\uFF1B|\u3001|\uA143|\uA148|\uA149|\uA147|\uA146|\uA142/g, '\n'); //Full width symbol, possible end of sentences。？！：；、
                    pineline = pineline.replace(/\-|\~/g, '');
                    pineline = pineline.replace(/\uFF0D|\uFF5E|\u22EF/g, ''); //－ ～ ⋯
                }

                if (document.getElementById('convertDialogRemoveQuotes').checked) {
                    pineline = pineline.replace(/\'|\"|\`/g, '');
                    pineline = pineline.replace(/\u2018|\u2019|\u201C|\u201D|\u300C|\u300D/g, ''); // ‘ ’ “ ” 「 」
                }

                if (document.getElementById('convertDialogSplitSpaces').checked) {
                    pineline = pineline.replace(/\s/gi, '\n');
                }

                if (document.getElementById('convertDialogRemoveEmptyLine').checked) {
                    var tmp = pineline.replace(/\r/g, '\n');
                    tmp = tmp.split(/\n/);
                    var output = '';
                    for (pieces of tmp) {
                        if (pieces != '') {
                            output += pieces + '\n';
                        }
                    }
                    pineline = output;
                }

                convertDialogOutput.value = pineline;

            } else if (current.convertDialog.lastSelection == 'charset') {

                var reader = new FileReader();
                reader.onload = function () {
                    var charset = document.getElementById('convertDialogTabCharsetSelection').options[document.getElementById('convertDialogTabCharsetSelection').selectedIndex].value;
                    var de = new TextDecoder(charset);
                    console.log(this.result);
                    document.getElementById('convertDialogOutput').value = de.decode(this.result);
                }

                //try {
                if ((document.getElementById('convertDialogCharsetFileInput').files.length) > 0) {
                    reader.readAsArrayBuffer(document.getElementById('convertDialogCharsetFileInput').files[0]);
                }
                //} catch (e) {};
            }
        }

        function finishConvert() {
            var convertDialogInput = document.getElementById('convertDialogInput');
            var convertDialogOutput = document.getElementById('convertDialogOutput');
            document.getElementById('subtitleTextArea').value = convertDialogOutput.value;
            convertDialogInput.value = '';
            convertDialogOutput.value = '';
            hideConvertDialog();
        }

        function convertDialogInit() {
            document.getElementById('convertDialogTab_subtitle_a').click();
        }
    </script>

    <div id="convertDialog" class="promptDialog">
        <div style="background:#777;color:#FFF; vertical-align:central; line-height:30px; font-size:16px;">
            {fiveLoadSub_convertDialog.title}
        </div>
        <br/>
        <div>
            <div class="pinTabs">
                <div id="convertDialogTab_subtitle"><a id="convertDialogTab_subtitle_a"
                                                       href="#convertDialogTab_subtitle"
                                                       onclick="current.convertDialog.lastSelection ='format';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.subtitle}</a>
                    <div>
                        <div style="text-align:center;">
                            <br/>
                            <span style="font-size:24px;">
                                <input type="radio" id="convertDialogInputFormat_srt" name="inputFormat" value="srt"
                                       onchange="updateConvertDialog();" checked/>SRT&nbsp;
                                <input type="radio" id="convertDialogInputFormat_sbv" name="inputFormat" value="sbv"
                                       onchange="updateConvertDialog();"/>SBV&nbsp;
                            </span>
                            <br/>


                        </div>
                    </div>

                </div>

                <div id="convertDialogTab_text"><a href="#convertDialogTab_text"
                                                   onclick="current.convertDialog.lastSelection ='text';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.text}</a>
                    <div>
                            <span style="font-size:16px;">
                                <br/>
                                <input id="convertDialogRemovePunctuations" type="checkbox" name="removePunctuations"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removePunctuations}
                                <br/><br/>
                                <input id="convertDialogSplitPunctuations" type="checkbox" name="splitPunctuations"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.splitPunctuations}
                                <br/><br/>
                                <input id="convertDialogRemoveQuotes" type="checkbox" name="removeQuotes"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removeQuotes}
                                <input id="convertDialogSplitSpaces" type="checkbox" name="splitSpaces"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.splitSpaces}
                                <input id="convertDialogRemoveEmptyLine" type="checkbox" name="removeEmptyLine"
                                       onchange="updateConvertDialog();"/>{fiveLoadSub_convertDialog.selection.removeEmptyLines}
                            </span>
                    </div>
                </div>

                <div id="convertDialogTab_charset"><a href="#convertDialogTab_charset"
                                                      onclick="current.convertDialog.lastSelection='charset';updateConvertDialog();">{fiveLoadSub_convertDialog.tabLabel.charset}</a>

                    <div style="text-align:center;">
                        <br/> {fiveLoadSub_convertDialog.selection.charsetImportFile}:
                        <input id="convertDialogCharsetFileInput" type="file" onchange="updateConvertDialog();"/>
                        <br/>
                        <br/> {fiveLoadSub_convertDialog.selection.charsetTo}:
                        <select id="convertDialogTabCharsetSelection" onchange="updateConvertDialog();">
                            <option value="big5">big5</option>
                            <option value="gbk">gbk</option>
                            <option value="gb18030">gb18030</option>
                            <option value="euc-jp">euc-jp</option>
                            <option value="iso-2022-jp">iso-2022-jp</option>
                            <option value="shift-jis">shift-jis</option>
                            <option value="euc-kr">euc-kr</option>
                            <option value="iso-2022-kr">iso-2022-kr</option>
                        </select>

                    </div>
                </div>
            </div>
        </div>

        <table width="98%" style="margin: 0 auto;">
            <tr>
                <td style="width:48%">
                    {fiveLoadSub_convertDialog.original}
                </td>
                <td style="width:4%"></td>
                <td style="width:48%">
                    {fiveLoadSub_convertDialog.conversionPreview}

                </td>
            </tr>
            <tr>
                <td style="width:48%">
                    <textarea id="convertDialogInput" onchange="updateConvertDialog();" style="width:100%" rows="30"
                              wrap="off"></textarea>
                </td>
                <td style="width:4%"></td>
                <td style="width:48%">
                    <textarea id="convertDialogOutput" style="width:100%" rows="30" wrap="off"></textarea>

                </td>
            </tr>
        </table>
        <div style="text-align:right;padding:0 10px;"><span id="cancelSubtitleDialogCacnelButton" style="color:#36F;"
                                                            onclick="hideConvertDialog();">{fiveLoadSub_convertDialog.cancel}</span>&nbsp;&nbsp;&nbsp;<span
                    id="convertDialogSaveButton" style="color: rgb(51, 102, 255);"><a style="color:#36F;"
                                                                                      onclick="finishConvert()"
                                                                                      href="#">{fiveLoadSub_convertDialog.OK}</a></span>
        </div>
    </div>
    <br/>

</div>
</div>
</body>
