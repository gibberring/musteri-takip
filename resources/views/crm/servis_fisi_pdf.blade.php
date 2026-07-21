<!DOCTYPE html>
<html lang="tr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Teknik Servis Formu - Servis No: {{ $servis->id ?? 'N/A' }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; /* Türkçe karakterler için önemli */
            line-height: 1.3; 
            font-size: 9px; 
            margin: 0; 
            padding: 0; 
            color: #333;
        }
        @page {
            margin: 25px;
        }
        .container { 
            width: 100%; 
            margin: 0 auto; 
        }
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 15px;
            table-layout: fixed; /* Sütun genişliklerinin daha iyi kontrolü için */
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            word-wrap: break-word; /* Uzun kelimelerin taşmasını engelle */
        }
        .firma-sol { 
            width: 35%; /* Genişlik ayarlandı */
            font-size:8px; 
            line-height:1.2;
        }
        .firma-sol .bold {
            font-size: 9px;
        }
        .baslik-orta { 
            width: 30%; /* Genişlik ayarlandı */
            text-align:center; 
            vertical-align:top; 
        }
        .servis-no-sag { 
            width: 35%; /* Genişlik ayarlandı */
            text-align:right; 
            font-size:8px; 
            vertical-align:top; 
            line-height:1.2;
        }
        .marka-baslik {
            font-size: 14px; /* Biraz küçültüldü */
            font-weight: bold; 
            margin-bottom:0;
            text-transform: uppercase;
        }
        .form-baslik {
            font-size: 10px; /* Biraz küçültüldü */
            font-weight: bold; 
            margin-top:0;
            text-transform: uppercase;
        }
        .section-title { 
            background-color: #e9ecef; /* Daha açık bir gri */
            padding: 4px 6px; 
            font-weight: bold; 
            text-align: center; 
            margin-top: 10px; 
            margin-bottom: 5px; 
            font-size: 9.5px; /* Biraz büyütüldü */
            border: 1px solid #dee2e6;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .info-section-table {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 10px;
            border-collapse: collapse; /* Dış kenarlık için */
        }
        .info-section-table > tbody > tr > td { /* Sadece doğrudan çocuk olan td'leri etkile */
            width: 50%;
            vertical-align: top;
            padding: 0; /* Dış padding'i sıfırla */
            border: 1px solid #dee2e6; /* Kart benzeri görünüm için kenarlık */
        }
        .card-content { /* Kart içeriği için padding */
            padding: 8px;
        }
        .info-table { /* İçerideki bilgi tablosu */
            width: 100%;
            border: none;
        }
        .info-table td { 
            border: none; 
            padding: 2px 0px; /* Dikey padding azaltıldı, yatay padding etikete bırakıldı */
            font-size: 8.5px; /* İçerik fontu biraz küçültüldü */
            line-height: 1.2;
        }
        .info-table td.label {
            font-weight: bold;
            width: 25%; /* Etiket genişliği */
            padding-right: 5px;
            vertical-align: top;
        }
        .info-table td.value {
            width: 75%; /* Değer genişliği */
            vertical-align: top;
        }

        .log-table, .kasa-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8px;
        }
        .log-table th, .log-table td,
        .kasa-table th, .kasa-table td {
            border: 1px solid #dee2e6;
            padding: 3px 4px;
            text-align: left;
            vertical-align: top;
        }
        .log-table th, .kasa-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 8.5px;
        }
        .text-right { text-align: right; }
        .notlar { 
            font-size: 7.5px; /* Notlar küçültüldü */
            line-height: 1.1; 
            padding-left: 15px; 
            margin-top: 5px;
            margin-bottom: 10px;
        }
        .notlar li { margin-bottom: 1px; }

        .signature-area-table {
            width: 100%;
            margin-top: 25px; /* Daha fazla boşluk */
            border: none;
            table-layout: fixed;
        }
        .signature-area-table td {
            border: none;
            padding: 0;
            text-align: center;
            vertical-align: top; /* İmza alanı ve metnin hizası için */
        }
        .signature-box {
            min-height: 50px; /* İmza için minimum yükseklik */
            border-bottom: 1px solid #333;
            margin-bottom: 3px;
        }
        .signature-box img {
            max-width: 100%;
            max-height: 45px; /* İmza resminin maksimum yüksekliği */
            display: block; /* Resmin altındaki boşluğu kaldırmak için */
            margin: 0 auto; /* Ortalama */
        }
        .signature-text {
            font-size: 8px;
        }
        .footer-note { 
            font-size: 7.5px; 
            text-align: center; 
            margin-top: 15px; 
            color: #555;
        }
        .no-data {
            text-align: center;
            font-size: 8px;
            color: #777;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="firma-sol">
                    @php
                        $fisFirma = $personelBilgileri->fis_firma ?? null;
                        $fisTel = $personelBilgileri->fis_tel ?? null;
                        $fisAdres = $personelBilgileri->fis_adres ?? null;
                    @endphp
                    @if($fisFirma || $fisTel || $fisAdres)
                        @if($fisFirma)<span class="bold">{{ $fisFirma }}</span><br>@endif
                        @if($fisTel){{ $fisTel }}<br>@endif
                        @if($fisAdres){{ \Illuminate\Support\Str::ucfirst(mb_strtolower($fisAdres, 'UTF-8')) }}@endif
                    @endif
                </td>
                <td class="baslik-orta">
                    <div class="form-baslik">TEKNİK SERVİS FORMU</div>
                </td>
                <td class="servis-no-sag">
                    <span style="font-weight:bold;">Servis No:</span> {{ $servis->id ?? 'N/A' }}<br>
                    <span style="font-weight:bold;">Kayıt Tarihi:</span> {{ $servis->created_at ? \Carbon\Carbon::parse($servis->created_at)->format('d.m.Y H:i') : 'N/A' }}
                </td>
            </tr>
        </table>

        <table class="info-section-table">
            <tr>
                <td>
                    <div class="card-content">
                        <div class="section-title" style="margin-top:0; margin-bottom:8px;">MÜŞTERİ BİLGİLERİ</div>
                        <table class="info-table">
                            <tr><td class="label">Adı Soyadı:</td><td class="value">{{ $servis->musteri->ad ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Telefon:</td><td class="value">{{ $servis->musteri->tel1 ?? '' }} {{ $servis->musteri->tel2 ? '/ '.$servis->musteri->tel2 : '' }}</td></tr>
                            <tr><td class="label">Adres:</td><td class="value">{{ $servis->musteri->adres ?? '' }}<br>{{ $servis->musteri->ilce?->ad ?? '' }} / {{ $servis->musteri->il?->ad ?? '' }}</td></tr>
                            @if($servis->musteri && $servis->musteri->musteri_tip == 1)
                            <tr><td class="label">Vergi Dairesi:</td><td class="value">{{ $servis->musteri->vdaire ?? '-' }}</td></tr>
                            <tr><td class="label">Vergi No:</td><td class="value">{{ $servis->musteri->vno ?? '-' }}</td></tr>
                            @endif
                            @if(!empty($servis->operator_not))
                            <tr><td class="label">Operatör Notu:</td><td class="value">{{ $servis->operator_not }}</td></tr>
                            @endif
                        </table>
                    </div>
                </td>
                <td>
                    <div class="card-content">
                        <div class="section-title" style="margin-top:0; margin-bottom:8px;">CİHAZ BİLGİSİ</div>
                         <table class="info-table">
                            <tr><td class="label">Markası:</td><td class="value">{{ $servis->marka->ad ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Cihaz Türü:</td><td class="value">{{ $servis->cihazTuru->ad ?? 'N/A' }}</td></tr>
                            <tr><td class="label">Modeli:</td><td class="value">{{ $servis->cihaz_model ?? '-' }}</td></tr>
                            <tr><td class="label">Seri No:</td><td class="value">{{ $servis->seri_no ?? '-' }}</td></tr>
                            <tr><td class="label">Arızası:</td><td class="value">{{ $servis->cihaz_arizasi ?? '-' }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <div class="section-title">SERVİS DURUMU: {{ $servis->servisDurum->ad ?? 'N/A' }}</div>

        <div class="section-title">- SERVİSTE YAPILAN İŞLEMLER -</div>
        @if($formattedIslemLoglari && count($formattedIslemLoglari) > 0)
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width:20%;">TARİH</th>
                        <th style="width:25%;">İŞLEM ADI</th>
                        <th>AÇIKLAMA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formattedIslemLoglari as $log)
                    <tr>
                        <td>{{ $log->tarih ? \Carbon\Carbon::parse($log->tarih . ' ' . $log->saat)->format('d.m.Y H:i') : 'N/A' }}</td>
                        <td>{{ $log->servisDurum->ad ?? ($log->aciklama && str_starts_with($log->aciklama, 'Cihaz Bilgileri Güncellendi') ? 'Bilgi Güncelleme' : ($log->aciklama ? 'Not' : 'Diğer')) }}</td>
                        <td>
                            {{ $log->formatted_aciklama }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">Bu servise ait işlem kaydı bulunmamaktadır.</p>
        @endif

        <div class="section-title">- ÖDEME HAREKETLERİ -</div>
        @if($kasaHareketleri && $kasaHareketleri->count() > 0)
            <table class="kasa-table">
                <thead>
                    <tr>
                        <th style="width:20%;">TARİH</th>
                        <th style="width:25%;">TAHSİL EDEN</th>
                        <th style="width:20%;">ÖDEME ŞEKLİ</th>
                        <th style="width:20%;">ÖDEME DURUMU</th>
                        <th style="width:15%;" class="text-right">TUTAR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kasaHareketleri->sortByDesc(function($item) { return $item->tarih . $item->saat; }) as $kasa)
                    <tr>
                        <td>{{ $kasa->tarih ? \Carbon\Carbon::parse($kasa->tarih . ' ' . $kasa->saat)->format('d.m.Y H:i') : 'N/A' }}</td>
                        <td>{{ $kasa->personel->ad ?? 'N/A' }}</td>
                        <td>{{ $kasa->odemeSekli->ad ?? 'N/A' }}</td>
                        <td>{{ $kasa->gerceklesme == 1 ? 'Tamamlandı' : 'Beklemede' }}</td>
                        <td class="text-right">{{ number_format($kasa->tutar, 2, ',', '.') }} TL</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data">Bu servise ait ödeme hareketi bulunmamaktadır.</p>
        @endif

        <div class="section-title">- NOTLAR -</div>
        <ol class="notlar">
            @if(isset($sabitNotlar) && count($sabitNotlar) > 0)
                @foreach($sabitNotlar as $not)
                    <li>{{ $not }}</li>
                @endforeach
            @else
                <li>Varsayılan not bulunamadı.</li>
            @endif
        </ol>

        <table class="signature-area-table">
            <tr>
                <td style="width:48%;">
                    <div class="signature-box">
                        @if($musteriImzaBase64)
                            <img src="{{ $musteriImzaBase64 }}" alt="Müşteri İmzası">
                        @endif
                    </div>
                    <div class="signature-text">Müşteri İmzası</div>
                </td>
                <td style="width:4%;"></td> {{-- Boşluk --}}
                <td style="width:48%;">
                    <div class="signature-box">
                         @if($teknisyenImzaBase64)
                            <img src="{{ $teknisyenImzaBase64 }}" alt="Teknisyen İmzası">
                        @endif
                    </div>
                    <div class="signature-text">Teknisyen İmzası</div>
                </td>
            </tr>
        </table>
        <div class="footer-note">
            Bu Servis Formu {{ $servisFisi ? \Carbon\Carbon::parse($servisFisi->tarih . ' ' . $servisFisi->saat)->format('d.m.Y H:i') : now()->locale('tr_TR')->isoFormat('DD.MM.YYYY HH:mm') }} Tarihinde Oluşturulmuştur.
        </div>
    </div>
</body>
</html>
