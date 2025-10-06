<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Dashboard BPJS Claim</h2>

    <div class="flex gap-4 mb-4">
        <select wire:model="month">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
            @endforeach
        </select>

        <select wire:model="year">
            @foreach(range(now()->year - 2, now()->year) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded">Total Pasien: {{ $summary['total'] }}</div>
        <div class="bg-green-100 p-4 rounded">Rawat Jalan: {{ $summary['rawat_jalan'] }}</div>
        <div class="bg-yellow-100 p-4 rounded">Rawat Inap: {{ $summary['rawat_inap'] }}</div>
    </div>

    <table class="w-full border-collapse border border-gray-300">
        <thead class="bg-gray-200">
            <tr>
                <th>No RM</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Kelas</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claims as $claim)
                <tr>
                    <td>{{ $claim->no_rm }}</td>
                    <td>{{ $claim->patient_name }}</td>
                    <td>{{ $claim->jenis_rawatan }}</td>
                    <td>{{ $claim->kelas_rawatan }}</td>
                    <td>{{ $claim->tanggal_rawatan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>