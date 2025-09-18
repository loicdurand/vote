if (!Date.now) Date.now = () => new Date().getTime();

const // 
    isFn = fn => ['[object AsyncFunction]', '[object Function]'].includes(({}).toString.call(fn)),
    stringConstructor = "test".constructor,
    arrayConstructor = [].constructor,
    objectConstructor = ({}).constructor;

export const // 

    // functions utils

    pipe = (...fns) => (x) => fns.reduce((v, f) => f(v), x),

    compose = (...funcs) => x => funcs.reduceRight((v, f) => f(v), x),

    // strings utils

    addZeros = (str: string | number, maxlen = 2): string => {
        str = '' + str;
        while (str.length < maxlen)
            str = "0" + str;
        return str;
    },

    escapeHTML = (str: string): string => str.replace(/[&<>"']/g, (m) => {
        const escape = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return escape[m];
    }),

    // noAccent = (str: string): string => {

    //     str ||= '';

    //     const // 
    //         accents = [
    //             /[\300-\306]/g, /[\340-\346]/g, // A, a
    //             /[\310-\313]/g, /[\350-\353]/g, // E, e
    //             /[\314-\317]/g, /[\354-\357]/g, // I, i
    //             /[\322-\330]/g, /[\362-\370]/g, // O, o
    //             /[\331-\334]/g, /[\371-\374]/g, // U, u
    //             /[\321]/g, /[\361]/g, // N, n
    //             /[\307]/g, /[\347]/g, // C, c
    //         ]
    //         , noaccent = ['A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', 'C', 'c'];

    //     for (var i = 0; i < accents.length; i++)
    //         str = str.replace(accents[i], noaccent[i]);

    //     return str;
    // },

    capitalize = (str: string, alsoAfterHyphenOrApostrophe: boolean = false): string => {
        if (!str) return str;
        return str.split(/\s/).map(txt => {
            // capitalise la 1ère lettre de chaque mot
            let capAfterSpaces = txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            // capitalise la 1ère lettre suivant un tiret ou un apostrophe
            if (alsoAfterHyphenOrApostrophe)
                return capAfterSpaces.replace(/[-'].{1}/, m => m.toUpperCase());
            return capAfterSpaces
        }).join(' ');

    },

    truncate = (str, n = 15, useWordBoundary = false) => {
        if (str.length <= n) { return str; }
        const subString = str.slice(0, n - 1); // the original check
        return (useWordBoundary ? subString.slice(0, subString.lastIndexOf(" ")) : subString) + "...";
    },

    unicId = (len: number = 7): string => Math.random().toString(36).slice(2, 2 + len),

    pluralize = (nb: number, sing: string, plur: string = ''): string => (isNaN(nb) || +nb > 1) ? plur || (sing + 's') : sing,

    empty = (eltOrSelector: string | Element) => {
        const // 
            getElem = (selector: string): Element | null => document.querySelector(selector),
            target = typeof eltOrSelector == 'string' ? getElem(eltOrSelector) : eltOrSelector;
        if (target)
            target.innerHTML = "";
        return target;
    },

    insertAfter = (referenceNode: Element, newNode: Element) => {
        if ('parentNode' in referenceNode && referenceNode.parentNode)
            referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
        return newNode;
    },

    onReady = async (selector: string): Promise<any> => {
        while (document.querySelector(selector) === null)
            await new Promise(resolve => requestAnimationFrame(resolve));
        return document.querySelector(selector);
    },

    when = async (varname): Promise<any> => {
        while (window[varname])
            await new Promise(resolve => requestAnimationFrame(resolve))
        return window[varname];
    },

    getParent = (elt: HTMLElement, match: string): (HTMLElement | false) => {
        while (!elt.matches(match) && elt.parentElement !== null && elt.parentElement.nodeName != 'BODY' && elt.parentElement.parentElement) {
            elt = elt.parentElement;
        }
        if (elt.parentElement !== null && elt.parentElement.nodeName == 'BODY') return false; else return elt;
    },

    // Objects utils

    isEmpty = obj => obj
        && Object.keys(obj).length === 0
        && Object.getPrototypeOf(obj) === Object.prototype,

    is = (object, type) => {

        if (object === null)
            return !type ? "null" : 'null' === type;
        if (object === undefined)
            return !type ? "undefined" : "undefined" === type;
        if (object.constructor === stringConstructor)
            return !type ? "String" : "String" === type;
        if (object.constructor === arrayConstructor)
            return !type ? "Array" : "Array" === type;
        if (object.constructor === objectConstructor)
            return !type ? "Object" : "Object" === type;
        if (!isNaN(object))
            return !type ? "Number" : "Number" === type;
        if (isFn(object))
            return !type ? "Function" : true;
        return !type ? "don't know" : false;
    },

    removeFalsy = (obj: object, copy = {}) => {
        Object.keys(obj).forEach(prop => {
            if (obj[prop])
                copy[prop] = obj[prop];
        });
        return copy;
    },

    // date utils

    time = (t: number | Function = 0) => {

        const // 
            cb: Function = typeof t === 'number' ? (o) => o : t,
            time = typeof t === 'number' ? t : 0,
            date = time ? new Date(time) : new Date(),
            Y = '' + date.getFullYear(),
            M = addZeros(date.getMonth() + 1, 2),
            D = addZeros(date.getDate(), 2),
            H = addZeros(date.getHours(), 2),
            m = addZeros(date.getMinutes(), 2),
            s = addZeros(date.getSeconds(), 2);
        return cb({ Y, M, D, H, m, s });
    },

    add1Day = (date) => time(+new Date(Date.parse(date) + (3600 * 1000 * 24))),

    subMinutes = (date, n) => time(+new Date(Date.parse(date) - (60 * 1000 * n))),

    addMinutes = (date, n) => time(+new Date(Date.parse(date) + (60 * 1000 * n))),

    isBetween = (limiteDebut: string, limiteFin: string) => (sDebut: string | { debut: string, fin: string }) => {
        const // 
            Debut = typeof sDebut === 'string' ? sDebut : sDebut.debut,
            Fin = typeof sDebut === 'string' ? sDebut : (sDebut.fin || sDebut.debut),
            limiteBasse = +limiteDebut.split('-').join(''),
            limiteHaute = +limiteFin.split('-').join(''),
            [debut] = Debut.split(/\s|T/),
            [fin] = Fin.split(/\s|T/),
            intDebut = +debut.split('-').join(''),
            intFin = +fin.split('-').join('');
        if (intFin < limiteBasse)
            return false;
        if (intDebut > limiteHaute)
            return false;
        return true;
    },

    FR = (EN_date = '', litteral = false, skipHours = '', getDayOfWeek = false) => {
        let jour_de_la_semaine = '';
        if (null === EN_date)
            return '';

        const //
            jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
            mois = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
            [date, heure] = EN_date.split(/\s|T|\+/),
            [DD, MM, YYYY] = date.split('-').reverse(),
            DDMMYYYY = litteral ? `${DD} ${mois[+MM]} ${YYYY}` : [DD, MM, YYYY].join('/'),
            HHmm = heure && !skipHours ? ' à ' + heure.replace(/:\d\d$/, '').replace(/:/, 'H') : '';
        if (getDayOfWeek)
            jour_de_la_semaine = jours[(new Date(Date.parse(date))).getDay()] + ' ';

        return `${jour_de_la_semaine}${DDMMYYYY}${HHmm}`;
    },

    diffInMonths = (debut, fin) => {
        const //
            [Ydeb, Mdeb] = debut.split(/-/),
            [Yfin, Mfin] = fin.split(/-/),
            nbMonthsDeb = +Ydeb * 12 + +Mdeb,
            nbMonthsFin = +Yfin * 12 + +Mfin;
        return nbMonthsFin - nbMonthsDeb;
    },

    deepSearch = (obj: Object, cb: Function) => {
        let result = undefined;
        try {
            JSON.stringify(obj, (_, testedValue) => {
                if (testedValue && cb(testedValue))
                    result = testedValue;
                return testedValue;
            });
            return result;
        } catch (err) {
            throw Error(cb.toString());
        }

    },

    mergeTruthyValues = (A, B) => {
        let res = {};
        Object.keys({ ...A, ...B }).map(key => {
            if (B[key].constructor === objectConstructor)
                return res[key] = mergeTruthyValues(A[key], B[key])
            res[key] = B[key] || A[key];
        });
        return res;
    },

    copyTextToClipboard = (text) => {
        function fallbackCopyTextToClipboard(text) {
            var textArea = document.createElement("textarea");
            textArea.value = text;

            // Avoid scrolling to bottom
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Fallback: Copying text command was ' + msg);
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }

            document.body.removeChild(textArea);
        }

        if (!navigator.clipboard) {
            fallbackCopyTextToClipboard(text);
            return;
        }
        navigator.clipboard.writeText(text).then(function () {
            console.log('Async: Copying to clipboard was successful!');
        }, function (err) {
            console.error('Async: Could not copy text: ', err);
        });
    }