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