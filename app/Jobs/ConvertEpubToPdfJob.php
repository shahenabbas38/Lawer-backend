<?php

namespace App\Jobs;

use App\Models\Precedent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class ConvertEpubToPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Precedent $precedent
    ) {}

    public function handle(): void
    {
        if ($this->precedent->file_type !== 'epub') {
            return;
        }

        $disk = Storage::disk('public');
        $epubPath = $disk->path($this->precedent->file_path);

        if (!file_exists($epubPath)) {
            Log::warning("ConvertEpubToPdf: ملف غير موجود: {$epubPath}");
            return;
        }

        $dir = dirname($epubPath);
        $baseName = pathinfo($epubPath, PATHINFO_FILENAME);
        $pdfPath = $dir . DIRECTORY_SEPARATOR . $baseName . '.pdf';

        $command = config('calibre.ebook_convert_path', 'ebook-convert');
        $process = new Process([
            $command,
            $epubPath,
            $pdfPath,
        ]);
        $process->setTimeout(300); // 5 دقائق كحد أقصى لملف كبير
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('ConvertEpubToPdf فشل', [
                'precedent_id' => $this->precedent->id,
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput(),
            ]);
            return;
        }

        if (!file_exists($pdfPath)) {
            Log::error("ConvertEpubToPdf: لم يُنشأ ملف PDF: {$pdfPath}");
            return;
        }

        // المسار النسبي للـ PDF داخل storage/app/public
        $relativePdfPath = 'precedents/' . $baseName . '.pdf';

        // حذف ملف الـ EPUB الأصلي من التخزين
        $disk->delete($this->precedent->file_path);

        // حذف الـ EPUB من القرص (لأن الـ path قد يكون مختلفاً عن storage)
        if (file_exists($epubPath)) {
            @unlink($epubPath);
        }

        // تحديث سجل الاجتهاد ليشير إلى الـ PDF
        $this->precedent->update([
            'file_path' => $relativePdfPath,
            'file_type' => 'pdf',
        ]);

        Log::info('ConvertEpubToPdf نجح', ['precedent_id' => $this->precedent->id]);
    }
}
