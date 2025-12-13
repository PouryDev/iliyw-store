<x-layouts.app :title="'ثبت شد'">
    <x-ui.card class="p-6 text-center">
        <div class="text-4xl mb-2">🎉</div>
        <h1 class="text-xl font-bold mb-2">سفارشت ثبت شد!</h1>
        <p class="mb-2">کد فاکتور: <span class="font-mono">{{ $invoice->invoice_number }}</span></p>
        <p class="text-cherry-700">پس از بررسی فیش واریزی، سفارش تایید می‌شود.</p>
        <a href="/" class="inline-block mt-4 text-cherry-600 underline">بازگشت به فروشگاه</a>
    </x-ui.card>
</x-layouts.app>


