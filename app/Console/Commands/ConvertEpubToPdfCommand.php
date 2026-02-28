<?php

namespace App\Console\Commands;

use App\Jobs\ConvertEpubToPdfJob;
use App\Models\Precedent;
use Illuminate\Console\Command;

class ConvertEpubToPdfCommand extends Command
{
    protected $signature = 'precedents:convert-epub-to-pdf
                            {--id= : تحويل اجتهاد واحد بالمعرّف}
                            {--sync : تشغيل التحويل فوراً بدل الطابور}';

    protected $description = 'تحويل ملفات الاجتهادات من EPUB إلى PDF (باستخدام Calibre)';

    public function handle(): int
    {
        $id = $this->option('id');
        $sync = $this->option('sync');

        if ($id) {
            $precedent = Precedent::find($id);
            if (!$precedent) {
                $this->error("اجتهاد غير موجود: {$id}");
                return self::FAILURE;
            }
            if ($precedent->file_type !== 'epub') {
                $this->warn("الاجتهاد {$id} ليس EPUB (نوعه: {$precedent->file_type}).");
                return self::SUCCESS;
            }
            $precedents = collect([$precedent]);
        } else {
            $precedents = Precedent::where('file_type', 'epub')->get();
            if ($precedents->isEmpty()) {
                $this->info('لا توجد اجتهادات من نوع EPUB لتحويلها.');
                return self::SUCCESS;
            }
        }

        $this->info('عدد الاجتهادات (EPUB) المحددة: ' . $precedents->count());

        foreach ($precedents as $precedent) {
            if ($sync) {
                (new ConvertEpubToPdfJob($precedent))->handle();
                $this->line("  تم تحويل الاجتهاد: {$precedent->id} - {$precedent->title}");
            } else {
                ConvertEpubToPdfJob::dispatch($precedent);
                $this->line("  أُضيف للطابور: {$precedent->id} - {$precedent->title}");
            }
        }

        if (!$sync && $precedents->count() > 0) {
            $this->newLine();
            $this->info('شغّل الطابور: php artisan queue:work');
        }

        return self::SUCCESS;
    }
}
