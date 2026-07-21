@extends('layouts.app')

@section('title', 'Operatör Karşılaştırma')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Operatör Karşılaştırma</h5>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div id="operatorComparisonDateInfo" class="text-muted mb-2" style="display:none;"></div>
                        <div id="operatorComparisonTableWrapper">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                        <div class="text-muted mt-2">
                            Bu liste, servisi oluşturan operatöre göre hesaplanır.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_specific_scripts')
<script>
    (function () {
        function fetchOperatorComparison() {
            var dateFrom = sessionStorage.getItem('operatorComparisonDateFrom') || '';
            var dateTo = sessionStorage.getItem('operatorComparisonDateTo') || '';

            var $info = $('#operatorComparisonDateInfo');
            if (dateFrom || dateTo) {
                $info.text('Tarih aralığı: ' + (dateFrom || '-') + ' / ' + (dateTo || '-')).show();
            } else {
                $info.hide();
            }

            $.ajax({
                url: "{{ route('servisler.operatorComparison.data') }}",
                type: "POST",
                dataType: "json",
                data: {
                    baslangic_tarih: dateFrom,
                    bitis_tarih: dateTo
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response && response.success) {
                        $('#operatorComparisonTableWrapper').html(response.html || '');
                    } else {
                        $('#operatorComparisonTableWrapper').html('<div class="text-center text-muted py-4">Veri bulunamadı.</div>');
                    }
                },
                error: function () {
                    $('#operatorComparisonTableWrapper').html('<div class="text-center text-danger py-4">Veriler alınamadı.</div>');
                }
            });
        }

        $(document).ready(function () {
            fetchOperatorComparison();
        });
    })();
</script>
@endpush
