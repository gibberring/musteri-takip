@once
<script>
window.restoreDeletedRecord = function (btnEl) {
    try {
        if (!btnEl) return false;
        var btn = btnEl;
        var form = btn.closest('form');
        var row = btn.closest('tr');
        var url = btn.getAttribute('data-restore-url') || (form ? form.getAttribute('action') : null);
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        var token = (tokenEl && tokenEl.value) || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');

        function showError(msg) {
            if (window.Swal && Swal.fire) {
                Swal.fire('Hata', msg || 'İşlem başarısız.', 'error');
            } else {
                alert(msg || 'İşlem başarısız.');
            }
        }

        function doRestore() {
            if (!url) { showError('İşlem adresi bulunamadı.'); return; }
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            if (token) {
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
            }
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                var text = xhr.responseText || '';
                var data = null;
                try { data = JSON.parse(text); } catch (e) { data = null; }
                if (xhr.status >= 200 && xhr.status < 300 && data && data.success) {
                    if (row) row.remove();
                    if (window.Swal && Swal.fire) {
                        Swal.fire('Başarılı', data.message || 'Kayıt geri alındı.', 'success');
                    }
                    return;
                }
                var msg = (data && data.message) ? data.message : (text || 'İşlem başarısız.');
                showError(msg);
            };
            xhr.onerror = function () { showError('İşlem başarısız.'); };
            xhr.send('_token=' + encodeURIComponent(token || ''));
        }

        if (window.Swal && Swal.fire) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Kaydı geri almak istiyor musunuz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet, geri al',
                cancelButtonText: 'Vazgeç'
            }).then(function (result) {
                if (result && (result.isConfirmed || result.value)) {
                    doRestore();
                }
            });
        } else {
            if (confirm('Kaydı geri almak istiyor musunuz?')) {
                doRestore();
            }
        }
        return false;
    } catch (e) {
        return false;
    }
};

/**
 * Silinen kayıtlar sayfalarında tarih aralığı + serbest metin aramasını birleştirip
 * debounce ile AJAX üzerinden ilgili tbody'yi yeniden yükler.
 * config: { searchInputId?, tarih1Id?, tarih2Id?, tbodyId, searchUrl, colCount }
 */
window.bindDeletedFilter = function (config) {
    var searchInput = config.searchInputId ? document.getElementById(config.searchInputId) : null;
    var tarih1 = config.tarih1Id ? document.getElementById(config.tarih1Id) : null;
    var tarih2 = config.tarih2Id ? document.getElementById(config.tarih2Id) : null;
    var tbody = document.getElementById(config.tbodyId);
    var timer = null;
    var xhr = null;

    function setTbodyHtml(html) {
        if (tbody) tbody.innerHTML = html;
    }

    function currentParams() {
        var params = [];
        if (searchInput) params.push('q=' + encodeURIComponent((searchInput.value || '').trim()));
        if (tarih1) params.push('tarih1=' + encodeURIComponent(tarih1.value || ''));
        if (tarih2) params.push('tarih2=' + encodeURIComponent(tarih2.value || ''));
        return params.join('&');
    }

    function loadRows() {
        if (xhr && xhr.abort) xhr.abort();
        setTbodyHtml('<tr><td colspan="' + config.colCount + '" class="text-center text-muted">Aranıyor...</td></tr>');
        xhr = new XMLHttpRequest();
        xhr.open('GET', config.searchUrl + '?' + currentParams(), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            if (xhr.status === 0) return;
            var data = null;
            try { data = JSON.parse(xhr.responseText || ''); } catch (e) { data = null; }
            if (xhr.status >= 200 && xhr.status < 300 && data && typeof data.html === 'string') {
                setTbodyHtml(data.html);
                return;
            }
            setTbodyHtml('<tr><td colspan="' + config.colCount + '" class="text-center text-danger">Arama başarısız.</td></tr>');
        };
        xhr.send();
    }

    function debounced() {
        clearTimeout(timer);
        timer = setTimeout(loadRows, 300);
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounced);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                loadRows();
            }
        });
    }
    if (tarih1) tarih1.addEventListener('change', debounced);
    if (tarih2) tarih2.addEventListener('change', debounced);
};
</script>
@endonce
