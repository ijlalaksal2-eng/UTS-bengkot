<x-layouts.app title="Dokter Dashboard">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase text-slate-500">Dokter</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Dashboard Dokter</h1>
        </div>

        <a href="{{ route('jadwal-periksa.index') }}"
            class="btn border-none bg-[#2d4499] px-5 text-white hover:bg-[#1e2d6b]">
            <i class="fa-regular fa-calendar-days"></i>
            Jadwal Periksa
        </a>
    </div>
</x-layouts.app>