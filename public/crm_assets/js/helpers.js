// === Genel Yardımcı Fonksiyonlar ===

/**
 * String'deki her kelimenin ilk harfini Türkçe'ye uygun büyük harfe çevirir.
 * @param {string} str Çevrilecek string.
 * @returns {string} Formatlanmış string.
 */
function ucwordsJs(str) {
    if (!str) return '';
    let lowerCaseStr = str.toLocaleLowerCase('tr-TR');
    return lowerCaseStr.replace(/(^|\s)([a-zıiüöğçş])/g, function(match, p1, p2) {
        return p1 + p2.toLocaleUpperCase('tr-TR');
    });
}

/**
 * String'in ilk harfini Türkçe'ye uygun büyük harfe çevirir.
 * @param {string} str Çevrilecek string.
 * @returns {string} Formatlanmış string.
 */
function ucfirstJs(str) {
    if (!str) return '';
    return str.charAt(0).toLocaleUpperCase('tr-TR') + str.slice(1);
}

/**
 * Bir değerin bir dizi içinde olup olmadığını kontrol eder.
 * @param {any} needle Aranan değer.
 * @param {Array} haystack Arama yapılacak dizi.
 * @returns {boolean} Değer dizide varsa true, yoksa false.
 */
function in_array_js(needle, haystack) {
    if (!Array.isArray(haystack)) return false;
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
    }
    return false;
}

/**
 * Telefon numarasını 0(XXX) XXX XX XX formatına çevirir ve başına 0 ekler.
 * @param {HTMLInputElement} inputElement Formatlanacak input elemanı.
 */
function formatPhoneNumber(inputElement) {
    let value = inputElement.value.replace(/\D/g, ''); // Sadece rakamları al
    
    if (value.length === 10 && value.startsWith('5')) {
        value = '0' + value;
    }
    
    if (value.length > 11) {
        value = value.substring(0, 11);
    }

    let formattedValue = '';
    if (value.length > 0) {
        if (!value.startsWith('0') && value.length <= 10) { 
            if (!(value.startsWith('5') && value.length === 10)){
                value = '0' + value.substring(0,10); 
            }
        } else if (value.startsWith('0') && value.length > 11) {
             value = value.substring(0,11); 
        } else if (!value.startsWith('0') && value.length > 10) {
             value = '0' + value.substring(0,10); 
        }

        if (value.startsWith('0')) {
            formattedValue = '0';
            if (value.length > 1) {
                formattedValue += '(' + value.substring(1, 4);
            }
            if (value.length > 4) {
                formattedValue += ') ' + value.substring(4, 7);
            }
            if (value.length > 7) {
                formattedValue += ' ' + value.substring(7, 9);
            }
            if (value.length > 9) {
                formattedValue += ' ' + value.substring(9, 11);
            }
        }
    }
    inputElement.value = formattedValue;
}

// Türkçe karakterleri normalleştiren (Latinize eden ve küçük harfe çeviren) fonksiyon
function normalizeTurkishChars(str) {
    if (typeof str !== 'string') {
        // Eğer string değilse veya null/undefined ise boş string veya uygun bir değer döndür
        return str === null || typeof str === 'undefined' ? '' : String(str);
    }
    let s = str.toLowerCase(); // Önce küçük harfe çevir
    s = s.replace(/[ıi̇]/g, 'i'); // ı, İ (noktalı İ) -> i
    s = s.replace(/[İ]/g, 'i');    // Büyük İ (noktasız) -> i
    s = s.replace(/[ş]/g, 's');
    s = s.replace(/[ğ]/g, 'g');
    s = s.replace(/[ü]/g, 'u');
    s = s.replace(/[ö]/g, 'o');
    s = s.replace(/[ç]/g, 'c');
    return s;
}

// Oturum süresi dolduğunda AJAX isteklerinde login'e yönlendir
if (window.jQuery) {
    $(document).ajaxError(function(event, jqxhr) {
        if (jqxhr && (jqxhr.status === 401 || jqxhr.status === 419)) {
            window.location.href = '/login';
        }
    });
}