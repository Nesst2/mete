@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Tagihan Vendor: {{ $vendor->nama }}</h2>
    @if ($vendor->gambar_vendor)
                <p><strong>Gambar Vendor:</strong></p>
                <img src="{{ Storage::url($vendor->gambar_vendor) }}" alt="Gambar Vendor" style="width: 200px; height: auto;">
                <!-- Tombol untuk melihat detail gambar -->
                <p>
                    <a href="{{ Storage::url($vendor->gambar_vendor) }}" target="_blank" class="btn btn-info">
                        Lihat Detail Gambar
                    </a>
                </p>
    @endif

    <form action="{{ route('tagihan.store') }}" method="POST">
        @csrf
        <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">

        @for ($i = 1; $i <= 15; $i++)
            <div class="mb-3" id="form{{ $i }}">
                <label for="status_kunjungan{{ $i }}" class="form-label">
                    Status Kunjungan (Form {{ $i }})
                </label>
                <select class="form-select" id="status_kunjungan{{ $i }}" name="status_kunjungan{{ $i }}"
                    {{ isset($formStatus["form{$i}"]) && $formStatus["form{$i}"] === 'locked' ? 'disabled' : '' }}>
                    <option value="">Pilih Status</option>
                    <option value="ada orang" {{ (isset($tagihanSebelumnya[$i-1]) && $tagihanSebelumnya[$i-1]['status_kunjungan'] == 'ada orang') ? 'selected' : '' }}>
                        Ada Orang
                    </option>
                    <option value="tertunda" {{ (isset($tagihanSebelumnya[$i-1]) && $tagihanSebelumnya[$i-1]['status_kunjungan'] == 'tertunda') ? 'selected' : '' }}>
                        Tertunda
                    </option>
                    <option value="masih" {{ (isset($tagihanSebelumnya[$i-1]) && $tagihanSebelumnya[$i-1]['status_kunjungan'] == 'masih') ? 'selected' : '' }}>
                        Masih
                    </option>
                    <option value="tidak ada orang" {{ (isset($tagihanSebelumnya[$i-1]) && $tagihanSebelumnya[$i-1]['status_kunjungan'] == 'tidak ada orang') ? 'selected' : '' }}>
                        Tidak Ada Orang
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="nominal{{ $i }}" class="form-label">
                    Nominal (Form {{ $i }})
                </label>
                <input type="number" class="form-control" id="nominal{{ $i }}" name="nominal{{ $i }}"
                    value="{{ isset($tagihanSebelumnya[$i-1]) ? number_format($tagihanSebelumnya[$i-1]['uang_masuk'], 0, '', '') : old("nominal{$i}") }}"
                    {{-- Jika data tagihan sudah ada, disable input --}}
                    {{ isset($tagihanSebelumnya[$i-1]) ? 'disabled data-existing=true' : (isset($formStatus["form{$i}"]) && $formStatus["form{$i}"] === 'locked' ? 'disabled' : '') }}>
            </div>

            @if (isset($tagihanSebelumnya[$i-1]))
                <div class="mb-3">
                    <label for="retur{{ $i }}" class="form-label">
                        Nominal Retur (Form {{ $i }})
                    </label>
                    <input type="number" class="form-control" id="retur{{ $i }}" name="retur[{{ $tagihanSebelumnya[$i-1]['id'] }}]"
                        value="{{ isset($returData[$tagihanSebelumnya[$i-1]['id']]) ? number_format($returData[$tagihanSebelumnya[$i-1]['id']]->jumlah_retur, 0, '', '') : old("retur[{$tagihanSebelumnya[$i-1]['id']}]") }}"
                        {{-- Disable retur jika status tagihan adalah "tidak ada orang" atau "masih", 
                             atau jika data retur sudah ada --}}
                        {{ (in_array($tagihanSebelumnya[$i-1]['status_kunjungan'], ['tidak ada orang', 'masih']) || isset($returData[$tagihanSebelumnya[$i-1]['id']])) ? 'disabled data-existing=true' : '' }}>
                </div>
                <p>
                    <small class="text-muted">Tanggal Submit Tagihan:
                        <span class="badge bg-info text-dark">
                            {{ \Carbon\Carbon::parse($tagihanSebelumnya[$i-1]['tanggal_masuk'])->format('d-m-Y') }}
                        </span>
                    </small>
                </p>
            @endif
        @endfor

        <button type="submit" class="btn btn-primary">Simpan Tagihan</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateNominal(formNumber) {
            const statusSelect = document.querySelector(`#status_kunjungan${formNumber}`);
            const nominalInput = document.querySelector(`#nominal${formNumber}`);

            // Jika input nominal sudah memiliki data existing, jangan ubah properti disabled-nya.
            if(nominalInput.hasAttribute('data-existing')) {
                return;
            }

            if (statusSelect && nominalInput) {
                if (statusSelect.value === 'ada orang' || statusSelect.value === 'tertunda') {
                    nominalInput.disabled = false;
                    nominalInput.placeholder = "Masukkan nominal, contoh: 10000";
                } else {
                    nominalInput.disabled = true;
                }
            }
        }

        // Inisialisasi dan event listener untuk semua form (1 hingga 15)
        for (let i = 1; i <= 15; i++) {
            updateNominal(i);
            const statusSelect = document.querySelector(`#status_kunjungan${i}`);
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    updateNominal(i);
                });
            }
        }
    });
</script>
@endpush
