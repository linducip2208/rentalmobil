@extends('pseo._layout')

@section('content')
<section class="py-16 lg:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="font-bold text-4xl lg:text-5xl text-stone-900 mb-4">Kontak Kami</h1>
        <p class="text-lg text-stone-500 mb-12">Hubungi kami untuk pertanyaan, pemesanan, atau kerja sama</p>

        <div class="grid lg:grid-cols-2 gap-10">
            {{-- Form --}}
            <div>
                <form method="POST" action="/contact" class="bg-white border border-stone-200 rounded-2xl p-8">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Nama Lengkap</label>
                            <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none" placeholder="Masukkan nama Anda">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none" placeholder="email@contoh.com">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Telepon</label>
                            <input type="tel" name="phone" class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none" placeholder="+62 812-3456-7890">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Subjek</label>
                            <select name="subject" class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none">
                                <option value="">Pilih subjek</option>
                                <option value="booking">Pertanyaan Booking</option>
                                <option value="harga">Info Harga</option>
                                <option value="kerjasama">Kerja Sama</option>
                                <option value="whitabel">Whitelabel / Source Code</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-1.5">Pesan</label>
                            <textarea name="message" rows="5" required class="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm focus:ring-2 focus:ring-brand-100 focus:border-brand-500 outline-none resize-none" placeholder="Tulis pesan Anda di sini..."></textarea>
                        </div>
                        <button type="submit" class="w-full py-3 bg-gradient-to-r from-brand-600 to-brand-700 text-white font-bold text-sm rounded-xl hover:from-brand-700 hover:to-brand-800 transition-all shadow-lg shadow-brand-600/20">
                            Kirim Pesan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Info --}}
            <div class="space-y-6">
                <div class="bg-white border border-stone-200 rounded-2xl p-6">
                    <h3 class="font-bold text-stone-900 mb-4">Informasi Kontak</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">📞</span>
                            <div>
                                <div class="font-semibold text-stone-800 text-sm">Telepon / WhatsApp</div>
                                <div class="text-stone-600 text-sm">+62 812-3456-7890</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">✉️</span>
                            <div>
                                <div class="font-semibold text-stone-800 text-sm">Email</div>
                                <div class="text-stone-600 text-sm">hello@rentalmobil.id</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">📍</span>
                            <div>
                                <div class="font-semibold text-stone-800 text-sm">Alamat</div>
                                <div class="text-stone-600 text-sm">Jl. Sudirman No. 123<br>Jakarta Selatan, DKI Jakarta 12190</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-xl mt-0.5">🕐</span>
                            <div>
                                <div class="font-semibold text-stone-800 text-sm">Jam Operasional</div>
                                <div class="text-stone-600 text-sm">Senin - Sabtu: 08:00 - 21:00<br>Minggu: 09:00 - 18:00</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Map Placeholder --}}
                <div class="bg-stone-200 rounded-2xl h-64 flex items-center justify-center">
                    <div class="text-center text-stone-500">
                        <div class="text-3xl mb-2">🗺️</div>
                        <p class="text-sm font-medium">Peta Lokasi</p>
                        <p class="text-xs text-stone-400">Google Maps embed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
