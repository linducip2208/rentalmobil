@extends('portal.layout')
@section('content')
<div class="mb-8"><p class="font-mono text-xs uppercase tracking-[.2em] text-blue-600">Ajak teman, dapat poin</p><h1 class="mt-2 text-3xl font-black sm:text-4xl">Program Referral</h1><p class="mt-2 text-slate-500">Bagikan kode Anda — dapatkan {{ \App\Models\SystemSetting::get('referral_reward_points', 100) }} poin untuk setiap teman yang menyelesaikan sewa pertamanya.</p></div>

<div class="grid gap-4 sm:grid-cols-4">
  <div class="lift rounded-2xl border border-slate-200 bg-white p-6"><p class="text-sm text-slate-500">Kode referral</p><strong class="mt-2 block font-mono text-lg tracking-wider">{{ $stats['code'] }}</strong></div>
  <div class="lift rounded-2xl border border-slate-200 bg-white p-6"><p class="text-sm text-slate-500">Teman bergabung</p><strong class="mt-2 block text-3xl">{{ $stats['total_referred'] }}</strong></div>
  <div class="lift rounded-2xl border border-slate-200 bg-white p-6"><p class="text-sm text-slate-500">Reward diberikan</p><strong class="mt-2 block text-3xl">{{ $stats['rewarded'] }}</strong></div>
  <div class="lift rounded-2xl border border-slate-950 bg-white p-6 text-white" style="background:#0f172a"><p class="text-sm text-slate-400">Total poin referral</p><strong class="mt-2 block text-3xl">{{ number_format($stats['total_points_earned'],0,',','.') }}</strong></div>
</div>

<div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">
  <p class="text-sm font-semibold text-blue-900">Link undangan Anda</p>
  <div class="mt-2 flex flex-wrap items-center gap-3">
    <code class="rounded-lg bg-white px-3 py-2 font-mono text-xs sm:text-sm">{{ $bookingUrl }}</code>
    <button type="button" onclick="navigator.clipboard.writeText('{{ $bookingUrl }}');this.textContent='✓ Tersalin'" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-blue-700">Copy Link</button>
  </div>
</div>

<section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
  <div class="border-b p-5"><h2 class="font-bold">Riwayat referral</h2></div>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[560px] text-sm">
      <thead><tr class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Poin</th></tr></thead>
      <tbody class="divide-y">
        @forelse($referrals as $ref)
        <tr>
          <td class="px-5 py-3 font-mono text-xs">{{ $ref->code }}</td>
          <td class="px-5 py-3">{{ $ref->referred_name ?? '—' }}</td>
          <td class="px-5 py-3 text-slate-500">{{ $ref->referred_email ?? '—' }}</td>
          <td class="px-5 py-3"><span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold capitalize">{{ str_replace('_',' ',$ref->status) }}</span></td>
          <td class="px-5 py-3 font-bold">{{ $ref->status === 'rewarded' ? '+' . number_format($ref->reward_value,0,',','.') : '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada referral. Bagikan kode Anda sekarang!</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="border-t p-4">{{ $referrals->links() }}</div>
</section>
@endsection
